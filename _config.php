<?php

// configuration for facebook connect. You should not edit any of these
// values and instead override from the mysite/_config file

// adds an extension hook to member
DataObject::add_extension('Member', 'FacebookMember');
// adds the default updateFacebookFields method to member, alter your own decorator here for special update behaviour
DataObject::add_extension('Member', 'UpdateFacebookMemberDefault');

// adds the code needed to check facebook
DataObject::add_extension('Controller', 'FacebookConnect');
// adds the default addFacebookUser method to Controller, alter your own decorator here for special add behaviour
DataObject::add_extension('Controller', 'AddFacebookMemberDefault');

// adds the authenticator to the built in login form
Authenticator::register('FacebookAuthenticator');

// don't forget to add Facebook App Keys to mysite/_config.php
