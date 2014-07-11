<?php

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;

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
	public $facebookMember;

	/**
	 * @var 
	 */
	private $session;

	/**
	 * @var Facebook\FacebookRedirectLoginHelper
	 */
	private $helper;

	/**
	 * @var string
	 */
	const SESSION_REDIRECT_URL_FLAG = 'redirectfacebookuser';

	/**
	 * @var string
	 */
	const FACEBOOK_ACCESS_TOKEN = 'facebookaccesstoken';

	/**
	 *
	 */
	public function __construct() {
		parent::__construct();
	
		$appId = Config::inst()->get(
			'FacebookControllerExtension', 'app_id'
		);

		$secret = Config::inst()->get(
			'FacebookControllerExtension', 'api_secret'
		);

		if(!$appId || !$secret) {
			return null;
		}

		FacebookSession::setDefaultApplication($appId, $secret);

		if(session_status() !== PHP_SESSION_ACTIVE) {
			Session::start();
		}
	}

	/**
	 * @return FacebookSession
	 */
	public function getFacebookSession() {
		if(!$this->session) {
			$accessToken = Session::get(
				FacebookControllerExtension::FACEBOOK_ACCESS_TOKEN
			);

			if($accessToken) {
				$this->session = new FacebookSession($accessToken);
			}
		}

		return $this->session;
	}

	/**
	 * @return FacebookRedirectLoginHelper
	 */
	public function getFacebookHelper() {
		if($this->helper) {
			return $this->helper;
		}

		try {
			$this->helper = Injector::inst()->create(
				"Facebook\FacebookRedirectLoginHelper", 
				$this->getFacebookCallbackLink()
			);
		} catch (Exception $e) {
			SS_Log::log($e, SS_Log::ERROR);
		}

		return $this->helper;
	}

	/**
	 * @return string
	 */
	public function getFacebookLoginLink() {
		// save the url that this page is on to session. The user will be 
		// redirected back here.
		Session::set(
			self::SESSION_REDIRECT_URL_FLAG, $this->getCurrentPageUrl()
		);

		$scope = Config::inst()->get(
			'FacebookControllerExtension', 'permissions'
		);

		if(!$scope) {
			$scope = array();
		}

		if($helper = $this->getFacebookHelper()) {
			return $helper->getLoginUrl($scope);
		}

		return null;
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
		$url = Director::protocol() . "$_SERVER[HTTP_HOST]";
		$pos = strpos($_SERVER['REQUEST_URI'], '?');

		$get = $_GET;

		// tidy up get.
		unset($get['code']); 
		unset($get['state']);
		unset($get['url']);

		// if the current page is the login page and the page contains a back
		// URL then we want to redirect the user to that instead.
		if(isset($get['BackURL'])) {
			$last = strlen($get['BackURL']);
			$end = ($pos = strpos($get['BackURL'], '?')) ? $pos : $last;
			$url .= substr($get['BackURL'], 0, $end);

			unset($get['BackURL']);
		} else if($pos !== false) {
			$url .= substr($_SERVER['REQUEST_URI'], 0, $pos);
		} else {
			$url .= $_SERVER['REQUEST_URI'];
		}

		$qs = http_build_query($get);
		$url .= ($qs) ? "?$qs" : '';

		return $url;
	}

	/**
	 * @return string
	 */
	public function getFacebookCallbackLink() {
		return Controller::join_links(
			Director::absoluteBaseUrl(), 
			'FacebookConnectAuthCallback/connect'
		);
	}
}