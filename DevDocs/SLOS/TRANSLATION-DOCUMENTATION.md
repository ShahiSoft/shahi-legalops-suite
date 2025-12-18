# ShahiTemplate - Translation Documentation

**Version**: 1.0.0  
**Last Updated**: December 13, 2025  
**Text Domain**: `shahi-template`

---

## 📚 **Overview**

ShahiTemplate is fully translation-ready and supports WordPress internationalization (i18n) standards. All user-facing strings are translatable, making the plugin accessible to global audiences.

**Key Features**:
- ✅ 100% translatable strings
- ✅ Consistent text domain throughout
- ✅ .pot template file included
- ✅ Helper methods for easy translation
- ✅ Context-aware translations
- ✅ Plural form support
- ✅ RTL (Right-to-Left) compatible

---

## 🌍 **Getting Started**

### For Plugin Users (Translators)

1. **Download a Translation Tool**
   - [Poedit](https://poedit.net/) (Recommended)
   - [GlotPress](https://wordpress.org/plugins/glotpress/) (WordPress plugin)
   - [Loco Translate](https://wordpress.org/plugins/loco-translate/) (WordPress plugin)

2. **Create Your Translation**
   - Open `languages/shahi-template.pot` in Poedit
   - Translate all strings to your language
   - Save as `shahi-template-{locale}.po` (e.g., `shahi-template-fr_FR.po`)
   - Poedit will automatically generate the `.mo` file

3. **Install Translation**
   - Upload both `.po` and `.mo` files to `wp-content/plugins/ShahiTemplate/languages/`
   - Translation will be automatically loaded based on your WordPress language setting

---

## 🔧 **For Developers**

### Text Domain

**Always use**: `shahi-template`

```php
// ✅ CORRECT
__('Dashboard', 'shahi-template')

// ❌ WRONG
__('Dashboard', 'other-domain')
__('Dashboard') // Missing domain
```

### Translation Functions

#### Basic Translation

```php
// Translate and return
$text = __('Dashboard', 'shahi-template');

// Translate and echo
_e('Welcome', 'shahi-template');

// Translate and escape for HTML
$safe = esc_html__('Settings', 'shahi-template');

// Translate, escape, and echo
esc_html_e('Save Changes', 'shahi-template');
```

#### Using the I18n Class

```php
use ShahiTemplate\Core\I18n;

// Translate and return
$text = I18n::translate('Dashboard');

// Translate and escape
$safe = I18n::translate_esc('Settings');

// Translate and echo
I18n::echo_translate('Welcome');

// Translate, escape, and echo
I18n::echo_translate_esc('Save Changes');
```

#### Context-Aware Translation

Use context when the same word has different meanings:

```php
// Without context - ambiguous
__('Post', 'shahi-template');

// With context - clear meaning
_x('Post', 'noun', 'shahi-template');  // A blog post
_x('Post', 'verb', 'shahi-template');  // To post/publish

// Using I18n class
I18n::translate_context('Post', 'noun');
I18n::translate_context('Post', 'verb');
```

**More Examples**:

```php
// "Last" can mean different things
_x('Last Week', 'time period', 'shahi-template');
_x('Last', 'previous in sequence', 'shahi-template');

// "Dashboard" can mean different things
_x('Dashboard', 'admin menu', 'shahi-template');
_x('Dashboard', 'car dashboard', 'shahi-template');
```

#### Plural Forms

```php
// Basic plural
$count = 5;
printf(
    _n('1 item', '%s items', $count, 'shahi-template'),
    $count
);

// Using I18n class
$message = I18n::translate_plural('1 module', '%s modules', $count);
printf($message, $count);

// Plural with context
$message = _nx('1 post', '%s posts', $count, 'blog posts', 'shahi-template');
printf($message, $count);
```

**Common Plural Examples**:

```php
// Items
_n('1 item', '%s items', $count, 'shahi-template');

// Modules
_n('1 module', '%s modules', $count, 'shahi-template');

// Users
_n('1 user', '%s users', $count, 'shahi-template');

// Time
_n('1 day', '%s days', $count, 'shahi-template');
_n('1 hour', '%s hours', $count, 'shahi-template');
_n('1 minute', '%s minutes', $count, 'shahi-template');
```

---

## 📋 **Translation Best Practices**

### 1. Always Use the Correct Text Domain

```php
// ✅ CORRECT
__('Settings', 'shahi-template')

// ❌ WRONG
__('Settings', 'wordpress')
__('Settings', 'shahi_template')  // Underscores instead of dashes
```

### 2. Never Use Variables in Text Domain

```php
// ❌ WRONG - Text domain must be a string literal
$domain = 'shahi-template';
__('Settings', $domain);

// ✅ CORRECT
__('Settings', 'shahi-template')
```

### 3. Keep Strings Simple and Context-Free

```php
// ❌ WRONG - Too specific, hard to translate
__('Click the blue button to save your changes now', 'shahi-template');

// ✅ CORRECT - Simple and reusable
__('Save Changes', 'shahi-template');
```

### 4. Avoid String Concatenation

```php
// ❌ WRONG - Can't be translated properly
echo __('You have', 'shahi-template') . ' ' . $count . ' ' . __('messages', 'shahi-template');

// ✅ CORRECT - Complete translatable sentence
printf(__('You have %s messages', 'shahi-template'), $count);
```

### 5. Use Placeholders for Dynamic Content

```php
// ❌ WRONG
echo __('Welcome', 'shahi-template') . ' ' . $username;

// ✅ CORRECT
printf(__('Welcome, %s!', 'shahi-template'), $username);

// Multiple placeholders
printf(
    __('User %1$s has %2$d unread messages.', 'shahi-template'),
    $username,
    $count
);
```

### 6. Escape Translated Strings

```php
// ✅ HTML context
echo '<h1>' . esc_html__('Dashboard', 'shahi-template') . '</h1>';

// ✅ Attribute context
echo '<input type="text" placeholder="' . esc_attr__('Search...', 'shahi-template') . '" />';

// ✅ URL context
echo '<a href="' . esc_url__('https://example.com', 'shahi-template') . '">Link</a>';

// ✅ JavaScript context
echo '<script>var msg = "' . esc_js__('Success', 'shahi-template') . '";</script>';
```

---

## 🔨 **I18n Class Reference**

### Text Domain Methods

```php
// Get the text domain
$domain = I18n::get_text_domain(); // Returns: 'shahi-template'

// Check if translations are loaded
$loaded = I18n::is_loaded(); // Returns: true/false
```

### Translation Methods

```php
// Basic translation
I18n::translate($text);
I18n::translate_esc($text);
I18n::echo_translate($text);
I18n::echo_translate_esc($text);

// Context translation
I18n::translate_context($text, $context);
I18n::translate_context_esc($text, $context);

// Plural translation
I18n::translate_plural($single, $plural, $number);
I18n::translate_plural_context($single, $plural, $number, $context);
```

### Language Management

```php
// Get current WordPress locale
$locale = I18n::get_current_locale(); // e.g., 'en_US', 'fr_FR'

// Get available translations
$languages = I18n::get_available_languages(); // Returns: ['fr_FR', 'es_ES', ...]

// Check if specific language is available
$available = I18n::is_language_available('fr_FR'); // Returns: true/false

// Get translation statistics
$stats = I18n::get_translation_stats();
// Returns: ['text_domain', 'loaded', 'current_locale', 'available_languages', ...]
```

### File System Methods

```php
// Get languages directory path
$path = I18n::get_languages_path();
// Returns: '/path/to/wp-content/plugins/ShahiTemplate/languages'

// Get .pot file path
$pot_path = I18n::get_pot_file_path();
// Returns: '/path/to/languages/shahi-template.pot'

// Check if .pot file exists
$exists = I18n::pot_file_exists(); // Returns: true/false
```

---

## 🌐 **Creating Translations**

### Using Poedit (Recommended)

1. **Install Poedit**
   - Download from [poedit.net](https://poedit.net/)
   - Available for Windows, Mac, Linux

2. **Create New Translation**
   - Open Poedit
   - File → New from POT/PO file
   - Select `languages/shahi-template.pot`
   - Choose your language (e.g., French)

3. **Translate Strings**
   - Translate each string in the list
   - Use suggestions and translation memory
   - Add translator comments if needed

4. **Save Translation**
   - File → Save
   - Save as `shahi-template-fr_FR.po` (for French)
   - Poedit automatically creates `shahi-template-fr_FR.mo`

5. **Install Translation**
   - Upload both `.po` and `.mo` files to plugin's `languages/` folder

### Using Loco Translate Plugin

1. **Install Loco Translate**
   ```
   WordPress Admin → Plugins → Add New → Search "Loco Translate"
   ```

2. **Create Translation**
   - Loco Translate → Plugins
   - Find "ShahiTemplate"
   - Click "New language"
   - Select language and location

3. **Translate**
   - Use the built-in editor
   - Translate strings one by one
   - Save changes

4. **Done!**
   - Translation is automatically applied

---

## 🗂️ **File Structure**

```
ShahiTemplate/
├── languages/
│   ├── shahi-template.pot          [Template file]
│   ├── shahi-template-fr_FR.po     [French translation source]
│   ├── shahi-template-fr_FR.mo     [French compiled translation]
│   ├── shahi-template-es_ES.po     [Spanish translation source]
│   ├── shahi-template-es_ES.mo     [Spanish compiled translation]
│   └── ... (other languages)
```

### File Types

- **`.pot`** - Portable Object Template (master template)
- **`.po`** - Portable Object (human-readable translation)
- **`.mo`** - Machine Object (compiled translation, used by WordPress)

---

## 🔄 **Translation Workflow**

### For Translators

1. Receive `.pot` file from developer
2. Create `.po` file in your language
3. Translate all strings
4. Generate `.mo` file (automatic in Poedit)
5. Test translation in WordPress
6. Submit `.po` and `.mo` files

### For Developers

1. Write code with translatable strings
2. Generate `.pot` file using WP-CLI or plugin
3. Distribute `.pot` to translators
4. Receive translated `.po` and `.mo` files
5. Include in plugin release
6. Update `.pot` when adding new strings

---

## 🛠️ **Generating POT File**

### Using WP-CLI (Recommended)

```bash
# Navigate to plugin directory
cd wp-content/plugins/ShahiTemplate

# Generate POT file
wp i18n make-pot . languages/shahi-template.pot --domain=shahi-template
```

### Using Poedit Pro

1. File → New
2. Select language
3. Properties → Translation properties
4. Add source paths
5. Extract → Scan sources
6. Save as `.pot`

### Using Loco Translate

1. Loco Translate → Plugins
2. Select ShahiTemplate
3. Click "Create template"
4. Click "Sync" to update

---

## 🎯 **Common Translation Patterns**

### Admin Menu Items

```php
add_menu_page(
    __('ShahiTemplate', 'shahi-template'),           // Page title
    __('ShahiTemplate', 'shahi-template'),           // Menu title
    'manage_options',
    'shahi-template',
    'callback_function'
);

add_submenu_page(
    'shahi-template',
    __('Dashboard', 'shahi-template'),               // Page title
    __('Dashboard', 'shahi-template'),               // Menu title
    'manage_options',
    'shahi-dashboard',
    'callback_function'
);
```

### Form Labels and Buttons

```php
<label for="setting-name">
    <?php esc_html_e('Setting Name', 'shahi-template'); ?>
</label>

<input 
    type="text" 
    id="setting-name" 
    placeholder="<?php esc_attr_e('Enter setting value', 'shahi-template'); ?>" 
/>

<button type="submit">
    <?php esc_html_e('Save Changes', 'shahi-template'); ?>
</button>
```

### Success/Error Messages

```php
// Success
$message = __('Settings saved successfully.', 'shahi-template');
echo '<div class="notice notice-success"><p>' . esc_html($message) . '</p></div>';

// Error
$error = __('Failed to save settings.', 'shahi-template');
echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';

// With placeholders
$message = sprintf(
    __('Module %s activated successfully.', 'shahi-template'),
    '<strong>' . esc_html($module_name) . '</strong>'
);
```

### JavaScript Translations

```php
// Localize script with translations
wp_localize_script('shahi-admin-js', 'shahiTranslations', array(
    'confirm_delete' => __('Are you sure you want to delete this item?', 'shahi-template'),
    'saving'         => __('Saving...', 'shahi-template'),
    'saved'          => __('Saved!', 'shahi-template'),
    'error'          => __('An error occurred.', 'shahi-template'),
));
```

```javascript
// Use in JavaScript
if (confirm(shahiTranslations.confirm_delete)) {
    // Delete item
}
```

---

## 🌍 **Language Codes Reference**

Common WordPress locale codes:

| Language | Code |
|----------|------|
| English (US) | `en_US` |
| English (UK) | `en_GB` |
| French (France) | `fr_FR` |
| Spanish (Spain) | `es_ES` |
| German | `de_DE` |
| Italian | `it_IT` |
| Portuguese (Brazil) | `pt_BR` |
| Portuguese (Portugal) | `pt_PT` |
| Russian | `ru_RU` |
| Chinese (Simplified) | `zh_CN` |
| Chinese (Traditional) | `zh_TW` |
| Japanese | `ja` |
| Korean | `ko_KR` |
| Arabic | `ar` |
| Dutch | `nl_NL` |
| Polish | `pl_PL` |
| Turkish | `tr_TR` |
| Swedish | `sv_SE` |
| Danish | `da_DK` |
| Norwegian | `nb_NO` |

Full list: [WordPress Locale Codes](https://wpastra.com/docs/complete-list-wordpress-locale-codes/)

---

## 📖 **Resources**

### Official Documentation
- [WordPress I18n](https://developer.wordpress.org/apis/handbook/internationalization/)
- [Plugin I18n](https://developer.wordpress.org/plugins/internationalization/)
- [Translating WordPress](https://make.wordpress.org/polyglots/handbook/)

### Tools
- [Poedit](https://poedit.net/) - Professional translation editor
- [Loco Translate](https://wordpress.org/plugins/loco-translate/) - WordPress plugin
- [WP-CLI i18n](https://github.com/wp-cli/i18n-command) - Command line tools

### Community
- [WordPress Polyglots](https://make.wordpress.org/polyglots/) - Translation team
- [GlotPress](https://translate.wordpress.org/) - Collaborative translation
- [Translation Forums](https://wordpress.org/support/forum/requests-and-feedback/translation-contributions/)

---

## ✅ **Translation Checklist**

### For Developers
- [ ] All user-facing strings wrapped in translation functions
- [ ] Consistent text domain (`shahi-template`) throughout
- [ ] No variables in text domain parameter
- [ ] Context added for ambiguous strings
- [ ] Placeholders used for dynamic content
- [ ] .pot file generated and included
- [ ] Translations loaded in plugin initialization

### For Translators
- [ ] Downloaded latest .pot file
- [ ] Translated all strings
- [ ] Tested plural forms
- [ ] Verified context translations
- [ ] Checked special characters and formatting
- [ ] Generated .mo file
- [ ] Tested translation in WordPress
- [ ] Submitted .po and .mo files

---

## 🆘 **Troubleshooting**

### Translation Not Loading

```php
// Check if translations are loaded
var_dump(I18n::is_loaded());

// Check current locale
var_dump(I18n::get_current_locale());

// Check available languages
var_dump(I18n::get_available_languages());

// Check .pot file exists
var_dump(I18n::pot_file_exists());
```

### Common Issues

1. **Wrong file names**
   - Must be: `shahi-template-{locale}.mo`
   - Not: `shahi_template-{locale}.mo` or `{locale}.mo`

2. **Wrong location**
   - Files must be in: `wp-content/plugins/ShahiTemplate/languages/`
   - Not in: WordPress core languages folder

3. **Missing .mo file**
   - Both `.po` and `.mo` files required
   - Use Poedit to generate `.mo` automatically

4. **WordPress language not set**
   - Settings → General → Site Language
   - Must match your translation file locale

---

**Document Version**: 1.0.0  
**Last Updated**: December 13, 2025  
**Text Domain**: shahi-template  
**Maintained By**: ShahiTemplate Team
