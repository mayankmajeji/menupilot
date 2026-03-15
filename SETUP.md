# WordPress Plugin CI/CD Setup Guide

Step-by-step guide to set up automated linting, static analysis, deployment, version stamping, and Slack notifications for a WordPress plugin hosted on GitHub and distributed via WordPress.org.

This guide covers both **new plugin repos** and **adding CI/CD to existing plugins**.

---

## Table of Contents

1. [Prerequisites](#1-prerequisites)
2. [Project Structure](#2-project-structure)
3. [PHP Linting — PHPCS](#3-php-linting--phpcs)
4. [PHP Static Analysis — PHPStan](#4-php-static-analysis--phpstan)
5. [JavaScript Linting — ESLint](#5-javascript-linting--eslint)
6. [CSS/SCSS Linting — Stylelint](#6-cssscss-linting--stylelint)
7. [Build Pipeline — Gulp](#7-build-pipeline--gulp)
8. [WordPress.org Distribution Files](#8-wordpressorg-distribution-files)
9. [GitHub Actions — Release Workflow](#9-github-actions--release-workflow)
10. [GitHub Actions — Manual Assets Workflow](#10-github-actions--manual-assets-workflow)
11. [GitHub Secrets](#11-github-secrets)
12. [Releasing a New Version](#12-releasing-a-new-version)
13. [Common Issues & Fixes](#13-common-issues--fixes)

---

## 1. Prerequisites

- **GitHub repository** with `main` as the default branch
- **WordPress.org SVN account** (plugin must be approved and listed)
- **Node.js** >= 18 and **npm**
- **PHP** >= 7.4 and **Composer**
- **Slack Incoming Webhook** (optional, for deploy notifications)

---

## 2. Project Structure

Ensure your plugin repo has this structure:

```
your-plugin/
├── .github/
│   └── workflows/
│       ├── deploy-tag.yml          # Release deployment workflow
│       └── deploy-assets.yml       # Manual asset update workflow
├── .wordpress-org/                 # WP.org assets (banners, icons, screenshots)
│   ├── banner-1544x500.png
│   ├── banner-772x250.png
│   ├── icon-128x128.png
│   ├── icon-256x256.png
│   └── screenshot-*.png
├── assets/                         # Plugin assets (JS, CSS/SCSS, images)
├── includes/                       # PHP source code
├── your-plugin.php                 # Main plugin file
├── readme.txt                      # WordPress.org readme
├── uninstall.php                   # Cleanup on uninstall
├── .distignore                     # Files excluded from WP.org distribution
├── .gitignore
├── composer.json
├── package.json
├── phpstan.neon
├── phpcs.xml.dist                  # PHPCS can also be .phpcs.xml.dist
├── .eslintrc.json
├── .stylelintrc.json
└── gulpfile.js
```

### .gitignore

```gitignore
vendor/
node_modules/
composer.lock
.DS_Store
tests/_output/
tests/_support/_generated/
```

### .distignore

Controls what gets excluded from the WordPress.org SVN deploy. The `10up/action-wordpress-plugin-deploy` action reads this file.

```
/.git
/.github
/.wordpress-org
/node_modules
/tests
/vendor
.distignore
.gitignore
.eslintrc.json
.eslintignore
.stylelintrc.json
.stylelintignore
composer.json
composer.lock
gulpfile.js
package.json
package-lock.json
phpcs.xml.dist
phpstan.neon
codeception.yml
CONTRIBUTING.md
CHANGELOG.md
SETUP.md
assets/css/**/*.scss
```

---

## 3. PHP Linting — PHPCS

### Install

```bash
composer require --dev squizlabs/php_codesniffer wp-coding-standards/wpcs phpcompatibility/phpcompatibility-wp dealerdirect/phpcodesniffer-composer-installer
```

### phpcs.xml.dist

Replace `your-plugin`, `YOUR_PLUGIN`, and `YourPlugin` with your actual prefixes and text domain.

```xml
<?xml version="1.0"?>
<ruleset name="Your Plugin Coding Standards">
    <description>PHP_CodeSniffer rules for Your Plugin.</description>

    <file>./includes</file>
    <file>./your-plugin.php</file>
    <file>./uninstall.php</file>

    <exclude-pattern>/vendor/*</exclude-pattern>
    <exclude-pattern>/node_modules/*</exclude-pattern>
    <exclude-pattern>/tests/*</exclude-pattern>
    <exclude-pattern>/.wordpress-org/*</exclude-pattern>
    <exclude-pattern>*.js</exclude-pattern>
    <exclude-pattern>*.css</exclude-pattern>

    <arg name="extensions" value="php"/>
    <arg value="s"/>  <!-- Show sniff codes -->
    <arg name="colors"/>

    <rule ref="WordPress">
        <exclude name="WordPress.DB.DirectDatabaseQuery"/>
        <exclude name="WordPress.DB.PreparedSQL.InterpolatedNotPrepared"/>
    </rule>

    <rule ref="WordPress.WP.I18n">
        <properties>
            <property name="text_domain" type="array">
                <element value="your-plugin"/>
            </property>
        </properties>
    </rule>

    <rule ref="WordPress.NamingConventions.PrefixAllGlobals">
        <properties>
            <property name="prefixes" type="array">
                <element value="your_plugin"/>
                <element value="YOUR_PLUGIN"/>
                <element value="YourPlugin"/>
            </property>
        </properties>
    </rule>

    <rule ref="PHPCompatibilityWP"/>
    <config name="testVersion" value="7.4-"/>
    <config name="minimum_wp_version" value="5.8"/>
</ruleset>
```

### Composer scripts

Add to `composer.json`:

```json
{
    "scripts": {
        "lint": "phpcs -d memory_limit=512M",
        "format": "phpcbf --standard=phpcs.xml.dist",
        "test:phpcs": "phpcs --standard=phpcs.xml.dist --report=checkstyle -d memory_limit=512M includes/ your-plugin.php uninstall.php"
    }
}
```

### Run locally

```bash
composer run lint          # Check for violations
composer run format        # Auto-fix violations
```

---

## 4. PHP Static Analysis — PHPStan

### Install

```bash
composer require --dev phpstan/phpstan szepeviktor/phpstan-wordpress
```

The `szepeviktor/phpstan-wordpress` package provides proper type stubs for all WordPress functions. Without it, PHPStan reports WordPress functions as "not found" and you'd need broad `ignoreErrors` workarounds.

### phpstan.neon

```neon
includes:
    - vendor/szepeviktor/phpstan-wordpress/extension.neon

parameters:
    level: 8
    paths:
        - includes
    ignoreErrors:
        # Plugin constants defined in the main plugin file (outside analysed paths).
        - '#Constant YOUR_PLUGIN_[A-Z_]+ not found\.#'
```

### Composer script

```json
{
    "scripts": {
        "test:phpstan": "phpstan analyze includes/"
    }
}
```

### Common PHPStan fixes after adding WordPress stubs

With proper stubs, PHPStan will likely reveal real errors:

- **Unreachable `return;` after `wp_send_json_success()` / `wp_send_json_error()`** — These are typed as `@return never`. Remove the `return;` statements.
- **Type mismatches with `esc_html()` / `esc_attr()`** — These expect `string`, not `int`. Cast: `esc_html( (string) $count )`.
- **`ob_get_clean()` returns `string|false`** — Handle the false case: `$output = ob_get_clean(); return false !== $output ? $output : '';`
- **Dynamic WP_Post nav menu properties** — Use `get_post_meta()` instead of `$item->menu_item_parent`.

---

## 5. JavaScript Linting — ESLint

### Install

```bash
npm install --save-dev eslint @wordpress/eslint-plugin
```

### .eslintrc.json

```json
{
    "parserOptions": {
        "sourceType": "script",
        "ecmaVersion": 2021
    },
    "env": {
        "browser": true,
        "jquery": true
    },
    "extends": ["eslint:recommended"],
    "globals": {
        "jQuery": "readonly",
        "wp": "readonly",
        "your_plugin": "readonly"
    },
    "rules": {
        "no-unused-vars": ["warn", { "argsIgnorePattern": "^_", "varsIgnorePattern": "^_" }]
    },
    "ignorePatterns": ["node_modules/", "vendor/", "*.min.js"]
}
```

### npm script

```json
{
    "scripts": {
        "lint:js": "eslint assets/js/**/*.js"
    }
}
```

---

## 6. CSS/SCSS Linting — Stylelint

### Install

```bash
npm install --save-dev stylelint @wordpress/stylelint-config stylelint-config-standard-scss
```

### .stylelintrc.json

```json
{
    "extends": ["stylelint-config-standard-scss"],
    "ignoreFiles": ["node_modules/**", "vendor/**", "**/*.min.css"],
    "rules": {
        "selector-class-pattern": null,
        "no-descending-specificity": null
    }
}
```

### npm script

```json
{
    "scripts": {
        "lint:css": "stylelint assets/css/**/*.scss"
    }
}
```

---

## 7. Build Pipeline — Gulp

### Install

```bash
npm install --save-dev gulp gulp-sass sass gulp-clean-css gulp-rename
```

### gulpfile.js

```js
const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const cleanCSS = require('gulp-clean-css');
const rename = require('gulp-rename');

function styles() {
    return gulp.src('assets/css/**/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(gulp.dest('assets/css'))
        .pipe(cleanCSS())
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('assets/css'));
}

function watch() {
    gulp.watch('assets/css/**/*.scss', styles);
}

exports.styles = styles;
exports.watch = watch;
exports.default = styles;
```

### npm scripts

```json
{
    "scripts": {
        "build": "gulp styles",
        "watch": "gulp watch"
    }
}
```

---

## 8. WordPress.org Distribution Files

### .wordpress-org/ directory

Place your WordPress.org assets here:

| File | Purpose |
|---|---|
| `banner-1544x500.png` | Plugin banner (hi-res) |
| `banner-772x250.png` | Plugin banner (standard) |
| `icon-128x128.png` | Plugin icon |
| `icon-256x256.png` | Plugin icon (hi-res) |
| `screenshot-*.png` | Screenshots for the plugin page |

### readme.txt

Must follow the [WordPress.org readme standard](https://developer.wordpress.org/plugins/wordpress-plugin-readme-file/). Key fields:

```
=== Your Plugin Name ===
Contributors: your-wp-username
Tags: tag1, tag2
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
```

The `Stable tag` is auto-updated by the release workflow — no manual edits needed.

---

## 9. GitHub Actions — Release Workflow

Create `.github/workflows/deploy-tag.yml`. This is the main workflow that runs on every GitHub Release.

### What it does (7 jobs)

1. **PHPCS** — PHP coding standards check
2. **PHPStan** — PHP static analysis
3. **ESLint + Stylelint** — JS and CSS linting
4. **Deploy** — Builds assets, stamps version from release tag, deploys to WordPress.org via SVN
5. **Assets** — Updates WordPress.org assets (banners, screenshots, readme)
6. **Stamp Version** — Pushes the version stamp back to `main` so the repo stays in sync
7. **Slack Notification** — Posts a release summary to Slack

### Version stamping

The workflow automatically stamps the release tag (e.g., `1.0.17`) into these files:

- `your-plugin.php` — Plugin header `Version:` and version constant
- `package.json` — `"version"` field
- `readme.txt` — `Stable tag:`

You never need to manually bump versions. Just create a GitHub Release and the workflow handles everything.

### Key points

- **CI checks run in parallel** (PHPCS, PHPStan, ESLint+Stylelint)
- **Deploy only runs if all CI checks pass** (`needs: [phpcs, phpstan, js]`)
- **Assets, Stamp, and Slack run in parallel after deploy succeeds**
- **Slack notification runs even if deploy fails** (`if: always()`) to report the failure
- **Version stamp uses `[skip ci]`** to avoid triggering other workflows
- **SVN is installed explicitly** — not pre-installed on GitHub Actions runners
- **`--ignore-platform-reqs`** on `composer install --no-dev` — dev dependencies may require higher PHP than the deploy runner

### Full workflow file

See `.github/workflows/deploy-tag.yml` in this repository for the complete implementation.

### Adapting for your plugin

1. Replace `menupilot.php` with your main plugin file name in the `sed` commands
2. Replace `MENUPILOT_VERSION` with your plugin's version constant name
3. Replace `menupilot` with your plugin slug in artifact names and URLs
4. Update the Slack notification header text

---

## 10. GitHub Actions — Manual Assets Workflow

Create `.github/workflows/deploy-assets.yml` for manually updating WordPress.org assets (banners, icons, screenshots, readme) without a full release.

### When to use

- Updated a screenshot or banner image
- Changed `readme.txt` (FAQ, description, etc.) without releasing a new version

### How to run

1. Go to **Actions** tab → **Deploy Assets to WordPress.org**
2. Click **Run workflow**
3. Enter the current plugin version (e.g., `1.0.17`)
4. Click **Run workflow**

The Actions tab will show: `1.0.17 | Assets`

### Full workflow file

See `.github/workflows/deploy-assets.yml` in this repository.

---

## 11. GitHub Secrets

Go to **Repository Settings → Secrets and variables → Actions** and add:

| Secret | Purpose | Required |
|---|---|---|
| `SVN_USERNAME` | WordPress.org SVN username | Yes |
| `SVN_PASSWORD` | WordPress.org SVN password | Yes |
| `SLACK_WEBHOOK_URL` | Slack Incoming Webhook URL | Optional |

`GITHUB_TOKEN` is automatically provided by GitHub Actions — no setup needed.

---

## 12. Releasing a New Version

### Steps

1. **Push your changes to `main`** — commit all code changes, no version bump needed
2. **Create a GitHub Release**:
   - Go to **Releases → Draft a new release**
   - Tag: `1.0.17` (create new tag)
   - Target: `main`
   - Title: `1.0.17`
   - Description: changelog notes
   - Click **Publish release**
3. **The workflow automatically**:
   - Runs PHPCS, PHPStan, ESLint, Stylelint
   - Stamps `1.0.17` into all version files
   - Builds assets (SCSS → CSS)
   - Deploys to WordPress.org via SVN
   - Updates WordPress.org assets (banners, screenshots)
   - Stamps version back to `main` branch
   - Sends Slack notification
4. **Pull locally** to get the version stamp:
   ```bash
   git pull origin main
   ```

### What you do NOT need to do

- ~~Manually edit version numbers in files~~ → auto-stamped from release tag
- ~~Run `npm run build` before releasing~~ → CI builds automatically
- ~~Deploy to WordPress.org manually~~ → CI handles SVN deployment
- ~~Update `Stable tag` in readme.txt~~ → auto-stamped

---

## 13. Common Issues & Fixes

### PHPCS: `Universal.Operators.DisallowShortTernary`

WordPress coding standards disallow the Elvis operator (`?:`). Use full ternary with an intermediate variable:

```php
// Bad
$output = ob_get_clean() ?: '';

// Good
$result = ob_get_clean();
$output = false !== $result ? $result : '';
```

### PHPStan: WordPress functions "not found"

Install proper WordPress stubs instead of using `ignoreErrors`:

```bash
composer require --dev szepeviktor/phpstan-wordpress
```

Then add to `phpstan.neon`:

```neon
includes:
    - vendor/szepeviktor/phpstan-wordpress/extension.neon
```

### Deploy: `composer install --no-dev` fails on PHP 7.4

Dev dependencies (e.g., Codeception) may require PHP 8+. Even with `--no-dev`, Composer resolves all dependencies. Fix:

```yaml
run: composer install --no-dev --no-interaction --prefer-dist --ignore-platform-reqs
```

### Deploy: `svn: command not found`

GitHub Actions runners don't always have SVN pre-installed. Add before the deploy step:

```yaml
- name: Install SVN
  run: sudo apt-get update -qq && sudo apt-get install -y subversion
```

### Slack: HTTP 400 from webhook

Slack's `section` block has a 3000-character limit on the `text` field. If the changelog between tags is large, truncate:

```bash
if [ ${#CHANGES} -gt 2500 ]; then
    CHANGES="${CHANGES:0:2500}…"
fi
```

Also limit the number of commits shown:

```bash
RAW=$(git log --pretty=format:"%s|%h" "${PREV_TAG}..${CURRENT_TAG}" | head -10)
```

### Version stamp fails to push to main

The deploy job checks out the release tag (detached HEAD), not `main`. Pushing `HEAD:main` fails if `main` has diverged. Fix: use a separate job that checks out `main` directly:

```yaml
stamp:
    needs: deploy
    steps:
        - uses: actions/checkout@v4
          with:
              ref: main
              token: ${{ secrets.GITHUB_TOKEN }}
        - run: |
              # sed commands to stamp version
              git push origin main
```

### Git contributor cleanup

If unwanted contributors appear on your repo (from `Co-Authored-By` trailers or wrong commit author emails):

```bash
pip3 install git-filter-repo

git-filter-repo \
    --mailmap <(printf "Correct Name <correct@email.com> Wrong Name <wrong@email.com>\n") \
    --force

# filter-repo removes the remote — re-add and force push
git remote add origin git@github.com:user/repo.git
git push --force origin main
```
