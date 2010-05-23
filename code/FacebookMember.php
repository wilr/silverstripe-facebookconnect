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
	
	/**
	 * Return the users image from the facebook connect.
	 *
	 * Takes one of 'square' (50x50), 'small' (50xXX) or 'large' (200xXX)
	 *
	 * @return String
	 */
	public function Picture($type = "square") {
		$controller = Controller::curr();
	
		if($controller && ($member = $controller->CurrentFacebookMember())) {
			return "http://graph.facebook.com/" . $member->FacebookUID ."/picture?type=$type";
		}

		return false;
	}
}