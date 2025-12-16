#!/bin/bash

##############################################################################
# ShahiTemplate Build Script
# 
# Creates a production-ready build of the plugin
# 
# Usage: ./bin/build.sh
##############################################################################

set -e  # Exit on error

echo ""
echo "╔═══════════════════════════════════════════════════════════╗"
echo "║           🚀 ShahiTemplate Production Build 🚀           ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;36m'
NC='\033[0m' # No Color

# Get plugin directory
PLUGIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BUILD_DIR="${PLUGIN_DIR}/build"
DIST_FILE="${PLUGIN_DIR}/dist/shahi-template.zip"

echo -e "${BLUE}Plugin Directory:${NC} ${PLUGIN_DIR}"
echo ""

# Step 1: Clean previous build
echo -e "${BLUE}[1/8]${NC} Cleaning previous build..."
if [ -d "$BUILD_DIR" ]; then
    rm -rf "$BUILD_DIR"
fi
mkdir -p "$BUILD_DIR"
mkdir -p "${PLUGIN_DIR}/dist"
echo -e "${GREEN}✓${NC} Build directory cleaned"

# Step 2: Install PHP dependencies
echo -e "${BLUE}[2/8]${NC} Installing PHP dependencies..."
cd "$PLUGIN_DIR"
if command -v composer &> /dev/null; then
    composer install --no-dev --optimize-autoloader --quiet
    echo -e "${GREEN}✓${NC} PHP dependencies installed"
else
    echo -e "${YELLOW}⚠${NC} Composer not found, skipping PHP dependencies"
fi

# Step 3: Install Node dependencies
echo -e "${BLUE}[3/8]${NC} Installing Node dependencies..."
if command -v npm &> /dev/null; then
    npm install --silent
    echo -e "${GREEN}✓${NC} Node dependencies installed"
else
    echo -e "${YELLOW}⚠${NC} npm not found, skipping Node dependencies"
fi

# Step 4: Build frontend assets
echo -e "${BLUE}[4/8]${NC} Building frontend assets..."
if command -v npm &> /dev/null; then
    npm run build --silent
    echo -e "${GREEN}✓${NC} Assets built successfully"
else
    echo -e "${YELLOW}⚠${NC} npm not found, skipping asset build"
fi

# Step 5: Copy files to build directory
echo -e "${BLUE}[5/8]${NC} Copying files to build directory..."

# Files and directories to include
cp -r "$PLUGIN_DIR/includes" "$BUILD_DIR/"
cp -r "$PLUGIN_DIR/assets" "$BUILD_DIR/"
cp -r "$PLUGIN_DIR/templates" "$BUILD_DIR/"
cp -r "$PLUGIN_DIR/languages" "$BUILD_DIR/"
cp -r "$PLUGIN_DIR/vendor" "$BUILD_DIR/"

# Copy root files
cp "$PLUGIN_DIR/shahi-template.php" "$BUILD_DIR/"
cp "$PLUGIN_DIR/readme.txt" "$BUILD_DIR/"
cp "$PLUGIN_DIR/README.md" "$BUILD_DIR/"
cp "$PLUGIN_DIR/LICENSE" "$BUILD_DIR/" 2>/dev/null || true
cp "$PLUGIN_DIR/composer.json" "$BUILD_DIR/"

# Copy documentation (optional)
cp "$PLUGIN_DIR/TEMPLATE-USAGE.md" "$BUILD_DIR/" 2>/dev/null || true

echo -e "${GREEN}✓${NC} Files copied"

# Step 6: Remove development files
echo -e "${BLUE}[6/8]${NC} Removing development files..."

# Remove development files from build
find "$BUILD_DIR" -name ".git*" -type f -delete
find "$BUILD_DIR" -name ".DS_Store" -type f -delete
find "$BUILD_DIR" -name "*.map" -type f -delete
find "$BUILD_DIR" -name "*.log" -type f -delete
find "$BUILD_DIR" -name "phpunit.xml" -type f -delete
find "$BUILD_DIR" -name ".phpunit.result.cache" -type f -delete

# Remove development directories
rm -rf "$BUILD_DIR/node_modules" 2>/dev/null || true
rm -rf "$BUILD_DIR/tests" 2>/dev/null || true
rm -rf "$BUILD_DIR/bin" 2>/dev/null || true
rm -rf "$BUILD_DIR/.git" 2>/dev/null || true

echo -e "${GREEN}✓${NC} Development files removed"

# Step 7: Optimize code
echo -e "${BLUE}[7/8]${NC} Optimizing code..."

# Minify CSS (if available)
if command -v cleancss &> /dev/null; then
    find "$BUILD_DIR/assets/css" -name "*.css" ! -name "*.min.css" -exec cleancss -o {}.min {} \;
    echo -e "${GREEN}✓${NC} CSS minified"
else
    echo -e "${YELLOW}⚠${NC} cleancss not found, skipping CSS minification"
fi

# Generate .pot file for translations
if command -v wp &> /dev/null; then
    wp i18n make-pot "$BUILD_DIR" "$BUILD_DIR/languages/shahi-template.pot" --quiet 2>/dev/null || true
    echo -e "${GREEN}✓${NC} Translation template generated"
fi

# Step 8: Create ZIP archive
echo -e "${BLUE}[8/8]${NC} Creating distribution package..."

cd "$(dirname "$BUILD_DIR")"
if [ -f "$DIST_FILE" ]; then
    rm "$DIST_FILE"
fi

zip -rq "$DIST_FILE" "$(basename "$BUILD_DIR")"

FILE_SIZE=$(du -h "$DIST_FILE" | cut -f1)

echo -e "${GREEN}✓${NC} Distribution package created: ${FILE_SIZE}"

# Cleanup
echo ""
echo -e "${BLUE}Cleaning up...${NC}"
rm -rf "$BUILD_DIR"

# Summary
echo ""
echo "╔═══════════════════════════════════════════════════════════╗"
echo "║                  ✅ Build Complete! ✅                    ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""
echo -e "${GREEN}Distribution file:${NC} $DIST_FILE"
echo -e "${GREEN}File size:${NC} $FILE_SIZE"
echo ""
echo "Next steps:"
echo "  1. Test the plugin from the dist/ directory"
echo "  2. Upload to WordPress.org or CodeCanyon"
echo "  3. Celebrate! 🎉"
echo ""

# Restore development dependencies
echo -e "${BLUE}Restoring development environment...${NC}"
cd "$PLUGIN_DIR"
if command -v composer &> /dev/null; then
    composer install --quiet
fi
if command -v npm &> /dev/null; then
    npm install --silent
fi
echo -e "${GREEN}✓${NC} Development environment restored"
echo ""
