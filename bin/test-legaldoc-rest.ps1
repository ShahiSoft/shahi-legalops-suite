# Test Legal Document REST API Endpoints
# PowerShell script to test all Legal Document REST API endpoints

$ErrorActionPreference = 'Stop'

# Configuration
$baseUrl = "http://localhost:8080"
$apiBase = "$baseUrl/index.php?rest_route=/slos/v1"
$username = "admin"
$password = "admin"

# Colors for output
function Write-Success { Write-Host $args -ForegroundColor Green }
function Write-Error { Write-Host $args -ForegroundColor Red }
function Write-Info { Write-Host $args -ForegroundColor Cyan }
function Write-Warning { Write-Host $args -ForegroundColor Yellow }

# Create auth header
$authPair = "${username}:${password}"
$authBytes = [System.Text.Encoding]::ASCII.GetBytes($authPair)
$authBase64 = [System.Convert]::ToBase64String($authBytes)
$headers = @{
    "Authorization" = "Basic $authBase64"
    "Content-Type" = "application/json"
}

Write-Info "`n========================================`n"
Write-Info "Legal Document REST API Test Suite`n"
Write-Info "========================================`n"

# Test 1: List documents (public endpoint)
Write-Info "Test 1: GET /legaldocs - List documents"
try {
    $response = Invoke-RestMethod -Uri "$apiBase/legaldocs" -Method Get -Headers @{ "Content-Type" = "application/json" }
    Write-Success "✓ Successfully retrieved documents list"
    Write-Host "  Found $($response.Count) document(s)"
    if ($response.Count -gt 0) {
        Write-Host "  First document: $($response[0].slug) (ID: $($response[0].id))"
    }
} catch {
    Write-Error "✗ Failed to retrieve documents: $_"
}

# Test 2: Create a new document (admin only)
Write-Info "`nTest 2: POST /legaldocs - Create new document"
$newDoc = @{
    type = "privacy-policy"
    slug = "test-privacy-policy"
    content = "<h1>Test Privacy Policy</h1><p>This is a test document created via REST API.</p>"
    status = "draft"
    locale = "en_US"
    changelog = "Initial version created via REST API"
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "$apiBase/legaldocs" -Method Post -Headers $headers -Body $newDoc
    $docId = $response.id
    Write-Success "✓ Successfully created document"
    Write-Host "  Document ID: $docId"
    Write-Host "  Slug: $($response.slug)"
    Write-Host "  Status: $($response.status)"
    Write-Host "  Version: $($response.version)"
} catch {
    Write-Error "✗ Failed to create document: $_"
    exit 1
}

# Test 3: Get single document
Write-Info "`nTest 3: GET /legaldocs/:id - Get single document"
try {
    $response = Invoke-RestMethod -Uri "$apiBase/legaldocs/$docId" -Method Get -Headers $headers
    Write-Success "✓ Successfully retrieved document"
    Write-Host "  ID: $($response.id)"
    Write-Host "  Slug: $($response.slug)"
    Write-Host "  Status: $($response.status)"
    Write-Host "  Version: $($response.version)"
} catch {
    Write-Error "✗ Failed to retrieve document: $_"
}

# Test 4: Update document to published status
Write-Info "`nTest 4: POST /legaldocs - Update document to published"
$updateDoc = @{
    type = "privacy-policy"
    slug = "test-privacy-policy"
    content = "<h1>Test Privacy Policy</h1><p>This is an updated version.</p>"
    status = "published"
    locale = "en_US"
    changelog = "Published via REST API"
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "$apiBase/legaldocs" -Method Post -Headers $headers -Body $updateDoc
    Write-Success "✓ Successfully updated document to published"
    Write-Host "  Status: $($response.status)"
    Write-Host "  Version: $($response.version)"
} catch {
    Write-Error "✗ Failed to update document: $_"
}

# Test 5: Get version history
Write-Info "`nTest 5: GET /legaldocs/:id/versions - Get version history"
try {
    $response = Invoke-RestMethod -Uri "$apiBase/legaldocs/$docId/versions" -Method Get -Headers $headers
    Write-Success "✓ Successfully retrieved version history"
    Write-Host "  Found $($response.Count) version(s)"
    foreach ($version in $response) {
        Write-Host "  - Version $($version.version): $($version.status) - $($version.changelog)"
    }
} catch {
    Write-Error "✗ Failed to retrieve version history: $_"
}

# Test 6: Filter documents by type
Write-Info "`nTest 6: GET /legaldocs?type=privacy-policy - Filter by type"
try {
    $response = Invoke-RestMethod -Uri "$apiBase/legaldocs?type=privacy-policy" -Method Get -Headers @{ "Content-Type" = "application/json" }
    Write-Success "✓ Successfully filtered documents by type"
    Write-Host "  Found $($response.Count) document(s)"
} catch {
    Write-Error "✗ Failed to filter documents: $_"
}

# Test 7: Filter documents by status
Write-Info "`nTest 7: GET /legaldocs?status=published - Filter by status"
try {
    $response = Invoke-RestMethod -Uri "$apiBase/legaldocs?status=published" -Method Get -Headers @{ "Content-Type" = "application/json" }
    Write-Success "✓ Successfully filtered documents by status"
    Write-Host "  Found $($response.Count) published document(s)"
} catch {
    Write-Error "✗ Failed to filter documents: $_"
}

# Test 8: Pagination
Write-Info "`nTest 8: GET /legaldocs?per_page=1&page=1 - Pagination"
try {
    $response = Invoke-RestMethod -Uri "$apiBase/legaldocs?per_page=1&page=1" -Method Get -Headers @{ "Content-Type" = "application/json" } -ResponseHeadersVariable responseHeaders
    Write-Success "✓ Successfully tested pagination"
    Write-Host "  Retrieved $($response.Count) document(s) per page"
    if ($responseHeaders.ContainsKey('X-WP-Total')) {
        Write-Host "  Total documents: $($responseHeaders['X-WP-Total'][0])"
    }
    if ($responseHeaders.ContainsKey('X-WP-TotalPages')) {
        Write-Host "  Total pages: $($responseHeaders['X-WP-TotalPages'][0])"
    }
} catch {
    Write-Error "✗ Failed pagination test: $_"
}

# Test 9: Public access to published documents (no auth)
Write-Info "`nTest 9: GET /legaldocs (public) - Access without authentication"
try {
    $response = Invoke-RestMethod -Uri "$apiBase/legaldocs?status=published" -Method Get -Headers @{ "Content-Type" = "application/json" }
    Write-Success "✓ Successfully accessed published documents without auth"
    Write-Host "  Found $($response.Count) document(s)"
} catch {
    Write-Error "✗ Failed to access published documents: $_"
}

# Test 10: Attempt to access drafts without auth (should filter)
Write-Info "`nTest 10: GET /legaldocs?status=draft (public) - Access drafts without auth"
try {
    $response = Invoke-RestMethod -Uri "$apiBase/legaldocs?status=draft" -Method Get -Headers @{ "Content-Type" = "application/json" }
    Write-Warning "! Public user attempted to access drafts"
    Write-Host "  Found $($response.Count) document(s) (should be 0 or only published)"
} catch {
    Write-Success "✓ Correctly restricted draft access"
}

# Test 11: Attempt to create document without auth (should fail)
Write-Info "`nTest 11: POST /legaldocs (public) - Create without auth"
try {
    $response = Invoke-RestMethod -Uri "$apiBase/legaldocs" -Method Post -Headers @{ "Content-Type" = "application/json" } -Body $newDoc
    Write-Error "✗ Public user was able to create document (security issue!)"
} catch {
    Write-Success "✓ Correctly denied create access to public user"
}

# Test 12: Get rollback version if we have at least 2 versions
Write-Info "`nTest 12: POST /legaldocs/:id/rollback - Rollback to previous version"
try {
    $versions = Invoke-RestMethod -Uri "$apiBase/legaldocs/$docId/versions" -Method Get -Headers $headers
    if ($versions.Count -ge 2) {
        $firstVersion = $versions[0].id
        $rollbackBody = @{
            version_id = $firstVersion
        } | ConvertTo-Json
        
        $response = Invoke-RestMethod -Uri "$apiBase/legaldocs/$docId/rollback" -Method Post -Headers $headers -Body $rollbackBody
        Write-Success "✓ Successfully rolled back document"
        Write-Host "  New version: $($response.version)"
        Write-Host "  Status: $($response.status)"
    } else {
        Write-Warning "! Skipping rollback test (need at least 2 versions)"
    }
} catch {
    Write-Error "✗ Failed to rollback document: $_"
}

# Summary
Write-Info "`n========================================`n"
Write-Success "REST API Test Suite Complete!`n"
Write-Info "========================================`n"
Write-Host "`nAll REST API endpoints have been tested."
Write-Host "Review the output above for any failures or issues.`n"
