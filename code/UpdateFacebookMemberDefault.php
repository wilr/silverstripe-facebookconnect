<?php

/**
 * Update Facebook member class to wrap the update method of the FacebookFields.
 *
 * An extension to the built in {@link Member} class this adds the fields which
 * may be required as part of the member
 *
 * Write your own Decorator with updateFacebookFields for a custom update method (maybe to save the avatar file)
 *
 * @package facebookconnect
 */

class UpdateFacebookMemberDefault extends DataObjectDecorator {
	
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