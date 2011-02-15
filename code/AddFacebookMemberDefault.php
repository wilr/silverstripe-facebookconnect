<?php

/**
 * Add Facebook member class to wrap the add method of the FacebookMember.
 *
 * An extension to the built in {@link Member} class this adds the fields which
 * may be required as part of the member
 *
 * Write your own Extension with addFacebookMember for a custom add method (maybe if you have a more complex memberclass inherit from Member)
 *
 * @package facebookconnect
 */

class UpdateFacebookMemberDefault extends Extension {
	
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
}