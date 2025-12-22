# Security Considerations

## Current Security Measures

### ✅ Implemented
- **Password Hashing**: Using PHP's `password_hash()` and `password_verify()`
- **SQL Injection Protection**: All queries use PDO prepared statements
- **XSS Protection**: HTML output is escaped using `htmlspecialchars()`
- **Session Security**: Session ID regeneration every 30 minutes
- **Login Attempt Tracking**: System tracks and can block excessive failed attempts
- **Access Logging**: All login attempts are logged with IP and user agent
- **Role-Based Access Control**: Permissions checked before accessing resources

### ⚠️ For Production Deployment

Before deploying to production, implement these additional security measures:

#### 1. CSRF Protection
The current forms do not include CSRF tokens. Implement CSRF protection:

```php
// Generate token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// In forms
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

// Validate
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    die('CSRF token validation failed');
}
```

#### 2. Change Default Credentials
The default admin password is `admin123`. **MUST be changed immediately** after installation:

```sql
-- Change admin password
UPDATE usuarios SET password = '$2y$10$YOUR_NEW_HASH' WHERE id = 1;
```

Or use the system's user management interface.

#### 3. Database Configuration
Update `config/config.php` with:
- Strong database password
- Different database user (not root)
- Restricted permissions (no DROP, CREATE on production)

```php
define('DB_PASS', 'use_strong_password_here');
define('DB_USER', 'cms_user'); // Not root
```

#### 4. Environment Configuration
Set production environment in `config/config.php`:

```php
define('ENVIRONMENT', 'production');
```

This will:
- Disable error display
- Enable error logging only
- Optimize performance

#### 5. HTTPS Configuration
Uncomment in `.htaccess`:

```apache
# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

#### 6. File Upload Security
When implementing file uploads:
- Validate file types on server side
- Restrict upload directory permissions
- Generate random filenames
- Store uploads outside document root if possible
- Check file content, not just extension

#### 7. Rate Limiting
Implement rate limiting for:
- Login attempts (already tracked, add enforcement)
- API endpoints
- Form submissions

#### 8. Content Security Policy
Add CSP headers:

```php
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com;");
```

#### 9. Security Headers
Add these headers to all responses:

```php
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
```

#### 10. Input Validation
Enhance input validation:
- Server-side validation for all inputs
- Whitelist allowed characters
- Validate lengths and formats
- Use prepared statements (already implemented)

#### 11. Session Configuration
In `php.ini` or via `ini_set()`:

```php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Only over HTTPS
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
```

#### 12. Backup Strategy
- Regular database backups
- Backup upload files
- Store backups securely (encrypted)
- Test restore procedures

#### 13. Monitoring
- Monitor login attempts
- Track failed authentication
- Alert on suspicious activity
- Regular security audits

## Reporting Security Issues

If you discover a security vulnerability, please email:
- security@gestorcontenidos.mx

Do not create public issues for security vulnerabilities.

## Security Checklist for Deployment

- [ ] Change default admin password
- [ ] Use strong database password
- [ ] Configure database user with minimal permissions
- [ ] Enable HTTPS
- [ ] Set ENVIRONMENT to 'production'
- [ ] Implement CSRF protection
- [ ] Configure security headers
- [ ] Set up file backup system
- [ ] Configure session security settings
- [ ] Review and restrict file permissions
- [ ] Disable directory listing
- [ ] Remove test.php in production
- [ ] Configure error logging (not display)
- [ ] Set up monitoring and alerts
- [ ] Regular security updates

## Regular Maintenance

1. **Keep PHP Updated**: Always use the latest stable PHP version
2. **Update Dependencies**: If adding libraries, keep them updated
3. **Review Logs**: Regularly check access and error logs
4. **Audit Users**: Periodically review user accounts and permissions
5. **Test Backups**: Regularly test backup restoration
6. **Security Scans**: Use tools like OWASP ZAP for vulnerability scanning

## Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)
- [MySQL Security Guide](https://dev.mysql.com/doc/refman/8.0/en/security.html)

---

**Remember**: Security is an ongoing process, not a one-time task. Stay informed about new vulnerabilities and best practices.
