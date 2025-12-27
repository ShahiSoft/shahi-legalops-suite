# TASK 2.14: Consent Preferences UI

**Phase:** 2 (Consent Management - CORE)  
**Effort:** 6-8 hours  
**Prerequisites:** TASK 2.13 complete (i18n)  
**Next Task:** [task-2.15-integration-tests.md](task-2.15-integration-tests.md)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 2.14 for the Shahi LegalOps Suite WordPress plugin.

CONTEXT:
Create a user-facing preferences UI where visitors can review and update their consent choices,
see history, and download their consent data (GDPR). Provide shortcode and REST-backed React/JS
component that reads/writes via Consent REST API.

INPUT STATE (verify these exist):
✅ Consent REST API (Task 2.3)
✅ Banner, scanner, script blocker
✅ Multi-language support

YOUR TASK:

1) **Create shortcode + view**
- Shortcode: [slos_consent_preferences]
- Renders a container div for JS app

Location: `includes/Shortcodes/Consent_Preferences_Shortcode.php`

```php
<?php
namespace Shahi\LegalOps\Shortcodes;

class Consent_Preferences_Shortcode {
    public function init() {
        add_shortcode( 'slos_consent_preferences', [ $this, 'render' ] );
    }

    public function render() {
        wp_enqueue_script( 'slos-consent-preferences' );
        wp_enqueue_style( 'slos-consent-preferences' );
        return '<div id="slos-consent-preferences"></div>';
    }
}
```

2) **Create JS UI**

Location: `assets/js/consent-preferences.js`

Features:
- Fetch current consents: GET /consents/user/:id
- Display toggles per purpose
- Save updates via POST /consents/grant or /withdraw
- Show consent history (from logs endpoint if available)
- Button to download data (export endpoint)

```javascript
(function(){
    'use strict';

    class ConsentPreferences {
        constructor(){
            this.api = window.slosConsentApi || '/wp-json/slos/v1';
            this.userId = window.slosConsentUserId || 0;
            this.container = document.getElementById('slos-consent-preferences');
            this.state = { consents: {}, history: [] };
            this.init();
        }

        async init(){
            if(!this.container) return;
            await this.loadConsents();
            await this.loadHistory();
            this.render();
        }

        async loadConsents(){
            try {
                const res = await fetch(`${this.api}/consents/user/${this.userId}`);
                const json = await res.json();
                this.state.consents = json.data.consents || {};
            } catch(e){
                console.error('Failed to load consents', e);
            }
        }

        async loadHistory(){
            try {
                const res = await fetch(`${this.api}/consents/logs?user_id=${this.userId}`);
                const json = await res.json();
                this.state.history = json.data || [];
            } catch(e){
                this.state.history = [];
            }
        }

        render(){
            this.container.innerHTML = `
                <div class="slos-pref-card">
                    <h3>${this.t('Your Privacy Choices')}</h3>
                    <p>${this.t('Manage your consent preferences below.')}</p>

                    <div class="slos-pref-list">
                        ${this.renderPurpose('functional', true)}
                        ${this.renderPurpose('analytics')}
                        ${this.renderPurpose('marketing')}
                        ${this.renderPurpose('advertising')}
                        ${this.renderPurpose('personalization')}
                    </div>

                    <div class="slos-pref-actions">
                        <button class="slos-btn" data-action="save">${this.t('Save Preferences')}</button>
                        <button class="slos-btn ghost" data-action="download">${this.t('Download My Data')}</button>
                    </div>

                    <div class="slos-pref-history">
                        <h4>${this.t('History')}</h4>
                        <ul>
                            ${this.state.history.map(item => `<li>${item.created_at}: ${item.action} ${item.purpose}</li>`).join('')}
                        </ul>
                    </div>
                </div>
            `;

            this.bindEvents();
        }

        renderPurpose(purpose, required=false){
            const enabled = this.state.consents[purpose] === true;
            const disabledAttr = required ? 'disabled' : '';
            return `
                <label class="slos-pref-item">
                    <div>
                        <strong>${this.t(this.capitalize(purpose))}</strong>
                        ${required ? `<span class="req">${this.t('Required')}</span>` : ''}
                        <div class="hint">${this.t(this.getPurposeDescription(purpose))}</div>
                    </div>
                    <div>
                        <input type="checkbox" data-purpose="${purpose}" ${enabled ? 'checked' : ''} ${disabledAttr}>
                    </div>
                </label>
            `;
        }

        bindEvents(){
            const saveBtn = this.container.querySelector('[data-action="save"]');
            const downloadBtn = this.container.querySelector('[data-action="download"]');

            if (saveBtn) saveBtn.addEventListener('click', () => this.save());
            if (downloadBtn) downloadBtn.addEventListener('click', () => this.download());
        }

        async save(){
            const checkboxes = this.container.querySelectorAll('input[type="checkbox"][data-purpose]');
            for (const cb of checkboxes){
                const purpose = cb.dataset.purpose;
                if (cb.checked){
                    await this.grant(purpose);
                } else {
                    await this.withdraw(purpose);
                }
            }
            alert(this.t('Preferences saved'));
        }

        async grant(purpose){
            await fetch(`${this.api}/consents/grant`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: this.userId, purpose })
            });
        }

        async withdraw(purpose){
            await fetch(`${this.api}/consents/withdraw`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: this.userId, purpose })
            });
        }

        async download(){
            const res = await fetch(`${this.api}/consents/export/${this.userId}`);
            const json = await res.json();
            const blob = new Blob([json.data || ''], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'consents.txt';
            a.click();
            URL.revokeObjectURL(url);
        }

        t(str){
            return (window.slosConsentI18n && window.slosConsentI18n[str]) || str;
        }

        capitalize(str){ return str.charAt(0).toUpperCase() + str.slice(1); }

        getPurposeDescription(purpose){
            const desc = {
                functional: 'Required for site operation',
                analytics: 'Helps us improve the site',
                marketing: 'Used for marketing communications',
                advertising: 'Used for personalized ads',
                personalization: 'Remembers your preferences'
            };
            return desc[purpose] || '';
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => new ConsentPreferences());
    } else {
        new ConsentPreferences();
    }
})();
```

3) **CSS for preferences UI**

Location: `assets/css/consent-preferences.css`

```css
#slos-consent-preferences { font-family: Arial, sans-serif; }
.slos-pref-card { border: 1px solid #ddd; padding: 20px; border-radius: 8px; background: #fff; }
.slos-pref-list { display: flex; flex-direction: column; gap: 12px; margin: 15px 0; }
.slos-pref-item { display: flex; justify-content: space-between; align-items: center; padding: 12px; border: 1px solid #eee; border-radius: 6px; }
.slos-pref-item .hint { color: #666; font-size: 13px; }
.slos-pref-item .req { background: #eee; padding: 2px 6px; border-radius: 4px; font-size: 12px; margin-left: 6px; }
.slos-pref-actions { display: flex; gap: 10px; }
.slos-btn { background: #4CAF50; color: #fff; border: none; padding: 10px 16px; border-radius: 4px; cursor: pointer; }
.slos-btn.ghost { background: #fff; color: #333; border: 1px solid #ccc; }
.slos-pref-history ul { list-style: disc; padding-left: 20px; }
@media (max-width: 640px){ .slos-pref-item { flex-direction: column; align-items: flex-start; gap: 6px; } }
```

4) **Enqueue assets**
- In main plugin enqueue: register `consent-preferences.js` + `consent-preferences.css`
- Localize with `slosConsentApi`, `slosConsentUserId`, `slosConsentI18n`

5) **REST support**
- Ensure routes used: /consents/user/:id, /consents/grant, /consents/withdraw, /consents/logs, /consents/export/:id

6) **Tests**

```bash
# Render shortcode
wp eval "echo do_shortcode('[slos_consent_preferences]');"

# Visit page containing shortcode and toggle purposes

# Check network calls for grant/withdraw

# Download data
# Should trigger export endpoint
```

OUTPUT STATE:
✅ Shortcode for preferences
✅ JS app for managing consents
✅ History display
✅ Data download
✅ Responsive styles

SUCCESS CRITERIA:
✅ Users can view/update consents
✅ History visible
✅ Download works
✅ Localized strings

ROLLBACK:
```bash
rm includes/Shortcodes/Consent_Preferences_Shortcode.php
rm assets/js/consent-preferences.js
rm assets/css/consent-preferences.css
```

TROUBLESHOOTING:
- Buttons do nothing: check JS enqueued
- API 403: ensure endpoints public or user logged in as needed
- History empty: ensure logs exist

COMMIT MESSAGE:
```
feat(consent): Add preferences UI

- Shortcode [slos_consent_preferences]
- JS UI for managing consent toggles
- History display from audit logs
- Download my data button
- Responsive styling

Task: 2.14 (6-8 hours)
Next: Task 2.15 - Integration Tests
```

WHAT TO REPORT BACK:
"✅ TASK 2.14 COMPLETE
- Shortcode + JS UI
- Toggle consents
- History display
- Data download
"
```

---

## ✅ COMPLETION CHECKLIST

- [ ] Shortcode created
- [ ] JS + CSS created
- [ ] Localized strings wired
- [ ] API calls verified
- [ ] Responsive verified
- [ ] Committed to git
- [ ] Ready for Task 2.15

---

**Status:** ✅ Ready to execute  
**Time:** 6-8 hours  
**Next:** [task-2.15-integration-tests.md](task-2.15-integration-tests.md)
