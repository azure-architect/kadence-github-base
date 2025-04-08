#!/bin/bash

# Client setup script for WordPress development template
# This script initializes a new client project from the template

# Text formatting
BOLD='\033[1m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if client name is provided
if [ -z "$1" ]; then
  echo -e "${RED}Please provide a client name${NC}"
  echo "Usage: ./setup.sh client-name"
  exit 1
fi

CLIENT_NAME=$1
CLIENT_SLUG=$(echo "$CLIENT_NAME" | tr '[:upper:]' '[:lower:]' | sed 's/ /-/g')

echo -e "${BOLD}Setting up project for client: ${GREEN}$CLIENT_NAME${NC}"
echo "Client slug: $CLIENT_SLUG"
echo

# Create client-specific directories if they don't exist
echo -e "${BOLD}Creating client-specific directories...${NC}"
mkdir -p "wp-content/themes/$CLIENT_SLUG"

# Create client-specific configs from templates
echo -e "${BOLD}Setting up configuration files...${NC}"

# Copy config templates and replace placeholders
for env in local staging production; do
  if [ -f "config/wp-config-$env.php" ]; then
    cp "config/wp-config-$env.php" "config/wp-config-$CLIENT_SLUG-$env.php"
    echo "Created config/wp-config-$CLIENT_SLUG-$env.php"
  fi
done

# Prompt for database details
echo -e "\n${BOLD}Database configuration:${NC}"
read -p "Database name [$CLIENT_SLUG]: " DB_NAME
DB_NAME=${DB_NAME:-$CLIENT_SLUG}

read -p "Database user [root]: " DB_USER
DB_USER=${DB_USER:-root}

read -p "Database password []: " DB_PASSWORD

read -p "Database host [localhost]: " DB_HOST
DB_HOST=${DB_HOST:-localhost}

# Replace placeholders in the config files
for env in local staging production; do
  if [ -f "config/wp-config-$CLIENT_SLUG-$env.php" ]; then
    sed -i "s/{{DB_NAME}}/$DB_NAME/g" "config/wp-config-$CLIENT_SLUG-$env.php"
    sed -i "s/{{DB_USER}}/$DB_USER/g" "config/wp-config-$CLIENT_SLUG-$env.php"
    sed -i "s/{{DB_PASSWORD}}/$DB_PASSWORD/g" "config/wp-config-$CLIENT_SLUG-$env.php"
    sed -i "s/{{DB_HOST}}/$DB_HOST/g" "config/wp-config-$CLIENT_SLUG-$env.php"
    sed -i "s/{{CLIENT_NAME}}/$CLIENT_NAME/g" "config/wp-config-$CLIENT_SLUG-$env.php"
    sed -i "s/{{CLIENT_SLUG}}/$CLIENT_SLUG/g" "config/wp-config-$CLIENT_SLUG-$env.php"
  fi
done

# Create client-specific GitHub workflow files
echo -e "\n${BOLD}Setting up GitHub workflows...${NC}"
if [ -f ".github/workflows/deploy.yml" ]; then
  sed -i "s/{{CLIENT_NAME}}/$CLIENT_NAME/g" ".github/workflows/deploy.yml"
  sed -i "s/{{CLIENT_SLUG}}/$CLIENT_SLUG/g" ".github/workflows/deploy.yml"
  echo "Updated .github/workflows/deploy.yml with client information"
fi

# Setup theme files
echo -e "\n${BOLD}Setting up theme files...${NC}"
if [ ! -d "wp-content/themes/$CLIENT_SLUG" ]; then
  mkdir -p "wp-content/themes/$CLIENT_SLUG"
fi

if [ -d "wp-content/themes/client-theme" ]; then
  # Copy template theme to client-specific theme
  cp -r "wp-content/themes/client-theme/"* "wp-content/themes/$CLIENT_SLUG/"
  
  # Update theme information
  if [ -f "wp-content/themes/$CLIENT_SLUG/style.css" ]; then
    sed -i "s/Theme Name: Client Theme/Theme Name: $CLIENT_NAME Theme/g" "wp-content/themes/$CLIENT_SLUG/style.css"
    sed -i "s/client-theme/$CLIENT_SLUG/g" "wp-content/themes/$CLIENT_SLUG/style.css"
    echo "Updated theme information in style.css"
  fi
fi

# Local by Flywheel setup recommendations
echo -e "\n${BOLD}Local by Flywheel setup:${NC}"
echo -e "${YELLOW}Please create a new site in Local by Flywheel with the following settings:${NC}"
echo "Site name: $CLIENT_NAME"
echo "Local site domain: $CLIENT_SLUG.local"
echo "WordPress username: admin"
echo "WordPress password: [Generate a strong password]"
echo "WordPress email: your@email.com"

# Final steps and instructions
echo -e "\n${BOLD}${GREEN}Setup complete!${NC}"
echo -e "${BOLD}Next steps:${NC}"
echo "1. Create a new site in Local by Flywheel using the settings above"
echo "2. Copy the customized files to your Local by Flywheel site directory"
echo "3. Initialize Git in the Local site and connect to your client repository"
echo "4. Update the README.md with client-specific information"
echo -e "${YELLOW}Don't forget to add the necessary GitHub secrets for deployment!${NC}"

exit 0