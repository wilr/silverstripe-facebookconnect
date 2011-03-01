<?php

// configuration for facebook connect. You should not edit any of these
// values and instead override from the mysite/_config file

// adds an extension hook to member
DataObject::add_extension('Member', 'FacebookMember');
// For custom add and update methods extend FacebookMember and add your Extended FacebookMember to Member instead
// DataObject::add_extension('Member', 'ExtendedFacebookMember');


// adds the default updateFacebookFields method to member, alter your own decorator here for special update behaviour
DataObject::add_extension('Member', 'UpdateFacebookMemberDefault');

// adds the authenticator to the built in login form
Authenticator::register('FacebookAuthenticator');

// don't forget to add Facebook App Keys to mysite/_config.php
