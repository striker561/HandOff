# Security Policy

## Reporting a Vulnerability

If you discover a security vulnerability within HandOff, please send an email
to the project maintainers. All security vulnerabilities will be promptly
addressed.

**Please do not report security vulnerabilities through public GitHub issues.**

We appreciate your help in making HandOff secure for everyone. We will
acknowledge receipt of your report within 48 hours and provide a timeline
for a fix within 72 hours.

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 0.x     | :white_check_mark: |

## Security Best Practices

When deploying HandOff in production:

- Ensure `APP_DEBUG=false` in your `.env` file
- Use a strong `APP_KEY` (generated via `php artisan key:generate`)
- Keep your dependencies updated (`composer update`, `npm update`)
- Use HTTPS in production
- Review Laravel's [security recommendations](https://laravel.com/docs/security)
