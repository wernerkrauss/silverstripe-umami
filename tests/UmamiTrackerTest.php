<?php

declare(strict_types=1);

namespace Netwerkstatt\Umami\Tests;

use Netwerkstatt\Umami\Extension\CCServiceExtension;
use Netwerkstatt\Umami\Services\UmamiTracker;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\SiteConfig\SiteConfig;

class UmamiTrackerTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testShouldRenderRequiresEnabledSiteConfigAndTrackedEnvironment(): void
    {
        Config::modify()->set(UmamiTracker::class, 'track_environments', [Director::get_environment_type()]);

        $tracker = UmamiTracker::create();
        $siteConfig = SiteConfig::current_site_config();

        $this->assertFalse($tracker->shouldRender(), 'not rendered while Umami is not configured');

        $siteConfig->UmamiActive = true;
        $siteConfig->UmamiScriptURL = 'https://umami.example.com/script.js';
        $siteConfig->UmamiWebsiteID = 'test-website-id';
        $siteConfig->write();

        $this->assertTrue($tracker->shouldRender(), 'rendered when enabled and environment matches');
    }

    public function testShouldRenderFalseInUntrackedEnvironment(): void
    {
        Config::modify()->set(UmamiTracker::class, 'track_environments', ['__untracked_environment__']);

        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->UmamiActive = true;
        $siteConfig->UmamiScriptURL = 'https://umami.example.com/script.js';
        $siteConfig->UmamiWebsiteID = 'test-website-id';
        $siteConfig->write();

        $this->assertFalse(
            UmamiTracker::create()->shouldRender(),
            'not rendered in an environment that is not tracked'
        );
    }

    public function testCCServiceExtensionRegistersAnalyticsCategoryAndService(): void
    {
        $extension = new CCServiceExtension();

        $config = ['categories' => ['necessary' => []], 'services' => []];
        $extension->updateCCJSConfig($config);

        $this->assertArrayHasKey('analytics', $config['categories']);
        $this->assertContains('umami', $config['categories']['analytics']['services']);
        $this->assertContains('umami', $config['services']);

        // existing categories must be kept
        $this->assertArrayHasKey('necessary', $config['categories']);
    }
}
