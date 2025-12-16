#!/bin/bash

##############################################################################
# ShahiTemplate Development Environment Setup
# 
# Sets up the development environment with all dependencies
# 
# Usage: ./bin/dev.sh
##############################################################################

set -e  # Exit on error

echo ""
echo "╔═══════════════════════════════════════════════════════════╗"
echo "║         🔧 ShahiTemplate Development Setup 🔧            ║"
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

echo -e "${BLUE}Plugin Directory:${NC} ${PLUGIN_DIR}"
echo ""

# Check requirements
echo -e "${BLUE}Checking requirements...${NC}"

HAS_PHP=false
HAS_COMPOSER=false
HAS_NODE=false
HAS_NPM=false

if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -n 1 | cut -d ' ' -f 2)
    echo -e "${GREEN}✓${NC} PHP: $PHP_VERSION"
    HAS_PHP=true
else
    echo -e "${RED}✗${NC} PHP not found"
fi

if command -v composer &> /dev/null; then
    COMPOSER_VERSION=$(composer --version | cut -d ' ' -f 3)
    echo -e "${GREEN}✓${NC} Composer: $COMPOSER_VERSION"
    HAS_COMPOSER=true
else
    echo -e "${RED}✗${NC} Composer not found"
fi

if command -v node &> /dev/null; then
    NODE_VERSION=$(node -v)
    echo -e "${GREEN}✓${NC} Node.js: $NODE_VERSION"
    HAS_NODE=true
else
    echo -e "${RED}✗${NC} Node.js not found"
fi

if command -v npm &> /dev/null; then
    NPM_VERSION=$(npm -v)
    echo -e "${GREEN}✓${NC} npm: $NPM_VERSION"
    HAS_NPM=true
else
    echo -e "${RED}✗${NC} npm not found"
fi

echo ""

# Install PHP dependencies
if [ "$HAS_COMPOSER" = true ]; then
    echo -e "${BLUE}[1/4]${NC} Installing PHP dependencies..."
    cd "$PLUGIN_DIR"
    composer install
    echo -e "${GREEN}✓${NC} PHP dependencies installed"
else
    echo -e "${YELLOW}⚠${NC} Skipping PHP dependencies (Composer not available)"
fi

# Install Node dependencies
if [ "$HAS_NPM" = true ]; then
    echo -e "${BLUE}[2/4]${NC} Installing Node dependencies..."
    cd "$PLUGIN_DIR"
    npm install
    echo -e "${GREEN}✓${NC} Node dependencies installed"
else
    echo -e "${YELLOW}⚠${NC} Skipping Node dependencies (npm not available)"
fi

# Build assets
if [ "$HAS_NPM" = true ]; then
    echo -e "${BLUE}[3/4]${NC} Building development assets..."
    npm run dev
    echo -e "${GREEN}✓${NC} Assets built"
else
    echo -e "${YELLOW}⚠${NC} Skipping asset build (npm not available)"
fi

# Setup Git hooks (if Git is available)
if command -v git &> /dev/null; then
    echo -e "${BLUE}[4/4]${NC} Setting up Git hooks..."
    
    HOOKS_DIR="$PLUGIN_DIR/.git/hooks"
    if [ -d "$HOOKS_DIR" ]; then
        # Create pre-commit hook
        cat > "$HOOKS_DIR/pre-commit" << 'EOF'
#!/bin/bash
# ShahiTemplate pre-commit hook

echo "Running pre-commit checks..."

# Check PHP syntax
FILES=$(git diff --cached --name-only --diff-filter=ACM "*.php")
if [ -n "$FILES" ]; then
    for FILE in $FILES; do
        php -l "$FILE" > /dev/null 2>&1
        if [ $? -ne 0 ]; then
            echo "PHP syntax error in: $FILE"
            exit 1
        fi
    done
fi

# Run PHPCS (if available)
if command -v phpcs &> /dev/null; then
    phpcs --standard=WordPress --extensions=php $FILES
    if [ $? -ne 0 ]; then
        echo "PHPCS failed. Please fix the issues or use --no-verify to skip."
        exit 1
    fi
fi

echo "Pre-commit checks passed!"
EOF
        chmod +x "$HOOKS_DIR/pre-commit"
        echo -e "${GREEN}✓${NC} Git hooks configured"
    fi
else
    echo -e "${YELLOW}⚠${NC} Git not available, skipping hooks setup"
fi

# Create .env file if it doesn't exist
if [ ! -f "$PLUGIN_DIR/.env" ]; then
    echo -e "${BLUE}Creating .env file...${NC}"
    cat > "$PLUGIN_DIR/.env" << 'EOF'
# ShahiTemplate Development Environment

# Development mode
WP_DEBUG=true
WP_DEBUG_LOG=true
WP_DEBUG_DISPLAY=false

# Database (adjust for your local environment)
DB_NAME=wordpress
DB_USER=root
DB_PASSWORD=
DB_HOST=localhost

# URLs
WP_HOME=http://localhost
WP_SITEURL=http://localhost

# Keys (generate at https://api.wordpress.org/secret-key/1.1/salt/)
# ...add your keys here
EOF
    echo -e "${GREEN}✓${NC} .env file created (please configure it)"
fi

# Summary
echo ""
echo "╔═══════════════════════════════════════════════════════════╗"
echo "║              ✅ Development Setup Complete! ✅           ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""

# Print available commands
echo -e "${BLUE}Available npm commands:${NC}"
if [ -f "$PLUGIN_DIR/package.json" ]; then
    echo "  npm run dev         - Build development assets"
    echo "  npm run build       - Build production assets"
    echo "  npm run watch       - Watch and rebuild assets"
    echo "  npm run lint        - Lint JavaScript files"
    echo "  npm run format      - Format code with Prettier"
fi

echo ""
echo -e "${BLUE}Available composer commands:${NC}"
echo "  composer test       - Run PHPUnit tests"
echo "  composer lint       - Run PHPCS linter"
echo "  composer fix        - Fix code style issues"

echo ""
echo -e "${BLUE}Available build commands:${NC}"
echo "  ./bin/build.sh      - Create production build"
echo "  ./bin/setup.php     - Configure plugin from template"

echo ""
echo -e "${GREEN}Happy coding! 🚀${NC}"
echo ""

# Offer to start watch mode
if [ "$HAS_NPM" = true ]; then
    echo -n "Start asset watch mode? [y/N]: "
    read -r RESPONSE
    if [ "$RESPONSE" = "y" ] || [ "$RESPONSE" = "Y" ]; then
        npm run watch
    fi
fi
