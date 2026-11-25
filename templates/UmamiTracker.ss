<%-- Umami analytics tracker. Rendered only in the configured environments and when enabled in SiteConfig. --%>
<%-- With active cookie consent, data-src is used instead of src, so the script is only fetched after the analytics category is accepted. --%>
<% if $UmamiTracker.ShouldRender %>
<script async defer <% if $UmamiTracker.RequiresConsent %>type="text/plain" data-category="analytics" data-service="umami" data-src="$SiteConfig.UmamiScriptURL.ATT"<% else %>src="$SiteConfig.UmamiScriptURL.ATT"<% end_if %> data-website-id="$SiteConfig.UmamiWebsiteID.ATT"></script>
<% end_if %>
