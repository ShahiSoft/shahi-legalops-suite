# TASK 2.6: Script Blocker

**Phase:** 2 (Consent Management - CORE)  
**Effort:** 10-12 hours  
**Prerequisites:** TASK 2.4, 2.5 complete (Banner + Scanner)  
**Next Task:** [task-2.7-geolocation-detection.md](task-2.7-geolocation-detection.md)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 2.6 for the Shahi LegalOps Suite WordPress plugin.

CONTEXT:
Create a script blocker that prevents analytics, marketing, and advertising scripts from loading
until the user grants consent. This is crucial for GDPR/CCPA compliance. Block Google Analytics,
Google Tag Manager, Facebook Pixel, and other third-party tracking scripts until consent is given.
Align with /v3docs/modules/01-CONSENT-IMPLEMENTATION.md and WINNING-FEATURES-2026: support 20+
platform patterns (GA/GTAG/GTM, Ads, Meta, LinkedIn, TikTok, Hotjar, Drift, Intercom, Segment,
Mixpanel, Amplitude, Heap, UET, Reddit, Pinterest, Twitter/X), block cookies + local/session
storage, and trigger Google Consent Mode v2 + optional IAB TCF signals on unblock.

INPUT STATE (verify these exist):
✅ Consent banner (Task 2.4)
✅ Cookie scanner (Task 2.5)
✅ Consent Service and REST API

YOUR TASK:

1. **Create Script_Blocker Service**

Location: `includes/Services/Script_Blocker.php`

```php
<?php
/**
 * Script Blocker Service
 * Blocks scripts until consent is granted.
 *
 * @package Shahi\LegalOps
 * @since 3.0.0
 */

namespace Shahi\LegalOps\Services;

class Script_Blocker extends Base_Service {

    /**
     * Blocked scripts (by purpose)
     */
    private $blocked_scripts = [
        'analytics' => [
            'google-analytics.com/analytics.js',
            'googletagmanager.com/gtag/js',
            'googletagmanager.com/gtm.js',
            'google-analytics.com/ga.js',
            'stats.wp.com',
            'hotjar.com',
            'cdn.segment.com',
            'cdn.amplitude.com',
            'cdn.mxpnl.com',
            'cdn.heapanalytics.com',
            'cdn.jsdelivr.net/npm/heap-js',
        ],
        'marketing' => [
            'connect.facebook.net',
            'facebook.com/tr',
            'doubleclick.net',
            'ads.google.com',
            'snap.licdn.com',
            'analytics.tiktok.com',
            'static.ads-twitter.com',
            'px.ads.linkedin.com',
            'googleadservices.com',
            'bat.bing.com',
            'analytics.twitter.com',
        ],
        'advertising' => [
            'googlesyndication.com',
            'adservice.google.com',
            '__gads',
            'tags.tiqcdn.com',
            'ads.pinterest.com',
            'ads.reddit.com',
        ],
    ];

    /**
     * Initialize blocker
     */
    public function init() {
        // Block scripts before they load
        add_action( 'wp_enqueue_scripts', [ $this, 'block_scripts' ], 1 );
        add_action( 'wp_head', [ $this, 'inject_blocker_script' ], 1 );
        add_filter( 'script_loader_tag', [ $this, 'modify_script_tags' ], 10, 3 );
    }

    /**
     * Block scripts
     */
    public function block_scripts() {
        global $wp_scripts;

        if ( ! $wp_scripts instanceof \WP_Scripts ) {
            return;
        }

        // Get user consents
        $consents = $this->get_user_consents();

        // Check each enqueued script
        foreach ( $wp_scripts->queue as $handle ) {
            $script_src = $wp_scripts->registered[ $handle ]->src ?? '';
            
            $purpose = $this->get_script_purpose( $script_src );
            
            // Block if no consent
            if ( $purpose && ! isset( $consents[ $purpose ] ) ) {
                wp_dequeue_script( $handle );
                $this->log_blocked_script( $handle, $script_src, $purpose );
            }
        }
    }

    /**
     * Inject blocker script
     */
    public function inject_blocker_script() {
        ?>
        <script type="text/javascript">
        (function() {
            'use strict';

            // Consent storage
            const slosConsents = JSON.parse(localStorage.getItem('slos_consents') || '{}');

            // Block scripts
            const originalCreateElement = document.createElement.bind(document);
            document.createElement = function(tagName) {
                const element = originalCreateElement(tagName);
                
                if (tagName.toLowerCase() === 'script') {
                    // Intercept script creation
                    const originalSetAttribute = element.setAttribute.bind(element);
                    element.setAttribute = function(name, value) {
                        if (name === 'src' && shouldBlockScript(value)) {
                            // Change type to prevent execution
                            originalSetAttribute('type', 'text/plain');
                            originalSetAttribute('data-slos-src', value);
                            originalSetAttribute('data-slos-blocked', 'true');
                            return;
                        }
                        originalSetAttribute(name, value);
                    };
                }
                
                return element;
            };

            // Check if script should be blocked
            function shouldBlockScript(src) {
                const blockedPatterns = {
                    analytics: [
                        'google-analytics.com',
                        'googletagmanager.com',
                        'stats.wp.com'
                    ],
                    marketing: [
                        'facebook.com',
                        'connect.facebook.net',
                        'doubleclick.net'
                    ],
                    advertising: [
                        'googlesyndication.com',
                        'adservice.google.com'
                    ]
                };

                for (const [purpose, patterns] of Object.entries(blockedPatterns)) {
                    if (!slosConsents[purpose] || !slosConsents[purpose].granted) {
                        for (const pattern of patterns) {
                            if (src.includes(pattern)) {
                                return true;
                            }
                        }
                    }
                }

                return false;
            }

            // Unblock scripts when consent is granted
            document.addEventListener('slos-consent-updated', function(event) {
                const consents = event.detail.consents;
                
                // Find blocked scripts
                const blockedScripts = document.querySelectorAll('script[data-slos-blocked="true"]');
                
                blockedScripts.forEach(script => {
                    const src = script.getAttribute('data-slos-src');
                    
                    if (!shouldBlockScript(src)) {
                        // Unblock script
                        const newScript = document.createElement('script');
                        newScript.src = src;
                        newScript.type = 'text/javascript';
                        
                        // Copy attributes
                        Array.from(script.attributes).forEach(attr => {
                            if (!attr.name.startsWith('data-slos')) {
                                newScript.setAttribute(attr.name, attr.value);
                            }
                        });
                        
                        // Replace blocked script
                        script.parentNode.replaceChild(newScript, script);
                    }
                });

                // Update localStorage
                localStorage.setItem('slos_consents', JSON.stringify(consents));
            });
        })();
        </script>
        <?php
    }

    /**
     * Modify script tags
     *
     * @param string $tag Script tag.
     * @param string $handle Script handle.
     * @param string $src Script source.
     * @return string Modified tag
     */
    public function modify_script_tags( $tag, $handle, $src ) {
        // Get user consents
        $consents = $this->get_user_consents();

        // Check if script requires consent
        $purpose = $this->get_script_purpose( $src );
        
        if ( $purpose && ! isset( $consents[ $purpose ] ) ) {
            // Block script by changing type
            $tag = str_replace( "type='text/javascript'", "type='text/plain' data-slos-purpose='$purpose'", $tag );
            $tag = str_replace( 'type="text/javascript"', 'type="text/plain" data-slos-purpose="' . $purpose . '"', $tag );
            
            // Add blocked attribute
            $tag = str_replace( '<script', '<script data-slos-blocked="true"', $tag );
        }

        return $tag;
    }

    /**
     * Get script purpose
     *
     * @param string $src Script source.
     * @return string|false Purpose or false
     */
    private function get_script_purpose( $src ) {
        foreach ( $this->blocked_scripts as $purpose => $patterns ) {
            foreach ( $patterns as $pattern ) {
                if ( strpos( $src, $pattern ) !== false ) {
                    return $purpose;
                }
            }
        }

        return false;
    }

    /**
     * Get user consents
     *
     * @return array Consents
     */
    private function get_user_consents() {
        $user_id = get_current_user_id();
        
        // Check cache first
        $cache_key = 'slos_consents_' . $user_id;
        $consents = wp_cache_get( $cache_key );
        
        if ( false !== $consents ) {
            return $consents;
        }

        // Get from database
        $consent_service = new Consent_Service();
        $purposes = [ 'analytics', 'marketing', 'advertising', 'personalization' ];
        $consents = [];

        foreach ( $purposes as $purpose ) {
            if ( $consent_service->has_consent( $user_id, $purpose ) ) {
                $consents[ $purpose ] = true;
            }
        }

        // Cache for 5 minutes
        wp_cache_set( $cache_key, $consents, '', 300 );

        return $consents;
    }

    /**
     * Log blocked script
     *
     * @param string $handle Script handle.
     * @param string $src Script source.
     * @param string $purpose Purpose.
     */
    private function log_blocked_script( $handle, $src, $purpose ) {
        $this->log( sprintf(
            'Blocked script: %s (%s) - Purpose: %s',
            $handle,
            $src,
            $purpose
        ) );
    }

    /**
     * Add custom script with blocking
     *
     * @param string $script Script content.
     * @param string $purpose Purpose (analytics, marketing, etc.).
     */
    public function add_blocked_script( $script, $purpose = 'analytics' ) {
        add_action( 'wp_footer', function() use ( $script, $purpose ) {
            $consents = $this->get_user_consents();
            $type = isset( $consents[ $purpose ] ) ? 'text/javascript' : 'text/plain';
            
            echo sprintf(
                '<script type="%s" data-slos-purpose="%s" data-slos-blocked="%s">%s</script>',
                esc_attr( $type ),
                esc_attr( $purpose ),
                isset( $consents[ $purpose ] ) ? 'false' : 'true',
                $script
            );
        } );
    }

    /**
     * Block Google Analytics
     */
    public function block_google_analytics() {
        $this->add_blocked_script( "
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
            
            ga('create', 'UA-XXXXX-Y', 'auto');
            ga('send', 'pageview');
        ", 'analytics' );
    }

    /**
     * Block Google Tag Manager
     */
    public function block_google_tag_manager() {
        $gtm_id = get_option( 'slos_gtm_id', '' );
        
        if ( empty( $gtm_id ) ) {
            return;
        }

        $this->add_blocked_script( "
            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','{$gtm_id}');
        ", 'analytics' );
    }

    /**
     * Block Facebook Pixel
     */
    public function block_facebook_pixel() {
        $pixel_id = get_option( 'slos_fb_pixel_id', '' );
        
        if ( empty( $pixel_id ) ) {
            return;
        }

        $this->add_blocked_script( "
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{$pixel_id}');
            fbq('track', 'PageView');
        ", 'marketing' );
    }
}
```

2. **Initialize Script Blocker**

In `shahi-legalops-suite.php`:

```php
// Initialize script blocker
$script_blocker = new \Shahi\LegalOps\Services\Script_Blocker();
$script_blocker->init();

// Block common scripts
add_action( 'wp_head', function() use ( $script_blocker ) {
    $script_blocker->block_google_analytics();
    $script_blocker->block_google_tag_manager();
    $script_blocker->block_facebook_pixel();
}, 1 );
```

3. **Test script blocking**

```bash
# Visit site in incognito (no consent)
# Check page source for blocked scripts
# Should see: type="text/plain" data-slos-blocked="true"

# Grant consent
# Scripts should reload with type="text/javascript"

# Check network tab
# Blocked scripts should not fire until consent
```

4. **Create admin settings for tracking IDs**

Add to settings page:

```php
// Google Tag Manager ID
add_settings_field(
    'slos_gtm_id',
    'Google Tag Manager ID',
    function() {
        $value = get_option( 'slos_gtm_id', '' );
        echo '<input type="text" name="slos_gtm_id" value="' . esc_attr( $value ) . '" class="regular-text" placeholder="GTM-XXXXXXX">';
    },
    'slos_settings',
    'slos_tracking_section'
);

// Facebook Pixel ID
add_settings_field(
    'slos_fb_pixel_id',
    'Facebook Pixel ID',
    function() {
        $value = get_option( 'slos_fb_pixel_id', '' );
        echo '<input type="text" name="slos_fb_pixel_id" value="' . esc_attr( $value ) . '" class="regular-text" placeholder="123456789012345">';
    },
    'slos_settings',
    'slos_tracking_section'
);

register_setting( 'slos_settings', 'slos_gtm_id' );
register_setting( 'slos_settings', 'slos_fb_pixel_id' );
```

OUTPUT STATE:
✅ Script_Blocker service
✅ Scripts blocked until consent
✅ Google Analytics blocker
✅ Google Tag Manager blocker
✅ Facebook Pixel blocker
✅ JavaScript intercept mechanism
✅ Auto-unblock on consent

VERIFICATION:

1. **Check scripts blocked:**
```bash
# View page source (without consent)
# Search for: data-slos-blocked="true"
# Should find blocked scripts
```

2. **Grant consent and verify unblock:**
- Accept analytics consent
- Check network tab
- GA/GTM scripts should load

3. **Check console:**
- No errors from blocked scripts

SUCCESS CRITERIA:
✅ Scripts blocked without consent
✅ Scripts unblock with consent
✅ GA/GTM/FB Pixel integration works
✅ No console errors
✅ Page loads without tracking

ROLLBACK:
```bash
rm includes/Services/Script_Blocker.php
git checkout shahi-legalops-suite.php
```

TROUBLESHOOTING:

**Problem 1: Scripts still loading**
- Check consent storage: localStorage.getItem('slos_consents')
- Verify script blocker initialized

**Problem 2: Scripts not unblocking**
- Check 'slos-consent-updated' event fired
- Verify event listener registered

**Problem 3: GTM not loading**
- Check GTM ID in settings
- Verify ID format: GTM-XXXXXXX

COMMIT MESSAGE:
```
feat(consent): Add script blocker

- Create Script_Blocker service
- Block scripts until consent granted
- Intercept script creation (JavaScript)
- Block Google Analytics
- Block Google Tag Manager  
- Block Facebook Pixel
- Auto-unblock on consent
- Modify script tags (type=text/plain)
- Add admin settings for tracking IDs

GDPR/CCPA compliant script blocking ready.

Task: 2.6 (10-12 hours)
Next: Task 2.7 - Geolocation Detection
```

WHAT TO REPORT BACK:
"✅ TASK 2.6 COMPLETE

Created:
- Script_Blocker service

Implemented:
- ✅ Script blocking mechanism
- ✅ JavaScript intercept (createElement override)
- ✅ Google Analytics blocker
- ✅ Google Tag Manager blocker
- ✅ Facebook Pixel blocker
- ✅ Auto-unblock on consent
- ✅ Script tag modification
- ✅ Admin settings (GTM/FB IDs)

Verification passed:
- ✅ Scripts blocked without consent
- ✅ Scripts unblock with consent
- ✅ No console errors
- ✅ Tracking prevented correctly

📍 Ready for TASK 2.7: [task-2.7-geolocation-detection.md](task-2.7-geolocation-detection.md)"
```

---

## ✅ COMPLETION CHECKLIST

- [ ] Script_Blocker.php created
- [ ] Script blocker initialized
- [ ] GA/GTM/FB Pixel blocked
- [ ] Admin settings added
- [ ] Scripts unblock on consent
- [ ] Tested and verified
- [ ] Committed to git
- [ ] Ready for Task 2.7

---

**Status:** ✅ Ready to execute  
**Time:** 10-12 hours  
**Next:** [task-2.7-geolocation-detection.md](task-2.7-geolocation-detection.md)
