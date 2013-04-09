<% if CurrentFacebookMember %>
	<% with CurrentFacebookMember %>
		<p>Welcome back $FirstName.</p>
		<% include ConnectLogout %>

		<img src="$Avatar(square)" alt="$FirstName" />
	<% end_with %>
<% end_if %>
