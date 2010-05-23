<?php

// configuration for facebook connect. You should not edit any of these
// values and instead override from the mysite/_config file

// adds an extension hook to member
DataObject::add_extension('Member', 'FacebookMember');

DataObject::add_extension('Controller', 'FacebookConnect');
