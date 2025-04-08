#!/bin/bash

# Deployment script for WordPress client sites
# This script handles the deployment of WordPress files to the server

# Text formatting
BOLD='\033[1m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Validate environment variables
if [ -z "$CLIENT_HOST" ] || [ -z "$CLIENT_PATH" ]; then
  echo -e "${RED}Error: Missing required environment variables${NC}"
  echo "Please make sure the following environment variables are set:"
  echo "  - CLIENT_HOST (e.g., user@example.com)"
  echo "  - CLIENT_PATH (e.g., /var/www/html/client-site)"
  exit 1
fi

# Define default values
DEPLOY_BRANCH=${DEPLOY_BRANCH:-"main"}
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)

# Check if we're on the deployment branch
if [ "$CURRENT_BRANCH" != "$DEPLOY_BRANCH" ]; then
  echo -e "${YELLOW}Warning: You are not on the $DEPLOY_BRANCH branch.${NC}"
  echo -e "Current branch: ${BOLD}$CURRENT_BRANCH${NC}"
  
  read -p "Do you want to continue with deployment? (y/n): " CONTINUE
  if [ "$CONTINUE" != "y" ]; then
    echo "Deployment aborted."
    exit 0
  fi
fi

echo -e "${BOLD}Starting deployment to ${GREEN}$CLIENT_HOST:$CLIENT_PATH${NC}"

# Create deploy exclusion list if it doesn't exist
if [ ! -f ".deployignore" ]; then
  echo -e "${YELLOW}Creating default .deployignore file...${NC}"
  cat > .deployignore << EOL
.git/
.github/
.gitignore
.deployignore
/node_modules/
/vendor/
/scripts/
/config/
README.md
*.log
*.sql
*.sqlite
.DS_Store
Thumbs.db
local-*.*
EOL
  echo "Created .deployignore file with default exclusions"
fi

# Create temporary directory with timestamp
TIMESTAMP=$(date +%Y%m%d%H%M%S)
TEMP_DIR="/tmp/deploy-$TIMESTAMP"
mkdir -p "$TEMP_DIR"

echo "Created temporary directory: $TEMP_DIR"

# Check if server directory exists, create if not
echo "Checking if remote directory exists..."
ssh "$CLIENT_HOST" "[ -d '$CLIENT_PATH' ] || mkdir -p '$CLIENT_PATH'"

# Copy files to temporary directory, excluding items in .deployignore
echo "Copying files to staging area..."
rsync -av --exclude-from='.deployignore' ./ "$TEMP_DIR/"

# Create a backup of the remote files (excluding uploads)
echo "Creating backup of existing files on server..."
ssh "$CLIENT_HOST" "if [ -d '$CLIENT_PATH' ]; then mkdir -p '${CLIENT_PATH}_backup_$TIMESTAMP'; rsync -a --exclude='wp-content/uploads' '$CLIENT_PATH/' '${CLIENT_PATH}_backup_$TIMESTAMP/'; fi"

# Deploy files to server
echo "Deploying files to server..."
rsync -avz --delete --exclude="wp-content/uploads" --exclude="wp-config.php" "$TEMP_DIR/" "$CLIENT_HOST:$CLIENT_PATH/"

# Clean up
echo "Cleaning up temporary files..."
rm -rf "$TEMP_DIR"

echo -e "${BOLD}${GREEN}Deployment complete!${NC}"
echo "Files deployed to: $CLIENT_HOST:$CLIENT_PATH"
echo "Backup created at: ${CLIENT_PATH}_backup_$TIMESTAMP"
echo

# Provide post-deployment instructions
echo -e "${BOLD}Post-deployment steps:${NC}"
echo "1. Verify the site is functioning correctly"
echo "2. Check for any plugin or theme updates needed"
echo "3. Clear any caches if necessary"

exit 0