<?php

namespace SilverStripe\CommentNotifications\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Comments\Model\Comment;
use SilverStripe\Security\Member;
use SilverStripe\Control\Email\Email;
use SilverStripe\CommentNotifications\Tests\Model\CommentNotifiableTestDataObject;

class CommentNotifiableTest extends SapphireTest
{
    protected static $fixture_file = 'CommentNotifications.yml';

    protected $oldhost = null;

    protected static $extra_dataobjects = [
        CommentNotifiableTestDataObject::class
    ];

    protected function setUp()
    {
        parent::setUp();

        Config::modify()->set(Email::class, 'admin_email', 'myadmin@mysite.com');

        $this->oldhost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
        $_SERVER['HTTP_HOST'] = 'www.mysite.com';
    }

    protected function tearDown()
    {
        $_SERVER['HTTP_HOST'] = $this->oldhost;

        parent::tearDown();
    }

    public function testGetRecipients()
    {
        $comment1 = $this->objFromFixture(Comment::class, 'comment1');
        $comment2 = $this->objFromFixture(Comment::class, 'comment2');
        $item1 = $this->objFromFixture(CommentNotifiableTestDataObject::class, 'item1');
        $item2 = $this->objFromFixture(CommentNotifiableTestDataObject::class, 'item2');

        $this->assertEquals(array('myadmin@mysite.com', 'andrew@address.com'), $item1->notificationRecipients($comment1));
        $this->assertEquals(array('myadmin@mysite.com'), $item2->notificationRecipients($comment2));
    }

    public function testNotificationSubject()
    {
        $recipient = $this->objFromFixture(Member::class, 'author');
        $comment1 = $this->objFromFixture(Comment::class, 'comment1');
        $comment2 = $this->objFromFixture(Comment::class, 'comment2');
        $item1 = $this->objFromFixture(CommentNotifiableTestDataObject::class, 'item1');
        $item2 = $this->objFromFixture(CommentNotifiableTestDataObject::class, 'item2');

        $this->assertEquals('A new comment has been posted', $item1->notificationSubject($comment1, $recipient));
        $this->assertEquals('A new comment has been posted', $item2->notificationSubject($comment2, $recipient));
    }

    public function testNotificationSender()
    {
        $comment1 = $this->objFromFixture(Comment::class, 'comment1');
        $author = $this->objFromFixture(Member::class, 'author');
        $item1 = $this->objFromFixture(CommentNotifiableTestDataObject::class, 'item1');

        $this->assertEquals('noreply@mysite.com', $item1->notificationSender($comment1, $author));
    }
}
