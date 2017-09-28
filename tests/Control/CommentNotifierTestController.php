<?php

namespace SilverStripe\CommentNotifications\Tests\Control;

use SilverStripe\Control\Controller;
use SilverStripe\Dev\TestOnly;
use SilverStripe\CommentNotifications\Extensions\CommentNotifier;

class CommentNotifierTestController extends Controller implements TestOnly
{
    private static $extensions = array(
        CommentNotifier::class
    );

    private static $url_segment = 'commentnotifiertest';
}
