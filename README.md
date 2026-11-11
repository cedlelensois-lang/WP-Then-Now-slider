# WP Then Now Slider

Before / after photo comparison slider for WordPress: create unlimited comparisons, display them with a shortcode or a Gutenberg block.

*Version française plus bas.*

## Features

- Unlimited saved comparisons, each with its own shortcode
- Gutenberg block (pick a saved comparison or two images)
- Horizontal or vertical, starting position, optional hover mode
- Labels that fade out when the handle passes over them
- Global or per-comparison colors — handle color and selection color (defaults to the theme color)
- Mouse, touch and keyboard support, accessible `role="slider"`
- No jQuery on the front end, no build step, assets loaded only where needed
- English and French included, translation ready

## Installation

1. Download the latest release zip (or **Code > Download ZIP** and rename the folder to `wp-then-now-slider`).
2. WordPress admin: **Plugins > Add New > Upload Plugin**, then activate.
3. Go to **Before / After > Add New**.

## Usage

```text
[then_now id="12"]
[then_now before="123" after="456" orientation="vertical" start="30"]
```

| Attribute | Values | Default |
|---|---|---|
| `id` | saved comparison ID | – |
| `before`, `after` | media ID or image URL | – |
| `before_label`, `after_label` | text | Before / After |
| `before_alt`, `after_alt` | text | media alt |
| `orientation` | `horizontal`, `vertical` | `horizontal` |
| `start` | 0–100 | `50` |
| `hover` | `yes`, `no` | `no` |
| `labels` | `yes`, `no` | `yes` |
| `color` | hex color | global setting |
| `focus_color` | hex color | global setting / theme |
| `class` | CSS class | – |

With `id`, any other attribute overrides the saved setting.
Content loaded with AJAX: `window.WPThenNowSlider.init(container)`.

## Upgrading from "Avant Après Slider" / "Before After Slider" 1.x

Deactivate and delete the old plugin, then install this one. On activation, comparisons, global colors and blocks are migrated automatically. Old `[avant_apres]` / `[before_after]` shortcodes keep working.

## Structure

```text
wp-then-now-slider/
├── wp-then-now-slider.php   # plugin (post type, shortcode, block, admin)
├── assets/
│   ├── css/style.css        # front end + editor
│   ├── css/admin.css
│   ├── js/slider.js         # front end slider (vanilla JS)
│   ├── js/block.js          # Gutenberg block (no build step)
│   └── js/admin.js          # media picker, color pickers
└── languages/               # .pot, fr_FR .po/.mo/.json
```

## License

GPL-2.0-or-later — see [LICENSE](LICENSE).

---

## Français

Slider de comparaison de photos avant / après pour WordPress. Créez autant de comparaisons que vous voulez dans **Avant / Après > Ajouter**, puis affichez-les avec le shortcode `[then_now id="12"]` ou le bloc Gutenberg « Avant / Après ».

- Couleurs globales (**Avant / Après > Réglages**) ou propres à chaque comparaison
- Couleur de sélection de la poignée = couleur du thème par défaut
- Interface en français si WordPress est en français, sinon en anglais
- Les anciens shortcodes `[avant_apres]` et `[before_after]` fonctionnent toujours
