#!/bin/bash

# Exit on error
set -e

# Define colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Define paths
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
RELEASE_DIR="$PROJECT_ROOT/releases"

# Trap to restore composer.json if script fails
cleanup_on_error() {
    if [ -f "$PROJECT_ROOT/composer.json.backup" ]; then
        echo -e "${RED}Error occurred. Restoring original composer.json...${NC}"
        mv "$PROJECT_ROOT/composer.json.backup" "$PROJECT_ROOT/composer.json"
    fi
}
trap cleanup_on_error ERR

# Get version from package.json
VERSION=$(grep -o '"version": "[^"]*' "$PROJECT_ROOT/package.json" | grep -o '[0-9]\+\.[0-9]\+\.[0-9]\+')
if [ -z "$VERSION" ]; then
    echo -e "${YELLOW}Warning: Could not extract version from package.json, using timestamp instead.${NC}"
    VERSION=$(date +"%Y%m%d_%H%M%S")
fi

RELEASE_NAME="laradashboard-v$VERSION"
RELEASE_PATH="$RELEASE_DIR/$RELEASE_NAME"
EXCLUDE_FILE="$SCRIPT_DIR/exclude-from-zip.txt"

# Clean up RELEASE_DIR if it exists first.
if [ -d "$RELEASE_DIR" ]; then
    echo -e "${YELLOW}Cleaning up previous release directory...${NC}"
    rm -rf "$RELEASE_DIR"
fi

# Create exclude file if it doesn't exist
if [ ! -f "$EXCLUDE_FILE" ]; then
    echo "Creating exclude file..."
    cat > "$EXCLUDE_FILE" << EOL
node_modules/
demo-screenshots/
modules/
.git/
.github/
releases/
.DS_Store
.env
.env.*
.phpunit.result.cache
npm-debug.log
yarn-error.log
storage/*.key
EOL
fi

# Create release directory if it doesn't exist
mkdir -p "$RELEASE_DIR"

echo -e "${YELLOW}Starting release build process for version $VERSION...${NC}"

# Check for Node.js availability
if command -v node &> /dev/null; then
    NODE_VERSION=$(node -v)
    echo -e "${GREEN}Using Node.js version: ${NODE_VERSION}${NC}"
    
    # Check if Node.js version is appropriate (v20.x recommended)
    if [[ "$NODE_VERSION" != *"v20"* ]]; then
        echo -e "${YELLOW}Warning: Node.js version $NODE_VERSION detected. This project recommends v20.x${NC}"
        echo -e "${YELLOW}Continuing with available Node.js version...${NC}"
    fi
else
    echo -e "${RED}Node.js not found. Please install Node.js to build frontend assets.${NC}"
    echo -e "${YELLOW}Attempting to continue without Node.js...${NC}"
fi

# Clear bootstrap cache to avoid stale provider references (like debugbar from dev)
echo -e "${GREEN}Clearing bootstrap cache...${NC}"
rm -f "$PROJECT_ROOT/bootstrap/cache/packages.php"
rm -f "$PROJECT_ROOT/bootstrap/cache/services.php"

# Create a clean composer.json without module autoload entries for the release build
echo -e "${GREEN}Creating clean composer.json for release build...${NC}"
cp "$PROJECT_ROOT/composer.json" "$PROJECT_ROOT/composer.json.backup"

# Remove module autoload entries and merge-plugin config using PHP
php -r '
$json = json_decode(file_get_contents("'"$PROJECT_ROOT"'/composer.json"), true);

// Remove module-related autoload entries
$json["autoload"]["psr-4"] = array_filter($json["autoload"]["psr-4"], function($path, $namespace) {
    return strpos($path, "modules/") !== 0 && strpos($namespace, "Modules\\\\") !== 0;
}, ARRAY_FILTER_USE_BOTH);

// Remove merge-plugin config that includes modules
if (isset($json["extra"]["merge-plugin"])) {
    unset($json["extra"]["merge-plugin"]);
}

// Remove wikimedia/composer-merge-plugin from allow-plugins since we removed merge-plugin
if (isset($json["config"]["allow-plugins"]["wikimedia/composer-merge-plugin"])) {
    unset($json["config"]["allow-plugins"]["wikimedia/composer-merge-plugin"]);
}

file_put_contents("'"$PROJECT_ROOT"'/composer.json", json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
'

# Install dependencies (skip scripts to avoid package:discover loading dev providers)
echo -e "${GREEN}Installing composer dependencies...${NC}"
composer install --no-dev --optimize-autoloader --no-scripts

# Restore original composer.json
echo -e "${GREEN}Restoring original composer.json...${NC}"
mv "$PROJECT_ROOT/composer.json.backup" "$PROJECT_ROOT/composer.json"

# Only run npm commands if Node.js is available
if command -v node &> /dev/null; then
    echo -e "${GREEN}Installing npm packages...${NC}"
    if command -v npm &> /dev/null; then
        npm ci || npm install
    else
        echo -e "${RED}npm not found. Skipping npm install step.${NC}"
    fi

    echo -e "${GREEN}Building frontend assets...${NC}"
    if command -v npm &> /dev/null; then
        npm run build
    else
        echo -e "${RED}npm not found. Skipping frontend build step.${NC}"
    fi
else
    echo -e "${RED}Skipping npm steps due to missing Node.js${NC}"
fi

# Create a fresh copy for distribution
echo -e "${GREEN}Creating release directory at: $RELEASE_PATH${NC}"
mkdir -p "$RELEASE_PATH"

# Copy all files to the release directory, except those in exclude file
echo -e "${GREEN}Copying project files...${NC}"
rsync -av --exclude-from="$EXCLUDE_FILE" "$PROJECT_ROOT/" "$RELEASE_PATH/"

# Create the .env.example file in the release
echo -e "${GREEN}Ensuring .env.example exists in the release...${NC}"
if [ -f "$PROJECT_ROOT/.env.example" ]; then
    cp "$PROJECT_ROOT/.env.example" "$RELEASE_PATH/.env.example"
fi

# Create zip file
echo -e "${GREEN}Creating zip archive...${NC}"
cd "$RELEASE_DIR"
zip -r "${RELEASE_NAME}.zip" "$RELEASE_NAME"

# Clean up
echo -e "${GREEN}Cleaning up temporary files...${NC}"
rm -rf "$RELEASE_PATH"

echo -e "${GREEN}Release build completed successfully!${NC}"
echo -e "${GREEN}Release zip file: $RELEASE_DIR/${RELEASE_NAME}.zip${NC}"

# Optional: List the created files
ls -lh "$RELEASE_DIR"/${RELEASE_NAME}.zip

echo -e "${YELLOW}Note: Dev dependencies were removed. Run 'composer install' to restore them for development.${NC}"
