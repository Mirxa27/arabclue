#!/bin/bash

# MySQL PHP Fixer - Main Script
# Handles MySQL database operations for PHP websites

set -euo pipefail

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BACKUP_DIR="${SCRIPT_DIR}/backups"
LOG_FILE="${SCRIPT_DIR}/mysql_fixer.log"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

# Error handling
error_exit() {
    log "${RED}ERROR: $1${NC}"
    exit 1
}

# Success message
success() {
    log "${GREEN}SUCCESS: $1${NC}"
}

# Warning message
warning() {
    log "${YELLOW}WARNING: $1${NC}"
}

# Info message
info() {
    log "${BLUE}INFO: $1${NC}"
}

# Create backup directory
setup_backup_dir() {
    mkdir -p "$BACKUP_DIR"
    info "Backup directory created: $BACKUP_DIR"
}

# Test MySQL connection
test_connection() {
    local host="$1"
    local port="$2"
    local user="$3"
    local password="$4"
    local database="$5"
    
    info "Testing MySQL connection to $host:$port..."
    
    # First, test if port is open
    info "Checking network connectivity..."
    if command -v nc >/dev/null 2>&1; then
        if nc -zv "$host" "$port" 2>&1 | grep -q "succeeded"; then
            success "Port $port is open on $host"
        else
            error_exit "Port $port is not reachable on $host. Check firewall and MySQL server status."
        fi
    else
        warning "nc (netcat) not available - skipping port check"
    fi
    
    # Try MySQL connection
    if command -v mysql >/dev/null 2>&1; then
        if mysql -h"$host" -P"$port" -u"$user" -p"$password" -e "USE $database; SELECT 1;" >/dev/null 2>&1; then
            success "Connection successful"
            return 0
        else
            error_exit "Connection failed - Check credentials and permissions"
        fi
    else
        error_exit "mysql client not installed. Cannot test full connection."
        info "For detailed testing, upload test_mysql.php to your PHP server"
        info "Or install MySQL client: brew install mysql-client (macOS)"
    fi
}

# Create database backup
create_backup() {
    local host="$1"
    local port="$2"
    local user="$3"
    local password="$4"
    local database="$5"
    local backup_name="$6"
    
    info "Creating backup: $backup_name"
    
    if mysqldump -h"$host" -P"$port" -u"$user" -p"$password" \
        --single-transaction \
        --routines \
        --triggers \
        "$database" > "$BACKUP_DIR/$backup_name.sql"; then
        success "Backup created: $BACKUP_DIR/$backup_name.sql"
        
        # Compress backup
        gzip "$BACKUP_DIR/$backup_name.sql"
        success "Backup compressed: $BACKUP_DIR/$backup_name.sql.gz"
        
        return 0
    else
        error_exit "Backup failed"
    fi
}

# Check database health
check_database_health() {
    local host="$1"
    local port="$2"
    local user="$3"
    local password="$4"
    local database="$5"
    
    info "Checking database health..."
    
    # Check table status
    mysql -h"$host" -P"$port" -u"$user" -p"$password" -e "
        USE $database;
        SELECT TABLE_NAME, ENGINE, TABLE_ROWS, DATA_LENGTH, INDEX_LENGTH 
        FROM information_schema.TABLES 
        WHERE TABLE_SCHEMA = '$database' AND TABLE_TYPE = 'BASE TABLE';
    " | tee -a "$LOG_FILE"
    
    # Check for slow queries
    info "Checking for slow queries..."
    mysql -h"$host" -P"$port" -u"$user" -p"$password" -e "
        SHOW GLOBAL STATUS LIKE 'Slow_queries';
    " | tee -a "$LOG_FILE"
    
    success "Database health check completed"
}

# Optimize database
optimize_database() {
    local host="$1"
    local port="$2"
    local user="$3"
    local password="$4"
    local database="$5"
    
    info "Optimizing database..."
    
    # Get all tables
    local tables=$(mysql -h"$host" -P"$port" -u"$user" -p"$password" -e "
        USE $database;
        SHOW TABLES;
    " | grep -v "Tables_in_" || true)
    
    # Optimize each table
    for table in $tables; do
        if [[ -n "$table" ]]; then
            info "Optimizing table: $table"
            mysql -h"$host" -P"$port" -u"$user" -p"$password" -e "
                USE $database;
                OPTIMIZE TABLE \`$table\`;
            " || warning "Failed to optimize table: $table"
        fi
    done
    
    success "Database optimization completed"
}

# Fix common PHP connection issues
fix_php_connection() {
    local config_file="$1"
    local db_host="$2"
    local db_port="$3"
    local db_name="$4"
    local db_user="$5"
    local db_pass="$6"
    
    info "Fixing PHP connection configuration..."
    
    if [[ ! -f "$config_file" ]]; then
        warning "Config file not found: $config_file"
        return 1
    fi
    
    # Create backup of config file
    cp "$config_file" "${config_file}.backup.$(date +%Y%m%d_%H%M%S)"
    
    # Update database configuration (example for common patterns)
    case "$config_file" in
        *.php)
            # Update PHP config file
            sed -i.bak "s/localhost\|127\.0\.0\.1/$db_host/g" "$config_file"
            sed -i.bak "s/port.*=.*[0-9]\+/port = $db_port/g" "$config_file"
            sed -i.bak "s/database.*=.*['\"][^'\"]*['\"]/database = '$db_name'/g" "$config_file"
            sed -i.bak "s/username.*=.*['\"][^'\"]*['\"]/username = '$db_user'/g" "$config_file"
            sed -i.bak "s/password.*=.*['\"][^'\"]*['\"]/password = '$db_pass'/g" "$config_file"
            ;;
        *.env)
            # Update .env file
            sed -i.bak "s/DB_HOST=.*/DB_HOST=$db_host/" "$config_file"
            sed -i.bak "s/DB_PORT=.*/DB_PORT=$db_port/" "$config_file"
            sed -i.bak "s/DB_DATABASE=.*/DB_DATABASE=$db_name/" "$config_file"
            sed -i.bak "s/DB_USERNAME=.*/DB_USERNAME=$db_user/" "$config_file"
            sed -i.bak "s/DB_PASSWORD=.*/DB_PASSWORD=$db_pass/" "$config_file"
            ;;
    esac
    
    success "PHP connection configuration updated"
}

# Main function
main() {
    local action="${1:-help}"
    
    case "$action" in
        "test")
            test_connection "$2" "$3" "$4" "$5" "$6"
            ;;
        "backup")
            setup_backup_dir
            create_backup "$2" "$3" "$4" "$5" "$6" "$7"
            ;;
        "health")
            check_database_health "$2" "$3" "$4" "$5" "$6"
            ;;
        "optimize")
            optimize_database "$2" "$3" "$4" "$5" "$6"
            ;;
        "fix-php")
            fix_php_connection "$2" "$3" "$4" "$5" "$6" "$7"
            ;;
        "help"|*)
            echo "MySQL PHP Fixer - Usage:"
            echo "  $0 test HOST PORT USER PASSWORD DATABASE"
            echo "  $0 backup HOST PORT USER PASSWORD DATABASE BACKUP_NAME"
            echo "  $0 health HOST PORT USER PASSWORD DATABASE"
            echo "  $0 optimize HOST PORT USER PASSWORD DATABASE"
            echo "  $0 fix-php CONFIG_FILE HOST PORT DATABASE USER PASSWORD"
            ;;
    esac
}

# Run main function with all arguments
main "$@"