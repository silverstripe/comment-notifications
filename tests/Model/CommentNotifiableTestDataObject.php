<?php

namespace SilverStripe\CommentNotifications\Tests\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\CommentNotifications\Extensions\CommentNotifiable;
use SilverStripe\Comments\Extensions\CommentsExtension;

class CommentNotifiableTestDataObject extends DataObject implements TestOnly
{
    private static $db = [
        "Title" => "Varchar(255)",
        "URLSegment" => "Varchar(255)",
    ];

    private static $has_one = [
        'Author' => 'SilverStripe\Security\Member'
    ];

    private static $extensions = [
        CommentNotifiable::class,
        CommentsExtension::class
    ];

    private static $table_name = 'CommentNotifiableTestDataObject';

    public function notificationRecipients($comment)
    {
        $author = $this->Author();

        if ($author && $author->exists()) {
            return [$author->Email];
        }

        return parent::notificationRecipients($comment);
    }

    public function Link($action = false)
    {
        return Controller::join_links(
            Director::baseURL(),
            $this->URLSegment
        );
    }
}
