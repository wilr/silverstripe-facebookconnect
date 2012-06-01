<?php

/**
 * Main controller class to handle Facebook Connect implementations. Extends the built in
 * SilverStripe controller to add addition template functionality.
 *
 * @package facebookconnect
 */

class FacebookConnect extends Extension {
	
	/**
	 * When a user interacts with the website should we create a
	 * {@link Member} on the site with their details and login that {@link Member}
	 * object via {@link Member->login()} 
	 *
	 * This is enabled by default since most apps (like forum) need valid
	 * {@link Member} objects. To disable this set {@link FacebookConnect::set_create_member(false)}
	 *
	 * By setting this to true we also require the 'email' permission from the
	 * open graph as members still need to be tided to emails as the identifier. 
	 */
	private static $create_member = true;
	
	/**
	 * If creating members is enabled then its a smart idea to set a group
	 * to save all the members too. For instance you might want to save all Facebook Connect
	 * members automatically to your mailing list once they approve the app.
	 *
	 * You must have {@link FacebookConnect::$create_member} set to true (as it is by default)
	 * for this to make any effect.
	 *
	 * @var Array array('groupcode', 'groupcode1')
	 */
	private static $member_groups = array();
	
	/**
	 * The permissions which you require for your application. The Facebook Connect API has a
	 * list of all the permissions. If you leave the $create_member option to true
	 * by default it adds the email permission no matter what you set here
	 *
	 * @see http://developers.facebook.com/docs/authentication/permissions
	 * @var Array
	 */
	private static $permissions = array();
	
	/**
	 * Syncs the users details between Facebook and the member database for existing
	 * members. If you wish for data to be persistent then it is recommended to sync
	 * data (email, names)
	 *
	 * @var bool
	 */
	private static $sync_member_details = true;

	/**
	 * @see FacebookConnect::set_api_secret($key);
	 *
	 * @var String API Secret for your Facebook App
	 */	
	private static $api_secret = "";

	/**
	 * @see FacebookConnect::set_app_id($key);
	 *
	 * @var String ID for your App
	 */
	private static $app_id = "";

	/**
	 * @see FacebookConnect::set_lang($lang);
	 *
	 * @var Locale for your App
	 */
	private static $lang = "en_US";
	
	public $facebookmember = false;
	
	/**
	 * Sets whether a {@link Member} object should be created when a facebook member on the
	 * site authenicates. If this is set to false to access the member and its data you will
	 * have to use <% control CurrentFacebookMember %> rather than <% control CurrentMember %>
	 *
	 * @param bool
	 */
	public static function set_create_member($bool) {
		self::$create_member = $bool;
	}
	
	public static function create_member() {
		return self::$create_member;
	}
	
	public static function set_member_groups($group) {
		if(is_array($group)) {
			self::$member_groups = $group;
		}
		else {
			self::$member_groups[] = $group;
		}
	}
	
	public static function get_member_groups() {
		return (count(self::$member_groups) > 0) ? self::$member_groups : false;
	}
	
	public static function set_permissions($permissions = array()) {
		self::$permissions = $permissions;
	}
	
	public static function get_permissions() {
		return self::$permissions;
	}
	
	public static function set_app_id($id) {
		self::$app_id = $id;
	}
	
	public static function get_app_id() {
		return self::$app_id;
	}
	
	public static function set_api_secret($secret) {
		self::$api_secret = $secret;
	}
	
	public static function get_api_secret() {
		return self::$api_secret;
	}
	
	public static function set_lang($lang) {
		self::$lang = $lang;
	}

	public static function get_lang() {
		return self::$lang;
	}

	/**
	 * Return the Facebook API class wrapped in a {@link SS_Cache} for
	 * performance. Creates a new object if no connection has been implemented
	 *
	 * @return Facebook
	 */
	public function getFacebook() {
		$cache = SS_Cache::factory(get_class($this));
		
		if(!($result = unserialize($cache->load(get_class($this))))) {
			$result = new Facebook(array(
				'appId'  => self::get_app_id(),
				'secret' => self::get_api_secret(),
				'cookie' => true,
			));
			
			$cache->save(serialize($result), get_class($this));
		}

		return $result;
	}
	
	/**
	 * Call an API function but cache the result. 
	 *
	 * Passes the call through to {@link Facebook::api()} but looks up the
	 * value in the {@link SS_Cache} first.
	 * 
	 * Bases the cache on the users session id so then we don't get incorrect information
	 * for when users are viewing the cache of different users.
	 *
	 * @param String name of value to cache as
	 * @param String params to pass through to the root api call
	 *
	 * @return the decoded response from the api
	 */
	public function callCached($name, $params) {
		$cache = SS_Cache::factory(get_class($this) . session_id());
		
		if(!($result = unserialize($cache->load(get_class($this) . $name)))) {
			$result = $this->getFacebook()->api($params);
			
			$cache->save(serialize($result), get_class($this) . $name);
		}
		
		return $result;
	}
	
	public function set_sync_member_details($bool) {
		self::$sync_member_details = $bool;
	}
	
	public function get_sync_member_details() {
		return self::$sync_member_details;
	}
	
	/**
	 * Extends the built in {@link Controller::init()} function to load the 
	 * required files for facebook connect.
	 */
	public function onBeforeInit() {
		$user = $this->getFacebook()->getUser();

		if(!isset($_GET['updatecache']) && $user) {
			try {
				$result = $this->callCached('me', '/me');

				// if email is empty and proxied_email is set instead
				// write down proxied_email to email
				if(!stristr($result['email'], '@')){
					$result['email'] = $result['proxied_email'];
				}
				
				// if logged in and authorized to fb sync details
				if($member = Member::currentUser()) {
					if(isset($result['email']) && ($result['email'] != $member->Email)) {
						// member email has changed. Require new login
						$member->logOut();
					} else {
						$member->updateFacebookFields($result);
					
						if(self::get_sync_member_details()) {
							$member->write();
						}
					}
				} else {
					// member is not currently logged into SilverStripe. Look up for
					// a member with the UID which matches and log them in or
					// create a new member
					$SQL_uid = Convert::raw2sql($result['id']);

					if($member = DataObject::get_one('Member', "\"FacebookUID\" = '$SQL_uid'")) {
						$member->updateFacebookFields($result);
						
						if(self::get_sync_member_details()) {
							// @todo will need to check the email has not changed as well
							$member->write();
						}
						
						$member->logIn();
					} else if(isset($result['email']) && ($member = DataObject::get_one(
							'Member', "\"Email\" = '". Convert::raw2sql($result['email']) ."'"))) {
						
						$member->updateFacebookFields($result);

						if(self::create_member()) {
							$member->write();
							$member->logIn();
						}
					} else {
						// create a new member
						$member = singleton('Member')->addFacebookMember($result, self::create_member());
					}
				}
				
				Session::set('logged-in-member-via-faceboook', true);
				
				if($groups = self::get_member_groups()) {
					foreach($groups as $group) {
						$member->addToGroupByCode($group);
					}
				}
				
				
				$this->facebookmember = $member;
			} catch (FacebookApiException $e) { }
		} 
		else {
			// no session or cookie so they have logged out of facebook
			if($logged = Session::get('logged-in-member-via-faceboook')) {

				Session::clear('logged-in-member-via-faceboook');
				
				$member = Member::currentUser();
				if($member) $member->logOut();
			}
		}
	}
	
	/**
	 * If {@link FacebookConnect::set_create_member()} is set to false then the build in
	 * CurrentMember functionality will not be functional. If the user has specifically 
	 * overridden the create member then they can update to use this function.
	 *
	 * It wraps the raw data fields from facebook.
	 *
	 * @return ArrayData
	 */
	public function getCurrentFacebookMember() {
		return (isset($this->facebookmember) && $this->facebookmember) ? $this->facebookmember : false;
	}
	
	
	/**
	 * Logout link
	 * 
	 * @return String
	 */
	public function getFacebookLogoutLink() {
		$link = $this->getLink();
		
		return $this->getFacebook()->getLogoutUrl(array('next' => Controller::join_links($link, '?updatecache=1&flush=1')));
	}

	/**
	 * Get the login link
	 *
	 * @return String
	 */
	public function getFacebookLoginLink() {
		$link = $this->getLink();
		
		return $this->getFacebook()->getLoginUrl(array('next' => Controller::join_links($link, '?updatecache=1&flush=1')));
	}
	
	/**
	 * Generate the link to the public accessible page this
	 * is being applied too. All controllers in SS do something completely
 	 * different so can't rely on link functions always being present
	 *
	 * @return String
	 */
	public function getLink() {
		$controller = Controller::curr();
		$link = Director::absoluteBaseURL();
		
		if($controller->hasMethod('AbsoluteLink')) {
			$link = $controller->AbsoluteLink();
		}
		else if($controller->hasMethod('Link')) {
			$link .= $controller->Link();
		}
		
		return $link;
	}
	
	/**
	 * Permissions to require on the login button
	 *
	 * @return String
	 */
	public function getFacebookPermissions() {
		
		$permissions = self::get_permissions();
		
		if(self::create_member()) {
			$permissions = $permissions + array('email');
		}	
			
		return implode(',', $permissions);
 	}
	
	/**
	 * App Id
	 *
	 * @return String
	 */
	public function getFacebookAppId() {
		
		return self::get_app_id();
 	}
	
	/**
	 * Language
	 *
	 * @return String
	 */
	public function getFacebookLanguage() {
		
		return self::get_lang();
 	}

}