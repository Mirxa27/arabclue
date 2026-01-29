# MySQL PHP Fixer - Quick Start Guide

Your new skill is ready to use! Here's how to get started:

## ✅ Skill Created Successfully

Location: `.opencode/skill/mysql-php-fixer/`

## 🚀 Quick Start

### Option 1: Use with OpenCode (@mysql-php-fixer)

Simply add `@mysql-php-fixer` at the top of your prompt:

```
@mysql-php-fixer
Test MySQL connection to my production database and check for issues
```

```
@mysql-php-fixer
Create backup of database, optimize tables, and verify PHP connection
```

### Option 2: Use the Script Directly

Navigate to the skill directory:

```bash
cd .opencode/skill/mysql-php-fixer
```

Test a connection:

```bash
./mysql_fixer.sh test localhost 3306 username password database_name
```

## 📁 What's Included

- **SKILL.md** - Skill documentation (used by OpenCode)
- **README.md** - Complete usage guide and examples
- **mysql_fixer.sh** - Main script (executable)
- **examples/** - Configuration examples for Laravel, WordPress, and custom PHP
- **.gitignore** - Protects backups and sensitive files

## 🔧 Common Tasks

### Fix Connection Issues

```bash
./mysql_fixer.sh test HOST PORT USER PASS DB
./mysql_fixer.sh fix-php config.php HOST PORT DB USER PASS
```

### Backup & Optimize

```bash
./mysql_fixer.sh backup HOST PORT USER PASS DB backup_$(date +%Y%m%d)
./mysql_fixer.sh optimize HOST PORT USER PASS DB
```

### Health Check

```bash
./mysql_fixer.sh health HOST PORT USER PASS DB
```

## 📚 Next Steps

1. Read the full README.md for detailed examples
2. Check examples/ directory for framework-specific configs
3. Test with a staging database first
4. Always create backups before making changes

## 🔒 Security Reminder

Never commit actual credentials to version control. Use environment variables or secure credential storage.

---

For detailed documentation, see README.md
