<?php

/**
 * Facebook member class to wrap the member functionality of the Facebook
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
		$fields->makeFieldReadonly('FacebookUID');
		$fields->makeFieldReadonly('FacebookLink');
		$fields->makeFieldReadonly('FacebookTimezone');
	}
	
	/**
	 * After logging out on the security logout panel log out of Facebook
	 */
	function memberLoggedOut() {
		if(!Director::redirected_to()) {
			if(Controller::curr()->getCurrentFacebookMember()) {
				return Director::redirect(Controller::curr()->getFacebookLogoutLink());
			}
		}
	}
	
	/**
	 * Takes one of 'square' (50x50), 'small' (50xXX) or 'large' (200xXX)
	 *
	 * @return String
	 */
	function getAvatar($type = "square") {
		$controller = Controller::curr();
	
		if($controller && ($member = $controller->getCurrentFacebookMember())) {
			return "http://graph.facebook.com/" . $member->FacebookUID ."/picture?type=$type";
		}

		return false;
	}
	
	/**
	 * Sync the new data from a users Facebook profile to the member database.
	 *
	 * @param array
	 */
	function updateFacebookFields($result) {
		$this->owner->Email 	= (isset($result['email'])) ? $result['email'] : "";
		$this->owner->FirstName	= (isset($result['first_name'])) ? $result['first_name'] : "";
		$this->owner->Surname	= (isset($result['last_name'])) ? $result['last_name'] : "";
		$this->owner->Link		= (isset($result['link'])) ? $result['link'] : "";
		$this->owner->FacebookUID	= (isset($result['id'])) ? $result['id'] : "";
		$this->owner->FacebookTimezone = (isset($result['timezone'])) ? $result['timezone'] : "";
	}
}