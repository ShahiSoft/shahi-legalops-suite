# GLOBAL THEME ENFORCEMENT SCRIPT
# This removes ALL hard-coded colors and enforces Accessibility Scanner theme

$cssFiles = Get-ChildItem -Path 'assets/css' -Filter '*.css' -Exclude '*.min.css','admin-global.css','dsr-modern.css','accessibility-scanner/admin.css'

Write-Host "Found $($cssFiles.Count) CSS files to process..." -ForegroundColor Cyan

foreach ($file in $cssFiles) {
    Write-Host "
Processing: $($file.Name)" -ForegroundColor Yellow
    
    $content = Get-Content $file.FullName -Raw
    $originalLength = $content.Length
    
    # REMOVE duplicate :root blocks (keep only first one or remove all)
    # This prevents color redefinitions
    $content = $content -replace '(?s)(:root\s*\{[^}]*\})', ''
    
    # Replace ALL white/light backgrounds with dark theme
    $content = $content -replace 'background:\s*#fff(?:fff)?(?:\s|;|!)', 'background: var(--shahi-bg-card, #1e293b);'
    $content = $content -replace 'background:\s*white(?:\s|;|!)', 'background: var(--shahi-bg-card, #1e293b);'
    $content = $content -replace 'background:\s*#f9f9f9(?:\s|;|!)', 'background: var(--shahi-bg-secondary, #1e293b);'
    $content = $content -replace 'background:\s*#fafafa(?:\s|;|!)', 'background: var(--shahi-bg-secondary, #1e293b);'
    $content = $content -replace 'background:\s*#f[0-9a-f]{5}(?:\s|;|!)', 'background: var(--shahi-bg-secondary, #1e293b);'
    
    # Replace black text with white/gray text
    $content = $content -replace 'color:\s*#000(?:000)?(?:\s|;|!)', 'color: var(--shahi-text-primary, #ffffff);'
    $content = $content -replace 'color:\s*black(?:\s|;|!)', 'color: var(--shahi-text-primary, #ffffff);'
    $content = $content -replace 'color:\s*#333(?:333)?(?:\s|;|!)', 'color: var(--shahi-text-secondary, #cbd5e1);'
    $content = $content -replace 'color:\s*#666(?:666)?(?:\s|;|!)', 'color: var(--shahi-text-muted, #94a3b8);'
    
    # Replace light borders with dark borders
    $content = $content -replace 'border(-[^:]*)?:\s*([^;]*\s)?#[de][de][de][de][de][de]', 'border$1: $2var(--shahi-border, #334155)'
    $content = $content -replace 'border(-[^:]*)?:\s*([^;]*\s)?#[cd][cd][cd]', 'border$1: $2var(--shahi-border, #334155)'
    
    if ($content.Length -ne $originalLength) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
        Write-Host "  ✓ Updated $($file.Name)" -ForegroundColor Green
    } else {
        Write-Host "  - No changes needed" -ForegroundColor Gray
    }
}

Write-Host "

Done! Processed $($cssFiles.Count) files." -ForegroundColor Cyan
