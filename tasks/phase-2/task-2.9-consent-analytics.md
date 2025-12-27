# TASK 2.9: Consent Analytics

**Phase:** 2 (Consent Management - ANALYTICS)  
**Effort:** 8-10 hours  
**Prerequisites:** Tasks 2.1–2.8 complete  
**Next Task:** [task-2.10-settings-page.md](task-2.10-settings-page.md)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
Implement Consent Analytics: aggregate metrics, trends, purpose breakdown, geo breakdown, device/browser stats.

DELIVER:
1) PHP analytics service `includes/Services/ConsentAnalyticsService.php`
2) REST endpoints `/wp-json/slos/v1/analytics/*` for aggregated data
3) Admin Analytics UI (separate page or tab) with charts
4) JS rendering for charts + filters (time range, purpose)
5) CSS styling

METRICS:
- Consent rate = granted / (granted + rejected) per period
- Withdrawal rate = withdrawn / granted per period
- Trends over time (daily points over range)
- Purpose breakdown (stacked counts by status)
- Geographic breakdown (top countries)
- Device/browser stats (from UA, if stored)

NOTES:
- Use efficient SQL; avoid heavy scans; aggregate with GROUP BY
- Time ranges: 7d, 30d, 90d
- Return JSON ready for plotting
```

---

## CONTEXT
Compliance teams require visibility into consent behavior trends and distributions. These analytics summarize key ratios and provide breakdowns to inform policies.

---

## INPUT STATE VERIFICATION
```bash
wp db query "SHOW TABLES LIKE 'wp_slos_consent'"
```

---

## COMPLETE CODE

### 1) PHP: ConsentAnalyticsService

Location: `includes/Services/ConsentAnalyticsService.php`

```php
<?php
namespace Shahi\LegalOps\Services;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ConsentAnalyticsService {
    protected $table;
    public function __construct(){
        global $wpdb; $this->table = $wpdb->prefix . 'slos_consent';
    }

    public function trend( $days = 30 ){
        global $wpdb; $days = max(1, min(90, intval($days)));
        $sql = $wpdb->prepare(
            "SELECT DATE(recorded_at) d,
                    SUM(status='granted') granted,
                    SUM(status='rejected') rejected,
                    SUM(status='withdrawn') withdrawn
             FROM {$this->table}
             WHERE recorded_at >= NOW() - INTERVAL %d DAY
             GROUP BY DATE(recorded_at)
             ORDER BY d ASC",
            $days
        );
        $rows = $wpdb->get_results( $sql, ARRAY_A );
        $out = [];
        foreach( $rows as $r ){
            $total = (int)$r['granted'] + (int)$r['rejected'];
            $consent_rate = $total>0 ? round( (int)$r['granted'] / $total, 4 ) : 0;
            $withdraw_rate = (int)$r['granted']>0 ? round( (int)$r['withdrawn'] / (int)$r['granted'], 4 ) : 0;
            $out[] = [
                'date' => $r['d'],
                'granted' => (int)$r['granted'],
                'rejected' => (int)$r['rejected'],
                'withdrawn'=> (int)$r['withdrawn'],
                'consent_rate' => $consent_rate,
                'withdraw_rate' => $withdraw_rate,
            ];
        }
        return $out;
    }

    public function purpose_breakdown( $days = 30 ){
        global $wpdb; $days = max(1, min(90, intval($days)));
        $sql = $wpdb->prepare(
            "SELECT purpose,
                    SUM(status='granted') granted,
                    SUM(status='rejected') rejected,
                    SUM(status='withdrawn') withdrawn
             FROM {$this->table}
             WHERE recorded_at >= NOW() - INTERVAL %d DAY
             GROUP BY purpose",
            $days
        );
        $rows = $wpdb->get_results( $sql, ARRAY_A );
        return array_map(function($r){
            return [
                'purpose' => $r['purpose'],
                'granted' => (int)$r['granted'],
                'rejected'=> (int)$r['rejected'],
                'withdrawn'=> (int)$r['withdrawn'],
            ];
        }, $rows );
    }

    public function geo_breakdown( $days = 30 ){
        global $wpdb; $days = max(1, min(90, intval($days)));
        $sql = $wpdb->prepare(
            "SELECT country_code, COUNT(*) c
             FROM {$this->table}
             WHERE recorded_at >= NOW() - INTERVAL %d DAY
             GROUP BY country_code
             ORDER BY c DESC
             LIMIT 20",
            $days
        );
        $rows = $wpdb->get_results( $sql, ARRAY_A );
        return array_map(function($r){ return ['country_code'=>$r['country_code'], 'count'=>(int)$r['c']]; }, $rows);
    }

    public function device_breakdown( $days = 30 ){
        global $wpdb; $days = max(1, min(90, intval($days)));
        $sql = $wpdb->prepare(
            "SELECT device, browser, COUNT(*) c
             FROM {$this->table}
             WHERE recorded_at >= NOW() - INTERVAL %d DAY
             GROUP BY device, browser
             ORDER BY c DESC
             LIMIT 20",
            $days
        );
        $rows = $wpdb->get_results( $sql, ARRAY_A );
        return array_map(function($r){ return ['device'=>$r['device'],'browser'=>$r['browser'],'count'=>(int)$r['c']]; }, $rows);
    }
}
```

### 2) REST Endpoints

Location: `includes/API/ConsentAnalyticsController.php`

```php
<?php
namespace Shahi\LegalOps\API;
use Shahi\LegalOps\Services\ConsentAnalyticsService;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ConsentAnalyticsController {
    public static function init(){ add_action( 'rest_api_init', [ __CLASS__, 'routes' ] ); }
    public static function routes(){
        register_rest_route( 'slos/v1', '/analytics/trend', [
            'methods' => 'GET',
            'permission_callback' => function(){ return current_user_can('manage_options'); },
            'callback' => function( $req ){ $days = intval($req->get_param('days') ?: 30); $s=new ConsentAnalyticsService(); return rest_ensure_response(['success'=>true,'data'=>$s->trend($days)]); }
        ] );
        register_rest_route( 'slos/v1', '/analytics/purpose', [
            'methods' => 'GET', 'permission_callback' => function(){ return current_user_can('manage_options'); },
            'callback' => function( $req ){ $days=intval($req->get_param('days') ?: 30); $s=new ConsentAnalyticsService(); return rest_ensure_response(['success'=>true,'data'=>$s->purpose_breakdown($days)]); }
        ] );
        register_rest_route( 'slos/v1', '/analytics/geo', [
            'methods' => 'GET', 'permission_callback' => function(){ return current_user_can('manage_options'); },
            'callback' => function( $req ){ $days=intval($req->get_param('days') ?: 30); $s=new ConsentAnalyticsService(); return rest_ensure_response(['success'=>true,'data'=>$s->geo_breakdown($days)]); }
        ] );
        register_rest_route( 'slos/v1', '/analytics/device', [
            'methods' => 'GET', 'permission_callback' => function(){ return current_user_can('manage_options'); },
            'callback' => function( $req ){ $days=intval($req->get_param('days') ?: 30); $s=new ConsentAnalyticsService(); return rest_ensure_response(['success'=>true,'data'=>$s->device_breakdown($days)]); }
        ] );
    }
}

\Shahi\LegalOps\API\ConsentAnalyticsController::init();
```

### 3) Admin UI Page

Location: `includes/Admin/ConsentAnalyticsPage.php`

```php
<?php
namespace Shahi\LegalOps\Admin;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ConsentAnalyticsPage {
    public static function init(){
        add_action( 'admin_menu', [ __CLASS__, 'menu' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'assets' ] );
    }
    public static function menu(){
        add_submenu_page( 'slos', 'Consent Analytics', 'Consent Analytics', 'manage_options', 'slos-consent-analytics', [ __CLASS__, 'render' ] );
    }
    public static function assets( $hook ){
        if( strpos($hook,'slos-consent-analytics')===false ) return;
        wp_enqueue_style( 'slos-admin-consent', plugin_dir_url(__FILE__) . '../../assets/css/admin-consent.css', [], '3.0.1' );
        wp_enqueue_script( 'slos-admin-analytics', plugin_dir_url(__FILE__) . '../../assets/js/admin-consent-analytics.js', ['jquery'], '3.0.1', true );
        wp_localize_script( 'slos-admin-analytics', 'slosAnalytics', [
            'trendUrl' => rest_url('slos/v1/analytics/trend'),
            'purposeUrl' => rest_url('slos/v1/analytics/purpose'),
            'geoUrl' => rest_url('slos/v1/analytics/geo'),
            'deviceUrl' => rest_url('slos/v1/analytics/device'),
            'nonce' => wp_create_nonce('wp_rest'),
        ] );
    }
    public static function render(){ ?>
        <div class="wrap slos-consent-dashboard">
            <h1>Consent Analytics</h1>
            <div class="filters" style="margin-bottom:12px">
                <label>Range
                    <select id="range-days"><option value="7">7d</option><option value="30" selected>30d</option><option value="90">90d</option></select>
                </label>
                <button class="button" id="refresh-analytics">Refresh</button>
            </div>
            <div class="chart-row">
                <div class="chart-card"><h3>Consent & Withdrawal Rates</h3><canvas id="chart-trend"></canvas></div>
                <div class="chart-card"><h3>Purpose Breakdown</h3><canvas id="chart-purpose"></canvas></div>
            </div>
            <div class="chart-row">
                <div class="chart-card"><h3>Top Countries</h3><canvas id="chart-geo"></canvas></div>
                <div class="chart-card"><h3>Devices/Browsers</h3><canvas id="chart-device"></canvas></div>
            </div>
        </div>
    <?php }
}

\Shahi\LegalOps\Admin\ConsentAnalyticsPage::init();
```

### 4) JS: Analytics Renderers

Location: `assets/js/admin-consent-analytics.js`

```javascript
(function($){
  'use strict';

  function drawDualLine(ctx, labels, a, b, colors){
    var w=ctx.canvas.width, h=ctx.canvas.height, g=ctx.getContext('2d');
    g.clearRect(0,0,w,h); var pad=30; var step=(w-pad*2)/(labels.length-1||1);
    var max = Math.max.apply(null,a.concat(b,[0.01])); var scale=(h-pad*2)/max;
    function plot(vals,color){ g.strokeStyle=color; g.lineWidth=2; g.beginPath();
      for(var i=0;i<labels.length;i++){ var x=pad+i*step, y=h-pad-vals[i]*scale; if(i===0) g.moveTo(x,y); else g.lineTo(x,y); }
      g.stroke(); }
    plot(a, colors[0]||'#4CAF50'); plot(b, colors[1]||'#f44336');
    g.fillStyle='#666'; g.font='10px sans-serif'; labels.forEach(function(l,i){ g.fillText(l, pad+i*step-8, h-10); });
  }

  function drawStackedBars(ctx, labels, stacks){
    var w=ctx.canvas.width,h=ctx.canvas.height,g=ctx.getContext('2d'); g.clearRect(0,0,w,h);
    var pad=30, bw=(w-pad*2)/labels.length*0.7, gap=(w-pad*2)/labels.length*0.3;
    var max=0; stacks.forEach(function(s){ var t=0; for(var k in s){ t+=s[k]; } max=Math.max(max,t); });
    var scale=(h-pad*2)/Math.max(1,max); var colors={'granted':'#4CAF50','rejected':'#f44336','withdrawn':'#2196F3'};
    labels.forEach(function(l,i){ var x=pad+i*(bw+gap), y=h-pad; ['granted','rejected','withdrawn'].forEach(function(k){ var v=stacks[i][k]||0; var hgt=v*scale; y-=hgt; g.fillStyle=colors[k]; g.fillRect(x,y,bw,hgt); }); g.fillStyle='#333'; g.fillText(l, x, h-10); });
  }

  function drawBars(ctx, labels, values, color){
    var w=ctx.canvas.width,h=ctx.canvas.height,g=ctx.getContext('2d'); g.clearRect(0,0,w,h);
    var pad=30,bw=(w-pad*2)/labels.length*0.6,gap=(w-pad*2)/labels.length*0.4; var max=Math.max.apply(null,values.concat([1])); var scale=(h-pad*2)/max;
    for(var i=0;i<labels.length;i++){ var x=pad+i*(bw+gap), y=h-pad-values[i]*scale; g.fillStyle=color||'#9C27B0'; g.fillRect(x,y,bw,values[i]*scale); g.fillStyle='#333'; g.fillText(labels[i]+': '+values[i], x, y-5); }
  }

  function fetch(url, days){ return $.ajax({url:url, headers:{'X-WP-Nonce': slosAnalytics.nonce}, data:{days:days}}); }

  function refresh(){
    var days = parseInt($('#range-days').val(),10)||30;
    $.when(
      fetch(slosAnalytics.trendUrl, days),
      fetch(slosAnalytics.purposeUrl, days),
      fetch(slosAnalytics.geoUrl, days),
      fetch(slosAnalytics.deviceUrl, days)
    ).done(function(trend,purpose,geo,device){
      var t = trend[0].data||[]; var labels = t.map(function(r){ return r.date; });
      var cr = t.map(function(r){ return +r.consent_rate; }); var wr = t.map(function(r){ return +r.withdraw_rate; });
      drawDualLine(document.getElementById('chart-trend').getContext('2d'), labels, cr, wr, ['#4CAF50','#f44336']);

      var p = purpose[0].data||[]; var pLabels = p.map(function(r){ return r.purpose; });
      var stacks = p.map(function(r){ return {granted:r.granted||0,rejected:r.rejected||0,withdrawn:r.withdrawn||0}; });
      drawStackedBars(document.getElementById('chart-purpose').getContext('2d'), pLabels, stacks);

      var g = geo[0].data||[]; drawBars(document.getElementById('chart-geo').getContext('2d'), g.map(function(r){return r.country_code;}), g.map(function(r){return r.count;}), '#FFC107');

      var d = device[0].data||[]; drawBars(document.getElementById('chart-device').getContext('2d'), d.map(function(r){return r.device+' '+r.browser;}), d.map(function(r){return r.count;}), '#03A9F4');
    });
  }

  $(function(){ $('#refresh-analytics').on('click', refresh); refresh(); });
})(jQuery);
```

### 5) CSS

Use `assets/css/admin-consent.css` from Task 2.8 (shared styles). Optionally add overrides:

```css
.chart-card canvas { width: 100%; height: 280px; }
```

### 6) Enqueue and Bootstrapping

Update `shahi-legalops-suite.php`:

```php
// require_once __DIR__ . '/includes/Services/ConsentAnalyticsService.php';
// require_once __DIR__ . '/includes/API/ConsentAnalyticsController.php';
// require_once __DIR__ . '/includes/Admin/ConsentAnalyticsPage.php';
```

---

## OUTPUT STATE
- ✅ Analytics service with aggregated queries
- ✅ REST endpoints for trend/purpose/geo/device
- ✅ Admin Analytics page with interactive charts
- ✅ Time range filter (7/30/90 days)

---

## VERIFICATION
```bash
# Trend API
curl -sS -H "X-WP-Nonce: $(wp nonce create wp_rest)" "http://localhost/wp-json/slos/v1/analytics/trend?days=30" | jq '.data[0]'

# Purpose breakdown
curl -sS -H "X-WP-Nonce: $(wp nonce create wp_rest)" "http://localhost/wp-json/slos/v1/analytics/purpose?days=30" | jq '.data[0]'

# Geo breakdown
curl -sS -H "X-WP-Nonce: $(wp nonce create wp_rest)" "http://localhost/wp-json/slos/v1/analytics/geo?days=30" | jq '.data[0]'

# Device breakdown
curl -sS -H "X-WP-Nonce: $(wp nonce create wp_rest)" "http://localhost/wp-json/slos/v1/analytics/device?days=30" | jq '.data[0]'
```

---

## SUCCESS CRITERIA
- Trend endpoint returns daily rows with consent and withdrawal rates.
- Purpose breakdown includes counts per status.
- Top countries endpoint returns up to 20 entries.
- Device/browser endpoint returns up to 20 entries.
- Admin page renders charts without console errors.

---

## ROLLBACK
```bash
# Remove analytics classes and routes (manual file cleanup if needed)
```

---

## TROUBLESHOOTING
- **401 REST errors:** Ensure you’re authenticated and nonce header is sent.
- **Charts empty:** Confirm data exists within selected range; try 90d.
- **Slow queries:** Add indexes on `recorded_at`, `purpose`, `status`, `country_code`.
- **Device/browser missing:** Ensure consent log captures `device` and `browser` fields when recording.

---

## COMMIT MESSAGE
```
feat(analytics): Add consent analytics service, REST endpoints, admin UI

- Aggregations for trend, purpose, geo, device
- REST routes under slos/v1
- Admin page with interactive charts

Task: 2.9 (8-10 hrs)
Next: 2.10 Settings Page
```

---

## WHAT TO REPORT BACK
"✅ TASK 2.9 COMPLETE
- Analytics service + endpoints
- Admin UI with charts
- Verified via curl and UI
📍 Ready for 2.10 Settings Page"

---

## ✅ COMPLETION CHECKLIST
- [ ] Service implemented
- [ ] REST routes exposed
- [ ] Admin page added
- [ ] Charts render and update
- [ ] Verified endpoints
- [ ] Commit prepared
- [ ] Proceed to 2.10
