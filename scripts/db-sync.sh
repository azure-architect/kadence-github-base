#!/bin/bash

# Database Synchronization Script for WordPress client sites
# This script handles syncing databases between different environments

# Text formatting
BOLD='\033[1m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Function to display usage information
usage() {
  echo -e "${BOLD}WordPress Database Synchronization Script${NC}"
  echo
  echo "Usage: $0 [action] [environment] [options]"
  echo
  echo "Actions:"
  echo "  pull        Pull database from remote environment to local"
  echo "  push        Push database from local to remote environment (use with caution!)"
  echo "  backup      Create a backup of the database in specified environment"
  echo
  echo "Environments:"
  echo "  local       Local development environment"
  echo "  staging     Staging/testing environment"
  echo "  production  Production environment"
  echo
  echo "Options:"
  echo "  --no-search-replace  Skip search and replace operations"
  echo "  --dry-run            Show what would be done without making changes"
  echo "  --help               Display this help message"
  echo
  echo "Examples:"
  echo "  $0 pull production   # Pull production database to local"
  echo "  $0 backup staging    # Create a backup of staging database"
  exit 1
}

# Check for minimum arguments
if [ $# -lt 2 ]; then
  usage
fi

# Parse arguments
ACTION=$1
ENV=$2
NO_SEARCH_REPLACE=0
DRY_RUN=0

# Parse additional options
shift 2
while [ $# -gt 0 ]; do
  case "$1" in
    --no-search-replace)
      NO_SEARCH_REPLACE=1
      ;;
    --dry-run)
      DRY_RUN=1
      ;;
    --help)
      usage
      ;;
    *)
      echo -e "${RED}Unknown option: $1${NC}"
      usage
      ;;
  esac
  shift
done

# Validate action
if [[ ! "$ACTION" =~ ^(pull|push|backup)$ ]]; then
  echo -e "${RED}Error: Invalid action '$ACTION'${NC}"
  usage
fi

# Validate environment
if [[ ! "$ENV" =~ ^(local|staging|production)$ ]]; then
  echo -e "${RED}Error: Invalid environment '$ENV'${NC}"
  usage
fi

# Load configuration for the client
CLIENT_CONFIG="./.db-sync-config"
if [ ! -f "$CLIENT_CONFIG" ]; then
  echo -e "${YELLOW}Configuration file not found. Creating default...${NC}"
  
  # Create default configuration
  cat > "$CLIENT_CONFIG" << EOL
# Database Sync Configuration

# Local Database
LOCAL_DB_HOST="localhost"
LOCAL_DB_NAME="wordpress"
LOCAL_DB_USER="root"
LOCAL_DB_PASS=""
LOCAL_URL="http://localhost"

# Staging Database
STAGING_DB_HOST="staging-server.com"
STAGING_DB_NAME="staging_db"
STAGING_DB_USER="staging_user"
STAGING_DB_PASS="staging_password" 
STAGING_REMOTE_PATH="/var/www/staging"
STAGING_URL="https://staging.example.com"

# Production Database
PRODUCTION_DB_HOST="production-server.com"
PRODUCTION_DB_NAME="production_db"
PRODUCTION_DB_USER="production_user"
PRODUCTION_DB_PASS="production_password"
PRODUCTION_REMOTE_PATH="/var/www/production"
PRODUCTION_URL="https://www.example.com"

# SSH Connection Info
STAGING_SSH_USER="user"
STAGING_SSH_HOST="staging-server.com"

PRODUCTION_SSH_USER="user"
PRODUCTION_SSH_HOST="production-server.com"

# Backup Settings
BACKUP_DIR="./backups"
BACKUP_RETAIN_DAYS=30
EOL
  
  echo -e "${RED}Please edit $CLIENT_CONFIG with your client's database information${NC}"
  echo "After configuring, run this script again."
  exit 1
fi

# Source the configuration file
source "$CLIENT_CONFIG"

# Set up variables based on the environment
if [ "$ENV" == "production" ]; then
  REMOTE_DB_HOST=$PRODUCTION_DB_HOST
  REMOTE_DB_NAME=$PRODUCTION_DB_NAME
  REMOTE_DB_USER=$PRODUCTION_DB_USER
  REMOTE_DB_PASS=$PRODUCTION_DB_PASS
  REMOTE_URL=$PRODUCTION_URL
  REMOTE_PATH=$PRODUCTION_REMOTE_PATH
  SSH_USER=$PRODUCTION_SSH_USER
  SSH_HOST=$PRODUCTION_SSH_HOST
elif [ "$ENV" == "staging" ]; then
  REMOTE_DB_HOST=$STAGING_DB_HOST
  REMOTE_DB_NAME=$STAGING_DB_NAME
  REMOTE_DB_USER=$STAGING_DB_USER
  REMOTE_DB_PASS=$STAGING_DB_PASS
  REMOTE_URL=$STAGING_URL
  REMOTE_PATH=$STAGING_REMOTE_PATH
  SSH_USER=$STAGING_SSH_USER
  SSH_HOST=$STAGING_SSH_HOST
fi

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Generate timestamp for file names
TIMESTAMP=$(date +%Y%m%d-%H%M%S)

# Function to create a database backup
backup_database() {
  local env=$1
  local host=$2
  local dbname=$3
  local user=$4
  local pass=$5
  local backup_file="$BACKUP_DIR/${env}_${dbname}_${TIMESTAMP}.sql"
  
  echo -e "${BOLD}Creating backup of $env database...${NC}"
  
  if [ "$env" == "local" ]; then
    # Local backup
    if [ $DRY_RUN -eq 1 ]; then
      echo "[DRY RUN] Would run: mysqldump --host=$host --user=$user --password=*** $dbname > $backup_file"
    else
      if [ -z "$pass" ]; then
        mysqldump --host="$host" --user="$user" "$dbname" > "$backup_file"
      else
        mysqldump --host="$host" --user="$user" --password="$pass" "$dbname" > "$backup_file"
      fi
      
      if [ $? -eq 0 ]; then
        echo -e "${GREEN}Backup created: $backup_file${NC}"
        gzip "$backup_file"
        echo "File compressed: ${backup_file}.gz"
      else
        echo -e "${RED}Error creating backup!${NC}"
        exit 1
      fi
    fi
  else
    # Remote backup
    if [ $DRY_RUN -eq 1 ]; then
      echo "[DRY RUN] Would run remote mysqldump on $SSH_USER@$SSH_HOST"
    else
      ssh "$SSH_USER@$SSH_HOST" "mysqldump --host='$host' --user='$user' --password='$pass' '$dbname' > /tmp/db_backup_$TIMESTAMP.sql"
      scp "$SSH_USER@$SSH_HOST:/tmp/db_backup_$TIMESTAMP.sql" "$backup_file"
      ssh "$SSH_USER@$SSH_HOST" "rm /tmp/db_backup_$TIMESTAMP.sql"
      
      if [ $? -eq 0 ]; then
        echo -e "${GREEN}Backup created: $backup_file${NC}"
        gzip "$backup_file"
        echo "File compressed: ${backup_file}.gz"
      else
        echo -e "${RED}Error creating remote backup!${NC}"
        exit 1
      fi
    fi
  fi
}

# Function to pull database from remote to local
pull_database() {
  echo -e "${BOLD}Pulling database from $ENV to local...${NC}"
  
  # Backup local database first
  backup_database "local" "$LOCAL_DB_HOST" "$LOCAL_DB_NAME" "$LOCAL_DB_USER" "$LOCAL_DB_PASS"
  
  # Backup remote database
  backup_database "$ENV" "$REMOTE_DB_HOST" "$REMOTE_DB_NAME" "$REMOTE_DB_USER" "$REMOTE_DB_PASS"
  
  # Get the latest backup file
  LATEST_BACKUP=$(ls -t "$BACKUP_DIR/${ENV}_${REMOTE_DB_NAME}_*.sql.gz" | head -1)
  
  if [ -z "$LATEST_BACKUP" ]; then
    echo -e "${RED}Error: No backup file found!${NC}"
    exit 1
  fi
  
  echo "Using backup file: $LATEST_BACKUP"
  gunzip -c "$LATEST_BACKUP" > "${LATEST_BACKUP%.gz}"
  
  # Import to local database
  if [ $DRY_RUN -eq 1 ]; then
    echo "[DRY RUN] Would import ${LATEST_BACKUP%.gz} to local database"
  else
    echo "Importing to local database..."
    if [ -z "$LOCAL_DB_PASS" ]; then
      mysql --host="$LOCAL_DB_HOST" --user="$LOCAL_DB_USER" "$LOCAL_DB_NAME" < "${LATEST_BACKUP%.gz}"
    else
      mysql --host="$LOCAL_DB_HOST" --user="$LOCAL_DB_USER" --password="$LOCAL_DB_PASS" "$LOCAL_DB_NAME" < "${LATEST_BACKUP%.gz}"
    fi
    
    if [ $? -eq 0 ]; then
      echo -e "${GREEN}Database imported successfully!${NC}"
    else
      echo -e "${RED}Error importing database!${NC}"
      exit 1
    fi
  fi
  
  # Run search and replace if needed
  if [ $NO_SEARCH_REPLACE -eq 0 ]; then
    echo "Running search and replace..."
    if [ $DRY_RUN -eq 1 ]; then
      echo "[DRY RUN] Would replace $REMOTE_URL with $LOCAL_URL in database"
    else
      # Check if wp-cli is available
      if command -v wp >/dev/null 2>&1; then
        echo "Using WP-CLI for search and replace..."
        wp search-replace "$REMOTE_URL" "$LOCAL_URL" --all-tables
      else
        echo -e "${YELLOW}WP-CLI not found. Manual search and replace required.${NC}"
        echo "Please replace $REMOTE_URL with $LOCAL_URL in your database."
      fi
    fi
  fi
  
  # Clean up
  rm -f "${LATEST_BACKUP%.gz}"
  
  echo -e "${BOLD}${GREEN}Database pull complete!${NC}"
}

# Function to push database from local to remote
push_database() {
  echo -e "${BOLD}${RED}WARNING: You are about to OVERWRITE the $ENV database!${NC}"
  echo -e "${RED}This action cannot be undone.${NC}"
  echo
  
  if [ $DRY_RUN -eq 0 ]; then
    read -p "Are you ABSOLUTELY sure you want to continue? (type 'yes' to confirm): " CONFIRM
    if [ "$CONFIRM" != "yes" ]; then
      echo "Operation cancelled."
      exit 0
    fi
    
    read -p "Please type the environment name ($ENV) again to confirm: " CONFIRM_ENV
    if [ "$CONFIRM_ENV" != "$ENV" ]; then
      echo "Environment confirmation failed. Operation cancelled."
      exit 0
    fi
  fi
  
  # Backup remote database first
  backup_database "$ENV" "$REMOTE_DB_HOST" "$REMOTE_DB_NAME" "$REMOTE_DB_USER" "$REMOTE_DB_PASS"
  
  # Backup local database
  backup_database "local" "$LOCAL_DB_HOST" "$LOCAL_DB_NAME" "$LOCAL_DB_USER" "$LOCAL_DB_PASS"
  
  # Get the latest local backup
  LATEST_BACKUP=$(ls -t "$BACKUP_DIR/local_${LOCAL_DB_NAME}_*.sql.gz" | head -1)
  
  if [ -z "$LATEST_BACKUP" ]; then
    echo -e "${RED}Error: No backup file found!${NC}"
    exit 1
  fi
  
  echo "Using backup file: $LATEST_BACKUP"
  gunzip -c "$LATEST_BACKUP" > "${LATEST_BACKUP%.gz}"
  
  # Run search and replace on the local backup if needed
  if [ $NO_SEARCH_REPLACE -eq 0 ]; then
    if [ $DRY_RUN -eq 1 ]; then
      echo "[DRY RUN] Would replace $LOCAL_URL with $REMOTE_URL in database dump"
    else
      echo "Preparing database for $ENV environment..."
      # Create temporary database for search and replace
      TEMP_DB="temp_${ENV}_${TIMESTAMP}"
      echo "Creating temporary database $TEMP_DB..."
      mysql --host="$LOCAL_DB_HOST" --user="$LOCAL_DB_USER" ${LOCAL_DB_PASS:+--password="$LOCAL_DB_PASS"} -e "CREATE DATABASE $TEMP_DB"
      
      # Import backup to temporary database
      mysql --host="$LOCAL_DB_HOST" --user="$LOCAL_DB_USER" ${LOCAL_DB_PASS:+--password="$LOCAL_DB_PASS"} "$TEMP_DB" < "${LATEST_BACKUP%.gz}"
      
      # Run search and replace
      if command -v wp >/dev/null 2>&1; then
        echo "Using WP-CLI for search and replace..."
        WP_CONFIG_PATH=$(pwd)/wp-config.php
        WP_PATH=$(pwd)
        
        # Temporarily modify wp-config to use temp database
        cp "$WP_CONFIG_PATH" "${WP_CONFIG_PATH}.bak"
        sed -i "s/define( *'DB_NAME', *'[^']*' *);/define( 'DB_NAME', '$TEMP_DB' );/g" "$WP_CONFIG_PATH"
        
        # Run search and replace
        cd "$WP_PATH" && wp search-replace "$LOCAL_URL" "$REMOTE_URL" --all-tables
        
        # Export the modified database
        mysql --host="$LOCAL_DB_HOST" --user="$LOCAL_DB_USER" ${LOCAL_DB_PASS:+--password="$LOCAL_DB_PASS"} -e "DROP DATABASE $TEMP_DB"
        
        # Restore original wp-config
        mv "${WP_CONFIG_PATH}.bak" "$WP_CONFIG_PATH"
      else
        echo -e "${YELLOW}WP-CLI not found. Manual search and replace required.${NC}"
        echo "Please manually replace $LOCAL_URL with $REMOTE_URL before pushing."
        read -p "Have you performed the necessary search and replace? (y/n): " PERFORMED_REPLACE
        if [ "$PERFORMED_REPLACE" != "y" ]; then
          echo "Operation cancelled."
          exit 0
        fi
      fi
    fi
  fi
  
  # Upload to remote server
  if [ $DRY_RUN -eq 1 ]; then
    echo "[DRY RUN] Would upload database to $ENV and import"
  else
    echo "Uploading database to $ENV server..."
    scp "${LATEST_BACKUP%.gz}" "$SSH_USER@$SSH_HOST:/tmp/db_upload.sql"
    
    echo "Importing database on $ENV server..."
    ssh "$SSH_USER@$SSH_HOST" "mysql --host='$REMOTE_DB_HOST' --user='$REMOTE_DB_USER' --password='$REMOTE_DB_PASS' '$REMOTE_DB_NAME' < /tmp/db_upload.sql && rm /tmp/db_upload.sql"
    
    if [ $? -eq 0 ]; then
      echo -e "${GREEN}Database successfully pushed to $ENV!${NC}"
    else
      echo -e "${RED}Error pushing database to $ENV!${NC}"
      exit 1
    fi
  fi
  
  # Clean up
  rm -f "${LATEST_BACKUP%.gz}"
  
  echo -e "${BOLD}${GREEN}Database push complete!${NC}"
}

# Clean up old backups
cleanup_old_backups() {
  if [ -d "$BACKUP_DIR" ]; then
    echo "Cleaning up old backups..."
    find "$BACKUP_DIR" -name "*.sql.gz" -type f -mtime +$BACKUP_RETAIN_DAYS -delete
  fi
}

# Execute the requested action
case "$ACTION" in
  pull)
    pull_database
    ;;
  push)
    push_database
    ;;
  backup)
    if [ "$ENV" == "local" ]; then
      backup_database "local" "$LOCAL_DB_HOST" "$LOCAL_DB_NAME" "$LOCAL_DB_USER" "$LOCAL_DB_PASS"
    else
      backup_database "$ENV" "$REMOTE_DB_HOST" "$REMOTE_DB_NAME" "$REMOTE_DB_USER" "$REMOTE_DB_PASS"
    fi
    ;;
esac

# Always clean up old backups after an operation
cleanup_old_backups

echo -e "${BOLD}Operation completed successfully!${NC}"
exit 0