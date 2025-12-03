# Security Policy

## Supported Versions

We actively support the following versions with security updates:

| Version | Supported          |
| ------- | ------------------ |
| Latest  | :white_check_mark: |

## Reporting a Vulnerability

We take security vulnerabilities seriously. If you discover a security issue, please report it responsibly.

**Please do NOT report security vulnerabilities through public GitHub issues.**

### How to Report

- **Email**: Send details to [manirujjamanakash@gmail.com](mailto:manirujjamanakash@gmail.com)

### What to Include

When reporting a vulnerability, please include:

- A clear description of the vulnerability
- Steps to reproduce the issue
- Potential impact of the vulnerability
- Any suggested fixes (optional but appreciated)

### Response Timeline

- **Initial Response**: Within 48 hours
- **Status Update**: Within 7 days
- **Resolution Target**: Within 30 days (depending on complexity)

### What to Expect

1. We will acknowledge receipt of your report
2. We will investigate and validate the issue
3. We will work on a fix and coordinate disclosure timing with you
4. We will credit you in our security acknowledgments (unless you prefer to remain anonymous)

## Security Best Practices

This Laravel application follows security best practices including:

- Input validation and sanitization
- CSRF protection on all forms
- SQL injection prevention via Eloquent ORM
- XSS protection through Blade templating
- Secure password hashing using bcrypt
- Environment-based configuration for sensitive data
- Access control and authorization checks
- Regular dependency updates

## Security Researchers

We would like to thank the following security researchers for responsibly reporting security issues:

- [P0cas (Jeongwon Jo) – RedAlert](https://github.com/P0cas)
- [ed6erunner](https://github.com/ed6erunner)

## Disclosure Policy

We follow a coordinated disclosure policy:

1. Security issues are fixed before public disclosure
2. We aim to release patches within 30 days of confirmed reports
3. Researchers are credited after the fix is released (with their permission)

## Contact

For security-related inquiries, please contact the maintainers directly rather than opening a public issue, via:

- **Email**: [manirujjamanakash@gmail.com](mailto:manirujjamanakash@gmail.com)