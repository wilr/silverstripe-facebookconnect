# Facebook Connect Integration Module

## Maintainer Contact 
 * Will Rossiter 
   <will (at) fullscreen (dot) io>
	
## Requirements
 * SilverStripe 3.1

## Overview

The module provides a **basic** interface for implementing the Facebook PHP SDK 
on your SilverStripe website. The Facebook SDK allows users to login to your 
website using their Facebook account details, creating a single sign-on within 
the existing SilverStripe member system.

### What it provides

* Loads the Facebook PHP SDK.

* Provides $FacebookLoginLink template variable to generate a link to login to
Facebook. Upon clicking the link and being redirected back to your application
the SilverStripe `Member::currentUser()` will be populated with a `Member` 
instance linked to the users Facebook profile.

```
<% with CurrentMember %>
	$Name $Avatar(small)
<% end_with %>
```	
	
## Installation

```
composer require "wilr/silverstripe-facebookconnect" "dev-master"
```

[Register your website / application](https://developers.facebook.com/apps/?action=create)
with Facebook.

Set your configuration through the SilverStripe Config API. For example I keep
my configuration in `mysite/_config/facebookconnect.yml` file:

```
FacebookControllerExtension:
  app_id: 'MyAppID'
  api_secret: 'Secret'
```

Update the database by running `/dev/build` to add the additional fields to 
the `Member` table and make sure you `?flush=1` when you reload your website.

```
<a href="$FacebookLoginLink">Login via Facebook</a>
```

You can also access the Facebook PHP SDK in your PHP code..

```php
// https://developers.facebook.com/docs/php/FacebookSession/4.0.0
$session = Controller::curr()->getFacebookSession();
```

For more information about what you can do through the SDK see:

https://developers.facebook.com/docs/reference/php/4.0.0

### Options

All the following values are set either via the Config API like follows

  Config::inst()->update('FacebookControllerExtension', '$option', '$value')

Or (more recommended) through the YAML API 

  FacebookControllerExtension:
    option: value

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

  FacebookControllerExtension:
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
	
