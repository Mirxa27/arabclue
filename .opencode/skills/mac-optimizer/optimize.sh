#!/bin/bash

# Mac Optimizer - Main optimization script
# Part of the mac-optimizer skill

set -euo pipefail

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging
LOG_FILE="$HOME/.mac_optimizer_log.txt"
BACKUP_DIR="$HOME/.mac_optimizer_backup"

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Function to log actions
log_action() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

# Function to check admin privileges
check_admin() {
    if [[ $EUID -ne 0 ]]; then
        echo -e "${YELLOW}Warning: Some operations require admin privileges${NC}"
        return 1
    fi
    return 0
}

# Function to create backup
backup_file() {
    local file="$1"
    if [[ -f "$file" ]]; then
        cp "$file" "$BACKUP_DIR/$(basename "$file").$(date +%s).backup"
        log_action "Backed up: $file"
    fi
}

# Function to clean temporary files
clean_temp_files() {
    echo -e "${BLUE}Cleaning temporary files...${NC}"
    
    # User temp files
    if [[ -d "$HOME/tmp" ]]; then
        rm -rf "$HOME/tmp"/*
        log_action "Cleaned user temp directory"
    fi
    
    # System temp files (requires admin)
    if check_admin; then
        rm -rf /tmp/*
        log_action "Cleaned system temp directory"
    fi
    
    echo -e "${GREEN}✓ Temporary files cleaned${NC}"
}

# Function to clear cache
clear_cache() {
    echo -e "${BLUE}Clearing system cache...${NC}"
    
    # User cache
    if [[ -d "$HOME/Library/Caches" ]]; then
        find "$HOME/Library/Caches" -name "*" -type f -mtime +7 -delete
        log_action "Cleaned user cache files older than 7 days"
    fi
    
    # System cache (requires admin)
    if check_admin; then
        rm -rf /Library/Caches/*
        log_action "Cleaned system cache directory"
    fi
    
    echo -e "${GREEN}✓ Cache cleared${NC}"
}

# Function to optimize memory
optimize_memory() {
    echo -e "${BLUE}Optimizing memory usage...${NC}"
    
    # Clear inactive memory
    sudo purge 2>/dev/null || log_action "Could not clear inactive memory (requires admin)"
    
    echo -e "${GREEN}✓ Memory optimization completed${NC}"
}

# Function to find large files
find_large_files() {
    echo -e "${BLUE}Finding large files (>100MB)...${NC}"
    
    find "$HOME" -type f -size +100M -exec ls -lh {} \; 2>/dev/null | \
        awk '{print $5, $9}' | sort -hr | head -20
    
    echo -e "${GREEN}✓ Large files analysis completed${NC}"
}

# Function to manage startup items
manage_startup() {
    echo -e "${BLUE}Analyzing startup items...${NC}"
    
    # List login items
    osascript -e 'tell application "System Events" to get the name of every login item' 2>/dev/null || \
        log_action "Could not retrieve login items"
    
    # List launch agents
    echo "User Launch Agents:"
    find "$HOME/Library/LaunchAgents" -name "*.plist" 2>/dev/null | \
        sed 's|.*/||' | sed 's|\.plist||'
    
    echo -e "${GREEN}✓ Startup items analysis completed${NC}"
}

# Function to repair permissions
repair_permissions() {
    echo -e "${BLUE}Repairing file permissions...${NC}"
    
    # Repair home directory permissions
    chmod 755 "$HOME"
    find "$HOME" -type d -exec chmod 755 {} \; 2>/dev/null
    find "$HOME" -type f -exec chmod 644 {} \; 2>/dev/null
    
    # Repair disk permissions (requires admin)
    if check_admin; then
        diskutil repairPermissions / 2>/dev/null || \
            log_action "Could not repair disk permissions"
    fi
    
    echo -e "${GREEN}✓ Permission repair completed${NC}"
}

# Function to check for updates
check_updates() {
    echo -e "${BLUE}Checking for system updates...${NC}"
    
    if command -v softwareupdate >/dev/null 2>&1; then
        softwareupdate -l
        log_action "Checked for available updates"
    else
        log_action "softwareupdate command not available"
    fi
    
    echo -e "${GREEN}✓ Update check completed${NC}"
}

# Function to show system stats
show_stats() {
    echo -e "${BLUE}System Statistics:${NC}"
    
    # Disk usage
    echo -e "${YELLOW}Disk Usage:${NC}"
    df -h | grep -E "Filesystem|/dev/"
    
    # Memory usage
    echo -e "${YELLOW}Memory Usage:${NC}"
    vm_stat | perl -ne '/page size of (\d+)/ and $size=$1; /Pages\s+([^:]+):\s+(\d+)/ and printf("%-16s % 16.2f MB\n", $1, $2 * $size / 1048576);'
    
    # System load
    echo -e "${YELLOW}System Load:${NC}"
    uptime
}

# Main optimization function
run_optimization() {
    local optimization_type="${1:-quick}"
    
    echo -e "${GREEN}Starting Mac optimization (${optimization_type})...${NC}"
    log_action "Starting optimization: $optimization_type"
    
    case "$optimization_type" in
        "quick")
            clean_temp_files
            clear_cache
            ;;
        "deep")
            clean_temp_files
            clear_cache
            optimize_memory
            find_large_files
            repair_permissions
            ;;
        "full")
            clean_temp_files
            clear_cache
            optimize_memory
            find_large_files
            manage_startup
            repair_permissions
            check_updates
            ;;
        "stats")
            show_stats
            ;;
        *)
            echo -e "${RED}Unknown optimization type: $optimization_type${NC}"
            echo "Available types: quick, deep, full, stats"
            exit 1
            ;;
    esac
    
    show_stats
    echo -e "${GREEN}✓ Optimization completed successfully!${NC}"
    log_action "Optimization completed: $optimization_type"
}

# Run the optimization
run_optimization "$@"