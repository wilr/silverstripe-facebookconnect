# Configuration

## Create Member records from Facebook Members

By default users who login to your site via Facebook (and give your website permission) are created member objects and saved to
your database. If you wish to turn off saving member data to your database you can set it in your mysite/_config.php file

	FacebookConnect::set_create_member(false)

However if you want this functionality enabled (which it is by default) but the members saved in a special group you can define
the groups to add them to (mailing lists, user permissions etc) by defining the following in your mysite/_config.php file

	FacebookConnect::set_member_groups('facebook-members');

Or as an array

	FacebookConnect::set_member_groups(array('group-1', 'group-2'));

## Adding functions to the Facebook Members

You can also write your own custom methods for adding and updating the Facebook Member Data (e.g.: if you have inherited from member and also save
Avatars to database). You can reach this by extending FacebookMember (e.g. ExtendedFacebookMember) and adding it instead of original FacebookMember
to Member.

	DataObject::add_extension('Member', 'ExtendedFacebookMember');

For more information about overriding facebook members see [Extending-Facebook-Members.md](Extending Facebook Members)