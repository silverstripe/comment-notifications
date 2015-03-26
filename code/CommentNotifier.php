<?php

/**
 * Extension applied to CommentingController to invoke notifications
 *
 * Relies on the parent object to {@see Comment} having the {@see CommentNotifiable} extension applied
 */
class CommentNotifier extends Extension {

	/**
	 * Notify Members of the post there is a new comment.
	 *
	 * @param Comment $comment
	 */
	public function onAfterPostComment(Comment $comment) {
		$parent = $comment->getParent();
		if(!$parent) return;
		
		// Ask parent to submit all recipients
		$recipients = $parent->notificationRecipients($comment);
		foreach($recipients as $recipient) {
			$this->notifyCommentRecipient($comment, $parent, $recipient);
		}
	}

	/**
	 * Validates for RFC 2822 compliant email adresses.
	 *
	 * @see http://www.regular-expressions.info/email.html
	 * @see http://www.ietf.org/rfc/rfc2822.txt
	 *
	 * @param string $email
	 * @return boolean
	 */
	public function isValidEmail($email) {
		if(!$email) return false;

		$pcrePattern = '^[a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*'
			. '@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$';

		// PHP uses forward slash (/) to delimit start/end of pattern, so it must be escaped
		$pregSafePattern = str_replace('/', '\\/', $pcrePattern);

		return preg_match('/' . $pregSafePattern . '/i', $email);
	}

	/**
	 * Send comment notification to a given recipient
	 *
	 * @param Comment $comment
	 * @param DataObject $parent Object with the {@see CommentNotifiable} extension applied
	 * @param Member|string $recipient Either a member object or an email address to which notifications should be sent
	 */
	public function notifyCommentRecipient($comment, $parent, $recipient) {
		$subject = $parent->notificationSubject($comment, $recipient);
		$sender = $parent->notificationSender($comment, $recipient);
		$template = $parent->notificationTemplate($comment, $recipient);

		// Validate email
		// Important in case of the owner being a default-admin or a username with no contact email
		$to = $recipient instanceof Member
			? $recipient->Email
			: $recipient;
		if(!$this->isValidEmail($to)) return;

		// Prepare the email
		$email = new Email();
		$email->setSubject($subject);
		$email->setFrom($sender);
		$email->setTo($to);
		$email->setTemplate($template);
		$email->populateTemplate(array(
			'Parent' => $parent,
			'Comment' => $comment,
			'Recipient' => $recipient,
		));

		// Until invokeWithExtensions supports multiple arguments
		if(method_exists($this->owner, 'updateCommentNotification')) {
			$this->owner->updateCommentNotification($email, $comment, $recipient);
		}
		$this->owner->extend('updateCommentNotification', $email, $comment, $recipient);
		
		return $email->send();
	}
}
