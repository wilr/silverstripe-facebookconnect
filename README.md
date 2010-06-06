# Facebook Connect Integration Module

## Maintainer Contact 
 * Will Rossiter 
   <will (at) silverstripe (dot) com>
	
## Requirements
 * SilverStripe 2.4 or newer.

## Overview
The module provides a **basic** interface for implementing facebook connect on a website. Specifically its to integrate
user sign-on and sign up on a SilverStripe website - for example allowing login functionality by facebook rather than
the existing security. You can extend it using your own code but for now this is a super basic edition

### What it provides

 * Loads and setups Facebook Connect interactivity
 * Authenticates users visiting the site - if they are logged into facebook then you can access there information via
   the following controls. You can also optionally set whether to save visitors information as members on your site
   (for example if you need to cache their information)
	
   If you haven't disabled the FacebookConnect::$create_member variable you can access the facebooks member information
   by using..

	``<% control CurrentMember %>
		$FirstName $LastName $Picture(small)
	<% end_control %>``
	
If you have disabled the creation of members you can use the facebook specific member control. This still returns a 
member object the only difference is that it won't save the information to the database

	<% control CurrentFacebookMember %>
		$FirstName $LastName $Picture(small)
	<% end_control %>
	
### What it does not provide (yet)

  * This current iteration only provides the backbone. In future updates I am aiming to integrate things like publishing
    stories to a users wall, interacting with events, groups and other things like friends.

  * More controls over the logged in members information (eg status updates, events, groups)
	
### How to use

  * To setup facebook connect your first need to download this and put it in your SilverStripe sites root folder. 
  * You need to register your website / application at http://developers.facebook.com/setup
  * Once you have registered your app then set the following in your mysite/_config.php file. Replace the values with the ones
    you should get after registering your app

	FacebookConnect::set_api_key('api-key');
	FacebookConnect::set_api_secret('api-secret');
	FacebookConnect::set_app_id('api-id');
	
You need to add the fb: namespace to your Page.ss file. For example your <html> tag at the top should look like

	<html lang="en" xmlns:fb="http://www.facebook.com/2008/fbml">

Once you have done that you should be able to use the includes provided in this module. Note you must include the ConnectRoot.ss
include. So include the following code in your template

	<% include ConnectRoot %>
	<% if CurrentFacebookMember %>
		<p>Hi $CurrentFacebookMember.FirstName</p>
		<% include ConnectLogout %>
	<% else %>
		<% include ConnectLogin %>
	<% end_if %>

You can also access the facebook member information in your PHP code. The Facebook API connection and current member are
cached on the controller object. So for example if this is in your Page_Controller class

	// returns the current facebook member (wrapped in a SS Member Object)	
	$this->CurrentFacebookMember();
	
	// returns the API connection which you can use to write your own query
	$this->getFacebook(); 
	
### Configuration

By default users who login to your site via facebook (and give your website permission) are created member objects and saved to 
your database. If you wish to turn off saving member data to your database you can set it in your mysite/_config.php file

	FacebookConnect::set_create_member(false)
	
However if you want this functionality enabled (which it is by default) but the members saved in a special group you can define
the groups to add them to (mailing lists, user permissions etc) by defining the following in your mysite/_config.php file

	FacebookConnect::set_member_groups('facebook-members');
	
Or as an array

	FacebookConnect::set_member_groups(array('group-1', 'group-2'));
	
	
### License

Released under the BSD License. 
	