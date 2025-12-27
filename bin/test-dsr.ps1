#!/usr/bin/env pwsh
# DSR Integration Tests Runner (PowerShell)
#
# Usage:
#   .\bin\test-dsr.ps1              # Run all DSR tests
#   .\bin\test-dsr.ps1 -verbose     # Run with verbose output
#
# @package     ShahiLegalopsSuite
# @subpackage  Tests
# @version     3.0.1
# @since       3.0.1

param(
    [switch]$verbose = $false,
    [switch]$help = $false
)

# Colors for output
$COLOR_GREEN = "`e[32m"
$COLOR_RED = "`e[31m"
$COLOR_YELLOW = "`e[33m"
$COLOR_BLUE = "`e[34m"
$COLOR_RESET = "`e[0m"

# Show help
if ($help) {
    Write-Host ""
    Write-Host "${COLOR_BLUE}DSR Integration Tests Runner${COLOR_RESET}"
    Write-Host ""
    Write-Host "Usage:"
    Write-Host "  .\bin\test-dsr.ps1              ${COLOR_YELLOW}# Run all DSR tests${COLOR_RESET}"
    Write-Host "  .\bin\test-dsr.ps1 -verbose     ${COLOR_YELLOW}# Run with verbose output${COLOR_RESET}"
    Write-Host "  .\bin\test-dsr.ps1 -help        ${COLOR_YELLOW}# Show this help${COLOR_RESET}"
    Write-Host ""
    Write-Host "Environment Variables:"
    Write-Host "  WP_ROOT_DIR    Path to WordPress installation (default: c:\docker-wp\html)"
    Write-Host ""
    exit 0
}

# Get script directory
$SCRIPT_DIR = Split-Path -Parent $MyInvocation.MyCommand.Path
$PLUGIN_DIR = Split-Path -Parent $SCRIPT_DIR

# Set WP_ROOT_DIR if not already set
if (-not $env:WP_ROOT_DIR) {
    $env:WP_ROOT_DIR = "c:\docker-wp\html"
}

Write-Host ""
Write-Host "${COLOR_BLUE}╔════════════════════════════════════════════════════════════════╗${COLOR_RESET}"
Write-Host "${COLOR_BLUE}║                                                                ║${COLOR_RESET}"
Write-Host "${COLOR_BLUE}║          ShahiLegalOps Suite - DSR Integration Tests          ║${COLOR_RESET}"
Write-Host "${COLOR_BLUE}║                         Version 3.0.1                          ║${COLOR_RESET}"
Write-Host "${COLOR_BLUE}║                                                                ║${COLOR_RESET}"
Write-Host "${COLOR_BLUE}╚════════════════════════════════════════════════════════════════╝${COLOR_RESET}"
Write-Host ""

# Check if WordPress is accessible
if (-not (Test-Path "$env:WP_ROOT_DIR\wp-load.php")) {
    Write-Host "${COLOR_RED}Error: WordPress not found at $env:WP_ROOT_DIR${COLOR_RESET}" -ForegroundColor Red
    Write-Host "Set WP_ROOT_DIR environment variable to your WordPress installation path."
    exit 1
}

# Check if PHP is available
try {
    $phpVersion = php -v 2>&1 | Select-Object -First 1
    Write-Host "${COLOR_GREEN}✓ PHP detected:${COLOR_RESET} $phpVersion"
} catch {
    Write-Host "${COLOR_RED}Error: PHP not found in PATH${COLOR_RESET}" -ForegroundColor Red
    exit 1
}

Write-Host ""

# Run tests using PHP
$TEST_FILE = Join-Path $PLUGIN_DIR "tests\integration\dsr\DSR_All_Tests.php"

if (-not (Test-Path $TEST_FILE)) {
    Write-Host "${COLOR_RED}Error: Test runner not found at $TEST_FILE${COLOR_RESET}" -ForegroundColor Red
    exit 1
}

Write-Host "${COLOR_YELLOW}Running DSR Integration Tests...${COLOR_RESET}"
Write-Host ""

# Execute tests
$result = php $TEST_FILE
$exitCode = $LASTEXITCODE

# Display results
Write-Host $result

# Exit with test result code
if ($exitCode -eq 0) {
    Write-Host ""
    Write-Host "${COLOR_GREEN}✓ All tests passed!${COLOR_RESET}"
    Write-Host ""
    exit 0
} else {
    Write-Host ""
    Write-Host "${COLOR_RED}✗ Some tests failed. Review output above.${COLOR_RESET}"
    Write-Host ""
    exit 1
}
