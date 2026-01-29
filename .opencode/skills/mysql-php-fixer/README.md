# MySQL PHP Fixer Skill

A comprehensive skill for updating and fixing remote MySQL databases for PHP websites.

## Installation

The skill is installed in `.opencode/skill/mysql-php-fixer/` and ready to use.

## Quick Start

1. **Test MySQL Connection:**

   ```bash
   ./mysql_fixer.sh test localhost 3306 username password database_name
   ```

2. **Create Backup:**

   ```bash
   ./mysql_fixer.sh backup localhost 3306 username password database_name backup_20250125
   ```

3. **Check Database Health:**
   ```bash
   ./mysql_fixer.sh health localhost 3306 username password database_name
   ```

## Usage Examples

### Using with @mysql-php-fixer

```
@mysql-php-fixer
Test MySQL connection to production database and check for issues
```

```
@mysql-php-fixer
Create backup, optimize database, and check health
```

### Direct Script Usage

#### Test Connection

```bash
./mysql_fixer.sh test HOST PORT USER PASSWORD DATABASE
```

#### Create Backup

```bash
./mysql_fixer.sh backup HOST PORT USER PASSWORD DATABASE BACKUP_NAME
```

#### Database Health Check

```bash
./mysql_fixer.sh health HOST PORT USER PASSWORD DATABASE
```

#### Optimize Database

```bash
./mysql_fixer.sh optimize HOST PORT USER PASSWORD DATABASE
```

#### Fix PHP Connection Config

```bash
./mysql_fixer.sh fix-php CONFIG_FILE HOST PORT DATABASE USER PASSWORD
```

## Common Scenarios

### Fixing PHP Connection Errors

1. **Test connectivity:**

   ```bash
   ./mysql_fixer.sh test your-db-host.com 3306 dbuser dbpass dbname
   ```

2. **Check if PHP config needs updating:**

   ```bash
   ./mysql_fixer.sh fix-php config/config.php your-db-host.com 3306 dbname dbuser dbpass
   ```

3. **For .env files:**
   ```bash
   ./mysql_fixer.sh fix-php .env your-db-host.com 3306 dbname dbuser dbpass
   ```

### Database Optimization

1. **First, create a backup:**

   ```bash
   ./mysql_fixer.sh backup your-db-host.com 3306 dbuser dbpass dbname pre_optimize_20250125
   ```

2. **Check database health:**

   ```bash
   ./mysql_fixer.sh health your-db-host.com 3306 dbuser dbpass dbname
   ```

3. **Run optimization:**
   ```bash
   ./mysql_fixer.sh optimize your-db-host.com 3306 dbuser dbpass dbname
   ```

### Routine Maintenance

```bash
# Create backup with timestamp
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
./mysql_fixer.sh backup your-db-host.com 3306 dbuser dbpass dbname "routine_backup_$TIMESTAMP"

# Check health
./mysql_fixer.sh health your-db-host.com 3306 dbuser dbpass dbname

# Optimize if needed
./mysql_fixer.sh optimize your-db-host.com 3306 dbuser dbpass dbname
```

## File Structure

```
.mysql-php-fixer/
├── SKILL.md           # Skill documentation (used by OpenCode)
├── README.md          # This file
├── mysql_fixer.sh     # Main script
├── backups/           # Directory for database backups (created automatically)
└── mysql_fixer.log    # Log file (created automatically)
```

## Backup Management

Backups are stored in the `backups/` directory with the format:

- `backup_name.sql` - Uncompressed backup
- `backup_name.sql.gz` - Compressed backup (automatically created)

### Restore from Backup

```bash
# Decompress (if needed)
gunzip backups/backup_name.sql.gz

# Restore
mysql -h HOST -u USER -p DATABASE < backups/backup_name.sql
```

## Security Best Practices

1. **Never commit credentials** - Use environment variables or secure credential storage
2. **Always backup first** - Before any modifications
3. **Test on staging** - Verify changes before applying to production
4. **Limit privileges** - Use database users with minimal required permissions
5. **Monitor logs** - Check `mysql_fixer.log` for any issues

## Troubleshooting

### Connection Issues

**Error: "Access denied"**

- Check username and password
- Verify user has permissions for the database
- Check MySQL host restrictions (`'user'@'%'` vs `'user'@'localhost'`)

**Error: "Can't connect"**

- Verify host and port are correct
- Check firewall settings
- Ensure MySQL is running on the remote server

### Backup Issues

**Error: "mysqldump command not found"**

- Install MySQL client tools:

  ```bash
  # macOS
  brew install mysql-client

  # Ubuntu/Debian
  sudo apt-get install mysql-client
  ```

### Optimization Issues

**Warning: "Failed to optimize table"**

- Table might be corrupted - run `REPAIR TABLE` first
- Check disk space
- Verify user has OPTIMIZE privileges

## Environment Variables

You can use environment variables instead of command-line arguments:

```bash
export MYSQL_HOST="your-db-host.com"
export MYSQL_PORT="3306"
export MYSQL_USER="dbuser"
export MYSQL_PASSWORD="dbpass"
export MYSQL_DATABASE="dbname"

./mysql_fixer.sh backup $MYSQL_HOST $MYSQL_PORT $MYSQL_USER $MYSQL_PASSWORD $MYSQL_DATABASE "backup_$(date +%Y%m%d)"
```

## Integration with PHP Frameworks

### Laravel (.env)

```bash
./mysql_fixer.sh fix-php .env your-db-host.com 3306 laravel_db dbuser dbpass
```

### WordPress (wp-config.php)

```bash
./mysql_fixer.sh fix-php wp-config.php your-db-host.com 3306 wordpress_db wpuser wppass
```

### Custom PHP Apps

For custom config files, ensure they follow the pattern:

```php
$database = 'database_name';
$host = 'localhost';
$port = 3306;
$username = 'db_user';
$password = 'db_pass';
```

## Logging

All operations are logged to `mysql_fixer.log` with timestamps:

```
[2025-01-25 14:30:45] INFO: Testing MySQL connection to your-db-host.com:3306...
[2025-01-25 14:30:46] SUCCESS: Connection successful
[2025-01-25 14:30:46] INFO: Creating backup: backup_20250125
[2025-01-25 14:31:23] SUCCESS: Backup created: backups/backup_20250125.sql
```

## Support

For issues or questions:

1. Check the log file for detailed error messages
2. Ensure all prerequisites are met
3. Test with a simple connection first
4. Review backup and restore procedures
