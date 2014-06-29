<?php

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;
use Facebook\FacebookJavaScriptLoginHelper;

/**
 * Main controller class to handle Facebook Connect implementations. Extends the 
 * built in SilverStripe controller to add addition template functionality.
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
	 * @var ArrayData
	 */
	public $facebookMember = false;

	/**
	 * @var 
	 */
	private $session = null;

	/**
	 * 
	 */
	public function __construct() {
		$this->beginFacebookSession();
	}

	/**
	 * @todo Error handler
	 *
	 * @return FacebookSession
	 */
	public function getFacebookSession() {
		if($this->session) {
			return $this->session;
		}

		try {
			$helper = Injector::inst()->create("Facebook\FacebookRedirectLoginHelper", 
				$this->getCurrentPageUrl()
			);
		} catch(\Exception $ex) {
			SS_Log::log($ex, SS_Log::ERR);
		}

		$this->session = $helper->getSessionFromRedirect();

		return $this->session;
	}

	/**
	 * Extends the built in {@link Controller::init()} function to load the 
	 * required files for facebook connect.
	 */
	public function onBeforeInit() {
		$session = $this->getFacebookSession();

		if(!$session) {
			return;
		}

		$user = $this->getCurrentFacebookMember();

		if($user) {
			$this->facebookMember = $member;

			try {
				if(!$member = Member::currentUser()) {
					// member is not currently logged into SilverStripe. Look up 
					// for a member with the UID which matches first.
					$member = Member::get()->filter(array(
						"FacebookUID" => $user->getId()
					))->first();

					if(!$member) {
						// see if we have a match based on email. From a 
						// security point of view, users have to confirm their 
						// email address in facebook so doing a match up is fine
						$email = $user->getProperty('email');

						if($email) {
							$member = Member::get()->filter(array(
								'Email' => $email
							))->first();
						}
					}

					if(!$member) {
						// fallback, if still 
						$member = Injector::create('Member');
					}
				}
				

				$this->updateMemberFromFacebook($member, $user);		
				$member->logIn();

				Session::set('logged-in-member-via-faceboook', true);
			} catch (Exception $e) { 
				SS_Log::log($e, SS_Log::ERR);
			}
		} else if($logged = Session::get('logged-in-member-via-faceboook')) {
			$this->logUserOut();
		}
	}
	
	/**
	 * @param Member
	 *
	 * @return Member
	 */
	protected function updateMemberFromFacebook($member, $info) {
		$sync = Config::inst()->get('FacebookControllerExtension', 'sync_member_details');
		$create = Config::inst()->get('FacebookControllerExtension', 'create_member');

		$member->updateFacebookFields($info, $sync);

		// sync details	to the database
		if(($member->ID && $sync) || $create) {
			if($member->isChanged()) {
				$member->write();
			}
		}

		// ensure members are in the correct groups
		if($groups = Config::inst()->get('FacebookControllerExtension', 'member_groups')) {
			foreach($groups as $group) {
				$member->addToGroupByCode($group);
			}
		}

		return $member;
	}

	/**
	 * @return FacebookSession|null
	 */
	protected function beginFacebookSession() {
		$appId = Config::inst()->get('FacebookControllerExtension', 'app_id');
		$secret = Config::inst()->get('FacebookControllerExtension', 'api_secret');

		if(!$appId || !$secret) {
			return null;
		}

		FacebookSession::setDefaultApplication($appId, $secret);

		if(session_status() !== PHP_SESSION_ACTIVE) {
			Session::start();
		}
	}

	/**
	 * @return GraphUser|null
	 */
	public function getFacebookUser() {
		try {
			$user = (new FacebookRequest(
				$session, 'GET', '/me'
			))->execute()->getGraphObject(GraphUser::className());

			return $user;
		} catch(FacebookRequestException $e) {
			SS_Log::log($e, SS_Log::ERR);
		}
	}

	/**
	 * @return void
	 */
	protected function logFacebookUserOut() {
		Session::clear('logged-in-member-via-faceboook');
			
		$member = Member::currentUser();

		if($member) {
			$member->logOut();
		}
	}

	/**
	 * @return ArrayData
	 */
	public function getCurrentFacebookMember() {
		if(isset($this->facebookMember) && $this->facebookMember) {
			return $this->facebookMember;
		}
	}
	
	
	/**
	 * @return string
	 */
	public function getFacebookLogoutLink() {
		$helper = new Facebook\FacebookRedirectLoginHelper($this->getCurrentPageUrl());

		return $helper->getLogoutUrl();
	}

	/**
	 * @return string
	 */
	public function getFacebookLoginLink() {
		$helper = new Facebook\FacebookRedirectLoginHelper($this->getCurrentPageUrl());
		$scope = Config::inst()->get('FacebookControllerExtension', 'permissions');
		if(!$scope) $scope = array();

		return $helper->getLoginUrl($scope);
	}

	/**
	 * @return string
	 */
	public function getFacebookAppId() {
		return Config::inst()->get('FacebookControllerExtension', 'app_id');
	}

	/**
	 * @return string
	 */
	public function getCurrentPageUrl() {
		return Director::protocol() . "//$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	}
}