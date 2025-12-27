#!/bin/bash
#
# Legal Documents Test Runner CLI Helper
#
# Usage: ./bin/test-legaldocs.sh [options]
#
# Options:
#   --suite=<name>    Run specific test suite (repository, service, lifecycle, etc.)
#   --coverage        Generate coverage report (requires xdebug)
#   --verbose         Verbose output
#   --help            Show this help message
#

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Default values
SUITE=""
COVERAGE=false
VERBOSE=false

# Parse arguments
for arg in "$@"; do
	case $arg in
		--suite=*)
			SUITE="${arg#*=}"
			;;
		--coverage)
			COVERAGE=true
			;;
		--verbose)
			VERBOSE=true
			;;
		--help)
			echo "Legal Documents Test Runner"
			echo ""
			echo "Usage: ./bin/test-legaldocs.sh [options]"
			echo ""
			echo "Options:"
			echo "  --suite=<name>    Run specific test suite:"
			echo "                      repository, service, lifecycle, version,"
			echo "                      rest-api, acceptance, locale, pdf, shortcodes, all"
			echo "  --coverage        Generate coverage report (requires xdebug)"
			echo "  --verbose         Verbose output"
			echo "  --help            Show this help message"
			echo ""
			echo "Examples:"
			echo "  ./bin/test-legaldocs.sh                    # Run all tests"
			echo "  ./bin/test-legaldocs.sh --suite=repository # Run repository tests only"
			echo "  ./bin/test-legaldocs.sh --coverage         # Run with coverage"
			exit 0
			;;
		*)
			echo -e "${RED}Unknown option: $arg${NC}"
			echo "Use --help for usage information"
			exit 1
			;;
	esac
done

# Get script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PLUGIN_DIR="$( cd "$SCRIPT_DIR/.." && pwd )"
TEST_DIR="$PLUGIN_DIR/tests/integration/legaldocs"

echo -e "${BLUE}╔═══════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║                                                           ║${NC}"
echo -e "${BLUE}║        ShahiLegalOps Suite - Legal Docs Tests             ║${NC}"
echo -e "${BLUE}║                                                           ║${NC}"
echo -e "${BLUE}╚═══════════════════════════════════════════════════════════╝${NC}"
echo ""

# Check if tests directory exists
if [ ! -d "$TEST_DIR" ]; then
	echo -e "${RED}Error: Tests directory not found: $TEST_DIR${NC}"
	exit 1
fi

# Function to run specific test suite
run_suite() {
	local suite_name=$1
	local test_file=""
	
	case $suite_name in
		repository)
			test_file="Legal_Doc_Repository_Test.php"
			;;
		service)
			test_file="Legal_Doc_Service_Test.php"
			;;
		lifecycle)
			test_file="Legal_Doc_Lifecycle_Test.php"
			;;
		version)
			test_file="Legal_Doc_Version_Management_Test.php"
			;;
		rest-api)
			test_file="Legal_Doc_REST_API_Test.php"
			;;
		acceptance)
			test_file="Legal_Doc_Acceptance_Test.php"
			;;
		locale)
			test_file="Legal_Doc_Locale_Test.php"
			;;
		pdf)
			test_file="Legal_Doc_PDF_Generation_Test.php"
			;;
		shortcodes)
			test_file="Legal_Doc_Shortcodes_Test.php"
			;;
		all)
			test_file="Legal_Doc_All_Tests.php"
			;;
		*)
			echo -e "${RED}Unknown test suite: $suite_name${NC}"
			exit 1
			;;
	esac
	
	if [ ! -f "$TEST_DIR/$test_file" ]; then
		echo -e "${RED}Error: Test file not found: $test_file${NC}"
		exit 1
	fi
	
	echo -e "${YELLOW}Running test suite: $suite_name${NC}"
	echo ""
	
	if [ "$VERBOSE" = true ]; then
		php "$TEST_DIR/$test_file"
	else
		php "$TEST_DIR/$test_file" 2>&1
	fi
	
	local exit_code=$?
	
	if [ $exit_code -eq 0 ]; then
		echo ""
		echo -e "${GREEN}✓ Test suite passed: $suite_name${NC}"
	else
		echo ""
		echo -e "${RED}✗ Test suite failed: $suite_name${NC}"
	fi
	
	return $exit_code
}

# Main execution
if [ -n "$SUITE" ]; then
	run_suite "$SUITE"
	exit $?
else
	# Run all tests
	run_suite "all"
	exit $?
fi

