# Extending the Facebook Member

If you need to override any of the Facebook member functionality there are a couple
steps which you need to take. First off you need to decide how much functionality you
are changing, if it is minor then you may want to __subclass__ the default extension.
If you're replacing most of the functionality you can create a new extension. For the
most part though, you will simply want to __subclass__ the extension.

* Remove the default extension

		DataObject::remove_extension('Member', 'FacebookMember');

* Create a new extension. In this example I use 'CustomFacebookMember' but you can
	use whatever you wish

		class CustomFacebookMember extends FacebookMember {
		  // your overridden and new functions
		}

* Add your custom extension on

		DataObject::add_extension('Member', 'CustomFacebookMember');

