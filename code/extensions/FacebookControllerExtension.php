<?php

require_once(BASE_PATH .'/vendor/facebook/php-sdk/src/facebook.php');

/**
 * Main controller class to handle Facebook Connect implementations. Extends the built in
 * SilverStripe controller to add addition template functionality.
 *
 * @package facebookconnect
 */

class FacebookControllerExtension extends Extension {
	
	/**
	 * @config
	 * @var bool $create_member
	 */
	private static $create_member = true;
	
	/**
	 * @config
	 * @var array $member_groups
	 */
	private static $member_groups = array();
	
	/**
	 * @see http://developers.facebook.com/docs/authentication/permissions
	 *
	 * @config
	 * @var array $permissions
	 */
	private static $permissions = array(
		'email'
	);
	
	/**
	 * @config
	 * @var bool $sync_member_details
	 */
	private static $sync_member_details = true;

	/**
	 * @config
	 * @var string $api_secret
	 */	
	private static $api_secret = "";

	/**
	 * @config
	 * @var string $app_id
	 */
	private static $app_id = "";

	/**
	 * @config
	 * @var string $lang
	 */
	private static $lang = "en_US";
	
	/**
	 * @var ArrayData
	 */
	public $facebookMember = false;
	

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
				'appId'  => Config::inst()->get('FacebookControllerExtension', 'app_id'),
				'secret' => Config::inst()->get('FacebookControllerExtension', 'api_secret'),
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
	 * @param string $name
	 * @param string $params
	 *
	 * @return array
	 */
	public function callCached($name, $params) {
		$cache = SS_Cache::factory(get_class($this) . session_id());
		
		if(!($result = unserialize($cache->load(get_class($this) . $name)))) {
			$result = $this->getFacebook()->api($params);
			
			$cache->save(serialize($result), get_class($this) . $name);
		}
		
		return $result;
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
					
						if(Config::inst()->get('FacebookControllerExtension', 'sync_member_details')) {
							$member->write();
						}
					}
				} else {
					// member is not currently logged into SilverStripe. Look up for
					// a member with the UID which matches and log them in or
					// create a new member
					$SQL_uid = Convert::raw2sql($result['id']);
					$member = Member::get()->filter("FacebookUID", $SQL_uid)->first();

					if($member) {
						$member->updateFacebookFields($result);
						
						if(Config::inst()->get('FacebookControllerExtension', 'sync_member_details')) {
							$member->write();
						}
						
						$member->logIn();
					} else if(isset($result['email']) && ($member = Member::get()->filter('Email', $result['email'])->first())) {
						$member->updateFacebookFields($result);

						if(Config::inst()->get('FacebookControllerExtension', 'create_member')) {
							$member->write();
							$member->logIn();
						}
					} else {
						// create a new member
						$member = singleton('Member')->addFacebookMember(
							$result, 
							Config::inst()->get('FacebookControllerExtension', 'create_member')
						);
					}
				}
				
				Session::set('logged-in-member-via-faceboook', true);
				
				if($groups = Config::inst()->get('FacebookControllerExtension', 'member_groups')) {
					foreach($groups as $group) {
						$member->addToGroupByCode($group);
					}
				}
				
				$this->facebookMember = $member;
			} catch (FacebookApiException $e) { 

			}
		} else if($logged = Session::get('logged-in-member-via-faceboook')) {
			Session::clear('logged-in-member-via-faceboook');
			
			$member = Member::currentUser();

			if($member) {
				$member->logOut();
			}
		}
	}
	
	/**
	 * @return ArrayData
	 */
	public function getCurrentfacebookMember() {
		if(isset($this->facebookMember) && $this->facebookMember) {
			return $this->facebookMember;
		}
	}
	
	
	/**
	 * Logout link
	 * 
	 * @return String
	 */
	public function getFacebookLogoutLink() {
		$link = $this->getLink();
		
		return $this->getFacebook()->getLogoutUrl(array(
			'next' => Controller::join_links($link, '?updatecache=1')
		));
	}

	/**
	 * @return string
	 */
	public function getFacebookLoginLink() {
		$link = $this->getLink();
		
		return $this->getFacebook()->getLoginUrl(array(
			'next' => Controller::join_links($link, '?updatecache=1')
		));
	}
	
	/**
	 * @returnstring
	 */
	public function getLink() {
		$controller = Controller::curr();
		$link = Director::absoluteBaseURL();
		
		if($controller->hasMethod('AbsoluteLink')) {
			$link = $controller->AbsoluteLink();
		} else if($controller->hasMethod('Link')) {
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
		return implode(',', Config::inst()->get('FacebookControllerExtension', 'permissions'));
	}

	/**
	 * App Id
	 *
	 * @return String
	 */
	public function getFacebookAppId() {
		return Config::inst()->get('FacebookControllerExtension', 'app_id');
	}

	/**
	 * Language
	 *
	 * @return String
	 */
	public function getFacebookLanguage() {
		return Config::inst()->get('FacebookControllerExtension', 'lang');
	}
}
