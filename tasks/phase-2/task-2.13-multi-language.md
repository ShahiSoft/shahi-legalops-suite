# TASK 2.13: Multi-language Support (Consent Module)

**Phase:** 2 (Consent Management - CORE)  
**Effort:** 8-10 hours  
**Prerequisites:** TASK 2.12 complete (Audit)  
**Next Task:** [task-2.14-consent-preferences-ui.md](task-2.14-consent-preferences-ui.md)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 2.13 for the Shahi LegalOps Suite WordPress plugin.

CONTEXT:
Add full i18n support for the consent module. Ensure all frontend strings (banner, preferences),
admin strings (settings, dashboards), REST responses, and emails are translatable. Provide .pot
file, and ensure compatibility with WPML/Polylang. Add RTL support where applicable.

INPUT STATE (verify these exist):
✅ Consent UI components (banner, preferences planned)
✅ Admin settings, analytics, dashboard
✅ Translation directory: /languages/shahi-template.pot

YOUR TASK:

1) **Wrap all strings in __()/ _e()/ _x()**
- Frontend JS: expose localized strings via wp_localize_script
- PHP templates/controllers/services: wrap with translation functions

2) **Generate/Update POT file**
- File: `languages/shahi-template.pot`
- Ensure consent-related strings included
- Include domain: `shahi-legalops`

3) **Add loader**

In main plugin bootstrap ensure:
```php
load_plugin_textdomain( 'shahi-legalops', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
```

4) **Localization for JS**
- In enqueue for banner/preferences scripts, pass strings via wp_localize_script:
```php
wp_localize_script( 'slos-consent-banner', 'slosConsentI18n', [
    'heading' => __( 'We value your privacy', 'shahi-legalops' ),
    'message' => __( 'We use cookies to enhance your experience.', 'shahi-legalops' ),
    'acceptAll' => __( 'Accept All', 'shahi-legalops' ),
    'rejectAll' => __( 'Reject All', 'shahi-legalops' ),
    'acceptSelected' => __( 'Accept Selected', 'shahi-legalops' ),
    'learnMore' => __( 'Learn More', 'shahi-legalops' ),
];
```
- Update JS to read from slosConsentI18n when rendering text

5) **WPML/Polylang compatibility**
- Ensure custom post types/options registered with 'show_in_rest' => true
- Use `pll_register_string` or WPML config (documented) for options (banner texts)

6) **RTL support**
- Add CSS rules for RTL (body[dir="rtl"] selectors) in banner/preferences CSS

7) **Tests**

```bash
# Regenerate POT (developer step)
wp i18n make-pot . languages/shahi-template.pot --domain=shahi-legalops

# Verify load
wp eval "
load_plugin_textdomain('shahi-legalops', false, dirname(plugin_basename(__FILE__)).'/languages/');
_e('We value your privacy','shahi-legalops');
"

# Check localized script
wp eval "global $wp_scripts; print_r($wp_scripts->registered['slos-consent-banner']->extra['data']);"
```

OUTPUT STATE:
✅ All strings wrapped for translation
✅ JS localized strings provided
✅ POT file updated with consent strings
✅ Textdomain loaded
✅ RTL styles added
✅ WPML/Polylang compatibility notes

SUCCESS CRITERIA:
✅ Consent module fully translatable
✅ POT file contains consent strings
✅ RTL layout works for banner/preferences
✅ Localized strings used in JS

ROLLBACK:
```bash
# No DB changes; revert files if needed
```

TROUBLESHOOTING:
- Strings not translating: confirm textdomain 'shahi-legalops'
- JS not localized: check wp_localize_script placement
- RTL broken: verify CSS selectors with [dir="rtl"]

COMMIT MESSAGE:
```
feat(consent): Add multi-language support

- Wrap all consent strings for translation
- Localize banner/preferences JS strings
- Update POT with consent strings
- Add RTL support for consent UI
- Ensure WPML/Polylang compatibility

Task: 2.13 (8-10 hours)
Next: Task 2.14 - Consent Preferences UI
```

WHAT TO REPORT BACK:
"✅ TASK 2.13 COMPLETE
- Translations added for consent module
- JS localized strings
- POT updated
- RTL support in CSS
"
```

---

## ✅ COMPLETION CHECKLIST

- [ ] All strings wrapped
- [ ] JS localized
- [ ] POT regenerated
- [ ] RTL styles added
- [ ] Verified loading textdomain
- [ ] Committed to git
- [ ] Ready for Task 2.14

---

**Status:** ✅ Ready to execute  
**Time:** 8-10 hours  
**Next:** [task-2.14-consent-preferences-ui.md](task-2.14-consent-preferences-ui.md)
