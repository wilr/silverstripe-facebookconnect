<% if CurrentFacebookMember %>
	<% control CurrentFacebookMember %>
		<p>Welcome back $FirstName.</p>
		<% include ConnectLogout %>
		<img src="$Avatar(square)" alt="$FirstName" />
	<% end_control %>
<% end_if %>
