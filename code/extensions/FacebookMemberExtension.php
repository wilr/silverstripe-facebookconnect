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

class FacebookMemberExtension extends DataExtension {
	
	private static $db = array(
		'Email'				=> 'Varchar(255)',	// alter Email to be able to save strings up to 255 chars,
		'FacebookUID' 		=> 'Varchar(200)',	// user ID on facebook
		'FacebookLink'		=> 'Varchar(200)',	// link to their facebook page
		'FacebookTimezone'	=> 'Varchar(200)',	// which timezone they're in
	);
	
	public function updateCMSFields(FieldList $fields) {
		$fields->makeFieldReadonly('FacebookUID');
		$fields->makeFieldReadonly('FacebookLink');
		$fields->makeFieldReadonly('FacebookTimezone');
	}
	
	/**
	 * Takes one of 'square' (50x50), 'small' (50xXX) or 'large' (200xXX)
	 *
	 * @return string $type
	 */
	public function getAvatar($type = "square") {
		$controller = Controller::curr();

		if($controller && ($session = $controller->getFacebookSession())) {
			try {
				$request = (new FacebookRequest($session, 'GET', "me/picture?type=$type&redirect=false"))->execute();
				$picture = $request->getGraphObject();

				return $picture->getProperty('url');

			} catch(FacebookRequestException $e) {
				SS_Log::log($e, SS_Log::ERR);
			}
		}
	}

	/**
	 * Sync the new data from a users Facebook profile to the member database.
	 *
	 * @param GraphUser $result
	 * @param bool $sync Flag to whether we override fields like first name
	 */
	public function updateFacebookFields(GraphUser $result, $override = true) {
		$this->owner->FacebookLink	= $result->getProperty('link');
		$this->owner->FacebookUID	= $result->getProperty('id');
		$this->owner->FacebookTimezone = $result->getProperty('timezone');

		if($override) {
			$email = $result->getProperty('email');

			if($email && !$this->owner->Email || !Email::validEmailAddress($this->owner->Email)) {
				$this->owner->Email = $email;
			}

			$this->owner->FirstName	= $result->getProperty('first_name');
			$this->owner->Surname	= $result->getProperty('last_name');
		}

		$this->owner->extend('onUpdateFacebookFields', $result);
	}
}