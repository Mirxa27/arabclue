---
name: mysql-php-fixer
description: Update and fix remote MySQL for PHP websites
---

## Use This Skill

Put this line at the very top of your prompt:
@mysql-php-fixer

Then describe your MySQL/PHP issue below it.

## What This Skill Does

This skill handles MySQL database issues for PHP websites including:

- **Connection Testing**: Test remote MySQL connectivity and credentials
- **Database Updates**: Apply schema changes and migrations safely
- **Performance Optimization**: Optimize queries, indexes, and database configuration
- **Security Fixes**: Patch vulnerabilities and secure database access
- **Backup & Recovery**: Create backups before changes and restore if needed
- **Error Resolution**: Diagnose and fix common MySQL/PHP connection issues
- **Version Compatibility**: Handle MySQL version upgrades and PHP compatibility

## Safety Features

- **Backup First**: Always creates database backups before modifications
- **Test Environment**: Validates changes in staging before production
- **Rollback Support**: Maintains rollback scripts for all changes
- **Connection Verification**: Tests connectivity before operations
- **Change Logging**: Records all modifications for audit trail

## Common MySQL/PHP Tasks

### Connection Issues

```
@mysql-php-fixer
Fix MySQL connection errors in PHP, test remote connectivity, update credentials
```

### Database Updates

```
@mysql-php-fixer
Apply database schema updates, run migrations safely, update table structure
```

### Performance Optimization

```
@mysql-php-fixer
Optimize slow queries, add missing indexes, tune MySQL configuration for PHP
```

### Security Fixes

```
@mysql-php-fixer
Secure MySQL access, update credentials, patch vulnerabilities, fix SQL injection risks
```

### Version Upgrade

```
@mysql-php-fixer
Upgrade MySQL version, ensure PHP compatibility, test application functionality
```

## Requirements

- PHP 7.4+ or 8.x
- MySQL 5.7+ or 8.x
- Database admin credentials
- SSH access to server (for remote operations)
- Backup storage location

## Output

Provides detailed reports on:

- Connection test results
- Database changes applied
- Performance improvements
- Security fixes implemented
- Backup locations and status
- Recommendations for ongoing maintenance
