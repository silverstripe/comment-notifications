# Comment Notifications

[![Build Status](https://travis-ci.org/silverstripe/comment-notifications.svg?branch=master)](https://travis-ci.org/silverstripe/comment-notifications)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/silverstripe/comment-notifications/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/silverstripe/comment-notifications/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/silverstripe/comment-notifications/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/silverstripe/comment-notifications/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/silverstripe/comment-notifications/badges/build.png?b=master)](https://scrutinizer-ci.com/g/silverstripe/comment-notifications/build-status/master)
[![codecov.io](https://codecov.io/github/silverstripe/comment-notifications/coverage.svg?branch=master)](https://codecov.io/github/silverstripe/comment-notifications?branch=master)

Provides simple comment notifications.

## Installation

```
composer require silverstripe/comment-notifications
```

To configure the default email address to receive notifications, place this in your `mysite/_config.yml`

```
SilverStripe\Control\Email\Email:
  admin_email: 'will@fullscreen.io'
```

## Configuration

Check out the [CommentNotifiable](src/Extensions/CommentNotifiable.php) class for the list of options you can override
in your project.

### Configuring Recipients

To define who receives the comment notification define a `updateNotificationRecipients` method and modify the list of
 email addresses.

**mysite/code/CommentNotificationExtension.php**

```
<?php

class CommentNotificationExtension extends DataExtension
{
    /**
     * @param array $existing
     * @param SilverStripe\Comments\Model\Comment $comment
     */
    public function updateNotificationRecipients(&$existing, $comment)
    {
        // send notification of the comment to all administrators in the CMS
        $admin = Group::get()->filter('Code', 'admin');

        foreach ($admin->Members() as $member) {
            $existing[] = $member->Email;
        }

        // or, send notification to the page author
        $page = $comment->Parent();

        if ($page) {
            $existing[] = $page->Author()->Email;
        }
    }
}
```

Apply the `CommentNotificationExtension` to any classes which have commenting enabled (e.g SiteTree)

**mysite/_config/extensions.yml**
```
SilverStripe\CMS\Model\SiteTree:
  extensions:
    - CommentNotificationExtension
```
