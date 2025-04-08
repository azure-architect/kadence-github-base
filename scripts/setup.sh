#!/bin/bash

# Client setup script for WordPress development template
# This script initializes a new client project from the template

# Text formatting
BOLD='\033[1m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Default values
USE_LOCAL_BY_FLYWHEEL=false

# Process command line arguments
while [[ $# -gt 0 ]]; do
  case $1 in
    --local-by-flywheel)
      USE_LOCAL_BY_FLYWHEEL=true
      shift
      ;;
    --*)
      echo "Unknown option $1"
      exit 1
      ;;
    *)
      if [ -z "$CLIENT_NAME" ]; then
        CLIENT_NAME=$1
      else
        echo "Unknown argument $1"
        exit 1
      fi
      shift
      ;;
  esac
done

# Check if client name is provided
if [ -z "$CLIENT_NAME" ]; then
  echo -e "${RED}Please provide a client name${NC}"
  echo "Usage: ./setup.sh client-name [--local-by-flywheel]"
  exit 1
fi

CLIENT_SLUG=$(echo "$CLIENT_NAME" | tr '[:upper:]' '[:lower:]' | sed 's/ /-/g')

echo -e "${BOLD}Setting up project for client: ${GREEN}$CLIENT_NAME${NC}"
echo "Client slug: $CLIENT_SLUG"
if [ "$USE_LOCAL_BY_FLYWHEEL" = true ]; then
  echo -e "${YELLOW}Using Local by Flywheel configuration${NC}"
fi
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

# Set default database values based on environment
if [ "$USE_LOCAL_BY_FLYWHEEL" = true ]; then
  echo -e "\n${BOLD}Using Local by Flywheel database settings:${NC}"
  DB_NAME="local"
  DB_USER="root"
  DB_PASSWORD="root"
  DB_HOST="localhost"
  echo "Database name: $DB_NAME (Local by Flywheel default)"
  echo "Database user: $DB_USER"
  echo "Database password: $DB_PASSWORD"
  echo "Database host: $DB_HOST"
else
  # Prompt for database details
  echo -e "\n${BOLD}Database configuration:${NC}"
  read -p "Database name [$CLIENT_SLUG]: " DB_NAME
  DB_NAME=${DB_NAME:-$CLIENT_SLUG}

  read -p "Database user [root]: " DB_USER
  DB_USER=${DB_USER:-root}

  read -p "Database password []: " DB_PASSWORD

  read -p "Database host [localhost]: " DB_HOST
  DB_HOST=${DB_HOST:-localhost}
fi

# Escape special characters in variables
DB_NAME_ESC=$(echo "$DB_NAME" | sed 's/[\/&]/\\&/g')
DB_USER_ESC=$(echo "$DB_USER" | sed 's/[\/&]/\\&/g')
DB_PASSWORD_ESC=$(echo "$DB_PASSWORD" | sed 's/[\/&]/\\&/g')
DB_HOST_ESC=$(echo "$DB_HOST" | sed 's/[\/&]/\\&/g')
CLIENT_NAME_ESC=$(echo "$CLIENT_NAME" | sed 's/[\/&]/\\&/g')
CLIENT_SLUG_ESC=$(echo "$CLIENT_SLUG" | sed 's/[\/&]/\\&/g')

# Replace placeholders in the config files
for env in local staging production; do
  if [ -f "config/wp-config-$CLIENT_SLUG-$env.php" ]; then
    sed -i '' "s/{{DB_NAME}}/$DB_NAME_ESC/g" "config/wp-config-$CLIENT_SLUG-$env.php"
    sed -i '' "s/{{DB_USER}}/$DB_USER_ESC/g" "config/wp-config-$CLIENT_SLUG-$env.php"
    sed -i '' "s/{{DB_PASSWORD}}/$DB_PASSWORD_ESC/g" "config/wp-config-$CLIENT_SLUG-$env.php"
    sed -i '' "s/{{DB_HOST}}/$DB_HOST_ESC/g" "config/wp-config-$CLIENT_SLUG-$env.php"
    sed -i '' "s/{{CLIENT_NAME}}/$CLIENT_NAME_ESC/g" "config/wp-config-$CLIENT_SLUG-$env.php"
    sed -i '' "s/{{CLIENT_SLUG}}/$CLIENT_SLUG_ESC/g" "config/wp-config-$CLIENT_SLUG-$env.php"
    
    # Verify placeholders were replaced
    if grep -q "{{" "config/wp-config-$CLIENT_SLUG-$env.php"; then
      echo -e "${RED}Warning: Some placeholders not replaced in config/wp-config-$CLIENT_SLUG-$env.php${NC}"
    fi
  fi
done

# Create client-specific GitHub workflow files
echo -e "\n${BOLD}Setting up GitHub workflows...${NC}"
if [ -f ".github/workflows/deploy.yml" ]; then
  sed -i '' "s/{{CLIENT_NAME}}/$CLIENT_NAME_ESC/g" ".github/workflows/deploy.yml"
  sed -i '' "s/{{CLIENT_SLUG}}/$CLIENT_SLUG_ESC/g" ".github/workflows/deploy.yml"
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
    sed -i '' "s/Theme Name: Client Theme/Theme Name: $CLIENT_NAME_ESC Theme/g" "wp-content/themes/$CLIENT_SLUG/style.css"
    sed -i '' "s/client-theme/$CLIENT_SLUG_ESC/g" "wp-content/themes/$CLIENT_SLUG/style.css"
    echo "Updated theme information in style.css"
  fi
fi

# Function to download and set up Kadence theme and plugins
setup_kadence() {
  echo -e "\n${BOLD}Setting up Kadence theme and plugins...${NC}"
  
  # Create directories if they don't exist
  mkdir -p "wp-content/themes"
  mkdir -p "wp-content/plugins"
  
  # Download Kadence theme
  echo "Downloading Kadence theme..."
  curl -L https://downloads.wordpress.org/theme/kadence.latest-stable.zip -o kadence.zip
  unzip -q kadence.zip -d wp-content/themes/
  rm kadence.zip
  
  # Download essential Kadence plugins
  echo "Downloading Kadence Blocks plugin..."
  curl -L https://downloads.wordpress.org/plugin/kadence-blocks.latest-stable.zip -o kadence-blocks.zip
  unzip -q kadence-blocks.zip -d wp-content/plugins/
  rm kadence-blocks.zip
  
  echo "Downloading Kadence Starter Templates plugin..."
  curl -L https://downloads.wordpress.org/plugin/kadence-starter-templates.latest-stable.zip -o kadence-starter-templates.zip
  unzip -q kadence-starter-templates.zip -d wp-content/plugins/
  rm kadence-starter-templates.zip
  
  # Add other plugins you commonly use
  echo "Downloading additional plugins..."
  
  # Advanced Custom Fields
  curl -L https://downloads.wordpress.org/plugin/advanced-custom-fields.latest-stable.zip -o acf.zip
  unzip -q acf.zip -d wp-content/plugins/
  rm acf.zip
  
  # Wordfence Security
  curl -L https://downloads.wordpress.org/plugin/wordfence.latest-stable.zip -o wordfence.zip
  unzip -q wordfence.zip -d wp-content/plugins/
  rm wordfence.zip
  
  # Yoast SEO
  curl -L https://downloads.wordpress.org/plugin/wordpress-seo.latest-stable.zip -o wordpress-seo.zip
  unzip -q wordpress-seo.zip -d wp-content/plugins/
  rm wordpress-seo.zip
  
  echo -e "${GREEN}Kadence theme and plugins setup complete!${NC}"
}

# Call the Kadence setup function
setup_kadence

# Copy the appropriate wp-config file to the root
echo -e "\n${BOLD}Setting up WordPress configuration...${NC}"
if [ -f "config/wp-config-$CLIENT_SLUG-local.php" ]; then
  cp "config/wp-config-$CLIENT_SLUG-local.php" "wp-config.php"
  if [ ! -f "wp-config.php" ]; then
    echo -e "${RED}Failed to copy wp-config file${NC}"
    exit 1
  fi
  echo "Created wp-config.php from template"
else
  echo -e "${RED}Config template not found: config/wp-config-$CLIENT_SLUG-local.php${NC}"
  exit 1
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