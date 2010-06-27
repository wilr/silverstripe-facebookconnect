<?php

/**
 * Return a Facebook Login Form for the website.
 *
 * @todo If Javascript is not enabled then it should default back to the standard link
 *
 * @package facebookconnect
 */
class FacebookLoginForm extends MemberLoginForm {
	
	protected $authenticator_class = 'FacebookAuthenticator';
	
	function __construct($controller, $name, $fields = null, $actions = null, $checkCurrentUser = true) {

		if($checkCurrentUser && Member::currentUser() && Member::logged_in_session_exists()) {
			$fields = new FieldSet(
				new HiddenField("AuthenticationMethod", null, $this->authenticator_class, $this)
			);
			
			$actions = new FieldSet(
				new FormAction("logout", _t('Member.BUTTONLOGINOTHER', "Log in as someone else"))
			);
		}
		else {
			$fields = new FieldSet(
				new LiteralField('FacebookLoginIn', "<fb:login-button perms='". $controller->getFacebookPermissions() ."'></fb:login-button>")
			);
			
			$actions = new FieldSet(
				new LiteralField('FacebookLoginLink', "<!-- <a href='".$controller->getFacebookLoginLink() ."'>". _t('FacebookLoginForm.LOGIN', 'Login') ."</a> -->")
			);
		}
		
		$backURL = (isset($_REQUEST['BackURL'])) ? $_REQUEST['BackURL'] : Session::get('BackURL');
		
		if(isset($backURL)) {
			$fields->push(new HiddenField('BackURL', 'BackURL', $backURL));
		}

		return parent::__construct($controller, $name, $fields, $actions);
	}
}