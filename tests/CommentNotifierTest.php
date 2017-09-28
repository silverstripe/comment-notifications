<?php

namespace SilverStripe\CommentNotifications\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Config\Config;
use SilverStripe\Security\Member;
use SilverStripe\Comments\Model\Comment;
use SilverStripe\CommentNotifications\Tests\Control\CommentNotifierTestController;
use SilverStripe\CommentNotifications\Tests\Model\CommentNotifiableTestDataObject;

class CommentNotifierTest extends SapphireTest
{
    protected static $fixture_file = 'CommentNotifications.yml';

    protected $oldhost = null;

    protected static $extra_dataobjects = [
        CommentNotifiableTestDataObject::class
    ];

    protected static $extra_controllers = [
        CommentNotifierTestController::class
    ];

    protected function setUp()
    {
        parent::setUp();

        Config::inst()->update('Email', 'admin_email', 'myadmin@mysite.com');
        $this->oldhost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
        $_SERVER['HTTP_HOST'] = 'www.mysite.com';
    }

    protected function tearDown()
    {
        $_SERVER['HTTP_HOST'] = $this->oldhost;

        parent::tearDown();
    }

    public function testSendEmail()
    {
        $author = $this->objFromFixture(Member::class, 'author');
        $item1 = $this->objFromFixture(CommentNotifiableTestDataObject::class, 'item1');
        $item2 = $this->objFromFixture(CommentNotifiableTestDataObject::class, 'item2');
        $comment1 = $this->objFromFixture(Comment::class, 'comment1');
        $comment2 = $this->objFromFixture(Comment::class, 'comment2');
        $comment3 = $this->objFromFixture(Comment::class, 'comment3');
        $controller = new CommentNotifierTestController();

        // Comment 1
        $result = $controller->notifyCommentRecipient($comment1, $item1, $author);

        $this->assertEmailSent('andrew@address.com', 'noreply@mysite.com', 'A new comment has been posted');

        $email = $this->findEmail('andrew@address.com', 'noreply@mysite.com', 'A new comment has been posted');

        $this->assertContains('<li>Bob Bobberson</li>', $email['Content']);
        $this->assertContains('<li>bob@address.com</li>', $email['Content']);
        $this->assertContains('<blockquote>Hey what a lovely comment</blockquote>', $email['Content']);

        $this->clearEmails();

        // Comment 2
        $result = $controller->notifyCommentRecipient($comment2, $item2, $author);
        $this->assertEmailSent('andrew@address.com', 'noreply@mysite.com', 'A new comment has been posted');

        $email = $this->findEmail('andrew@address.com', 'noreply@mysite.com', 'A new comment has been posted');
        $this->assertContains('<li>Secret</li>', $email['Content']);
        $this->assertContains('<li>secret@notallowed.com</li>', $email['Content']);
        $this->assertContains('<blockquote>I don&#039;t want to disclose my details</blockquote>', $email['Content']);

        $this->clearEmails();

        // Comment 3
        $result = $controller->notifyCommentRecipient($comment3, $item1, $author);
        $this->assertEmailSent('andrew@address.com', 'noreply@mysite.com', 'A new comment has been posted');

        $email = $this->findEmail('andrew@address.com', 'noreply@mysite.com', 'A new comment has been posted');

        $this->assertContains('<li>Anonymous</li>', $email['Content']);
        $this->assertContains('<li>notlogged@in.com</li>', $email['Content']);
        $this->assertContains('<blockquote>I didn&#039;t log in</blockquote>', $email['Content']);
    }
}
