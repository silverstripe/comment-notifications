<p>
    There is a new comment on the <a href="{$Comment.Link}">{$Parent.Title}</a> page.
</p>

<p>
    <strong>Please review the comment for approval.</strong>
</p>

<ul>
    <li>{$Comment.Created.Nice}</li>
	<% if $Comment.AuthorName %>
		<li>{$Comment.AuthorName}</li>
	<% end_if %>
	<% if $Comment.Email %>
		<li>{$Comment.Email}</li>
	<% end_if %>
	<% if $IsSpam %>
		<li><em>This comment was automatically detected as spam</em></li>
	<% end_if %>
</ul>

<blockquote><% with Comment %>$Comment<% end_with %></blockquote>

<% if $ApproveLink || $HamLink || $SpamLink || $DeleteLink %>
	<ul>
		<% if $ApproveLink %>
			<li><strong>Approve it: </strong><a href="$ApproveLink.ATT">$ApproveLink.XML</a></li>
		<% end_if %>
		<% if $SpamLink %>
			<li><strong>Mark as Spam: </strong><a href="$SpamLink.ATT">$SpamLink.XML</a></li>
		<% end_if %>
		<% if $HamLink %>
			<li><strong>Mark as not Spam: </strong><a href="$HamLink.ATT">$HamLink.XML</a></li>
		<% end_if %>
		<% if $DeleteLink %>
			<li><strong>Delete it: </strong><a href="$DeleteLink.ATT">$DeleteLink.XML</a></li>
		<% end_if %>
	</ul>
<% else %>
	You can view or moderate this comment at <a href="{$Comment.Link}">{$Parent.Title}</a>
<% end_if %>
