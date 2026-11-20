=== WP Then Now Slider ===
Tags: before after, image comparison, slider, compare images, photo
Requires at least: 7.0
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 2.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create and manage multiple before / after photo comparisons with a draggable slider.

== Description ==

Show the difference between two photos (then / now, before / after) with a smooth
draggable handle. Create as many comparisons as you want, reuse them anywhere with a
shortcode or the Gutenberg block.

* Unlimited saved comparisons, each with its own shortcode
* Gutenberg block (saved comparison or images picked directly)
* Layouts: horizontal, vertical, reversed order
* Display modes: drag, follow the mouse, click to switch, side by side
* Handle styles (circle, square, arrows, line) and visibility (always, on hover, hidden)
* Image filters: black & white, sepia, vintage, warm, cool, high contrast, vivid, blur
* Watermark (image or text), custom badge, detailed caption (below or over the image)
* Fullscreen button (native fullscreen, simulated on iPhone)
* Custom labels that fade out when the handle passes over them
* Global or per-comparison colors (handle + selection color; default = theme color)
* Mouse, touch and keyboard (arrows, Shift+arrows, Home/End), accessible slider role
* No jQuery on the front end, deferred script, assets only loaded where needed
* Responsive images (srcset) and lazy loading handled by WordPress
* English and French included, translation ready (.pot)

== Installation ==

1. Plugins > Add New > Upload Plugin > choose wp-then-now-slider.zip
2. Activate the plugin.
3. Go to Before / After > Add New.

== Usage ==

= Saved comparisons =
Before / After > Add New, choose the before and after images, save.
Copy the shortcode shown on the right: [then_now id="12"]

= Gutenberg block =
Add the "Before / After" block (Media category), then pick a saved comparison
or choose two images directly.

= Shortcode =
[then_now id="12"]
[then_now before="123" after="456"]

Attributes (with id they override the saved settings):

* Images: before, after (media ID or URL), before_label, after_label, labels (yes|no), before_alt, after_alt
* Layout: mode (slider|hover|click|side), orientation (horizontal|vertical), reverse (yes|no),
  start (0-100), handle_style (circle|square|arrows|line), handle_visibility (always|hover|hidden),
  fullscreen (yes|no)
* Colors: color, focus_color
* Filters: before_filter, after_filter (none|grayscale|sepia|vintage|warm|cool|contrast|saturate|blur)
* Watermark: watermark_image (media ID), watermark_text, watermark_position
  (top-left|top-center|top-right|bottom-left|bottom-center|bottom-right|center), watermark_opacity (5-100)
* Badge: badge_text, badge_position, badge_color
* Caption: caption (basic HTML), caption_position (below|overlay)
* class

Example:
[then_now id="12" mode="click" before_filter="grayscale" badge_text="2024" fullscreen="yes"]

Legacy names [avant_apres] and [before_after] still work when no other plugin uses them.

= JavaScript =
For content loaded with AJAX: window.WPThenNowSlider.init(container)

== Frequently Asked Questions ==

= The handle selection color does not match my theme =
The default uses the theme's "primary" (or "accent") color, available in block themes.
Set a color in Before / After > Settings for classic themes.

= My images are not aligned =
Use two images with the same dimensions.

= What happens to my comparisons if I delete the plugin? =
Uninstalling removes the plugin settings and cache. Saved comparisons are kept, so nothing
is lost if you reinstall. To remove them too, move them to the Trash before uninstalling.

= Who can see a draft or private comparison? =
Only users allowed to read it. In a page, a shortcode or block pointing to an unpublished
comparison shows nothing to visitors.

== Updates ==

The plugin checks the GitHub Releases of cedlelensois-lang/WP-Then-Now-slider and shows
updates in Dashboard > Updates and on the Plugins page, like any other plugin.
Use "Check for updates" under the plugin name to check immediately.
Optional (private repository or GitHub API limits), in wp-config.php:
define( 'WPTN_GITHUB_TOKEN', 'your-token' );

== External services ==

To check for updates, the plugin requests https://api.github.com/repos/cedlelensois-lang/WP-Then-Now-slider/releases/latest
about every 6 hours from the admin area, and downloads the release zip from github.com when you
update. No data about your site or its visitors is sent (only a generic User-Agent with the plugin version).
GitHub terms: https://docs.github.com/site-policy/github-terms/github-terms-of-service
GitHub privacy statement: https://docs.github.com/site-policy/privacy-policies/github-general-privacy-statement

== Upgrade Notice ==

= 2.3.0 =
Requires WordPress 7.0 and PHP 8.1. Security hardening and WordPress 7.1 compatibility (block API v3).

== Changelog ==

= 2.3.0 =
* Requires WordPress 7.0+ and PHP 8.1+; tested up to WordPress 7.1.
* Block registered with block.json and block API version 3 (iframed editor of WordPress 7.1).
* Security: unpublished comparisons are only displayed to users allowed to read them.
* Security: comparisons are no longer exposed through the public REST API.
* Security: update check validates the download URL and version, sends no site data, and is limited to administrators.
* Security: invalid form values (arrays) are rejected instead of causing a PHP error.
* Legacy data migration limited to plugin activation or administrators; targeted cache cleaning.
* New uninstall routine (settings and cache).
* Responsive images (srcset/sizes) and smart lazy loading through WordPress; deferred front-end script.
* Accessibility: aria-valuetext on the handle, reduced motion respected.
* Editor components use the WordPress 7 sizes; no inline JavaScript in admin screens.
* French translations: non-breaking spaces, block title and description translated.

= 2.2.0 =
* Automatic updates from GitHub Releases (Update URI), "View details" window, "Check for updates" link.

= 2.1.0 =
* Display modes: drag, hover, click to switch, side by side.
* Reversed order, handle styles and handle visibility (always / on hover / hidden).
* Image filters, watermark (image or text), badge, detailed caption.
* Fullscreen button.
* Settings screen organised in collapsible sections; block panels generated from the same settings.
* Fix: color pickers on the Settings page.

= 2.0.0 =
* Renamed to WP Then Now Slider (new slug, text domain and prefixes).
* New [then_now] shortcode; [avant_apres] and [before_after] kept as aliases.
* Automatic migration of comparisons, settings and blocks from 1.x.

= 1.4.0 =
* English / French translations.

= 1.3.0 =
* Global or individual colors.

= 1.2.0 =
* Handle selection color.

= 1.1.0 =
* Saved comparisons (custom post type), list with thumbnails and shortcodes.

= 1.0.0 =
* Initial release.
