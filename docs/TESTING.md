# Testing Guide

This document provides comprehensive testing guidelines for MenuPilot.

## Table of Contents

- [Overview](#overview)
- [Setup](#setup)
- [Unit Tests](#unit-tests)
- [Integration Tests](#integration-tests)
- [Acceptance Tests](#acceptance-tests)
- [Code Quality](#code-quality)

## Overview

MenuPilot uses Codeception for testing, which provides:

- **Unit Tests** - Test individual classes and functions in isolation
- **Integration Tests** - Test WordPress integration and database interactions
- **Acceptance Tests** - Test full user workflows in a browser

## Setup

### Prerequisites

- PHP 7.4+
- Composer
- Node.js 18+
- MySQL (for integration tests)
- Chrome/ChromeDriver (for acceptance tests)

### Installation

```bash
# Install dependencies
composer install
npm install

# Build Codeception support files
vendor/bin/codecept build
```

### Configuration

1. Copy `tests/_envs/local.yml.example` to `tests/_envs/local.yml`
2. Update database credentials for integration tests
3. Update WebDriver URL for acceptance tests

## Unit Tests

Unit tests test individual classes and methods in isolation.

### Running Unit Tests

```bash
# Run all unit tests
vendor/bin/codecept run unit

# Run specific test
vendor/bin/codecept run unit SettingsTest

# Run with coverage
vendor/bin/codecept run unit --coverage --coverage-html
```

### Writing Unit Tests

```php
<?php
namespace MenuPilot\Tests\Unit;

use Codeception\Test\Unit;
use MenuPilot\Settings;

class SettingsTest extends Unit {
    
    protected function _before() {
        // Setup before each test
    }
    
    public function testGetSettings() {
        $settings = new Settings();
        $result = $settings->get_settings();
        $this->assertIsArray($result);
    }
}
```

## Integration Tests

Integration tests test WordPress integration, including database operations and hooks.

### Running Integration Tests

```bash
# Start test environment (if using Docker)
composer run test:start

# Run integration tests
vendor/bin/codecept run integration

# Stop test environment
composer run test:stop
```

### Writing Integration Tests

```php
<?php
namespace MenuPilot\Tests\Integration;

use Codeception\TestCase\WPTestCase;

class PluginActivationTest extends WPTestCase {
    
    public function testPluginIsActivated() {
        $this->assertTrue(defined('MENUPILOT_VERSION'));
    }
    
    public function testSettingsOptionExists() {
        $settings = get_option('menupilot_settings');
        $this->assertNotFalse($settings);
    }
}
```

## Acceptance Tests

Acceptance tests simulate real user interactions in a browser.

### Running Acceptance Tests

```bash
# Start Selenium/ChromeDriver
# (or use Docker)

# Run acceptance tests
vendor/bin/codecept run acceptance

# Run with UI
vendor/bin/codecept run acceptance --debug
```

### Writing Acceptance Tests

```php
<?php

class AdminSettingsPageCest {
    
    public function _before(\AcceptanceTester $I) {
        $I->loginAsAdmin();
    }
    
    public function testSettingsPageLoads(\AcceptanceTester $I) {
        $I->amOnAdminPage('admin.php?page=menupilot-settings');
        $I->see('MenuPilot');
    }
    
    public function testSaveSettings(\AcceptanceTester $I) {
        $I->amOnAdminPage('admin.php?page=menupilot-settings');
        $I->fillField('api_key', 'test-key');
        $I->click('Save Settings');
        $I->see('Settings saved successfully');
    }
}
```

## Code Quality

### PHP CodeSniffer

Check code against WordPress Coding Standards:

```bash
# Check for issues
composer lint

# Auto-fix issues
composer format
```

### PHPStan

Static analysis for PHP:

```bash
composer run test:phpstan
```

### JavaScript Linting

```bash
# Check JavaScript
npm run lint:js

# Auto-fix JavaScript
npm run fix:js
```

### CSS Linting

```bash
# Check CSS/SCSS
npm run lint:css

# Auto-fix CSS/SCSS
npm run fix:css
```

### All Linters

```bash
# Run all linters
npm run lint:all
```

## Continuous Integration

### GitHub Actions Example

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '7.4'
          
      - name: Install Composer dependencies
        run: composer install
        
      - name: Run tests
        run: composer test
```

## Best Practices

1. **Write tests first** - TDD approach when possible
2. **Test edge cases** - Don't just test the happy path
3. **Keep tests isolated** - Each test should be independent
4. **Use descriptive names** - Test names should describe what they test
5. **Mock external dependencies** - Don't rely on external APIs in tests
6. **Maintain test data** - Use fixtures for consistent test data

## Troubleshooting

### Tests fail with database errors

- Check database credentials in `tests/_envs/local.yml`
- Ensure MySQL is running
- Try recreating the test database

### Acceptance tests fail to connect

- Verify WebDriver/Selenium is running
- Check the URL in `tests/acceptance.suite.yml`
- Try running with `--debug` flag

### Tests are slow

- Use `@group` annotations to run specific test groups
- Consider using SQLite for faster integration tests
- Use `--fail-fast` to stop on first failure

## Additional Resources

- [Codeception Documentation](https://codeception.com/docs)
- [WordPress Testing Documentation](https://make.wordpress.org/core/handbook/testing/)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
