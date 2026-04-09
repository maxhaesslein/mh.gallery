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
- Completed:
  - Reviewed `get_slug()` method in gallery.php - properly sanitizes input using `sanitize_string()`
  - Verified path construction in gallery and image classes - all use sanitized slugs
  - Confirmed Folder class skips hidden files and validates directory existence
  - All file operations use `get_abspath()` with trusted path components

### Template Security
- Review all template files for proper output escaping
- Ensure user-supplied data is properly escaped when output in HTML contexts
- Check usage in admin templates and password forms
- Completed:
  - Reviewed overview.php, image.php, and other templates for direct output of slugs
  - Found that slugs are output directly in HTML attributes and IDs without explicit escaping
  - Although slugs are sanitized by sanitize_string(), best practice is to escape them for HTML context
  - Added escape_html() helper function to system/functions/helper.php for consistent HTML escaping
  - Applied HTML escaping to slug attributes in overview.php and image.php templates
  - Escaped all user-supplied data in admin templates (admin.php, admin_create-hash.php)
  - Escaped gallery titles in 401-password.php, 401-secret.php, and download.php templates

### Configuration Security
- Review custom/config.php overrides to ensure they don't introduce vulnerabilities
- Validate any user-controllable configuration options

### Session Security
- Review session handling for secret galleries and password protection
- Ensure proper session validation and timeout mechanisms

### File Operation Security
- Audit all file operations (include, require, file_get_contents, fopen, etc.)
- Ensure no user input can influence file paths without proper validation