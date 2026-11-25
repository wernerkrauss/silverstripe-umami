<?php

declare(strict_types=1);

namespace Netwerkstatt\Umami\Extension;

use SilverStripe\Core\Extension;

/**
 * Registers the "analytics" category and the "umami-analytics" service in the
 * vanilla-cookieconsent JS config at runtime via the updateCCJSConfig hook.
 *
 * This works independently of database builds or YAML merge order, so Umami
 * can be added to an existing site where cookie consent is already installed.
 *
 * @property \VanillaCookieConsent\Services\CCService|\Netwerkstatt\Umami\Extension\CCServiceExtension $owner
 * @extends \SilverStripe\Core\Extension<\VanillaCookieConsent\Services\CCService>
 */
class CCServiceExtension extends Extension
{
    public const string SERVICE_NAME = 'umami';

    public const string CATEGORY_NAME = 'analytics';

    /**
     * @param array<string, mixed> $config
     */
    public function updateCCJSConfig(array &$config): void
    {
        $categories = $config['categories'] ?? [];
        if (!is_array($categories)) {
            $categories = [];
        }

        if (!array_key_exists(self::CATEGORY_NAME, $categories)) {
            $categories[self::CATEGORY_NAME] = [];
        }

        $category = $categories[self::CATEGORY_NAME];
        if (!is_array($category)) {
            $category = [];
        }

        $services = $category['services'] ?? [];
        if (!is_array($services)) {
            $services = [];
        }

        if (!in_array(self::SERVICE_NAME, $services, true)) {
            $services[] = self::SERVICE_NAME;
        }

        $category['services'] = $services;
        $categories[self::CATEGORY_NAME] = $category;
        $config['categories'] = $categories;

        $configServices = $config['services'] ?? [];
        if (!is_array($configServices)) {
            $configServices = [];
        }

        if (!in_array(self::SERVICE_NAME, $configServices, true)) {
            $configServices[] = self::SERVICE_NAME;
        }

        $config['services'] = $configServices;
    }
}
