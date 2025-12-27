# Runs the consent integration test suite
$ErrorActionPreference = 'Stop'

$workspace = Split-Path -Parent $MyInvocation.MyCommand.Path
$root = Split-Path -Parent $workspace

# Ensure vendor autoload exists
$vendorPhpUnit = Join-Path $root 'vendor' 'bin' 'phpunit'

if (-Not (Test-Path $vendorPhpUnit)) {
  Write-Host 'phpunit not found in vendor/bin. Run: composer install' -ForegroundColor Yellow
}

$phpunitXml = Join-Path $root 'phpunit.xml.dist'
if (-Not (Test-Path $phpunitXml)) {
  Write-Host 'phpunit.xml.dist missing. Using defaults.' -ForegroundColor Yellow
}

# Set WordPress root if not already set
if (-Not $Env:WP_ROOT_DIR) {
  $Env:WP_ROOT_DIR = 'c:\docker-wp\html'
}

# Run testsuite
& $vendorPhpUnit --testsuite consent --colors=always
