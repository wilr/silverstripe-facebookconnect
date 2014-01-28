<?php

/**
 * Return a Facebook Login Form for the website.
 *
 * @package facebookconnect
 */
class FacebookLoginForm extends MemberLoginForm {
	
	protected $authenticator_class = 'FacebookAuthenticator';
	
	public function __construct($controller, $name, $fields = null, $actions = null, $checkCurrentUser = true) {
		if($checkCurrentUser && Member::currentUser() && Member::logged_in_session_exists()) {
			$fields = new FieldList(
				new HiddenField("AuthenticationMethod", null, $this->authenticator_class, $this)
			);
			
			$actions = new FieldList(
				new FormAction("logout", _t('Member.BUTTONLOGINOTHER', "Log in as someone else"))
			);
		}
		else {
			$fields = new FieldList(
				new LiteralField('FacebookLoginIn', "<fb:login-button scope='". $controller->getFacebookPermissions() ."'></fb:login-button>")
			);
			
			$actions = new FieldList(
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
