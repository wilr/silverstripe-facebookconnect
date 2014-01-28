# Facebook Connect Integration Module

## Maintainer Contact 
 * Will Rossiter 
   <will (at) fullscreen (dot) io>
	
## Requirements
 * SilverStripe 3.1

## Overview

The module provides a **basic** interface for implementing Facebook Connect on 
your SilverStripe website. Facebook Connect allows users to login to your 
website using their Facebook account details. This module integrates that 
single sign-on within the existing SilverStripe member system.

This has been designed to use the Javascript SDK rather than the OAuth 
interface. If you want to use the OAuth version see 
[here](https://svn.pocketrent.com/public/facebook/trunk/).

### What it provides

* Loads and setups Facebook Connect for Single Sign on via the Javascript SDK

* Authenticates users visiting the site - if they are logged into Facebook then 
 you can access their information via the following controls. You can also 
 optionally set whether to save visitors information as members on your site 
 (for example if you need to cache their information)

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
	
### How to use

To setup Facebook Connect your first need to download the module:

```
composer require wilr/silverstripe-facebookconnect
```

[Register your website / application](https://developers.facebook.com/apps/?action=create)
with Facebook.

Set your configuration through the SilverStripe Config API. For instance, you 
could put this in your `mysite/_config/facebookconnect.yml` file:

```
FacebookControllerExtension:
  app_id: 'MyAppID'
  api_secret: 'Secret'
```

Update the database by running `/dev/build` to add the additional fields to 
the `Member` table.

Include the `ConnectInit.ss` template in the `<body>` part of every site you 
wish to call a Facebook function. This includes the Facebook JavaScript SDK. 

E.g. on `Page.ss`

```
<body>
  <% include ConnectInit %>
```

Once you have done that you should be able to use the includes provided in this 
module.

```
<% if CurrentFacebookMember %>
	<p>Hi $CurrentFacebookMember.FirstName</p>
	<% include ConnectLogout %>
<% else %>
	<% include ConnectLogin %>
<% end_if %>
```

You can also access the Facebook member information in your PHP code. The 
Facebook API connection and current member are cached on the controller object. 
So for example if this is in your Page_Controller class

```php
// returns the current facebook member (wrapped in a SS Member Object)  
$this->getCurrentFacebookMember();

// returns the API connection which you can use to write your own query
$this->getFacebook(); 
```

### Options

All the following values are set either via the PHP Config API like follows

  Config::inst()->update('FacebookControllerExtension', 'OptionName', 'Value')

Or (more recommended) through the YAML API 

  FacebookControllerExtension:
    OptionName: Value

### app_id

Your app id. Found on the Facebook Developer Page.

### api_secret

Facebook API secret. Again, from your Application page.

### create_member 

  Optional, default: true

Whether or not to create a `Member` record in the database with the users 
information. If you disable this, ensure your code uses $CurrentFacebookMember
rather than $Member. Other access functionality (such as admin access) will not
work.

### member_groups

  Optional, default ''
	
A list of group codes to add the user. For instance if you want every member who
joins through facebook to be added to a group `Facebook Members` set the 
following:

  FacebookConnectExtensions:
    member_groups:
      - facebook_members

### permissions

  Optional, default 'email'

A list of permission codes you want from the user. Permission codes are listed
on [developers.facebook.com](https://developers.facebook.com/docs/reference/login).

Ensure you include email in your list if you require `create_member`.

### sync_member_details

  Optional, default true

Flag as to whether to replace user information (such as name) in your database
with the values from Facebook.

## License

Released under the BSD-3-Clause License. 
	
