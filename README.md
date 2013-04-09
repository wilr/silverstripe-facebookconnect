# Facebook Connect Integration Module

## Maintainer Contact 
 * Will Rossiter 
   <will (at) fullscreen (dot) io>
	
## Requirements
 * SilverStripe 3.0

## Overview

The module provides a **basic** interface for implementing Facebook connect on 
a website. Facebook connect allows users to login to your website using their 
Facebook account. This module integrates that single sign-on within the existing 
SilverStripe member system.

This has been designed to use the Javascript SDK rather than the OAuth interface.

### What it provides

 * Loads and setups Facebook Connect for Single Sign on via the Javascript SDK

 * Authenticates users visiting the site - if they are logged into Facebook then 
 you can access their information via the following controls. You can also 
 optionally set whether to save visitors information as members on your site 
 (for example if you need to cache their information)
	
   If you haven't disabled the FacebookConnect::$create_member variable you can 
   access the Facebook's member information by using:

    ```
	<% with CurrentMember %>
		$FirstName $LastName $Avatar(small)
	<% end_with %>
	```

   If you have disabled the creation of members you can use the Facebook specific 
   member control. This still returns a member object the only difference is that 
   it won't save the information to the database

    ```
	<% with CurrentFacebookMember %>
		$FirstName $LastName $Avatar(small)
	<% end_with %>
    ```
	
### What it does not provide (yet)

  * This current iteration only provides the backbone. In future updates I am aiming 
  to integrate things like publishing stories to a users wall, interacting with events, 
  groups and other things like friends.
  * More controls over the logged in members information (eg status updates, events, 
  groups). If you need this functionality you can build this on top of the Facebook API 
  which is exposed:

    ```php
	function foo() {
		// returns the likes		
		$likes = $this->getFacebook()->api('/me/likes');
	}
    ```
	
### How to use

  * To setup Facebook connect your first need to download this and put it in your 
  SilverStripe sites root folder. 
  * You need to register your website / application at 
  https://developers.facebook.com/apps/?action=create
  * Once you have registered your app set the following in your mysite/_config.php 
  file. Replace the values with the ones you should get after registering your app.

    ```php
    FacebookConnect::set_app_id('app-id');
    FacebookConnect::set_api_secret('api-secret');
    FacebookConnect::set_lang('en_US');
    ```

  * Update the database by running `/dev/build` to add the additional fields to 
  the `Member` table.

  * Include the `ConnectInit.ss` template in the `<body>` part of every site you 
  wish to call a Facebook function. This includes the Facebook JavaScript SDK. 
  E.g. on `FrontendPage.ss`

    ```
    <body>
        <% include ConnectInit %>
    ```

Once you have done that you should be able to use the includes provided in this module.

```
<% if CurrentFacebookMember %>
	<p>Hi $CurrentFacebookMember.FirstName</p>
	<% include ConnectLogout %>
<% else %>
	<% include ConnectLogin %>
<% end_if %>
```

You can also access the Facebook member information in your PHP code. The Facebook API 
connection and current member are cached on the controller object. So for example if 
this is in your Page_Controller class

```php
// returns the current facebook member (wrapped in a SS Member Object)	
$this->getCurrentFacebookMember();

// returns the API connection which you can use to write your own query
$this->getFacebook(); 
```
	
### License

Released under the BSD License. 
	
