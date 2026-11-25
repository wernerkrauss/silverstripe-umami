# silverstripe-umami

Umami analytics integration for Silverstripe 6. Renders the Umami tracker script in a
configurable set of environments, with settings editable in the CMS (SiteConfig) and
optional integration with `pixelpoems/silverstripe-vanilla-cookieconsent`.

## Installation

```bash
composer require wernerkrauss/silverstripe-umami
```

## Usage

1. Add the include to your page template (e.g. in `<head>` of `Page.ss`):

   ```silverstripe
   <% include UmamiTracker %>
   ```

   The `UmamiTracker` service also exposes itself as the template global `$UmamiTracker` in
   every template (it implements `TemplateGlobalProvider`), independent of the current controller.

2. In the CMS, open SiteConfig -> Analytics and set:
   - **Enable Umami analytics**
   - **Umami script URL**, e.g. `https://umami.example.com/script.js`
   - **Umami website ID** (the key from your Umami dashboard when adding the website)

3. Run `dev/build` once so the new SiteConfig fields are created, then flush.

## Configuration

```yaml
Netwerkstatt\Umami\Services\UmamiTracker:
  # Render the tracker only in these environment types (SS_ENVIRONMENT_TYPE)
  track_environments:
    - live
  # If true and the cookie consent module is active, the script is blocked
  # until the "analytics" category is accepted
  require_consent: true
```

## Cookie consent integration

If `pixelpoems/silverstripe-vanilla-cookieconsent` is installed, this module registers the
`analytics` category and the `umami` service in the consent JS config at runtime
via the `updateCCJSConfig` extension hook (`_config/cookieconsent.yml`, gated with
`Only: moduleexists`). Because the registration happens on every request, Umami can be
added to an existing site where cookie consent is already installed - no database build or
YAML merge order required. The tracker template then renders the script as
`type="text/plain" data-category="analytics" data-service="umami"` with `data-src`
instead of `src`, so the script is not even fetched before the visitor accepts analytics
(the consent library moves `data-src` to `src` on consent). Without an active consent
modal, the template renders a plain `src` script. In the preferences dialog the service
shows up as "Umami" (the consent module capitalises the service name for the label).

The category title/description shown in the dialog are translated via the module's
`lang/de.yml` / `lang/en.yml` (keys `VanillaCookieConsent\Categories.Analytics` and
`...AnalyticsDescription`), stating that Umami is privacy-friendly and stores no cookies.
A project can override these texts with its own wording in `app/lang/` (higher priority),
e.g. to mention that Umami is self-hosted on a specific host. Note: the service label in the
preferences dialog is just the capitalised service name ("Umami") - the consent module does
not localise service labels, so project-specific text belongs in the category description.

Note: Umami is cookieless, so no storage/access on the device occurs (ePrivacy/TDDDG s25 is
not triggered). GDPR still applies to the processed data (e.g. IP address), so keeping the
tracker behind consent is the safe default for EU sites.

## Template override

Place `templates/UmamiTracker.ss` in your theme to override the default markup.

## Tests

The module ships a `phpunit.xml.dist` for standalone use (`vendor/bin/phpunit` inside the module
repository). Inside a project, the project's root `phpunit.xml.dist` includes the module's
`tests/` directory, so `ddev phpunit` runs them together with the app tests.

Besides unit tests (`UmamiTrackerTest`), `UmamiTrackerHttpTest` is a `FunctionalTest` that
requests the homepage over HTTP and asserts on the rendered tracker markup. It uses a YAML
fixture for the home page (`tests/fixtures.yml`) and disables Fluent via `$illegal_extensions`
so no locale records are needed.
