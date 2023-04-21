# Comment Notifications

[![CI](https://github.com/silverstripe/comment-notifications/actions/workflows/ci.yml/badge.svg)](https://github.com/silverstripe/comment-notifications/actions/workflows/ci.yml)
[![Silverstripe supported module](https://img.shields.io/badge/silverstripe-supported-0071C4.svg)](https://www.silverstripe.org/software/addons/silverstripe-commercially-supported-module-list/)

Provides simple email notifications for when new visitor comments are posted.

## Installation

```sh
composer require silverstripe/comment-notifications
```

## Configuration

To configure the default email address to receive notifications, place this in your `mysite/_config.yml`

```yaml
SilverStripe\Control\Email\Email:
  admin_email: 'will@fullscreen.io'
```

Check out the [CommentNotifiable](src/Extensions/CommentNotifiable.php) class for the list of options you can override
in your project.

### Configuring Recipients

To define who receives the comment notification define a `updateNotificationRecipients` method and modify the list of
 email addresses.

**mysite/code/CommentNotificationExtension.php**

```php
<?php

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Comments\Model\Comment;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Security\Group;

class CommentNotificationExtension extends DataExtension
{
    /**
     * @param array $existing
     * @param Comment $comment
     */
    public function updateNotificationRecipients(&$existing, $comment)
    {
        // send notification of the comment to all administrators in the CMS
        $admin = Group::get()->filter('Code', 'admin');

        foreach ($admin as $group) {
            foreach ($group->Members() as $member) {
                $existing[] = $member->Email;
            }
        }

        // or, notify the user who originally created the page
        $page = $comment->Parent();
        if ($page instanceof SiteTree) {
            /** @var ArrayList $pageVersion */
            $pageVersion = $page->allVersions('', '', 1); // get the original version
            if ($pageVersion && $pageVersion->count()) {
                $existing[] = $pageVersion->first()->Author()->Email;
            }
        }
    }
}
```

Apply the `CommentNotificationExtension` to any classes which have commenting enabled (e.g SiteTree)

**mysite/_config/extensions.yml**
```yaml
SilverStripe\CMS\Model\SiteTree:
  extensions:
    - CommentNotificationExtension
```
