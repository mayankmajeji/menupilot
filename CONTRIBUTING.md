# Contributing to MenuPilot

Thank you for considering contributing to MenuPilot! This document outlines the guidelines for contributing to this project.

## Code of Conduct

Please be respectful and considerate in your interactions with other contributors.

## How to Contribute

### Reporting Bugs

Before creating bug reports, please check existing issues. When creating a bug report, include:

- A clear and descriptive title
- Detailed steps to reproduce the issue
- Expected behavior vs actual behavior
- Screenshots if applicable
- WordPress version, PHP version, and plugin version
- Any relevant error messages or logs

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, include:

- A clear and descriptive title
- Detailed description of the proposed feature
- Explanation of why this enhancement would be useful
- Examples of how it would work

### Pull Requests

1. Fork the repository
2. Create a new branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Run tests (`composer test` and `npm run lint:all`)
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

## Development Setup

1. Clone the repository
2. Run `composer install`
3. Run `npm install`
4. Make your changes
5. Run tests before committing

## Coding Standards

### PHP

- Follow WordPress Coding Standards
- Use PHP 7.4+ features (type declarations, etc.)
- Add PHPDoc comments for all functions and methods
- Run `composer lint` before committing

### JavaScript

- Follow WordPress JavaScript Coding Standards
- Use modern ES6+ syntax where appropriate
- Run `npm run lint:js` before committing

### CSS/SCSS

- Follow WordPress CSS Coding Standards
- Use SCSS with the module system (@use/@forward)
- Run `npm run lint:css` before committing

## Testing

- Write tests for new features
- Ensure all tests pass before submitting PR
- Aim for good test coverage

### Running Tests

```bash
# PHP CodeSniffer
composer lint

# PHPStan
composer run test:phpstan

# Unit tests
npm run test:unit

# Integration tests
npm run test:integration

# All tests
composer test
npm test
```

## Commit Messages

- Use clear and descriptive commit messages
- Start with a verb in present tense (Add, Fix, Update, etc.)
- Reference issue numbers when applicable

Examples:
```
Add user settings export feature
Fix settings validation bug (#123)
Update documentation for API endpoints
```

## Questions?

If you have questions, feel free to open an issue or reach out to the maintainers.

Thank you for contributing! 🎉
