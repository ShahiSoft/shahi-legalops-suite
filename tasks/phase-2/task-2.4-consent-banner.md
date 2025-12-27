# TASK 2.4: Consent Banner Component

**Phase:** 2 (Consent Management - CORE)  
**Effort:** 10-12 hours  
**Prerequisites:** TASK 2.3 complete (REST API exists)  
**Next Task:** [task-2.5-cookie-scanner.md](task-2.5-cookie-scanner.md)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 2.4 for the Shahi LegalOps Suite WordPress plugin.

CONTEXT:
Consent REST API exists (Task 2.3 complete). Now create the frontend consent banner that users
see when visiting the site. This includes 4 banner templates (EU/GDPR, CCPA, Simple, Advanced),
responsive design, animations, granular category toggles, and integration with the API. Align with
/v3docs/modules/01-CONSENT-IMPLEMENTATION.md and WINNING-FEATURES-2026: 40+ languages with auto-
detection + RTL, WCAG 2.2 AA banner accessibility, Google Consent Mode v2 signaling, optional IAB
TCF TC string, preference center/history link, and compliance-proof export hooks.

This is the primary user-facing component of the consent management system.

INPUT STATE (verify these exist):
✅ Consent REST API at /wp-json/slos/v1/consents/*
✅ Consent Service with grant/withdraw methods
✅ Frontend assets directory: assets/js/, assets/css/

YOUR TASK:

1. **Create Banner JavaScript** (localized with 40+ languages, RTL aware, emits GCMv2/TCF signals)

Location: `assets/js/consent-banner.js`

```javascript
/**
 * Consent Banner
 * Displays and manages consent banner on frontend.
 *
 * @package Shahi\LegalOps
 * @since 3.0.0
 */

(function() {
    'use strict';

    class ConsentBanner {
        constructor() {
            this.config = window.slosConsentConfig || {};
            this.apiUrl = this.config.apiUrl || '/wp-json/slos/v1/consents';
            this.userId = this.config.userId || 0;
            this.bannerTemplate = this.config.template || 'eu';
            this.position = this.config.position || 'bottom';
            this.theme = this.config.theme || 'light';
            this.purposes = [];
            this.consents = {};
            this.locale = this.config.locale || (navigator.language || 'en');
            this.translations = window.slosConsentI18n || {};

            this.init();
        }

        /**
         * Initialize banner
         */
        async init() {
            // Check if consent already given
            const hasConsent = await this.checkConsent('analytics');
            if (hasConsent && !this.shouldShowAgain()) {
                return; // Don't show banner
            }

            // Load valid purposes
            await this.loadPurposes();

            // Show banner
            this.showBanner();

            // Bind events
            this.bindEvents();
        }

        /**
         * Load valid purposes from API
         */
        async loadPurposes() {
            try {
                const response = await fetch(`${this.apiUrl}/purposes`);
                const data = await response.json();
                this.purposes = data.data.purposes || [];
            } catch (error) {
                console.error('Failed to load purposes:', error);
                this.purposes = ['necessary','functional','analytics','marketing','personalization'];
            }
        }

        /**
         * Check if user has consent
         */
        async checkConsent(purpose) {
            try {
                const response = await fetch(`${this.apiUrl}/check/${this.userId}/${purpose}`);
                const data = await response.json();
                return data.data.has_consent;
            } catch (error) {
                return false;
            }
        }

        /**
         * Show banner
         */
        showBanner() {
            const banner = this.createBanner();
            document.body.appendChild(banner);

            // Animate in
            setTimeout(() => {
                banner.classList.add('slos-banner-visible');
            }, 100);
        }

        /**
         * Create banner element
         */
        createBanner() {
            const div = document.createElement('div');
            div.id = 'slos-consent-banner';
            div.className = `slos-banner slos-banner-${this.position} slos-banner-${this.theme} slos-template-${this.bannerTemplate}`;

            div.innerHTML = this.getBannerHTML();

            return div;
        }

        /**
         * Get banner HTML based on template
         */
        getBannerHTML() {
            switch (this.bannerTemplate) {
                case 'eu':
                case 'gdpr':
                    return this.getEUBannerHTML();
                case 'ccpa':
                    return this.getCCPABannerHTML();
                case 'advanced':
                    return this.getAdvancedBannerHTML();
                default:
                    return this.getSimpleBannerHTML();
            }
        }

        /**
         * EU/GDPR Banner (with granular options)
         */
        getEUBannerHTML() {
            return `
                <div class="slos-banner-content">
                    <div class="slos-banner-header">
                        <h3>${this.t('heading','We value your privacy')}</h3>
                    </div>
                    <div class="slos-banner-body">
                        <p>${this.t('message','We use cookies to enhance your browsing experience, serve personalized content, and analyze our traffic. By clicking "Accept All", you consent to our use of cookies.')}</p>
                        
                        <div class="slos-consent-options">
                            ${this.purposes.map(purpose => `
                                <div class="slos-consent-option">
                                    <label>
                                        <input type="checkbox" 
                                               class="slos-consent-checkbox" 
                                               data-purpose="${purpose}"
                                               ${purpose === 'functional' ? 'checked disabled' : ''}>
                                        <span class="slos-purpose-label">${this.formatPurpose(purpose)}</span>
                                        ${purpose === 'functional' ? '<span class="slos-required">(Required)</span>' : ''}
                                    </label>
                                    <p class="slos-purpose-description">${this.getPurposeDescription(purpose)}</p>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    <div class="slos-banner-footer">
                        <button class="slos-btn slos-btn-accept-all" data-action="accept-all">${this.t('acceptAll','Accept All')}</button>
                        <button class="slos-btn slos-btn-accept-selected" data-action="accept-selected">${this.t('acceptSelected','Accept Selected')}</button>
                        <button class="slos-btn slos-btn-reject-all" data-action="reject-all">${this.t('rejectAll','Reject All')}</button>
                        <a href="#" class="slos-privacy-link">${this.t('privacyLink','Privacy Policy')}</a>
                    </div>
                </div>
            `;
        }

        /**
         * CCPA Banner (opt-out focus)
         */
        getCCPABannerHTML() {
            return `
                <div class="slos-banner-content">
                    <div class="slos-banner-body">
                        <p>We use cookies. California residents: you have the right to opt-out of the sale of personal information.</p>
                    </div>
                    <div class="slos-banner-footer">
                        <button class="slos-btn slos-btn-accept-all" data-action="accept-all">${this.t('accept','Accept')}</button>
                        <button class="slos-btn slos-btn-reject-all" data-action="do-not-sell">${this.t('doNotSell','Do Not Sell My Info')}</button>
                        <a href="#" class="slos-settings-link" data-action="settings">${this.t('settings','Settings')}</a>
                    </div>
                </div>
            `;
        }

        /**
         * Simple banner (just accept/reject)
         */
        getSimpleBannerHTML() {
            return `
                <div class="slos-banner-content">
                    <p>${this.t('simpleMsg','This website uses cookies to ensure you get the best experience.')}</p>
                    <div class="slos-banner-actions">
                        <button class="slos-btn slos-btn-accept" data-action="accept-all">${this.t('accept','Accept')}</button>
                        <button class="slos-btn slos-btn-reject" data-action="reject-all">${this.t('decline','Decline')}</button>
                        <a href="#" class="slos-learn-more">${this.t('learnMore','Learn More')}</a>
                    </div>
                </div>
            `;
        }

        /**
         * Advanced banner (detailed options)
         */
        getAdvancedBannerHTML() {
            // Similar to EU but with more details
            return this.getEUBannerHTML();
        }

        /**
         * Bind event handlers
         */
        bindEvents() {
            const banner = document.getElementById('slos-consent-banner');
            if (!banner) return;

            // Accept all
            banner.querySelectorAll('[data-action="accept-all"]').forEach(btn => {
                btn.addEventListener('click', () => this.acceptAll());
            });

            // Reject all
            banner.querySelectorAll('[data-action="reject-all"], [data-action="do-not-sell"]').forEach(btn => {
                btn.addEventListener('click', () => this.rejectAll());
            });

            // Accept selected
            banner.querySelectorAll('[data-action="accept-selected"]').forEach(btn => {
                btn.addEventListener('click', () => this.acceptSelected());
            });
        }

        /**
         * Accept all consents
         */
        async acceptAll() {
            for (const purpose of this.purposes) {
                await this.grantConsent(purpose);
            }
            this.hideBanner();
            this.reloadScripts();
        }

        /**
         * Reject all consents
         */
        async rejectAll() {
            // Only accept required (functional)
            await this.grantConsent('functional');
            this.hideBanner();
        }

        /**
         * Accept selected consents
         */
        async acceptSelected() {
            const checkboxes = document.querySelectorAll('.slos-consent-checkbox:checked');
            for (const checkbox of checkboxes) {
                const purpose = checkbox.dataset.purpose;
                await this.grantConsent(purpose);
            }
            this.hideBanner();
            this.reloadScripts();
        }

        /**
         * Grant consent for purpose
         */
        async grantConsent(purpose) {
            try {
                const response = await fetch(`${this.apiUrl}/grant`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        user_id: this.userId,
                        purpose: purpose,
                        consent_text: document.getElementById('slos-consent-banner')?.querySelector('p')?.textContent || '',
                        consent_method: 'explicit'
                    })
                });

                const data = await response.json();
                if (data.success) {
                    this.consents[purpose] = true;
                    this.saveToLocalStorage(purpose);
                    this.emitConsentSignals();
                }
            } catch (error) {
                console.error(`Failed to grant ${purpose} consent:`, error);
            }
        }

        /**
         * Hide banner
         */
        hideBanner() {
            const banner = document.getElementById('slos-consent-banner');
            if (banner) {
                banner.classList.remove('slos-banner-visible');
                setTimeout(() => {
                    banner.remove();
                }, 300); // Match animation duration
            }
        }

        /**
         * Save consent to localStorage (for faster checks)
         */
        saveToLocalStorage(purpose) {
            try {
                const consents = JSON.parse(localStorage.getItem('slos_consents') || '{}');
                consents[purpose] = {
                    granted: true,
                    timestamp: Date.now()
                };
                localStorage.setItem('slos_consents', JSON.stringify(consents));
            } catch (error) {
                // Ignore localStorage errors
            }
        }

        /**
         * Reload scripts that need consent
         */
        reloadScripts() {
            // Trigger event for script blocker
            document.dispatchEvent(new CustomEvent('slos-consent-updated', {
                detail: { consents: this.consents }
            }));

            // Reload page if configured

        emitConsentSignals() {
            // Google Consent Mode v2 signals
            if (window.gtag) {
                const state = this.buildConsentModeState();
                window.gtag('consent', 'update', state);
            }
            // Optional IAB TCF stub (replace when TC string service available)
            document.dispatchEvent(new CustomEvent('slos-tcf-update', { detail: { consents: this.consents } }));
        }

        buildConsentModeState() {
            // Map purposes to GCM v2 keys; defaults conservative
            return {
                ad_storage: this.consents.marketing ? 'granted' : 'denied',
                analytics_storage: this.consents.analytics ? 'granted' : 'denied',
                ad_user_data: this.consents.marketing ? 'granted' : 'denied',
                ad_personalization: this.consents.personalization ? 'granted' : 'denied'
            };
        }

        t(key, fallback) {
            return this.translations[key] || fallback;
        }
            if (this.config.reloadOnConsent) {
                location.reload();
            }
        }

        /**
         * Check if should show banner again
         */
        shouldShowAgain() {
            // Check if 30 days passed
            try {
                const consents = JSON.parse(localStorage.getItem('slos_consents') || '{}');
                const lastConsent = Object.values(consents)[0];
                if (lastConsent && lastConsent.timestamp) {
                    const daysPassed = (Date.now() - lastConsent.timestamp) / (1000 * 60 * 60 * 24);
                    return daysPassed > 30;
                }
            } catch (error) {
                // Ignore
            }
            return false;
        }

        /**
         * Format purpose name
         */
        formatPurpose(purpose) {
            return purpose.charAt(0).toUpperCase() + purpose.slice(1);
        }

        /**
         * Get purpose description
         */
        getPurposeDescription(purpose) {
            const descriptions = {
                functional: 'Required for the website to function properly',
                analytics: 'Helps us understand how visitors use our website',
                marketing: 'Used to deliver relevant ads and marketing campaigns',
                advertising: 'Used to show personalized advertisements',
                personalization: 'Remembers your preferences and settings'
            };
            return descriptions[purpose] || '';
        }
    }

    // Initialize when DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            new ConsentBanner();
        });
    } else {
        new ConsentBanner();
    }
})();
```

2. **Create Banner CSS**

Location: `assets/css/consent-banner.css`

```css
/**
 * Consent Banner Styles
 */

/* Banner Container */
#slos-consent-banner {
    position: fixed;
    left: 0;
    right: 0;
    z-index: 999999;
    background: #fff;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
    padding: 20px;
    transform: translateY(100%);
    transition: transform 0.3s ease-in-out;
}

#slos-consent-banner.slos-banner-visible {
    transform: translateY(0);
}

/* Position variants */
.slos-banner-bottom {
    bottom: 0;
}

.slos-banner-top {
    top: 0;
    bottom: auto;
    transform: translateY(-100%);
}

.slos-banner-top.slos-banner-visible {
    transform: translateY(0);
}

/* Theme variants */
.slos-banner-dark {
    background: #333;
    color: #fff;
}

.slos-banner-light {
    background: #fff;
    color: #333;
}

/* Banner Content */
.slos-banner-content {
    max-width: 1200px;
    margin: 0 auto;
}

.slos-banner-header h3 {
    margin: 0 0 10px;
    font-size: 18px;
    font-weight: 600;
}

.slos-banner-body p {
    margin: 0 0 15px;
    font-size: 14px;
    line-height: 1.6;
}

/* Consent Options */
.slos-consent-options {
    margin: 20px 0;
    padding: 15px;
    background: #f5f5f5;
    border-radius: 4px;
}

.slos-banner-dark .slos-consent-options {
    background: #444;
}

.slos-consent-option {
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #ddd;
}

.slos-banner-dark .slos-consent-option {
    border-bottom-color: #555;
}

.slos-consent-option:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.slos-consent-option label {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-weight: 500;
}

.slos-consent-checkbox {
    margin-right: 10px;
}

.slos-required {
    margin-left: 10px;
    font-size: 12px;
    color: #666;
}

.slos-purpose-description {
    margin: 5px 0 0 30px;
    font-size: 13px;
    color: #666;
}

.slos-banner-dark .slos-purpose-description {
    color: #aaa;
}

/* Banner Footer */
.slos-banner-footer {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
    margin-top: 20px;
}

/* Buttons */
.slos-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
}

.slos-btn-accept-all,
.slos-btn-accept,
.slos-btn-accept-selected {
    background: #4CAF50;
    color: white;
}

.slos-btn-accept-all:hover {
    background: #45a049;
}

.slos-btn-reject-all,
.slos-btn-reject {
    background: #f44336;
    color: white;
}

.slos-btn-reject-all:hover {
    background: #da190b;
}

.slos-btn-accept-selected {
    background: #2196F3;
}

.slos-btn-accept-selected:hover {
    background: #0b7dda;
}

/* Links */
.slos-privacy-link,
.slos-settings-link,
.slos-learn-more {
    color: #2196F3;
    text-decoration: none;
    font-size: 14px;
    margin-left: auto;
}

.slos-privacy-link:hover {
    text-decoration: underline;
}

/* Responsive */
@media (max-width: 768px) {
    #slos-consent-banner {
        padding: 15px;
    }

    .slos-banner-footer {
        flex-direction: column;
        align-items: stretch;
    }

    .slos-btn {
        width: 100%;
        margin-bottom: 5px;
    }

    .slos-privacy-link {
        margin: 10px 0 0;
        text-align: center;
    }
}

/* CCPA Template */
.slos-template-ccpa .slos-banner-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.slos-template-ccpa .slos-banner-body {
    flex: 1;
    margin-right: 20px;
}

.slos-template-ccpa .slos-banner-footer {
    margin-top: 0;
}

/* Simple Template */
.slos-template-simple .slos-banner-content {
    text-align: center;
}

.slos-template-simple .slos-banner-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin-top: 15px;
}
```

3. **Enqueue assets in main plugin**

Update `shahi-legalops-suite.php`:

```php
// Enqueue frontend banner
add_action( 'wp_enqueue_scripts', function() {
    if ( ! is_admin() ) {
        wp_enqueue_style(
            'slos-consent-banner',
            plugin_dir_url( __FILE__ ) . 'assets/css/consent-banner.css',
            [],
            '3.0.1'
        );

        wp_enqueue_script(
            'slos-consent-banner',
            plugin_dir_url( __FILE__ ) . 'assets/js/consent-banner.js',
            [],
            '3.0.1',
            true
        );

        wp_localize_script( 'slos-consent-banner', 'slosConsentConfig', [
            'apiUrl' => rest_url( 'slos/v1/consents' ),
            'userId' => get_current_user_id(),
            'template' => get_option( 'slos_banner_template', 'eu' ),
            'position' => get_option( 'slos_banner_position', 'bottom' ),
            'theme' => get_option( 'slos_banner_theme', 'light' ),
            'reloadOnConsent' => false,
        ] );
    }
} );
```

4. **Test banner**

```bash
# Visit site in incognito mode
# Should see consent banner

# Test accept all
# Click "Accept All" → banner should disappear

# Test localStorage
# Check browser console: localStorage.getItem('slos_consents')

# Test API call
# Check network tab for POST to /wp-json/slos/v1/consents/grant

# Verify database
wp db query "SELECT * FROM wp_slos_consent WHERE user_id=0 LIMIT 5"
```

OUTPUT STATE:
✅ Consent banner JavaScript (400+ lines)
✅ Consent banner CSS (responsive, animated)
✅ 4 banner templates (EU, CCPA, Simple, Advanced)
✅ Granular consent toggles
✅ API integration (grant consent)
✅ localStorage caching
✅ Responsive design

VERIFICATION:

1. **Check files:**
```bash
ls -la assets/js/consent-banner.js
ls -la assets/css/consent-banner.css
```

2. **Test banner shows:**
- Visit site in incognito mode
- Should see banner at bottom/top
- Banner should have animation

3. **Test accept all:**
- Click "Accept All"
- Banner should disappear
- Check console for API call
- Verify localStorage

4. **Test granular consent:**
- Uncheck some purposes
- Click "Accept Selected"
- Only checked purposes should be granted

5. **Test responsive:**
- Resize browser window
- Banner should adapt to mobile

SUCCESS CRITERIA:
✅ Banner displays on frontend
✅ 4 templates working
✅ Accept/reject working
✅ API calls successful
✅ localStorage caching
✅ Responsive design
✅ Animations smooth

ROLLBACK:
```bash
rm assets/js/consent-banner.js
rm assets/css/consent-banner.css
git checkout shahi-legalops-suite.php
```

TROUBLESHOOTING:

**Problem 1: Banner not showing**
- Check browser console for errors
- Verify scripts enqueued: View page source, search for "consent-banner"

**Problem 2: API calls failing**
- Check REST API working: `curl http://localhost/wp-json/slos/v1/consents/purposes`
- Check CORS headers
- Check user ID (0 for anonymous)

**Problem 3: CSS not loading**
- Hard refresh: Ctrl+F5
- Check file path in View Source

COMMIT MESSAGE:
```
feat(consent): Add consent banner component

- Create consent banner JavaScript (4 templates)
- Implement EU/GDPR banner with granular options
- Add CCPA opt-out banner
- Create simple and advanced templates
- Responsive CSS with animations
- API integration (grant/check consent)
- localStorage caching
- Theme support (light/dark)
- Position support (top/bottom)

Frontend consent collection ready.

Task: 2.4 (10-12 hours)
Next: Task 2.5 - Cookie Scanner
```

WHAT TO REPORT BACK:
"✅ TASK 2.4 COMPLETE

Created:
- consent-banner.js (400+ lines)
- consent-banner.css (responsive, animated)

Implemented:
- ✅ 4 banner templates (EU, CCPA, Simple, Advanced)
- ✅ Granular consent toggles
- ✅ Accept all/selected/none
- ✅ API integration
- ✅ localStorage caching
- ✅ Responsive design
- ✅ Smooth animations
- ✅ Theme support (light/dark)
- ✅ Position support (top/bottom)

Verification passed:
- ✅ Banner displays on frontend
- ✅ All templates working
- ✅ API calls successful
- ✅ Responsive on mobile
- ✅ Animations smooth

📍 Ready for TASK 2.5: [task-2.5-cookie-scanner.md](task-2.5-cookie-scanner.md)"
```

---

## ✅ COMPLETION CHECKLIST

- [ ] consent-banner.js created
- [ ] consent-banner.css created
- [ ] 4 templates implemented
- [ ] API integration working
- [ ] Banner displays on frontend
- [ ] Responsive design verified
- [ ] Committed to git
- [ ] Ready for Task 2.5

---

**Status:** ✅ Ready to execute  
**Time:** 10-12 hours  
**Next:** [task-2.5-cookie-scanner.md](task-2.5-cookie-scanner.md)
