<?php

// configuration for facebook connect. You should not edit any of these
// values and instead override from the mysite/_config file

// adds an extension hook to member
DataObject::add_extension('Member', 'FacebookMember');

// adds the code needed to check facebook
DataObject::add_extension('Controller', 'FacebookConnect');

// adds the authenticator to the built in login form
Authenticator::register('FacebookAuthenticator');
