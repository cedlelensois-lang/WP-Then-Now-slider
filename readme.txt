=== WP Then Now Slider ===
Tags: before after, image comparison, slider, compare images, photo
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.2
Stable tag: 2.1.0
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
* No jQuery on the front end, assets only loaded where needed
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

== Changelog ==

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
