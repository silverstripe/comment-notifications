<?php

class CommentNotifiableTest extends SapphireTest {

	protected static $fixture_file = 'CommentNotifications.yml';

	protected $oldhost = null;

	protected $extraDataObjects = array(
		'CommentNotifiableTest_DataObject'
	);
	
	public function setUp() {
		parent::setUp();
		Config::nest();
		
		Config::inst()->update('Email', 'admin_email', 'myadmin@mysite.com');
		$this->oldhost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
		$_SERVER['HTTP_HOST'] = 'www.mysite.com';
	}

	public function tearDown() {
		$_SERVER['HTTP_HOST'] = $this->oldhost;
		Config::unnest();
		parent::tearDown();
	}

	public function testGetRecipients() {
		$comment1 = $this->objFromFixture('Comment', 'comment1');
		$comment2 = $this->objFromFixture('Comment', 'comment2');
		$item1 = $this->objFromFixture('CommentNotifiableTest_DataObject', 'item1');
		$item2 = $this->objFromFixture('CommentNotifiableTest_DataObject', 'item2');
		$this->assertEquals(array('andrew@address.com'), $item1->notificationRecipients($comment1)->column('Email'));
		$this->assertEquals(array('myadmin@mysite.com'), $item2->notificationRecipients($comment2));
	}

	public function testNotificationSubject() {
		$recipient = $this->objFromFixture('Member', 'author');
		$comment1 = $this->objFromFixture('Comment', 'comment1');
		$comment2 = $this->objFromFixture('Comment', 'comment2');
		$item1 = $this->objFromFixture('CommentNotifiableTest_DataObject', 'item1');
		$item2 = $this->objFromFixture('CommentNotifiableTest_DataObject', 'item2');

		$this->assertEquals('A new comment has been posted', $item1->notificationSubject($comment1, $recipient));
		$this->assertEquals('A new comment has been posted', $item2->notificationSubject($comment2, $recipient));
	}

	public function testNotificationSender() {
		$comment1 = $this->objFromFixture('Comment', 'comment1');
		$author = $this->objFromFixture('Member', 'author');
		$item1 = $this->objFromFixture('CommentNotifiableTest_DataObject', 'item1');
		$this->assertEquals('noreply@mysite.com', $item1->notificationSender($comment1, $author));
	}

}

/**
 * @mixin CommentNotifiable
 */
class CommentNotifiableTest_DataObject extends DataObject implements TestOnly {

	private static $db = array(
		"Title" => "Varchar(255)",
		"URLSegment" => "Varchar(255)",
	);

	private static $has_one = array(
		'Author' => 'Member',
	);

	private static $extensions = array(
		'CommentNotifiable',
	);

	public function notificationRecipients($comment) {
		$author = $this->Author();
		if($author && $author->exists()) return new ArrayList(array($author));
		return parent::notificationRecipients($comment);
	}

	public function Link($action = false) {
		return Controller::join_links(
			Director::baseURL(),
			$this->URLSegment
		);
	}
}
