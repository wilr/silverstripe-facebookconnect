<?php

/**
 * Temp facebook member class to wrap the member functionality of the facebook
 * members into the member object.
 *
 * An extension to the built in {@link Member} class this adds the fields which
 * may be required as part of the member
 *
 * @package facebookconnect
 */

class FacebookMember extends DataObjectDecorator {
	
	public function extraStatics() {
		return array(
			'db' => array(
				'FacebookUID' 		=> 'Varchar(200)', // user ID on facebook
				'FacebookLink'		=> 'Varchar(200)', // link to their facebook page
				'FacebookTimezone'	=> 'Varchar(200)', // which timezone they're in
			)
		);
	}
	
	function updateCMSFields(&$fields) {
		$fields->makeFieldReadonly('Email');
	}
	
	/**
	 * After logging out on the security logout panel log out of facebook
	 *
	 */
	function memberLoggedOut() {
		if(!Director::redirected_to()) {
			return Director::redirect(Controller::curr()->FacebookLogoutLink());
		}
	}
	/**
	 * Takes one of 'square' (50x50), 'small' (50xXX) or 'large' (200xXX)
	 *
	 * @return String
	 */
	function Picture($type = "square") {
		$controller = Controller::curr();
	
		if($controller && ($member = $controller->CurrentFacebookMember())) {
			return "http://graph.facebook.com/" . $member->FacebookUID ."/picture?type=$type";
		}

		return false;
	}
	
	function updateFacebookFields($result) {
		$this->owner->Email 	= (isset($result['email'])) ? $result['email'] : "";
		$this->owner->FirstName	= (isset($result['first_name'])) ? $result['first_name'] : "";
		$this->owner->Surname	= (isset($result['last_name'])) ? $result['last_name'] : "";
		$this->owner->Link		= (isset($result['link'])) ? $result['link'] : "";
		$this->owner->FacebookUID	= (isset($result['id'])) ? $result['id'] : "";
		$this->owner->FacebookTimezone = (isset($result['timezone'])) ? $result['timezone'] : "";
	}
}