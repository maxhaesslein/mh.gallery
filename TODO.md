# Security

## Recommended Security Improvements

### Input Sanitization
- Sanitize all `$_GET` parameters before use to prevent injection attacks
- Completed:
  - `system/site/templates/download.php`: Added validation for `$_GET['create']` 
  - `system/classes/route.php`: Added validation for `$_GET['lock']` and `$_GET['end-session']` checks
  - `system/classes/route.php`: Reviewed `$_GET` assignment to `$query_parameters` (line 28) - determined it's used internally for secret validation and is properly handled

### Directory Traversal Protection
- Validate and sanitize any user input used in file paths
- Check usage of gallery slugs and paths constructed from user input
- Review `get_slug()` and path construction methods in gallery and image classes

### Template Security
- Review all template files for proper output escaping
- Ensure user-supplied data is properly escaped when output in HTML contexts
- Check usage in admin templates and password forms

### Configuration Security
- Review custom/config.php overrides to ensure they don't introduce vulnerabilities
- Validate any user-controllable configuration options

### Session Security
- Review session handling for secret galleries and password protection
- Ensure proper session validation and timeout mechanisms

### File Operation Security
- Audit all file operations (include, require, file_get_contents, fopen, etc.)
- Ensure no user input can influence file paths without proper validation