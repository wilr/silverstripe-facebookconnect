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
				'Email'				=> 'Varchar(255)',	// alter Email to be able to save strings up to 255 chars,
														// according to facbooks proxied email addresses this is mandatory
				'FacebookUID' 		=> 'Varchar(200)',	// user ID on facebook
				'FacebookLink'		=> 'Varchar(200)',	// link to their facebook page
				'FacebookTimezone'	=> 'Varchar(200)',	// which timezone they're in
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
		$controller = Controller::curr();
		
		if(!Director::redirected_to()) {
			if($controller->getCurrentFacebookMember()) {
				$session = $controller->getFacebook()->getSession();

				// have to bruteforce this as Security/logout does its own redirection which
				// we have no control over
				header('Location: https://www.facebook.com/logout.php?access_token='.$session['access_token'] . '&next='. Director::absoluteBaseURL() .'?updatecache=1');
				die();
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
	 * create a new User based on the Facebook Member.
	 *
	 * @param array
	 * @param bool
	 * @return DataObject
	 */
	function addFacebookMember($result, $create_member){

		$member = new Member();
		$member->updateFacebookFields($result);

		if($create_member) {
			$member->write();
			$member->logIn();
		}

		// the returnvalue must be an instance of Member.
		// whether it's an inherited instance doesn't matter.
		return $member;
	}

	/**
	 * Sync the new data from a users Facebook profile to the member database.
	 *
	 * @param array
	 */
	function updateFacebookFields($result) {
		// only Update Email if ist already set to a correct Email,
		// while $result['email'] is still a proxied_email
		if(!Email::validEmailAddress($this->owner->Email) || (!stristr($result['email'], '@facebook.com') && !DataObject::get_one('Member', "\"Email\" = '". Convert::raw2sql($result['email']) ."'"))){
			$this->owner->Email 	= (isset($result['email'])) ? $result['email'] : "";
		}
		$this->owner->FirstName	= (isset($result['first_name'])) ? $result['first_name'] : "";
		$this->owner->Surname	= (isset($result['last_name'])) ? $result['last_name'] : "";
		$this->owner->Link		= (isset($result['link'])) ? $result['link'] : "";
		$this->owner->FacebookUID	= (isset($result['id'])) ? $result['id'] : "";
		$this->owner->FacebookTimezone = (isset($result['timezone'])) ? $result['timezone'] : "";
	}
}