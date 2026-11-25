<?php

declare(strict_types=1);

namespace Netwerkstatt\Umami\Tests;

use Netwerkstatt\Umami\Services\UmamiTracker;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\SiteConfig\SiteConfig;
use TractorCow\Fluent\Extension\FluentSiteTreeExtension;
use TractorCow\Fluent\Model\Locale;

/**
 * HTTP-level tests: requests the homepage through the full stack and asserts
 * on the rendered UmamiTracker include in Page.ss.
 *
 * Note: expects a project setup where Page.ss contains `<% include UmamiTracker %>`.
 * Fluent is disabled for these tests (via $illegal_extensions) so URLs stay unprefixed
 * and no Fluent locale records are needed.
 */
class UmamiTrackerHttpTest extends FunctionalTest
{
    protected $usesDatabase = true;

    protected static $fixture_file = 'fixtures.yml';

    protected $requireDefaultRecordsFrom = [Locale::class];

    protected static $illegal_extensions = [
        SiteTree::class => [FluentSiteTreeExtension::class],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Config::modify()->set(UmamiTracker::class, 'track_environments', [Director::get_environment_type()]);

        foreach (SiteTree::get() as $page) {
            $page->publishRecursive();
        }
    }

    private function configureSiteConfig(bool $active, bool $consentActive, string $websiteId): void
    {
        $config = SiteConfig::current_site_config();
        $config->UmamiActive = $active;
        $config->UmamiScriptURL = 'https://umami.example.com/script.js';
        $config->UmamiWebsiteID = $websiteId;
        $config->CCActive = $consentActive;
        $config->write();
    }

    public function testTrackerRenderedWithConsentAttributesWhenEnabledAndConsentActive(): void
    {
        $this->configureSiteConfig(true, true, 'test-key-123');

        $response = $this->get('/');

        $this->assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        // with consent active: no src, only data-src (script is fetched only after consent)
        $this->assertStringContainsString('data-src="https://umami.example.com/script.js"', $body);
        $this->assertStringNotContainsString(' src="https://umami.example.com/script.js"', $body);
        $this->assertStringContainsString('data-website-id="test-key-123"', $body);
        $this->assertStringContainsString('type="text/plain"', $body);
        $this->assertStringContainsString('data-category="analytics"', $body);
        $this->assertStringContainsString('data-service="umami"', $body);
    }

    public function testTrackerRenderedWithoutConsentAttributesWhenConsentInactive(): void
    {
        $this->configureSiteConfig(true, false, 'test-key-456');

        $body = (string) $this->get('/')->getBody();

        // without consent: plain src, no data-src, no consent attributes
        $this->assertStringContainsString(' src="https://umami.example.com/script.js"', $body);
        $this->assertStringNotContainsString('data-src', $body);
        $this->assertStringContainsString('data-website-id="test-key-456"', $body);
        $this->assertStringNotContainsString('data-category="analytics"', $body);
    }

    public function testTrackerNotRenderedWhenDisabled(): void
    {
        $this->configureSiteConfig(false, true, 'test-key-789');

        $body = (string) $this->get('/')->getBody();

        $this->assertStringNotContainsString('data-website-id', $body);
        $this->assertStringNotContainsString('umami.example.com/script.js', $body);
    }
}
