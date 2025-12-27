# Test Legal Document REST API Endpoints
# PowerShell script to test all Legal Document REST API endpoints

$ErrorActionPreference = 'Continue'

# Configuration
$baseUrl = "http://localhost:8080/index.php?rest_route="
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
Write-Info "Test 1: GET /slos/v1/legaldocs - List documents"
try {
    $uri = "$baseUrl/slos/v1/legaldocs"
    $response = Invoke-RestMethod -Uri $uri -Method Get -Headers @{ "Content-Type" = "application/json" }
    Write-Success "✓ Successfully retrieved documents list"
    Write-Host "  Found $($response.Count) document(s)"
    if ($response.Count -gt 0) {
        Write-Host "  First document: $($response[0].slug) (ID: $($response[0].id))"
    }
} catch {
    Write-Error "✗ Failed to retrieve documents: $($_.Exception.Message)"
}

# Test 2: Create a new document (admin only)
Write-Info "`nTest 2: POST /slos/v1/legaldocs - Create new document"
$newDoc = @{
    type = "privacy-policy"
    slug = "test-privacy-policy-$(Get-Date -Format 'yyyyMMddHHmmss')"
    content = "<h1>Test Privacy Policy</h1><p>This is a test document created via REST API.</p>"
    status = "draft"
    locale = "en_US"
    changelog = "Initial version created via REST API"
} | ConvertTo-Json

try {
    $uri = "$baseUrl/slos/v1/legaldocs"
    $response = Invoke-RestMethod -Uri $uri -Method Post -Headers $headers -Body $newDoc
    $script:docId = $response.id
    Write-Success "✓ Successfully created document"
    Write-Host "  Document ID: $($script:docId)"
    Write-Host "  Slug: $($response.slug)"
    Write-Host "  Status: $($response.status)"
    Write-Host "  Version: $($response.version)"
} catch {
    Write-Error "✗ Failed to create document: $($_.Exception.Message)"
    Write-Host "Response: $($_.ErrorDetails.Message)"
}

# Test 3: Get single document
if ($script:docId) {
    Write-Info "`nTest 3: GET /slos/v1/legaldocs/:id - Get single document"
    try {
        $uri = "$baseUrl/slos/v1/legaldocs/$($script:docId)"
        $response = Invoke-RestMethod -Uri $uri -Method Get -Headers $headers
        Write-Success "✓ Successfully retrieved document"
        Write-Host "  ID: $($response.id)"
        Write-Host "  Slug: $($response.slug)"
        Write-Host "  Status: $($response.status)"
        Write-Host "  Version: $($response.version)"
    } catch {
        Write-Error "✗ Failed to retrieve document: $($_.Exception.Message)"
    }
}

# Test 4: Get version history
if ($script:docId) {
    Write-Info "`nTest 4: GET /slos/v1/legaldocs/:id/versions - Get version history"
    try {
        $uri = "$baseUrl/slos/v1/legaldocs/$($script:docId)/versions"
        $response = Invoke-RestMethod -Uri $uri -Method Get -Headers $headers
        Write-Success "✓ Successfully retrieved version history"
        Write-Host "  Found $($response.Count) version(s)"
        foreach ($version in $response) {
            Write-Host "  - Version $($version.version): $($version.status)"
        }
    } catch {
        Write-Error "✗ Failed to retrieve version history: $($_.Exception.Message)"
    }
}

# Test 5: Update document to published
if ($script:docId) {
    Write-Info "`nTest 5: POST /slos/v1/legaldocs - Update to published"
    $updateDoc = @{
        type = "privacy-policy"
        slug = "test-privacy-policy-published"
        content = "<h1>Test Privacy Policy</h1><p>This is an updated published version.</p>"
        status = "published"
        locale = "en_US"
        changelog = "Published via REST API"
    } | ConvertTo-Json

    try {
        $uri = "$baseUrl/slos/v1/legaldocs"
        $response = Invoke-RestMethod -Uri $uri -Method Post -Headers $headers -Body $updateDoc
        $script:publishedDocId = $response.id
        Write-Success "✓ Successfully created published document"
        Write-Host "  Document ID: $($script:publishedDocId)"
        Write-Host "  Status: $($response.status)"
        Write-Host "  Version: $($response.version)"
    } catch {
        Write-Error "✗ Failed to update document: $($_.Exception.Message)"
    }
}

# Test 6: Filter by type
Write-Info "`nTest 6: GET /slos/v1/legaldocs?type=privacy-policy - Filter by type"
try {
    $uri = "$baseUrl/slos/v1/legaldocs&type=privacy-policy"
    $response = Invoke-RestMethod -Uri $uri -Method Get -Headers @{ "Content-Type" = "application/json" }
    Write-Success "✓ Successfully filtered by type"
    Write-Host "  Found $($response.Count) document(s)"
} catch {
    Write-Error "✗ Failed to filter: $($_.Exception.Message)"
}

# Test 7: Filter by status (published)
Write-Info "`nTest 7: GET /slos/v1/legaldocs?status=published - Filter by status"
try {
    $uri = "$baseUrl/slos/v1/legaldocs&status=published"
    $response = Invoke-RestMethod -Uri $uri -Method Get -Headers @{ "Content-Type" = "application/json" }
    Write-Success "✓ Successfully filtered by status"
    Write-Host "  Found $($response.Count) published document(s)"
} catch {
    Write-Error "✗ Failed to filter: $($_.Exception.Message)"
}

# Test 8: Public access to published documents
Write-Info "`nTest 8: GET /slos/v1/legaldocs (no auth) - Public access"
try {
    $uri = "$baseUrl/slos/v1/legaldocs&status=published"
    $response = Invoke-RestMethod -Uri $uri -Method Get -Headers @{ "Content-Type" = "application/json" }
    Write-Success "✓ Public access to published documents works"
    Write-Host "  Found $($response.Count) document(s)"
} catch {
    Write-Error "✗ Public access failed: $($_.Exception.Message)"
}

# Test 9: Try to create without auth (should fail)
Write-Info "`nTest 9: POST /slos/v1/legaldocs (no auth) - Should fail"
try {
    $uri = "$baseUrl/slos/v1/legaldocs"
    $response = Invoke-RestMethod -Uri $uri -Method Post -Headers @{ "Content-Type" = "application/json" } -Body $newDoc
    Write-Error "✗ Security issue: Public user created document!"
} catch {
    Write-Success "✓ Correctly denied create access to public user"
}

# Summary
Write-Info "`n========================================`n"
Write-Success "REST API Test Suite Complete!`n"
Write-Info "========================================`n"

if ($script:docId) {
    Write-Host "`nTest document created with ID: $($script:docId)"
}
if ($script:publishedDocId) {
    Write-Host "Published document created with ID: $($script:publishedDocId)"
}
Write-Host "`nReview the output above for any failures.`n"
