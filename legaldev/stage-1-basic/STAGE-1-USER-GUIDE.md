# Stage 1: Foundation Hub - User Guide

> **Version:** 1.0  
> **Last Updated:** December 24, 2025  
> **For:** Shahi LegalOps Suite v4.1.0+

---

## Overview

The **Legal Document Hub** is your centralized dashboard for generating and managing essential legal documents for your website. With Stage 1, you can:

- Complete a company profile questionnaire once
- Generate three professional legal documents with one click
- View, edit, and export your documents
- Track document versions and profile changes

### Supported Documents

| Document | Purpose | Template |
|----------|---------|----------|
| **Privacy Policy** | Discloses how you collect, use, and protect personal data | GDPR/CCPA compliant |
| **Terms of Service** | Defines rules and conditions for using your website/service | Business standard |
| **Cookie Policy** | Explains your cookie usage and consent mechanisms | EU Cookie Law compliant |

---

## Getting Started

### Step 1: Enable the Foundation Hub

1. Go to **WordPress Admin** → **SLOS Settings** → **Modules**
2. Find **Legal Documents** module
3. Toggle to **Enable**
4. Click **Save Changes**

### Step 2: Complete Your Company Profile

Before generating documents, you must complete the company profile wizard:

1. Navigate to **Legal Docs** → **Company Profile**
2. Complete all 8 sections of the questionnaire
3. Each section has required and optional fields
4. Progress is auto-saved as you complete each section
5. View your completion percentage in the progress bar

#### Profile Sections

| Step | Section | Required Fields |
|------|---------|-----------------|
| 1 | Company Info | Legal name, address, business type |
| 2 | Contacts | Legal email, DPO email |
| 3 | Website | URL, service description |
| 4 | Data Collection | Data types, purposes |
| 5 | Third Parties | (Optional) Data processors |
| 6 | Cookies | Essential cookies (at least 1) |
| 7 | Legal Framework | Jurisdiction, GDPR applicability |
| 8 | Retention | Default retention period |

### Step 3: Access the Document Hub

1. Navigate to **Legal Docs** → **Document Hub**
2. View all three document cards
3. Check your profile completion banner
4. Start generating documents

---

## Document Hub Interface

### Profile Completion Banner

At the top of the Document Hub, you'll see your profile status:

- **Green (100%)**: Profile complete - Ready to generate
- **Yellow (50-99%)**: Profile incomplete - Some fields missing
- **Red (<50%)**: Profile needs attention - Many fields missing

Click **Continue Setup** or **Edit Profile** to update your profile.

### Document Cards

Each document type has a card showing:

- **Status Badge**: Current document state
- **Version Number**: e.g., v1.0, v1.2
- **Last Updated**: When the document was last modified
- **Action Buttons**: Available actions for the document

### Status Badges Explained

| Badge | Color | Meaning |
|-------|-------|---------|
| **Not Generated** | Gray | Document hasn't been created yet |
| **Draft** | Blue | Document generated, awaiting review |
| **Outdated** | Orange | Profile changed since last generation |

---

## Generating Documents

### First-Time Generation

1. Click the **Generate** button on any document card
2. Review the pre-generation modal showing:
   - Document type
   - Profile fields to be used
   - Any missing fields (if applicable)
3. Click **Confirm Generate**
4. Wait for generation to complete (~2-5 seconds)
5. View success message
6. Document is saved as **Draft**

### Regenerating Documents

When your profile changes, documents become **outdated**:

1. Look for orange **Outdated** badge on cards
2. Click **Regenerate** button
3. Confirm in the modal dialog
4. New version is created (previous saved to history)

### Bulk Regeneration

To update all outdated documents at once:

1. Click **Regenerate All** button in header
2. Confirm bulk action
3. All outdated documents are regenerated

---

## Document Actions

### View Document

Click the **👁 View** button to:
- Preview document content in a modal
- Review generated text before publishing
- Verify placeholder resolution

### Edit Document

Click the **✏ Edit** button to:
- Open the full document editor
- Make manual changes to content
- Add custom sections

**Note:** Manual edits are preserved in version history but may be overwritten on regeneration.

### Download PDF

Click the **⬇ Download** button to:
- Export document as PDF
- Save for offline use
- Share with legal counsel for review

### Version History

Click the **🕐 History** button to:
- View all previous versions
- See who made changes and when
- Review change reasons

---

## Embedding Documents on Your Site

### Using Shortcodes

Each document has a unique shortcode for embedding:

```
[slos_legal_doc type="privacy-policy"]
[slos_legal_doc type="terms-of-service"]
[slos_legal_doc type="cookie-policy"]
```

### Shortcode Options

| Attribute | Default | Description |
|-----------|---------|-------------|
| `type` | required | Document type (privacy-policy, terms-of-service, cookie-policy) |
| `title` | true | Show/hide document title |
| `updated` | true | Show/hide last updated date |
| `toc` | false | Show table of contents |

### Example Usage

```html
<!-- Basic usage -->
[slos_legal_doc type="privacy-policy"]

<!-- Without title -->
[slos_legal_doc type="terms-of-service" title="false"]

<!-- With table of contents -->
[slos_legal_doc type="cookie-policy" toc="true"]
```

### Creating Legal Pages

1. Create a new WordPress Page (e.g., "Privacy Policy")
2. Add the shortcode to the page content
3. Publish the page
4. Link from your footer menu

---

## Profile Management

### Editing Your Profile

1. Go to **Legal Docs** → **Company Profile**
2. Navigate to the section you want to edit
3. Update the fields
4. Click **Save Step** or **Save All**

**Important:** Changing profile data marks existing documents as **Outdated**.

### Required vs Optional Fields

- **Required fields** (marked with *) must be completed before generating documents
- **Optional fields** enhance your documents but aren't mandatory
- Completion percentage counts only mandatory fields

### Data Validation

The system validates:
- Email addresses (proper format)
- URLs (valid format with https://)
- Required arrays (at least one item)
- Whitespace-only entries (treated as empty)

---

## Legal Disclaimer

### Important Notice

All generated documents include an automatic legal disclaimer:

> **LEGAL DISCLAIMER:** This document has been auto-generated based on the information you provided. It should be reviewed by qualified legal counsel before use. [Your Company] and the developers of this tool accept no liability for the accuracy or legal sufficiency of this document.

### Why Draft Only?

Documents are always saved as **Draft** (never auto-published) because:

1. Legal documents require professional review
2. Auto-generated content may need customization
3. Compliance requirements vary by jurisdiction
4. Your specific business may have unique needs

**Always have a qualified legal professional review documents before publishing.**

---

## Troubleshooting

### "Profile Incomplete" Error

**Problem:** Cannot generate documents.

**Solution:**
1. Check the completion banner percentage
2. Click **Continue Setup** to see missing fields
3. Complete all required fields
4. Return to Document Hub

### "Document Outdated" Warning

**Problem:** Document shows outdated badge.

**Solution:**
1. This is normal after profile updates
2. Click **Regenerate** to update
3. Or keep existing content if changes don't affect this document

### Generation Takes Too Long

**Problem:** Generation spinner doesn't complete.

**Solution:**
1. Check server performance
2. Disable conflicting plugins temporarily
3. Clear browser cache
4. Try a different browser
5. Contact support if issue persists

### Placeholders Not Resolved

**Problem:** Document shows `{{field_name}}` text.

**Solution:**
1. Ensure profile field is filled
2. Check for spelling in custom templates
3. Verify field mapping in settings
4. Report as bug if standard template

### PDF Export Fails

**Problem:** Download button doesn't work.

**Solution:**
1. Ensure DomPDF library is installed
2. Check PHP memory limit (min 128MB)
3. Verify write permissions on temp folder
4. Try HTML export as alternative

---

## Best Practices

### Before Generating

1. ✅ Complete all mandatory profile fields
2. ✅ Review data for accuracy
3. ✅ Verify your jurisdiction setting
4. ✅ Confirm GDPR applicability
5. ✅ List all your cookies and data types

### After Generating

1. ✅ Review each document carefully
2. ✅ Have legal counsel review before publishing
3. ✅ Test shortcodes on staging site
4. ✅ Verify PDF export works
5. ✅ Set up regular review schedule

### Ongoing Maintenance

1. ✅ Update profile when business changes
2. ✅ Regenerate documents after profile updates
3. ✅ Review documents quarterly
4. ✅ Monitor regulatory changes
5. ✅ Keep version history for compliance

---

## FAQ

### Q: Can I edit the generated documents?

**A:** Yes! Click **Edit** to open the document editor. Your changes are saved and preserved in version history.

### Q: What happens to my edits when I regenerate?

**A:** Manual edits are saved in version history before regeneration. The new version starts fresh from your current profile.

### Q: Can I use custom templates?

**A:** Stage 1 uses built-in professional templates. Custom templates will be available in Stage 2.

### Q: Is GDPR compliance automatic?

**A:** The templates include GDPR-compliant language when you enable GDPR in your legal framework settings. However, actual compliance depends on your business practices.

### Q: How many versions are kept?

**A:** All versions are kept by default. Version pruning will be available in a future update.

### Q: Can I restore a previous version?

**A:** Version viewing is available in Stage 1. Restore functionality will be added in Stage 2.

---

## Support

### Getting Help

- **Documentation:** `/docs/` folder in plugin directory
- **Support Forum:** [WordPress.org Support](https://wordpress.org/support/plugin/shahi-legalops-suite/)
- **Email:** support@shahilegalops.com
- **GitHub Issues:** For bug reports and feature requests

### Reporting Bugs

When reporting issues, please include:

1. WordPress version
2. PHP version
3. Plugin version
4. Steps to reproduce
5. Expected vs actual behavior
6. Console/error log screenshots

---

## Changelog

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | Dec 24, 2025 | Initial Stage 1 user guide |

---

**Next:** [Stage 1 API Reference](STAGE-1-API-REFERENCE.md) | [Stage 1 Developer Guide](STAGE-1-DEVELOPER-GUIDE.md)
