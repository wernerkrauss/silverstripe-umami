<?php

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Dev\TestOnly;

class Page extends SiteTree implements TestOnly
{
}

class PageController extends \SilverStripe\CMS\Controllers\ContentController implements TestOnly
{
}
