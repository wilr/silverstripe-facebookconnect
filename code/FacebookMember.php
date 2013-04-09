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

class FacebookMember extends DataExtension {
	
	private static $db = array(
		'Email'				=> 'Varchar(255)',	// alter Email to be able to save strings up to 255 chars,
		'FacebookUID' 		=> 'Varchar(200)',	// user ID on facebook
		'FacebookLink'		=> 'Varchar(200)',	// link to their facebook page
		'FacebookTimezone'	=> 'Varchar(200)',	// which timezone they're in
	);
	
	public function updateCMSFields(FieldList $fields) {
		$fields->makeFieldReadonly('Email');
		$fields->makeFieldReadonly('FacebookUID');
		$fields->makeFieldReadonly('FacebookLink');
		$fields->makeFieldReadonly('FacebookTimezone');
	}
	
	/**
	 * After logging out on the security logout panel log out of Facebook
	 */
	public function memberLoggedOut() {
		$controller = Controller::curr();
		
		if(!$controller->redirectedTo()) {
			if($controller->getCurrentFacebookMember()) {
				$token = $controller->getFacebook()->getAccessToken();

				// https://github.com/facebook/php-sdk/issues/507
				header("Location: ".$controller->getFacebook()->getLogoutUrl(array(
					'next' => Director::absoluteBaseUrl()."?updatecache=1&flush=1"
				)));
				
				die();
			}
		}
	}
	
	/**
	 * Takes one of 'square' (50x50), 'small' (50xXX) or 'large' (200xXX)
	 *
	 * @return string
	 */
	public function getAvatar($type = "square") {
		$controller = Controller::curr();

		if($controller && ($member = $controller->getCurrentFacebookMember())) {
			return sprintf(
				"http://graph.facebook.com/%s/picture?type=%s",
				 $member->FacebookUID, $type
			);
		}
	}
        
    /**
	 * Create a new User based on the Facebook Member.
	 *
	 * @param array
	 * @param bool
	 *
	 * @return DataObject
	 */
	public function addFacebookMember($result, $create_member) {
		$member = new Member();
		$member->updateFacebookFields($result);

		if($create_member) {
			$member->write();
			$member->logIn();
		}

		// the return value must be an instance of Member.
		// whether it's an inherited instance doesn't matter.
		$this->owner->extend('onAddFacebookMember', $result);

		return $member;
	}

	/**
	 * Sync the new data from a users Facebook profile to the member database.
	 *
	 * @param array
	 */
	public function updateFacebookFields($result) {
		// only Update Email if ist already set to a correct Email,
		// while $result['email'] is still a proxied_email
		if(!Email::validEmailAddress($this->owner->Email) || (!stristr($result['email'], '@facebook.com') && !DataObject::get_one('Member', "\"Email\" = '". Convert::raw2sql($result['email']) ."'"))){
			$this->owner->Email 	= (isset($result['email'])) ? $result['email'] : "";
		}

		$this->owner->FirstName	= (isset($result['first_name'])) ? $result['first_name'] : "";
		$this->owner->Surname	= (isset($result['last_name'])) ? $result['last_name'] : "";
		$this->owner->FacebookLink	= (isset($result['link'])) ? $result['link'] : "";
		$this->owner->FacebookUID	= (isset($result['id'])) ? $result['id'] : "";
		$this->owner->FacebookTimezone = (isset($result['timezone'])) ? $result['timezone'] : "";

		$this->owner->extend('onUpdateFacebookFields', $result);
	}
}
