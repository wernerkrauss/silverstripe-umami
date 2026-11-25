<?php

declare(strict_types=1);

namespace Netwerkstatt\Umami\Extension;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;

/**
 * Adds Umami analytics configuration to SiteConfig.
 *
 * The website ID (key) is created in the Umami dashboard when a new website is added there.
 *
 * @property \SilverStripe\SiteConfig\SiteConfig|\Netwerkstatt\Umami\Extension\SiteConfigExtension $owner
 * @property bool $UmamiActive
 * @property string $UmamiScriptURL
 * @property string $UmamiWebsiteID
 * @method bool UmamiEnabled()
 * @extends \SilverStripe\Core\Extension<\SilverStripe\SiteConfig\SiteConfig>
 */
class SiteConfigExtension extends Extension
{
    /**
     * @var array<string>
     * @config
     */
    private static array $db = [
        'UmamiActive' => 'Boolean',
        'UmamiScriptURL' => 'Varchar(255)',
        'UmamiWebsiteID' => 'Varchar(64)',
    ];

    public function updateCMSFields(FieldList $fields): void
    {
        $fields->findOrMakeTab(
            'Root.Analytics',
            _t(self::class . '.AnalyticsTab', 'Analytics')
        );

        $fields->addFieldsToTab('Root.Analytics', [
            CheckboxField::create(
                'UmamiActive',
                _t(self::class . '.UmamiActive', 'Enable Umami analytics')
            )->setDescription(_t(
                self::class . '.UmamiActiveDescription',
                'If enabled, the Umami tracking script is rendered in the configured environments'
                . ' (after consent, if the consent modal is active).'
            )),
            TextField::create(
                'UmamiScriptURL',
                _t(self::class . '.UmamiScriptURL', 'Umami script URL')
            )->setDescription(_t(
                self::class . '.UmamiScriptURLDescription',
                'URL of the Umami tracking script.'
            ))->setAttribute('placeholder', 'https://umami.example.com/script.js'),
            TextField::create(
                'UmamiWebsiteID',
                _t(self::class . '.UmamiWebsiteID', 'Umami website ID')
            )->setDescription(_t(
                self::class . '.UmamiWebsiteIDDescription',
                'The website ID (key) from your Umami dashboard when adding this website.'
            )),
        ]);
    }

    /**
     * Whether Umami tracking is enabled and fully configured.
     */
    public function getUmamiEnabled(): bool
    {
        return (bool) $this->owner->UmamiActive
            && !empty($this->owner->UmamiScriptURL)
            && !empty($this->owner->UmamiWebsiteID);
    }
}
