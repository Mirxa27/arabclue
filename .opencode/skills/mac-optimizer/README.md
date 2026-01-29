# Mac Optimizer Skill

A comprehensive Mac optimization and maintenance skill for opencode.

## Usage

Add `@mac-optimizer` to the top of your prompt, then describe your optimization task.

### Examples

**Quick Cleanup:**

```
@mac-optimizer
Perform a quick system cleanup: remove temp files, empty trash, clear cache
```

**Deep Optimization:**

```
@mac-optimizer
Perform deep optimization: clean temp files, clear cache, optimize memory, find large files
```

**Full System Maintenance:**

```
@mac-optimizer
Perform complete system maintenance: cleanup, memory optimization, startup management, permission repair, update check
```

## Features

- **System Cleanup**: Removes temporary files, cache, and logs
- **Storage Optimization**: Finds and helps remove large unused files
- **Memory Management**: Clears inactive memory and optimizes RAM
- **Startup Optimization**: Analyzes and manages launch agents
- **Permission Repair**: Fixes file and disk permissions
- **System Updates**: Checks for available updates
- **Safety Features**: Creates backups and requires confirmation for risky operations

## Requirements

- macOS 10.14 or later
- Admin privileges recommended for full functionality
- At least 10GB free disk space recommended

## Files

- `SKILL.md`: Skill documentation and usage guide
- `optimize.sh`: Main optimization script
- `README.md`: This file

The skill creates backup files in `~/.mac_optimizer_backup` and logs actions in `~/.mac_optimizer_log.txt`.
