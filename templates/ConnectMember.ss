<% if CurrentMember %>
	<% control CurrentMember %>
		<p>Welcome back $FirstName.</p>

		<img src="$Avatar(square)" alt="$FirstName" />
	<% end_control %>
<% end_if %>
