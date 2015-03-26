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
</ul>

<blockquote>{$Comment.Comment}</blockquote>

You can view or moderate this comment at <a href="{$Comment.Link}">{$Parent.Title}</a>
