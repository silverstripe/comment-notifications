<?php

/**
 * Apply this to a class which may have comments applied to it, and is aware of users who should be notified of
 * such comments
 */
class CommentNotifiable extends DataExtension {

	/**
	 * Default subject line if the owner doesn't override it
	 *
	 * @config
	 * @var string
	 */
	private static $default_notification_subject = 'A new comment has been posted';

	/**
	 * Default sender
	 *
	 * @config
	 * @var string
	 */
	private static $default_notification_sender = 'noreply@{host}';

	/**
	 * Default template to use for comment notifications
	 *
	 * @config
	 * @var string
	 */
	private static $default_notification_template = 'CommentEmail';

	/**
	 * Return the list of members or emails to send comment notifications to
	 *
	 * @param Comment $comment
	 * @return array|Traversable
	 */
	public function notificationRecipients($comment) {
		// Override this in your extending class to declare recipients
		$list = array();
		if($adminEmail = Email::config()->admin_email) {
			$list[] = $adminEmail;
		}
		if ($this->owner->hasMethod('updateNotificationRecipients')) {
		    $list = $this->owner->updateNotificationRecipients(&$list, $comment);
	    } else {
		    $this->owner->extend('updateNotificationRecipients', &$list, $comment);
	    }	
	    return $list;
	}

	/**
	 * Gets the email subject line for comment notifications
	 *
	 * @param Comment $comment Comment
	 * @param Member|string $recipient
	 * @return string
	 */
	public function notificationSubject($comment, $recipient) {
		$subject = $this->owner->config()->default_notification_subject;
		$this->owner->extend('updateNotificationSubject', $subject, $comment, $recipient);
		return $subject;
	}

	/**
	 * Get the sender email address to use for email notifications
	 *
	 * @param Comment $comment
	 * @param Member|string $recipient
	 * @return string
	 */
	public function notificationSender($comment, $recipient) {
		$sender = $this->owner->config()->default_notification_sender;

		// Do hostname substitution
		$host = isset($_SERVER['HTTP_HOST'])
			? preg_replace('/^www\./i', '', $_SERVER['HTTP_HOST'])
			: 'localhost';
		$sender = preg_replace('/{host}/', $host, $sender);
		
		$this->owner->extend('updateNotificationSender', $sender, $comment, $recipient);
		return $sender;
	}

	/**
	 * Determine the template to use for this email
	 *
	 * @param Comment $comment
	 * @param Member|string $recipient
	 * @return string Template name (excluding .ss extension)
	 */
	public function notificationTemplate($comment, $recipient) {
		$template = $this->owner->config()->default_notification_template;
		$this->owner->extend('updateNotificationTemplate', $template, $comment, $recipient);
		return $template;
	}

	/**
	 * Update the notification email
	 * 
	 * @param Email $email
	 * @param Comment $comment
	 * @param Member|string $recipient
	 */
	public function updateCommentNotification($email, $comment, $recipient) {}
	
}
