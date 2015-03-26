<?php

class CommentNotifierTest extends SapphireTest {

	protected static $fixture_file = 'CommentNotifications.yml';

	protected $oldhost = null;

	protected $extraDataObjects = array(
		'CommentNotifiableTest_DataObject'
	);
	
	public function setUp() {
		parent::setUp();
		Email::set_mailer(new EmailTest_Mailer());
		Config::nest();
		Config::inst()->update('Email', 'admin_email', 'myadmin@mysite.com');
		$this->oldhost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
		$_SERVER['HTTP_HOST'] = 'www.mysite.com';
		Commenting::add('CommentNotifiableTest_DataObject');
	}

	public function tearDown() {
		Commenting::remove('CommentNotifiableTest_DataObject');
		$_SERVER['HTTP_HOST'] = $this->oldhost;
		Config::unnest();
		Email::set_mailer(new Mailer());
		parent::tearDown();
	}

	public function testSendEmail() {
		$author = $this->objFromFixture('Member', 'author');
		$item1 = $this->objFromFixture('CommentNotifiableTest_DataObject', 'item1');
		$item2 = $this->objFromFixture('CommentNotifiableTest_DataObject', 'item2');
		$comment1 = $this->objFromFixture('Comment', 'comment1');
		$comment2 = $this->objFromFixture('Comment', 'comment2');
		$comment3 = $this->objFromFixture('Comment', 'comment3');
		$controller = new CommentNotifierTest_Controller();


		// Commen 1
		$result = $controller->notifyCommentRecipient($comment1, $item1, $author);
		$this->assertEquals('andrew@address.com', $result['to']);
		$this->assertEquals('noreply@mysite.com', $result['from']);
		$this->assertEquals('A new comment has been posted', $result['subject']);
		$this->assertContains('<li>Bob Bobberson</li>', $result['content']);
		$this->assertContains('<li>bob@address.com</li>', $result['content']);
		$this->assertContains('<blockquote>Hey what a lovely comment</blockquote>', $result['content']);
		$this->assertContains(
			'You can view or moderate this comment at <a href="http://www.mysite.com/item1#comment-' .
				$comment1->ID . '">An Object</a>',
			$result['content']
		);

		// Comment 2
		$result = $controller->notifyCommentRecipient($comment2, $item2, $author);
		$this->assertEquals('andrew@address.com', $result['to']);
		$this->assertEquals('noreply@mysite.com', $result['from']);
		$this->assertEquals('A new comment has been posted', $result['subject']);
		$this->assertContains('<li>Secret</li>', $result['content']);
		$this->assertContains('<li>secret@notallowed.com</li>', $result['content']);
		$this->assertContains('<blockquote>I don&#039;t want to disclose my details</blockquote>', $result['content']);
		$this->assertContains(
			'You can view or moderate this comment at <a href="http://www.mysite.com/item2#comment-' .
				$comment2->ID . '">Another One</a>',
			$result['content']
		);

		// Comment 3
		$result = $controller->notifyCommentRecipient($comment3, $item1, $author);
		$this->assertEquals('andrew@address.com', $result['to']);
		$this->assertEquals('noreply@mysite.com', $result['from']);
		$this->assertEquals('A new comment has been posted', $result['subject']);
		$this->assertContains('<li>Anonymous</li>', $result['content']);
		$this->assertContains('<li>notlogged@in.com</li>', $result['content']);
		$this->assertContains('<blockquote>I didn&#039;t log in</blockquote>', $result['content']);
		$this->assertContains(
			'You can view or moderate this comment at <a href="http://www.mysite.com/item1#comment-' .
				$comment3->ID . '">An Object</a>',
			$result['content']
		);
	}
}

class CommentNotifierTest_Controller extends Controller implements TestOnly {
	private static $extensions = array(
		'CommentNotifier'
	);
}