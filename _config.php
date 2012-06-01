<?php

// configuration for facebook connect. You should not edit any of these
// values and instead override from the mysite/_config file

require_once(dirname(__FILE__) . "/thirdparty/facebook-php-sdk/src/facebook.php");

// adds an extension hook to member
DataObject::add_extension('Member', 'FacebookMember');

// adds the authenticator to the built in login form
Authenticator::register('FacebookAuthenticator');

// add the facebook controller
DataObject::add_extension('Controller', 'FacebookConnect');

// don't forget to add Facebook App Keys to mysite/_config.php
