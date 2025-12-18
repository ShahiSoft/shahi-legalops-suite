/**
 * Consent Geo Detection
 *
 * Detects user region and applies region-specific banner styling.
 *
 * @package ShahiLegalOpsSuite\Modules\Consent\Assets
 * @since 1.0.0
 */

(function() {
	'use strict';

	/**
	 * Apply region-specific CSS class to banner.
	 *
	 * Adds classes like:
	 * - banner-eu (for EU region)
	 * - banner-us-ca (for US-CA region)
	 * - banner-gdpr (for GDPR compliance mode)
	 * - banner-ccpa (for CCPA compliance mode)
	 */
	function applyRegionStyling() {
		// Wait for banner element to be ready.
		var bannerEl = document.getElementById('complyflow-banner');
		if (!bannerEl) {
			// Try again after a short delay.
			setTimeout(applyRegionStyling, 100);
			return;
		}

		// Get region from global data.
		var region = window.complyflowData && window.complyflowData.region || 'default';
		var mode = window.complyflowData && window.complyflowData.mode || 'default';

		// Apply region class.
		bannerEl.classList.add('banner-' + region.toLowerCase().replace(/-/g, '_'));

		// Apply mode class.
		if (mode) {
			bannerEl.classList.add('banner-' + mode.toLowerCase().replace(/-/g, '_'));
		}

		// Log region detection for debugging.
		if (window.complyflowDebug) {
			console.log('[Consent] Region detected:', region, 'Mode:', mode);
		}
	}

	/**
	 * Load region-specific CSS file.
	 *
	 * Loads additional CSS for region-specific styling.
	 */
	function loadRegionalStyles() {
		var region = window.complyflowData && window.complyflowData.region || 'default';
		var cssFile = 'consent-banner-' + region.toLowerCase().replace(/-/g, '_') + '.css';

		// Try to load regional CSS.
		fetch(window.complyflowData.pluginUrl + 'assets/css/' + cssFile)
			.then(function(response) {
				if (response.ok) {
					var link = document.createElement('link');
					link.rel = 'stylesheet';
					link.href = window.complyflowData.pluginUrl + 'assets/css/' + cssFile;
					link.type = 'text/css';
					document.head.appendChild(link);

					if (window.complyflowDebug) {
						console.log('[Consent] Loaded regional styles:', cssFile);
					}
				}
			})
			.catch(function(error) {
				// Regional CSS not found, which is fine - default styles will be used.
				if (window.complyflowDebug) {
					console.log('[Consent] Regional CSS not available:', cssFile);
				}
			});
	}

	/**
	 * Initialize geo detection when ready.
	 *
	 * Listens for the complyflow-ready event, then applies region styling.
	 */
	function init() {
		// If already ready, apply immediately.
		if (window.complyflowReady) {
			applyRegionStyling();
			loadRegionalStyles();
			return;
		}

		// Wait for complyflow-ready event.
		document.addEventListener('complyflow-ready', function() {
			applyRegionStyling();
			loadRegionalStyles();
		});

		// Timeout fallback in case event doesn't fire.
		setTimeout(function() {
			if (!window.complyflowReady) {
				applyRegionStyling();
				loadRegionalStyles();
			}
		}, 2000);
	}

	// Initialize when DOM is ready.
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
