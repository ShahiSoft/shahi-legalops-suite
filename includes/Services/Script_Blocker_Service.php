<?php
/**
 * Script Blocker Service
 *
 * Blocks analytics/marketing/advertising scripts until consent is granted.
 * Intercepts enqueued scripts and DOM-inserted script tags.
 *
 * @package     ShahiLegalopsSuite
 * @subpackage  Services
 * @version     3.0.1
 */

namespace ShahiLegalopsSuite\Services;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Script_Blocker_Service extends Base_Service {

    /**
     * Patterns for blocked scripts by purpose
     * @var array
     */
    private $blocked_patterns = array(
        'analytics' => array(
            'google-analytics.com',
            'googletagmanager.com',
            'gtag/js',
            'ga.js',
            'stats.wp.com',
            'hotjar.com',
            'cdn.segment.com',
            'cdn.amplitude.com',
            'cdn.mxpnl.com',
            'cdn.heapanalytics.com',
        ),
        'marketing' => array(
            'connect.facebook.net',
            'facebook.com/tr',
            'doubleclick.net',
            'googleadservices.com',
            'px.ads.linkedin.com',
            'bat.bing.com',
            'static.ads-twitter.com',
        ),
        'advertising' => array(
            'googlesyndication.com',
            'adservice.google.com',
            'ads.pinterest.com',
            'ads.reddit.com',
        ),
    );

    /**
     * Initialize hooks
     */
    public function init(): void {
        add_action( 'wp_head', array( $this, 'inject_blocker_script' ), 1 );
        add_filter( 'script_loader_tag', array( $this, 'modify_script_tag' ), 10, 3 );
    }

    /**
     * Determine script purpose by src
     */
    public function get_script_purpose( string $src ): string {
        $src = strtolower( $src );
        foreach ( $this->blocked_patterns as $purpose => $patterns ) {
            foreach ( $patterns as $p ) {
                if ( false !== strpos( $src, strtolower( $p ) ) ) {
                    return $purpose;
                }
            }
        }
        return '';
    }

    /**
     * Modify script tags during enqueue to block execution when needed
     */
    public function modify_script_tag( string $tag, string $handle, string $src ): string {
        $purpose = $this->get_script_purpose( $src );
        if ( '' === $purpose ) {
            return $tag;
        }

        // Check consent from localStorage (client-side enforces; server sets marker)
        // Add data attributes so the client blocker can decide
        $attrs = sprintf( ' data-slos-purpose="%s"', esc_attr( $purpose ) );
        if ( false === strpos( $tag, 'data-slos-purpose' ) ) {
            $tag = str_replace( '<script ', '<script ' . $attrs . ' ', $tag );
        }
        return $tag;
    }

    /**
     * Inject blocker script in head to intercept dynamic script creation
     */
    public function inject_blocker_script(): void {
        ?>
<script id="slos-script-blocker" type="text/javascript">
(function(){
  'use strict';
  var consents = {};
  try {
    consents = JSON.parse(localStorage.getItem('slos_consents')||'{}');
  } catch(e) { consents = {}; }

  function hasConsent(purpose){
    var c = consents[purpose];
    return !!(c && (c.granted === true || c.granted === 1));
  }

  var blockedPatterns = {
    analytics: [
      'google-analytics.com', 'googletagmanager.com', 'gtag/js', 'ga.js', 'stats.wp.com',
      'hotjar.com', 'cdn.segment.com', 'cdn.amplitude.com', 'cdn.mxpnl.com', 'cdn.heapanalytics.com'
    ],
    marketing: [
      'connect.facebook.net', 'facebook.com/tr', 'doubleclick.net', 'googleadservices.com',
      'px.ads.linkedin.com', 'bat.bing.com', 'static.ads-twitter.com'
    ],
    advertising: [
      'googlesyndication.com', 'adservice.google.com', 'ads.pinterest.com', 'ads.reddit.com'
    ]
  };

  function purposeForSrc(src){
    var s = (src||'').toLowerCase();
    for(var purpose in blockedPatterns){
      var arr = blockedPatterns[purpose];
      for(var i=0;i<arr.length;i++){
        if(s.indexOf(arr[i].toLowerCase())!==-1){ return purpose; }
      }
    }
    return '';
  }

  // Intercept dynamic script creation
  var origCreate = document.createElement.bind(document);
  document.createElement = function(tag){
    var el = origCreate(tag);
    if((tag||'').toLowerCase()==='script'){
      var origSet = el.setAttribute.bind(el);
      el.setAttribute = function(name,value){
        if(name==='src'){
          var purpose = purposeForSrc(value||'');
          if(purpose && !hasConsent(purpose)){
            origSet('type','text/plain');
            origSet('data-slos-blocked','true');
            origSet('data-slos-src',value||'');
            origSet('data-slos-purpose',purpose);
          }
        }
        try { origSet(name,value); } catch(e){ /* ignore */ }
      };
    }
    return el;
  };

  // Unblock scripts when consent updates
  document.addEventListener('slos-consent-updated', function(e){
    try { consents = e.detail.consents || consents; } catch(err){}
    var blocked = document.querySelectorAll('script[data-slos-blocked="true"]');
    blocked.forEach(function(s){
      var purpose = s.getAttribute('data-slos-purpose');
      var src = s.getAttribute('data-slos-src');
      if(purpose && hasConsent(purpose)){
        var repl = document.createElement('script');
        repl.setAttribute('src', src);
        repl.setAttribute('async','');
        s.parentNode.insertBefore(repl, s.nextSibling);
        s.remove();
      }
    });
    // Optional: emit Google Consent Mode v2 update
    if(window.dataLayer){ window.dataLayer.push({event:'slos_consent_update'}); }
  });
})();
</script>
<?php
    }
}
