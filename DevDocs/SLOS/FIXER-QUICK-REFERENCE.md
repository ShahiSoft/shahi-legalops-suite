# Quick Reference: Accessibility Fixer System

## For Developers

### Using the Fixer System

**Get a specific fixer:**
```php
$fixer = FixerRegistry::get_fixer('missing-alt-text');
$result = $fixer->fix($content);
```

**Check if fixer exists:**
```php
if (FixerRegistry::has_fixer('my-issue')) {
    // Fixer is available
}
```

**Get all fixer IDs:**
```php
$ids = FixerRegistry::get_all_fixer_ids();
```

**Get fixer count:**
```php
$count = FixerRegistry::get_fixer_count(); // 65
```

### Creating a New Fixer

**Step 1: Create fixer class**
```php
namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Fixes\Fixers;

class MyIssueFixer extends BaseFixer {
    public function get_id() {
        return 'my-issue';
    }
    
    public function get_description() {
        return 'Fix my specific issue';
    }
    
    public function fix($content) {
        $dom = $this->get_dom($content);
        $fixed_count = 0;
        
        // Your fixing logic here
        
        return [
            'fixed_count' => $fixed_count,
            'content' => $this->dom_to_html($dom)
        ];
    }
}
```

**Step 2: Register in FixerRegistry**
```php
'my-issue' => MyIssueFixer::class,
```

### Using BaseFixer Utilities

**Parse HTML safely:**
```php
$dom = $this->get_dom($content);
```

**Convert DOM to HTML:**
```php
$html = $this->dom_to_html($dom);
```

**Generate alt text from image source:**
```php
$alt = $this->generate_alt_text('/images/user-profile.jpg');
// Returns: "User profile"
```

---

## For Site Administrators

### Where Are the Fixers?

All fixers are in: `wp-content/plugins/Shahi LegalOps Suite/includes/Modules/AccessibilityScanner/Fixes/`

### How Does Fixing Work?

1. User clicks "Fix" button in Accessibility Dashboard
2. System identifies the issue type
3. Appropriate fixer class is loaded
4. Fixer analyzes page content and applies fixes
5. Database is updated with results
6. Dashboard shows number of issues fixed

### What Gets Fixed?

| Category | Count | Examples |
|----------|-------|----------|
| Images & Links | 14 | Alt text, link text, headers |
| Forms | 11 | Labels, fieldsets, inputs |
| Headings | 6 | Hierarchy, structure, uniqueness |
| Tables & Media | 12 | Headers, captions, SVG |
| Interactivity | 12 | Focus, keyboard, contrast |
| ARIA & Semantic | 10 | Roles, attributes, elements |
| **Total** | **65** | **All major issue types** |

### Monitoring Fixes

Check log file: `wp-content/debug.log`

Typical log entry:
```
[04-Jan-2024 10:30:45 UTC] Fix applied: missing-alt-text on /contact, fixed 3 images
```

---

## For QA Testing

### Test Cases

**Test 1: Single Issue Fix**
```
1. Open page with missing alt text
2. Click "Fix" on "Missing Alt Text" issue
3. Verify: "3 issues fixed" message appears
4. Verify: Issue removed from list
5. Verify: Page score increased
```

**Test 2: Bulk Fix**
```
1. Open page with multiple issues
2. Click "Fix All Issues"
3. Verify: All issues fixed
4. Verify: Page score significantly improved
5. Verify: No duplicate fixes
```

**Test 3: Error Handling**
```
1. Delete a page while fixing
2. Verify: Error message displayed
3. Verify: No database corruption
4. Verify: Other pages still work
```

**Test 4: Large Content**
```
1. Test with page containing 5MB+ HTML
2. Verify: Fix completes without timeout
3. Verify: No memory issues
4. Verify: Output is valid HTML
```

**Test 5: Malformed HTML**
```
1. Create page with intentionally broken HTML
2. Click "Fix"
3. Verify: Fix still works
4. Verify: Output is valid HTML
5. Verify: No data loss
```

---

## Fixer Categories Quick Lookup

### Need to fix images or links? → LinkAndImageFixers
- Missing alt text
- Generic link text
- New window links
- Skip links

### Need to fix forms? → FormFixers
- Missing labels
- Fieldset/legend
- Form validation
- Custom controls

### Need to fix headings? → HeadingFixers
- Skipped levels
- Multiple H1s
- Duplicate headings
- Empty headings

### Need to fix tables/media? → ContentFixers
- Table headers
- Table captions
- SVG titles
- Iframe titles

### Need to fix interactivity? → InteractivityFixers
- Keyboard focus
- Color contrast
- Touch targets
- Positive tabindex

### Need to fix ARIA/HTML? → AriaAndSemanticFixers
- Missing ARIA roles
- Invalid ARIA
- Semantic elements
- Live regions

---

## Common Issues & Solutions

### "No issues fixed" after clicking Fix
**Check:**
- Is the issue actually present in the content?
- Was the page already fixed?
- Run a fresh accessibility scan

### Fix button shows error
**Check:**
- Is JavaScript enabled?
- Check browser console for errors
- Check wp-content/debug.log
- Clear browser cache

### HTML output looks wrong
**Check:**
- View page source to verify
- Check debug.log for parsing errors
- Try a different browser
- Try a simpler page

### Fixes not saving to database
**Check:**
- WordPress database is accessible
- Database user has write permissions
- Check for database errors in logs
- Try fixing a different page

---

## Files Reference

| File | Purpose | Fixers |
|------|---------|--------|
| BaseFixer.php | DOM utilities | Utilities only |
| LinkAndImageFixers.php | Images & links | 14 |
| FormFixers.php | Form elements | 11 |
| HeadingFixers.php | Headings | 6 |
| ContentFixers.php | Tables & media | 12 |
| InteractivityFixers.php | Focus & input | 12 |
| AriaAndSemanticFixers.php | ARIA & HTML | 10 |
| FixerRegistry.php | Central dispatcher | Routing |
| AccessibilityFixer.php | Main class | Integration |

---

## Performance Expectations

| Operation | Time | Notes |
|-----------|------|-------|
| Fix single issue | < 100ms | Typical page size |
| Fix all issues | < 1s | Up to 20 issues |
| Large content | < 2s | 500KB+ HTML |
| Bulk fixes | < 5s | 50+ pages |

---

## Security Notes

✅ All AJAX endpoints verified with nonce
✅ User permissions checked before fixing
✅ HTML safely parsed with libxml error handling
✅ No code execution in fixers
✅ Input sanitized throughout
✅ No SQL queries in fixers

---

## Support Resources

**Documentation Files:**
- `FIXER-SYSTEM-COMPLETION-REPORT.md` - Full completion summary
- `FIXER-SYSTEM-ARCHITECTURE.md` - Technical architecture
- `FIXER-IMPLEMENTATION-STATUS.md` - Implementation details

**Code Files:**
- `includes/Modules/AccessibilityScanner/Fixes/` - All fixer code
- `includes/Modules/AccessibilityScanner/AccessibilityScanner.php` - AJAX handlers

**Debugging:**
- Enable `WP_DEBUG` in wp-config.php
- Check `wp-content/debug.log`
- Use browser DevTools Network tab
- Test with smallest content possible

---

## Contact & Support

For issues or questions:
1. Check debug.log for error messages
2. Review relevant documentation files
3. Check code comments for implementation details
4. Test with minimal content to isolate issues

---

**Last Updated**: January 2024
**Version**: 1.0
**Status**: Production Ready
