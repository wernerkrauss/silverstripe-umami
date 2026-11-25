<?php

declare(strict_types=1);

namespace Netwerkstatt\Umami\Services;

use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\TemplateGlobalProvider;

/**
 * Decides whether and how the Umami tracker script is rendered.
 *
 * The tracker is rendered only in the configured environments and only when
 * Umami is enabled and fully configured in SiteConfig. If the vanilla
 * cookie consent module is installed and active (and require_consent is on),
 * the script is blocked until the "analytics" category is accepted.
 *
 * The service also exposes itself as the template global $UmamiTracker in every
 * template (via TemplateGlobalProvider), independent of the current controller.
 */
class UmamiTracker implements TemplateGlobalProvider
{
    use Configurable;
    use Injectable;

    /**
     * @return array<string, string>
     */
    public static function get_template_global_variables()
    {
        return [
            'UmamiTracker' => 'getUmamiTracker',
        ];
    }

    public static function getUmamiTracker(): self
    {
        return static::create();
    }

    /**
     * Environment types in which the tracker is rendered.
     *
     * @var array<string>
     * @config
     */
    private static array $track_environments = ['live'];

    /**
     * Whether the tracker requires consent before loading.
     *
     * Only applies when pixelpoems/silverstripe-vanilla-cookieconsent is installed
     * and active on the site.
     *
     * @config
     */
    private static bool $require_consent = true;

    public function shouldRender(): bool
    {
        $environments = $this->config()->get('track_environments') ?: [];
        if (!in_array(Director::get_environment_type(), $environments, true)) {
            return false;
        }

        return (bool) SiteConfig::current_site_config()->UmamiEnabled;
    }

    public function requiresConsent(): bool
    {
        if (!$this->config()->get('require_consent')) {
            return false;
        }

        if (!class_exists('VanillaCookieConsent\\Services\\CCService')) {
            return false;
        }

        return (bool) SiteConfig::current_site_config()->getField('CCActive');
    }
}
