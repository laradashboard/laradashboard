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

# Install dependencies
echo -e "${GREEN}Installing composer dependencies...${NC}"
composer install --no-dev --optimize-autoloader

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
