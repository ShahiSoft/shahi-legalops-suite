/**
 * Consent Banner Component
 *
 * Displays consent banner and handles user interactions.
 * Alpine.js compatible (can be extended to React).
 *
 * @package ShahiLegalOpsSuite\Modules\Consent
 * @since 1.0.0
 */

(function() {
  'use strict';

  const config = window.complyflowConfig || {};
  const bannerConfig = config.banner || {};
  const API_URL = config.apiUrl || '/wp-json/complyflow/v1/consent';

  /**
   * Banner Controller Class
   */
  class ConsentBanner {
    constructor() {
      this.sessionId = config.sessionId || this.generateSessionId();
      this.isOpen = false;
      this.showPreferences = false;
      this.preferences = {};
      this.banner = null;
    }

    /**
     * Initialize banner.
     */
    init() {
      // Check if user already gave consent (in session/cookie).
      const stored = this.getStoredConsent();
      if (stored) {
        console.log('[Consent Banner] Consent already given, skipping banner');
        return;
      }

      // Inject banner HTML.
      this.render();

      // Attach event listeners.
      this.attachListeners();

      console.log('[Consent Banner] Initialized');
    }

    /**
     * Generate session ID.
     */
    generateSessionId() {
      // Simple UUID v4 implementation.
      return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        const r = (Math.random() * 16) | 0;
        const v = c === 'x' ? r : (r & 0x3) | 0x8;
        return v.toString(16);
      });
    }

    /**
     * Get stored consent from session/local storage.
     */
    getStoredConsent() {
      // Check localStorage first.
      const stored = localStorage.getItem(`complyflow_consent_${this.sessionId}`);
      if (stored) {
        try {
          return JSON.parse(stored);
        } catch (e) {
          console.warn('[Consent Banner] Invalid stored consent');
        }
      }
      return null;
    }

    /**
     * Store consent in localStorage.
     */
    storeConsent(consent) {
      localStorage.setItem(
        `complyflow_consent_${this.sessionId}`,
        JSON.stringify(consent)
      );
    }

    /**
     * Render banner HTML.
     */
    render() {
      const template = bannerConfig.template || 'top_bar';
      const position = bannerConfig.position || 'top';
      const html = this.getBannerTemplate();

      // Create container.
      const container = document.createElement('div');
      container.id = 'complyflow-banner-root';
      container.innerHTML = html;

      // Add to DOM.
      if ('top_bar' === template && 'top' === position) {
        document.body.insertBefore(container, document.body.firstChild);
      } else if ('bottom_bar' === template) {
        document.body.appendChild(container);
      } else {
        document.body.appendChild(container);
      }

      this.banner = container;
    }

    /**
     * Get banner HTML template.
     */
    getBannerTemplate() {
      const colors = bannerConfig.colors || {};
      const text = bannerConfig.text || {};
      const template = bannerConfig.template || 'top_bar';

      const bgColor = colors.background || '#ffffff';
      const textColor = colors.text || '#111827';
      const primaryColor = colors.primary || '#1f2937';
      const btnAccept = colors.button_accept || '#10b981';
      const btnReject = colors.button_reject || '#ef4444';
      const btnCustomize = colors.button_customize || '#3b82f6';

      const title = text.title || 'We use cookies';
      const description = text.description || 'We use cookies to enhance your experience.';
      const acceptLabel = text.accept_all || 'Accept All';
      const rejectLabel = text.reject_all || 'Reject All';
      const customizeLabel = text.customize || 'Customize';

      return `
        <div class="complyflow-banner" style="
          background-color: ${bgColor};
          color: ${textColor};
          border-top: 1px solid #e5e7eb;
          box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
          padding: 24px;
          position: fixed;
          bottom: 0;
          left: 0;
          right: 0;
          z-index: 999999;
          font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
          font-size: 14px;
          line-height: 1.5;
        ">
          <div style="max-width: 1200px; margin: 0 auto;">
            <h2 style="
              margin: 0 0 12px 0;
              font-size: 16px;
              font-weight: 600;
              color: ${primaryColor};
            ">${title}</h2>

            <p style="
              margin: 0 0 16px 0;
              color: ${textColor};
              opacity: 0.8;
            ">${description}</p>

            <div class="complyflow-banner-actions" style="
              display: flex;
              gap: 12px;
              align-items: center;
              flex-wrap: wrap;
            ">
              <button class="complyflow-btn-accept" data-action="accept-all" style="
                background-color: ${btnAccept};
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 6px;
                cursor: pointer;
                font-size: 14px;
                font-weight: 500;
                transition: opacity 0.2s;
              ">${acceptLabel}</button>

              <button class="complyflow-btn-reject" data-action="reject-all" style="
                background-color: ${btnReject};
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 6px;
                cursor: pointer;
                font-size: 14px;
                font-weight: 500;
                transition: opacity 0.2s;
              ">${rejectLabel}</button>

              <button class="complyflow-btn-customize" data-action="customize" style="
                background-color: transparent;
                color: ${btnCustomize};
                border: 1px solid ${btnCustomize};
                padding: 10px 20px;
                border-radius: 6px;
                cursor: pointer;
                font-size: 14px;
                font-weight: 500;
                transition: background-color 0.2s;
              ">${customizeLabel}</button>
            </div>
          </div>
        </div>

        <!-- Preferences Modal (initially hidden) -->
        <div class="complyflow-preferences-modal" style="
          display: none;
          position: fixed;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background-color: rgba(0,0,0,0.5);
          z-index: 999998;
          align-items: center;
          justify-content: center;
        ">
          <div style="
            background-color: ${bgColor};
            color: ${textColor};
            border-radius: 12px;
            padding: 32px;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 25px rgba(0,0,0,0.15);
          ">
            <h2 style="
              margin: 0 0 16px 0;
              font-size: 20px;
              font-weight: 700;
              color: ${primaryColor};
            ">Customize Preferences</h2>

            <div class="complyflow-categories" id="complyflow-categories">
              <!-- Categories will be injected here -->
            </div>

            <div style="
              display: flex;
              gap: 12px;
              margin-top: 24px;
              justify-content: flex-end;
            ">
              <button class="complyflow-btn-cancel" data-action="cancel" style="
                background-color: transparent;
                color: ${textColor};
                border: 1px solid #d1d5db;
                padding: 10px 20px;
                border-radius: 6px;
                cursor: pointer;
                font-size: 14px;
              ">Cancel</button>

              <button class="complyflow-btn-save" data-action="save-preferences" style="
                background-color: ${btnAccept};
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 6px;
                cursor: pointer;
                font-size: 14px;
                font-weight: 500;
              ">Save Preferences</button>
            </div>
          </div>
        </div>
      `;
    }

    /**
     * Attach event listeners to banner buttons.
     */
    attachListeners() {
      if (!this.banner) return;

      // Accept All button.
      const btnAccept = this.banner.querySelector('[data-action="accept-all"]');
      if (btnAccept) {
        btnAccept.addEventListener('click', () => this.acceptAll());
      }

      // Reject All button.
      const btnReject = this.banner.querySelector('[data-action="reject-all"]');
      if (btnReject) {
        btnReject.addEventListener('click', () => this.rejectAll());
      }

      // Customize button.
      const btnCustomize = this.banner.querySelector('[data-action="customize"]');
      if (btnCustomize) {
        btnCustomize.addEventListener('click', () => this.showCustomizeModal());
      }

      // Modal Save button.
      const btnSave = this.banner.querySelector('[data-action="save-preferences"]');
      if (btnSave) {
        btnSave.addEventListener('click', () => this.savePreferences());
      }

      // Modal Cancel button.
      const btnCancel = this.banner.querySelector('[data-action="cancel"]');
      if (btnCancel) {
        btnCancel.addEventListener('click', () => this.hideCustomizeModal());
      }
    }

    /**
     * Accept all consents.
     */
    acceptAll() {
      const preferences = {
        necessary: true,
        functional: true,
        analytics: true,
        marketing: true,
      };

      this.saveConsent(preferences);
    }

    /**
     * Reject all non-necessary consents.
     */
    rejectAll() {
      const preferences = {
        necessary: true,
        functional: false,
        analytics: false,
        marketing: false,
      };

      this.saveConsent(preferences);
    }

    /**
     * Show customize modal.
     */
    showCustomizeModal() {
      const modal = this.banner.querySelector('.complyflow-preferences-modal');
      if (modal) {
        modal.style.display = 'flex';
        this.renderCategories();
      }
    }

    /**
     * Hide customize modal.
     */
    hideCustomizeModal() {
      const modal = this.banner.querySelector('.complyflow-preferences-modal');
      if (modal) {
        modal.style.display = 'none';
      }
    }

    /**
     * Render category toggles in modal.
     */
    renderCategories() {
      const container = this.banner.querySelector('#complyflow-categories');
      if (!container) return;

      const categories = config.categories || [];
      let html = '';

      for (const cat of categories) {
        const disabled = cat.required ? 'disabled' : '';
        html += `
          <div style="
            padding: 16px 0;
            border-bottom: 1px solid #e5e7eb;
          ">
            <div style="
              display: flex;
              justify-content: space-between;
              align-items: center;
              margin-bottom: 8px;
            ">
              <label style="
                font-weight: 500;
                cursor: pointer;
              ">
                <input type="checkbox" name="category_${cat.id}" class="complyflow-category-toggle" 
                  ${cat.id === 'necessary' ? 'checked' : ''} ${disabled} style="margin-right: 8px;">
                ${cat.label}
              </label>
            </div>
            <p style="
              margin: 0;
              font-size: 13px;
              color: #6b7280;
            ">${cat.description || ''}</p>
          </div>
        `;
      }

      container.innerHTML = html;
    }

    /**
     * Save preferences from modal.
     */
    savePreferences() {
      const checkboxes = this.banner.querySelectorAll('.complyflow-category-toggle');
      const preferences = {
        necessary: true,
      };

      checkboxes.forEach((checkbox) => {
        const category = checkbox.name.replace('category_', '');
        preferences[category] = checkbox.checked;
      });

      this.saveConsent(preferences);
    }

    /**
     * Save consent to backend and update page.
     */
    saveConsent(preferences) {
      // Store locally.
      this.storeConsent(preferences);

      // Send to backend via REST API.
      fetch(API_URL + '/preferences', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          session_id: this.sessionId,
          region: config.region || 'US',
          categories: preferences,
          banner_version: config.bannerVersion || '1.0.0',
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          console.log('[Consent Banner] Consent saved', data);

          // Hide banner.
          this.hideBanner();

          // Emit signals.
          this.emitSignals(preferences);

          // Replay blocked scripts.
          if (typeof window.complyflowReplayScripts === 'function') {
            window.complyflowReplayScripts(preferences);
          }

          // Re-enable blocked iframes.
          if (typeof window.complyflowEnableIframes === 'function') {
            window.complyflowEnableIframes(preferences);
          }
        })
        .catch((err) => {
          console.error('[Consent Banner] Error saving consent:', err);
        });
    }

    /**
     * Hide banner after consent.
     */
    hideBanner() {
      if (this.banner) {
        this.banner.style.display = 'none';
      }
    }

    /**
     * Emit consent signals to analytics, GTM, etc.
     */
    emitSignals(preferences) {
      // Emit WP Consent API.
      for (const [category, granted] of Object.entries(preferences)) {
        document.dispatchEvent(
          new CustomEvent('wp_consent_category_set', {
            detail: { category, granted },
          })
        );
      }

      // Update GTM dataLayer.
      if (typeof window.dataLayer !== 'undefined') {
        window.dataLayer.push({
          event: 'consent_update',
          consent_analytics: preferences.analytics,
          consent_marketing: preferences.marketing,
          consent_functional: preferences.functional,
        });
      }

      // Update GCM via gtag.
      if (typeof window.gtag === 'function') {
        const gcmPayload = {
          analytics_storage: preferences.analytics ? 'granted' : 'denied',
          ad_storage: preferences.marketing ? 'granted' : 'denied',
          ad_user_data: preferences.marketing && preferences.functional ? 'granted' : 'denied',
          ad_personalization: preferences.marketing ? 'granted' : 'denied',
        };

        window.gtag('consent', 'update', gcmPayload);
      }
    }
  }

  /**
   * Initialize banner when DOM ready.
   */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      const banner = new ConsentBanner();
      banner.init();
      window.complyflowBanner = banner;
    });
  } else {
    const banner = new ConsentBanner();
    banner.init();
    window.complyflowBanner = banner;
  }
})();
