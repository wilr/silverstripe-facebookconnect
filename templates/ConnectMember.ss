<% if CurrentFacebookMember %>
	<% control CurrentFacebookMember %>
		$ClassName
		<p>Welcome back $FirstName.</p>

		<img src="$Picture">
	<% end_control %>
<% end_if %>
	