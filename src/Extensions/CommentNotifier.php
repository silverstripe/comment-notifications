<?php

namespace SilverStripe\CommentNotifications\Extensions;

use SilverStripe\Comments\Model\Comment;
use SilverStripe\Security\Member;
use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Extension;

/**
 * Extension applied to CommentingController to invoke notifications
 *
 * Relies on the parent object to {@see Comment} having the {@see CommentNotifiable} extension applied..
 */
class CommentNotifier extends Extension
{

    /**
     * Notify Members of the post there is a new comment.
     *
     * @param Comment $comment
     */
    public function onAfterPostComment(Comment $comment)
    {
        $parent = $comment->Parent();

        if (!$parent || !$parent->hasMethod('notificationRecipients')) {
            return;
        }

        // Ask parent to submit all recipients
        $recipients = $parent->notificationRecipients($comment);

        foreach ($recipients as $recipient) {
            $this->notifyCommentRecipient($comment, $parent, $recipient);
        }
    }

    /**
     * Send comment notification to a given recipient
     *
     * @param Comment $comment
     * @param DataObject $parent Object with the {@see CommentNotifiable} extension applied
     * @param Member|string $recipient Either a member object or an email address to which notifications should be sent
     */
    public function notifyCommentRecipient($comment, $parent, $recipient)
    {
        $subject = $parent->notificationSubject($comment, $recipient);
        $sender = $parent->notificationSender($comment, $recipient);
        $template = $parent->notificationTemplate($comment, $recipient);
        $to = ($recipient instanceof Member) ? $recipient->Email : $recipient;

        // Validate email
        // Important in case of the owner being a default-admin or a username with no contact email
        // Assume arrays are in email => name format
        $validateTo = is_array($to) ? $validate = array_keys($to)[0] : $to;
        if (!Email::is_valid_address($validateTo)) {
            return;
        }

        // Prepare the email
        $email = Email::create();
        $email->setSubject($subject);
        $email->setFrom($sender);
        $email->setTo($to);
        $email->setHTMLTemplate($template);

        if ($recipient instanceof Member) {
            $email->setData([
                'Parent' => $parent,
                'Comment' => $comment,
                'Recipient' => $recipient,
                'ApproveLink' => $comment->ApproveLink($recipient),
                'HamLink' => $comment->HamLink($recipient),
                'SpamLink' => $comment->SpamLink($recipient),
                'DeleteLink' => $comment->DeleteLink($recipient),
            ]);
        } else {
            $email->setData([
                'Parent' => $parent,
                'Comment' => $comment,
                'ApproveLink' => false,
                'SpamLink' => false,
                'DeleteLink' => false,
                'HamLink' => false,
                'Recipient' => $recipient
            ]);
        }

        $this->owner->invokeWithExtensions('updateCommentNotification', $email, $comment, $recipient);

        return $email->send();
    }
}
