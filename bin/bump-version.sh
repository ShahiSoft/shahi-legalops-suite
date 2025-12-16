#!/bin/bash
################################################################################
# Version Bump Script for ShahiTemplate
# 
# Updates version numbers across all plugin files
# 
# Usage: bash bin/bump-version.sh [version]
# Example: bash bin/bump-version.sh 1.2.0
#
# @package ShahiTemplate
# @version 1.0.0
################################################################################

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

################################################################################
# Function: print_header
# Display script header
################################################################################
print_header() {
    echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${BLUE}║          ShahiTemplate Version Bump Script                ║${NC}"
    echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
    echo ""
}

################################################################################
# Function: validate_version
# Validate semantic version format (major.minor.patch)
################################################################################
validate_version() {
    local version=$1
    
    if [[ ! $version =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        echo -e "${RED}❌ Invalid version format: $version${NC}"
        echo -e "${YELLOW}Expected format: major.minor.patch (e.g., 1.0.0)${NC}"
        return 1
    fi
    
    return 0
}

################################################################################
# Function: get_current_version
# Read current version from VERSION file
################################################################################
get_current_version() {
    if [ -f "$PROJECT_ROOT/VERSION" ]; then
        cat "$PROJECT_ROOT/VERSION"
    else
        echo "1.0.0"
    fi
}

################################################################################
# Function: update_version_file
# Update VERSION file
################################################################################
update_version_file() {
    local new_version=$1
    
    echo -n "$new_version" > "$PROJECT_ROOT/VERSION"
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Updated VERSION file${NC}"
        return 0
    else
        echo -e "${RED}✗ Failed to update VERSION file${NC}"
        return 1
    fi
}

################################################################################
# Function: update_main_plugin_file
# Update version in main plugin file header
################################################################################
update_main_plugin_file() {
    local new_version=$1
    local plugin_file="$PROJECT_ROOT/shahi-template.php"
    
    if [ ! -f "$plugin_file" ]; then
        echo -e "${YELLOW}⚠ Main plugin file not found: $plugin_file${NC}"
        return 1
    fi
    
    # Update Version: line in plugin header
    sed -i "s/^ \* Version:.*/ * Version:           $new_version/" "$plugin_file"
    
    # Update define version constant
    sed -i "s/define( 'SHAHI_TEMPLATE_VERSION', '.*' );/define( 'SHAHI_TEMPLATE_VERSION', '$new_version' );/" "$plugin_file"
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Updated shahi-template.php${NC}"
        return 0
    else
        echo -e "${RED}✗ Failed to update shahi-template.php${NC}"
        return 1
    fi
}

################################################################################
# Function: update_plugin_class
# Update VERSION constant in Plugin class
################################################################################
update_plugin_class() {
    local new_version=$1
    local plugin_class="$PROJECT_ROOT/includes/class-plugin.php"
    
    if [ ! -f "$plugin_class" ]; then
        echo -e "${YELLOW}⚠ Plugin class file not found: $plugin_class${NC}"
        return 1
    fi
    
    # Update const VERSION line
    sed -i "s/const VERSION = '.*';/const VERSION = '$new_version';/" "$plugin_class"
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Updated includes/class-plugin.php${NC}"
        return 0
    else
        echo -e "${RED}✗ Failed to update includes/class-plugin.php${NC}"
        return 1
    fi
}

################################################################################
# Function: update_readme
# Update Stable tag in README.md
################################################################################
update_readme() {
    local new_version=$1
    local readme="$PROJECT_ROOT/README.md"
    
    if [ ! -f "$readme" ]; then
        echo -e "${YELLOW}⚠ README.md not found${NC}"
        return 1
    fi
    
    # Update version mentions in README
    sed -i "s/\*\*Version\*\*:.*/**Version**: $new_version/" "$readme"
    sed -i "s/Version:.*$new_version/Version: $new_version/" "$readme"
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Updated README.md${NC}"
        return 0
    else
        echo -e "${RED}✗ Failed to update README.md${NC}"
        return 1
    fi
}

################################################################################
# Function: update_package_json
# Update version in package.json if it exists
################################################################################
update_package_json() {
    local new_version=$1
    local package_json="$PROJECT_ROOT/package.json"
    
    if [ ! -f "$package_json" ]; then
        echo -e "${YELLOW}⚠ package.json not found (skipping)${NC}"
        return 0
    fi
    
    # Update version in package.json
    sed -i "s/\"version\": \".*\"/\"version\": \"$new_version\"/" "$package_json"
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Updated package.json${NC}"
        return 0
    else
        echo -e "${RED}✗ Failed to update package.json${NC}"
        return 1
    fi
}

################################################################################
# Function: update_changelog
# Add version entry to CHANGELOG.md
################################################################################
update_changelog() {
    local new_version=$1
    local changelog="$PROJECT_ROOT/CHANGELOG.md"
    local current_date=$(date +%Y-%m-%d)
    
    if [ ! -f "$changelog" ]; then
        echo -e "${YELLOW}⚠ CHANGELOG.md not found${NC}"
        return 1
    fi
    
    # PLACEHOLDER: This adds a basic version entry
    # In production, you should manually edit CHANGELOG.md with actual changes
    
    # Create temporary file with new version entry
    local temp_file=$(mktemp)
    
    # Add new version entry after [Unreleased] section
    awk -v version="$new_version" -v date="$current_date" '
        /## \[Unreleased\]/ {
            print $0
            print ""
            print "## [" version "] - " date
            print ""
            print "### Added"
            print "- PLACEHOLDER: Add changes for version " version
            print ""
            print "### Changed"
            print "- PLACEHOLDER: Add changes for version " version
            print ""
            print "### Fixed"
            print "- PLACEHOLDER: Add changes for version " version
            print ""
            next
        }
        { print }
    ' "$changelog" > "$temp_file"
    
    mv "$temp_file" "$changelog"
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Updated CHANGELOG.md${NC}"
        echo -e "${YELLOW}⚠ PLACEHOLDER entries added - please update CHANGELOG.md manually${NC}"
        return 0
    else
        echo -e "${RED}✗ Failed to update CHANGELOG.md${NC}"
        return 1
    fi
}

################################################################################
# Function: create_git_tag
# Create git tag for the new version
################################################################################
create_git_tag() {
    local new_version=$1
    
    # Check if git is available
    if ! command -v git &> /dev/null; then
        echo -e "${YELLOW}⚠ Git not found (skipping tag creation)${NC}"
        return 0
    fi
    
    # Check if we're in a git repository
    if ! git rev-parse --git-dir > /dev/null 2>&1; then
        echo -e "${YELLOW}⚠ Not a git repository (skipping tag creation)${NC}"
        return 0
    fi
    
    echo ""
    read -p "Create git tag v$new_version? (y/n) " -n 1 -r
    echo ""
    
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        # Check if tag already exists
        if git rev-parse "v$new_version" >/dev/null 2>&1; then
            echo -e "${YELLOW}⚠ Tag v$new_version already exists${NC}"
            return 1
        fi
        
        # Create annotated tag
        git tag -a "v$new_version" -m "Release version $new_version"
        
        if [ $? -eq 0 ]; then
            echo -e "${GREEN}✓ Created git tag v$new_version${NC}"
            echo -e "${BLUE}ℹ Push tag with: git push origin v$new_version${NC}"
            return 0
        else
            echo -e "${RED}✗ Failed to create git tag${NC}"
            return 1
        fi
    else
        echo -e "${YELLOW}⚠ Skipped git tag creation${NC}"
        return 0
    fi
}

################################################################################
# Function: display_summary
# Display summary of changes
################################################################################
display_summary() {
    local old_version=$1
    local new_version=$2
    
    echo ""
    echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${BLUE}║                    Version Bump Summary                   ║${NC}"
    echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "${YELLOW}Old Version:${NC} $old_version"
    echo -e "${GREEN}New Version:${NC} $new_version"
    echo ""
    echo -e "${BLUE}Updated Files:${NC}"
    echo "  • VERSION"
    echo "  • shahi-template.php"
    echo "  • includes/class-plugin.php"
    echo "  • README.md"
    echo "  • CHANGELOG.md"
    [ -f "$PROJECT_ROOT/package.json" ] && echo "  • package.json"
    echo ""
    echo -e "${YELLOW}Next Steps:${NC}"
    echo "  1. Review CHANGELOG.md and replace PLACEHOLDER entries"
    echo "  2. Commit changes: git add . && git commit -m \"Bump version to $new_version\""
    echo "  3. Push tag: git push origin v$new_version"
    echo "  4. Run build: bash bin/build.sh"
    echo "  5. Create GitHub release"
    echo ""
}

################################################################################
# Main Script
################################################################################
main() {
    print_header
    
    # Check if version argument provided
    if [ $# -eq 0 ]; then
        echo -e "${RED}❌ Error: Version number required${NC}"
        echo ""
        echo -e "${YELLOW}Usage:${NC}"
        echo "  bash bin/bump-version.sh [version]"
        echo ""
        echo -e "${YELLOW}Example:${NC}"
        echo "  bash bin/bump-version.sh 1.2.0"
        echo ""
        exit 1
    fi
    
    local new_version=$1
    
    # Validate version format
    if ! validate_version "$new_version"; then
        exit 1
    fi
    
    # Get current version
    local old_version=$(get_current_version)
    
    echo -e "${BLUE}Current version:${NC} $old_version"
    echo -e "${BLUE}New version:${NC}     $new_version"
    echo ""
    
    # Confirm version bump
    read -p "Continue with version bump? (y/n) " -n 1 -r
    echo ""
    echo ""
    
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${YELLOW}Version bump cancelled${NC}"
        exit 0
    fi
    
    # Perform updates
    echo -e "${BLUE}Updating version numbers...${NC}"
    echo ""
    
    update_version_file "$new_version"
    update_main_plugin_file "$new_version"
    update_plugin_class "$new_version"
    update_readme "$new_version"
    update_package_json "$new_version"
    update_changelog "$new_version"
    
    # Create git tag
    create_git_tag "$new_version"
    
    # Display summary
    display_summary "$old_version" "$new_version"
    
    echo -e "${GREEN}✅ Version bump completed successfully!${NC}"
    echo ""
}

# Run main function
main "$@"
