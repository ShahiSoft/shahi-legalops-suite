# Test PDF Generation for Legal Documents
# This script tests the PDF generation functionality added in Task 4.8

Write-Host "======================================" -ForegroundColor Cyan
Write-Host "Legal Document PDF Generation Test" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""

# Configuration
$baseUrl = "http://localhost"
# Note: Admin credentials would be required for authenticated endpoints

# Function to test AJAX PDF download
function Test-AjaxPdfDownload {
    param(
        [int]$docId,
        [string]$nonce
    )
    
    Write-Host "Testing AJAX PDF Download for document ID: $docId" -ForegroundColor Yellow
    
    $url = "$baseUrl/wp-admin/admin-ajax.php?action=slos_download_pdf&doc_id=$docId&nonce=$nonce"
    
    try {
        $response = Invoke-WebRequest -Uri $url -Method Get -UseBasicParsing
        
        if ($response.StatusCode -eq 200 -and $response.Headers.'Content-Type' -like 'application/pdf*') {
            Write-Host "✓ AJAX PDF download successful" -ForegroundColor Green
            Write-Host "  Content-Type: $($response.Headers.'Content-Type')" -ForegroundColor Gray
            Write-Host "  Content-Length: $($response.Headers.'Content-Length') bytes" -ForegroundColor Gray
            return $true
        } else {
            Write-Host "✗ Unexpected response" -ForegroundColor Red
            Write-Host "  Status: $($response.StatusCode)" -ForegroundColor Red
            Write-Host "  Content-Type: $($response.Headers.'Content-Type')" -ForegroundColor Red
            return $false
        }
    } catch {
        Write-Host "✗ Request failed: $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

# Function to test REST API PDF endpoint
function Test-RestApiPdf {
    param(
        [int]$docId
    )
    
    Write-Host ""
    Write-Host "Testing REST API PDF Endpoint for document ID: $docId" -ForegroundColor Yellow
    
    $url = "$baseUrl/wp-json/slos/v1/legaldocs/$docId/pdf"
    
    try {
        $response = Invoke-WebRequest -Uri $url -Method Get -UseBasicParsing
        
        if ($response.StatusCode -eq 200 -and $response.Headers.'Content-Type' -like 'application/pdf*') {
            Write-Host "✓ REST API PDF download successful" -ForegroundColor Green
            Write-Host "  Content-Type: $($response.Headers.'Content-Type')" -ForegroundColor Gray
            Write-Host "  Content-Length: $($response.Headers.'Content-Length') bytes" -ForegroundColor Gray
            return $true
        } else {
            Write-Host "✗ Unexpected response" -ForegroundColor Red
            Write-Host "  Status: $($response.StatusCode)" -ForegroundColor Red
            Write-Host "  Content-Type: $($response.Headers.'Content-Type')" -ForegroundColor Red
            return $false
        }
    } catch {
        Write-Host "✗ Request failed: $($_.Exception.Message)" -ForegroundColor Red
        if ($_.Exception.Response) {
            $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
            $responseBody = $reader.ReadToEnd()
            Write-Host "  Response: $responseBody" -ForegroundColor Red
        }
        return $false
    }
}

# Function to verify PDF file structure
function Test-PdfStructure {
    param(
        [byte[]]$pdfData
    )
    
    Write-Host ""
    Write-Host "Verifying PDF Structure..." -ForegroundColor Yellow
    
    # Check for PDF header
    $header = [System.Text.Encoding]::ASCII.GetString($pdfData[0..4])
    if ($header -eq '%PDF-') {
        Write-Host "✓ Valid PDF header found" -ForegroundColor Green
        
        # Extract PDF version
        $version = [System.Text.Encoding]::ASCII.GetString($pdfData[5..7])
        Write-Host "  PDF Version: $version" -ForegroundColor Gray
        return $true
    } else {
        Write-Host "✗ Invalid PDF header" -ForegroundColor Red
        Write-Host "  Expected: %PDF-, Got: $header" -ForegroundColor Red
        return $false
    }
}

# Main test execution
Write-Host "Prerequisites:" -ForegroundColor Cyan
Write-Host "1. WordPress site running at: $baseUrl" -ForegroundColor White
Write-Host "2. Legal Docs module enabled" -ForegroundColor White
Write-Host "3. At least one published legal document exists" -ForegroundColor White
Write-Host "4. DOMPDF library installed via composer" -ForegroundColor White
Write-Host ""

$docId = Read-Host "Enter a published document ID to test (or press Enter to skip manual tests)"

if ($docId) {
    Write-Host ""
    Write-Host "=== Manual Test Instructions ===" -ForegroundColor Cyan
    Write-Host ""
    
    Write-Host "Test 1: Admin AJAX Download" -ForegroundColor Yellow
    Write-Host "1. Log into WordPress admin" -ForegroundColor White
    Write-Host "2. Navigate to Legal Docs list" -ForegroundColor White
    Write-Host "3. Find document ID $docId" -ForegroundColor White
    Write-Host "4. Click 'Download PDF' action link" -ForegroundColor White
    Write-Host "5. Verify PDF downloads with correct filename and content" -ForegroundColor White
    Write-Host ""
    
    Write-Host "Test 2: Editor Download Button" -ForegroundColor Yellow
    Write-Host "1. Edit document ID $docId" -ForegroundColor White
    Write-Host "2. Click 'Download PDF' button (visible only for published docs)" -ForegroundColor White
    Write-Host "3. Verify PDF downloads correctly" -ForegroundColor White
    Write-Host ""
    
    Write-Host "Test 3: REST API Endpoint" -ForegroundColor Yellow
    Write-Host "1. Visit: $baseUrl/wp-json/slos/v1/legaldocs/$docId/pdf" -ForegroundColor White
    Write-Host "2. Verify PDF displays or downloads" -ForegroundColor White
    Write-Host "3. Test with ?download=false for inline display" -ForegroundColor White
    Write-Host ""
    
    Write-Host "Test 4: PDF Content Verification" -ForegroundColor Yellow
    Write-Host "1. Open downloaded PDF" -ForegroundColor White
    Write-Host "2. Verify header with logo and site name" -ForegroundColor White
    Write-Host "3. Verify document title and metadata" -ForegroundColor White
    Write-Host "4. Verify table of contents (if document > 500 words)" -ForegroundColor White
    Write-Host "5. Verify content formatting (headings, lists, tables)" -ForegroundColor White
    Write-Host "6. Verify footer with page numbers" -ForegroundColor White
    Write-Host ""
    
    Write-Host "Test 5: Cache Verification" -ForegroundColor Yellow
    Write-Host "1. Download PDF twice for same document" -ForegroundColor White
    Write-Host "2. Second download should be faster (cached)" -ForegroundColor White
    Write-Host "3. Publish new version of document" -ForegroundColor White
    Write-Host "4. Download PDF again - should reflect new content" -ForegroundColor White
    Write-Host ""
    
    Write-Host "Test 6: Permission Checks" -ForegroundColor Yellow
    Write-Host "1. Log out or use incognito mode" -ForegroundColor White
    Write-Host "2. Try accessing REST endpoint for published doc - should work" -ForegroundColor White
    Write-Host "3. Try accessing AJAX endpoint without login - should fail" -ForegroundColor White
    Write-Host "4. Try downloading draft document as non-admin - should fail" -ForegroundColor White
}

Write-Host ""
Write-Host "=== Automated Checks ===" -ForegroundColor Cyan
Write-Host ""

# Check if composer.json includes DOMPDF
Write-Host "Checking composer.json for DOMPDF..." -ForegroundColor Yellow
$composerPath = Join-Path $PSScriptRoot "..\composer.json"
if (Test-Path $composerPath) {
    $composer = Get-Content $composerPath -Raw | ConvertFrom-Json
    if ($composer.require.'dompdf/dompdf') {
        Write-Host "✓ DOMPDF dependency found: $($composer.require.'dompdf/dompdf')" -ForegroundColor Green
    } else {
        Write-Host "✗ DOMPDF not found in composer.json" -ForegroundColor Red
    }
} else {
    Write-Host "✗ composer.json not found" -ForegroundColor Red
}

# Check if PDF_Generator class exists
Write-Host ""
Write-Host "Checking PDF_Generator service class..." -ForegroundColor Yellow
$pdfGenPath = Join-Path $PSScriptRoot "..\includes\Services\PDF_Generator.php"
if (Test-Path $pdfGenPath) {
    Write-Host "✓ PDF_Generator.php exists" -ForegroundColor Green
    
    $content = Get-Content $pdfGenPath -Raw
    $checks = @(
        @{ Pattern = 'class PDF_Generator'; Name = 'Class definition' },
        @{ Pattern = 'function generate_pdf'; Name = 'generate_pdf method' },
        @{ Pattern = 'function stream_pdf'; Name = 'stream_pdf method' },
        @{ Pattern = 'function clear_cache'; Name = 'clear_cache method' },
        @{ Pattern = 'function get_cache_key'; Name = 'Cache key generation' },
        @{ Pattern = 'function generate_toc'; Name = 'Table of contents' },
        @{ Pattern = 'function build_header'; Name = 'Header generation' },
        @{ Pattern = 'function build_footer'; Name = 'Footer generation' }
    )
    
    foreach ($check in $checks) {
        if ($content -match $check.Pattern) {
            Write-Host "  ✓ $($check.Name)" -ForegroundColor Green
        } else {
            Write-Host "  ✗ $($check.Name) not found" -ForegroundColor Red
        }
    }
} else {
    Write-Host "✗ PDF_Generator.php not found" -ForegroundColor Red
}

# Check if PDF styles CSS exists
Write-Host ""
Write-Host "Checking PDF styles CSS..." -ForegroundColor Yellow
$cssPath = Join-Path $PSScriptRoot "..\assets\css\pdf-styles.css"
if (Test-Path $cssPath) {
    Write-Host "✓ pdf-styles.css exists" -ForegroundColor Green
    
    $cssContent = Get-Content $cssPath -Raw
    $cssSize = (Get-Item $cssPath).Length
    Write-Host "  File size: $cssSize bytes" -ForegroundColor Gray
    
    $cssChecks = @(
        '.pdf-header',
        '.pdf-footer',
        '.pdf-toc',
        '.pdf-content',
        '@page'
    )
    
    foreach ($selector in $cssChecks) {
        if ($cssContent -match [regex]::Escape($selector)) {
            Write-Host "  ✓ $selector defined" -ForegroundColor Green
        } else {
            Write-Host "  ✗ $selector not found" -ForegroundColor Red
        }
    }
} else {
    Write-Host "✗ pdf-styles.css not found" -ForegroundColor Red
}

# Check if REST endpoint is registered
Write-Host ""
Write-Host "Checking REST API controller..." -ForegroundColor Yellow
$controllerPath = Join-Path $PSScriptRoot "..\includes\API\Legal_Doc_REST_Controller.php"
if (Test-Path $controllerPath) {
    $controllerContent = Get-Content $controllerPath -Raw
    
    if ($controllerContent -match '/pdf') {
        Write-Host "✓ PDF endpoint registered" -ForegroundColor Green
        
        if ($controllerContent -match 'function get_pdf') {
            Write-Host "  ✓ get_pdf handler found" -ForegroundColor Green
        }
        
        if ($controllerContent -match 'function get_pdf_permissions_check') {
            Write-Host "  ✓ Permission check found" -ForegroundColor Green
        }
    } else {
        Write-Host "✗ PDF endpoint not found" -ForegroundColor Red
    }
} else {
    Write-Host "✗ Legal_Doc_REST_Controller.php not found" -ForegroundColor Red
}

# Check if AJAX handler exists
Write-Host ""
Write-Host "Checking AJAX download handler..." -ForegroundColor Yellow
$actionsPath = Join-Path $PSScriptRoot "..\includes\Admin\Legal_Doc_Actions.php"
if (Test-Path $actionsPath) {
    $actionsContent = Get-Content $actionsPath -Raw
    
    if ($actionsContent -match 'slos_download_pdf') {
        Write-Host "✓ AJAX download handler found" -ForegroundColor Green
        
        if ($actionsContent -match 'function ajax_download_pdf') {
            Write-Host "  ✓ ajax_download_pdf method found" -ForegroundColor Green
        }
    } else {
        Write-Host "✗ AJAX download handler not found" -ForegroundColor Red
    }
} else {
    Write-Host "✗ Legal_Doc_Actions.php not found" -ForegroundColor Red
}

# Check if download buttons added to UI
Write-Host ""
Write-Host "Checking UI integration..." -ForegroundColor Yellow

# Check List Table
$listTablePath = Join-Path $PSScriptRoot "..\includes\Admin\Legal_Doc_List_Table.php"
if (Test-Path $listTablePath) {
    $listTableContent = Get-Content $listTablePath -Raw
    
    if ($listTableContent -match 'download_pdf') {
        Write-Host "✓ Download button added to List Table" -ForegroundColor Green
    } else {
        Write-Host "✗ Download button not found in List Table" -ForegroundColor Red
    }
}

# Check Editor template
$editorTemplatePath = Join-Path $PSScriptRoot "..\templates\admin\legaldoc-editor.php"
if (Test-Path $editorTemplatePath) {
    $editorContent = Get-Content $editorTemplatePath -Raw
    
    if ($editorContent -match 'download-pdf-btn') {
        Write-Host "✓ Download button added to Editor" -ForegroundColor Green
    } else {
        Write-Host "✗ Download button not found in Editor" -ForegroundColor Red
    }
}

# Check Editor JavaScript
$editorJsPath = Join-Path $PSScriptRoot "..\assets\js\legaldoc-editor.js"
if (Test-Path $editorJsPath) {
    $editorJsContent = Get-Content $editorJsPath -Raw
    
    if ($editorJsContent -match 'downloadPDF') {
        Write-Host "✓ PDF download handler added to JavaScript" -ForegroundColor Green
    } else {
        Write-Host "✗ PDF download handler not found in JavaScript" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "=== Test Summary ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Automated checks completed. Review results above." -ForegroundColor White
Write-Host "For full testing, follow the manual test instructions with a real WordPress environment." -ForegroundColor White
Write-Host ""
Write-Host "Expected behavior:" -ForegroundColor Yellow
Write-Host "1. Published documents should have 'Download PDF' links" -ForegroundColor White
Write-Host "2. PDFs should include branding, TOC (for long docs), and proper formatting" -ForegroundColor White
Write-Host "3. PDFs should be cached for performance" -ForegroundColor White
Write-Host "4. Public can download published docs, admins can download all" -ForegroundColor White
Write-Host "5. REST endpoint: /wp-json/slos/v1/legaldocs/{id}/pdf" -ForegroundColor White
Write-Host ""
Write-Host "======================================" -ForegroundColor Cyan
Write-Host "Test script completed" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Cyan
