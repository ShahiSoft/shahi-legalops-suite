/**
 * Consent Banner JavaScript
 *
 * @package ShahiPrivacyShield
 */

(function($) {
	'use strict';

	const ShahiConsentBanner = {
		/**
		 * Initialize consent banner
		 */
		init: function() {
			this.bindEvents();
		},

		/**
		 * Bind event handlers
		 */
		bindEvents: function() {
			const self = this;

			// Accept all button
			$(document).on('click', '[data-action="accept-all"]', function(e) {
				e.preventDefault();
				self.acceptAll();
			});

			// Accept selected button
			$(document).on('click', '[data-action="accept-selected"]', function(e) {
				e.preventDefault();
				self.acceptSelected();
			});

			// Reject all button
			$(document).on('click', '[data-action="reject-all"]', function(e) {
				e.preventDefault();
				self.rejectAll();
			});

			// Close button
			$(document).on('click', '.shahi-consent-close', function(e) {
				e.preventDefault();
				self.closeBanner();
			});

			// Overlay click
			$(document).on('click', '.shahi-consent-overlay', function(e) {
				// Don't close on overlay click to ensure user makes a choice
			});

			// Manage preferences link
			$(document).on('click', '.shahi-consent-manage', function(e) {
				e.preventDefault();
				// Expand options if collapsed
			});
		},

		/**
		 * Accept all cookies
		 */
		acceptAll: function() {
			const consents = {
				necessary: true,
				analytics: true,
				marketing: true,
				preferences: true
			};

			this.saveConsent(consents);
		},

		/**
		 * Accept selected cookies
		 */
		acceptSelected: function() {
			const consents = {};

			$('.shahi-consent-option input[type="checkbox"]').each(function() {
				const name = $(this).attr('name').replace('consent_', '');
				consents[name] = $(this).is(':checked');
			});

			this.saveConsent(consents);
		},

		/**
		 * Reject all cookies (except necessary)
		 */
		rejectAll: function() {
			const consents = {
				necessary: true,
				analytics: false,
				marketing: false,
				preferences: false
			};

			this.saveConsent(consents);
		},

		/**
		 * Save consent preferences
		 *
		 * @param {Object} consents Consent preferences
		 */
		saveConsent: function(consents) {
			const self = this;

			$.ajax({
				url: shahiPrivacyShieldConsent.ajaxUrl,
				type: 'POST',
				data: {
					action: 'shahi_privacy_shield_save_consent',
					nonce: shahiPrivacyShieldConsent.nonce,
					consents: consents
				},
				beforeSend: function() {
					self.showLoading();
				},
				success: function(response) {
					if (response.success) {
						self.closeBanner();
						
						// Store consent in localStorage for quick access
						localStorage.setItem('shahi_privacy_shield_consent', JSON.stringify(consents));

						// Reload page to apply consent preferences
						setTimeout(function() {
							window.location.reload();
						}, 500);
					} else {
						self.showError(response.data || 'Failed to save consent preferences');
					}
				},
				error: function() {
					self.showError('Network error. Please try again.');
				},
				complete: function() {
					self.hideLoading();
				}
			});
		},

		/**
		 * Close consent banner
		 */
		closeBanner: function() {
			$('#shahi-privacy-shield-consent-banner').fadeOut(300);
		},

		/**
		 * Show loading state
		 */
		showLoading: function() {
			$('.shahi-consent-btn').prop('disabled', true).css('opacity', 0.6);
		},

		/**
		 * Hide loading state
		 */
		hideLoading: function() {
			$('.shahi-consent-btn').prop('disabled', false).css('opacity', 1);
		},

		/**
		 * Show error message
		 *
		 * @param {string} message Error message
		 */
		showError: function(message) {
			// Simple alert for now - could be improved with a custom modal
			alert(message);
		},

		/**
		 * Check if consent given for specific type
		 *
		 * @param {string} type Consent type
		 * @return {boolean} Has consent
		 */
		hasConsentFor: function(type) {
			// Check cookie first
			const cookieConsent = this.getCookieConsent();
			if (cookieConsent && typeof cookieConsent[type] !== 'undefined') {
				return cookieConsent[type];
			}

			// Check localStorage
			const localConsent = localStorage.getItem('shahi_privacy_shield_consent');
			if (localConsent) {
				try {
					const consent = JSON.parse(localConsent);
					return consent[type] || false;
				} catch (e) {
					return false;
				}
			}

			return false;
		},

		/**
		 * Get consent from cookie
		 *
		 * @return {Object|null} Consent object or null
		 */
		getCookieConsent: function() {
			const name = 'shahi_privacy_shield_consent=';
			const decodedCookie = decodeURIComponent(document.cookie);
			const ca = decodedCookie.split(';');

			for (let i = 0; i < ca.length; i++) {
				let c = ca[i];
				while (c.charAt(0) === ' ') {
					c = c.substring(1);
				}
				if (c.indexOf(name) === 0) {
					try {
						return JSON.parse(c.substring(name.length, c.length));
					} catch (e) {
						return null;
					}
				}
			}
			return null;
		}
	};

	// Initialize when document is ready
	$(document).ready(function() {
		ShahiConsentBanner.init();

		// Expose to global scope for external use
		window.ShahiConsentBanner = ShahiConsentBanner;
	});

})(jQuery);
