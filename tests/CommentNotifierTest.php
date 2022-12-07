<?php

namespace SilverStripe\CommentNotifications\Tests;

use SilverStripe\CommentNotifications\Extensions\CommentNotifier;
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

    protected function setUp(): void
    {
        parent::setUp();

        Config::modify()->set('Email', 'admin_email', 'myadmin@mysite.com');
        $this->oldhost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
        $_SERVER['HTTP_HOST'] = 'www.mysite.com';
    }

    protected function tearDown(): void
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

        $this->assertStringContainsString('<li>Bob Bobberson</li>', $email['Content']);
        $this->assertStringContainsString('<li>bob@address.com</li>', $email['Content']);
        $this->assertStringContainsString('<blockquote>Hey what a lovely comment</blockquote>', $email['Content']);

        $this->clearEmails();

        // Comment 2
        $result = $controller->notifyCommentRecipient($comment2, $item2, $author);
        $this->assertEmailSent('andrew@address.com', 'noreply@mysite.com', 'A new comment has been posted');

        $email = $this->findEmail('andrew@address.com', 'noreply@mysite.com', 'A new comment has been posted');
        $this->assertStringContainsString('<li>Secret</li>', $email['Content']);
        $this->assertStringContainsString('<li>secret@notallowed.com</li>', $email['Content']);
        $this->assertStringContainsString(
            '<blockquote>I don&#039;t want to disclose my details</blockquote>',
            $email['Content']
        );

        $this->clearEmails();

        // Comment 3
        $result = $controller->notifyCommentRecipient($comment3, $item1, $author);
        $this->assertEmailSent('andrew@address.com', 'noreply@mysite.com', 'A new comment has been posted');

        $email = $this->findEmail('andrew@address.com', 'noreply@mysite.com', 'A new comment has been posted');

        $this->assertStringContainsString('<li>Anonymous</li>', $email['Content']);
        $this->assertStringContainsString('<li>notlogged@in.com</li>', $email['Content']);
        $this->assertStringContainsString('<blockquote>I didn&#039;t log in</blockquote>', $email['Content']);

        $this->clearEmails();

        // Comment 3 without an author
        $result = $controller->notifyCommentRecipient($comment3, $item1, 'foobar@silverstripe.org');
        $this->assertEmailSent('foobar@silverstripe.org', 'noreply@mysite.com', 'A new comment has been posted');

        $this->clearEmails();

        // Comment 3 without a valid email
        $result = $controller->notifyCommentRecipient($comment3, $item1, '<foobar1>');
        $noEmail = (bool) $this->findEmail('<foobar1>', 'noreply@mysite.com', 'A new comment has been posted');

        $this->assertFalse($noEmail);
    }

    public function testOnAfterPostComment()
    {
        $this->clearEmails();

        $comment1 = $this->objFromFixture(Comment::class, 'comment1');

        $controller = new CommentNotifierTestController();
        $controller->invokeWithExtensions('onAfterPostComment', $comment1);

        // test that after posting a comment the notifications are sent.
        $this->assertEmailSent('andrew@address.com', 'noreply@mysite.com', 'A new comment has been posted');
    }

    /**
     * @dataProvider provideGetToAddress
     */
    public function testGetToAddress($recipient, string $expected): void
    {
        // need to create Member in unit-test rather than dataProvider so that config manifests are available
        if ($recipient === 'MEMBER') {
            $recipient = new Member();
            $recipient->Email = 'member@example.com';
        }
        $method = new \ReflectionMethod(CommentNotifier::class, 'getToAddress');
        $method->setAccessible(true);
        $notifier = new CommentNotifier();
        $actual = $method->invoke($notifier, $recipient);
        $this->assertSame($expected, $actual);
    }

    public function provideGetToAddress(): array
    {
        return [
            ['MEMBER', 'member@example.com'],
            ['string@example.com', 'string@example.com'],
            [['arraykey@example.com' => 'Name'], 'arraykey@example.com'],
            // not testing invalid email addresses as they'll fail validation in a third party library
            // called after calling Email::is_valid_address($to) in CommentNotifier::notifyCommentRecipient()
        ];
    }
}
