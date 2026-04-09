# MH.Gallery Agent Guidelines

## Project Overview
This is a simple and lightweight PHP gallery without dependencies. The project follows a procedural PHP structure with some object-oriented elements in the system/classes directory.

## Directory Structure
- `/` - Root directory with index.php
- `/content` - Gallery content (user-managed)
- `/cache` - Generated thumbnails and images (auto-generated)
- `/custom` - Custom configurations and overrides
- `/system` - Core application code

## Build/Lint/Test Commands

### Installation
No build process required. This is a pure PHP application.

### Running the Application
Access via web server. PHP 8.0+ required with mbstring and gd extensions.

### Code Validation
Since this is a PHP project without a formal test suite:

#### Syntax Check
```bash
# Check syntax of all PHP files
find . -name "*.php" -not -path "./cache/*" -not -path "./content/*" -exec php -l {} \;

# Check syntax of specific file
php -l path/to/file.php
```

#### Code Style Check
No automated linting tools configured. Follow the code style guidelines below.

#### Running Tests
No formal test suite exists. Manual testing via browser is expected.

### Single Test Equivalent
To test a specific functionality:
1. Navigate to the relevant URL in a browser
2. Check the output matches expectations
3. Verify no PHP errors appear

## Code Style Guidelines

### PHP Version
Target: PHP 8.0+
Use typed properties, return types, and proper namespace declarations where applicable.

### File Organization
- PHP files should end with `?>` only when necessary (omitted preferred for pure PHP files)
- One class per file when using classes
- System files in `/system`, user customizations in `/custom`

### Indentation and Formatting
- Use tabs for indentation (not spaces)
- Opening braces for functions/classes on same line
- Closing braces on their own line
- Maximum line length: 120 characters
- One statement per line
- Blank lines to separate logical sections

### Naming Conventions
- Classes: PascalCase (e.g., `Gallery`, `Image`)
- Functions and methods: snake_case (e.g., `get_gallery()`, `process_image()`)
- Variables: snake_case (e.g., `$gallery_list`, `$image_path`)
- Constants: UPPER_SNAKE_CASE (e.g., `CACHE_LIFETIME`, `IMAGE_QUALITY`)
- Files: lowercase with underscores (e.g., `helper.php`, `gallery.php`)
- Config arrays: snake_case keys

### Import/Include Statements
- Use `require_once` for essential files
- Use `include_once` for optional files
- Prefer relative paths from project root
- Load system files via autoloader pattern where implemented
- Custom configs override system configs

### Type Declarations
- Use return type declarations when beneficial
- Use parameter type declarations for public APIs
- Accept iterable types where appropriate (PHP 7.1+)
- Use union types when needed (PHP 8.0+)
- Avoid mixed types when more specific types apply

### Error Handling
- Use exceptions for exceptional conditions
- System functions should return false/null on failure where appropriate
- Check return values of system functions
- Use try/catch for file operations and external resource access
- Log errors to PHP error log when appropriate
- Display user-friendly messages, not raw errors

### Security Practices
- Sanitize all user inputs (GET, POST, COOKIE)
- Use prepared statements if/when database is introduced
- Escape output for HTML context (use `htmlspecialchars()`)
- Validate file paths to prevent directory traversal
- Use proper password hashing (already implemented in admin area)
- Validate file uploads (though none currently exist)

### Documentation
- Only use comments when necessary to understand the code
- Comments always in english!
- Comment complex logic blocks
- Keep comments up-to-date when code changes

### Specific to This Project
- Gallery and image processing functions should handle edge cases (missing files, corrupt images)
- Configuration system: custom/config.php overrides system/config.php
- Template files should be minimal PHP, mostly HTML
- JavaScript should be unobtrusive and fallback to non-JS behavior
- CSS should be modular and overrideable via custom/assets/css/
- Language files should be easy to customize/extend

### Git Practices
- Commit messages should be descriptive
- Separate commits for functional changes vs formatting
- Don't commit cache/ directory contents
- Content/ directory is user-managed, be careful with commits
- Custom/ directory is for user overrides, document changes

## Additional Notes
This project prioritizes simplicity and compatibility over modern PHP frameworks.
Changes should maintain the zero-dependency requirement and shared hosting compatibility.
When in doubt, examine existing code patterns in the system/ directory.