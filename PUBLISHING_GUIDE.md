# Complete Guide: Publishing and Installing Your Laravel Package on Packagist / Composer

This guide covers everything you need to know to publish your package to [Packagist.org](https://packagist.org) and install it in any Laravel application via standard Composer commands.

---

## 📑 Table of Contents
1. [Prerequisites](#1-prerequisites)
2. [Package Directory Structure](#2-package-directory-structure)
3. [Step 1: Preparing `composer.json`](#step-1-preparing-composerjson)
4. [Step 2: Pushing the Package to GitHub / GitLab](#step-2-pushing-the-package-to-github--gitlab)
5. [Step 3: Creating a Release / Version Tag](#step-3-creating-a-release--version-tag)
6. [Step 4: Publishing on Packagist.org](#step-4-publishing-on-packagistorg)
7. [Step 5: Setting Up Auto-Update Webhooks](#step-5-setting-up-auto-update-webhooks)
8. [Step 6: Installing the Package in Any Laravel Project](#step-6-installing-the-package-in-any-laravel-project)
9. [Step 7: Testing & Verification](#step-7-testing--verification)

---

## 1. Prerequisites

- A **GitHub / GitLab / Bitbucket** account.
- A free account on [Packagist.org](https://packagist.org).
- Git installed on your computer.

---

## 2. Package Directory Structure

Your standalone package folder (`laracast-forms/`) is organized as follows:

```text
laracast-forms/
├── .gitignore
├── LICENSE
├── README.md
├── composer.json
├── phpunit.xml.dist
├── config/
│   └── forms.php
├── routes/
│   └── web.php
├── resources/
│   └── views/
│       └── test.blade.php
├── src/
│   ├── Commands/
│   │   ├── MakeFormCommand.php
│   │   └── TestFormCommand.php
│   ├── Contracts/
│   │   ├── FormRegistryInterface.php
│   │   └── FormTest.php
│   ├── Exceptions/
│   │   ├── FormNotFoundException.php
│   │   └── InvalidFormHandlerException.php
│   ├── Facades/
│   │   └── Forms.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── FormTestController.php
│   │   └── Middleware/
│   │       └── EnsureFormTestingEnvironment.php
│   ├── Results/
│   │   └── FormResult.php
│   ├── Stubs/
│   │   └── form.stub
│   ├── Forms.php
│   └── FormsServiceProvider.php
└── tests/
    ├── TestCase.php
    ├── ValidationTest.php
    ├── RegistrationTest.php
    ├── ExecutionTest.php
    ├── CustomFormClassTest.php
    ├── UnknownFormTest.php
    ├── CommandsTest.php
    ├── DevUiTest.php
    └── FeeQueryTest.php
```

---

## Step 1: Preparing `composer.json`

Ensure your package's `composer.json` has the correct vendor and package name (e.g. `your-github-username/laracast-forms` or `khan/forms`):

```json
{
    "name": "your-username/laracast-forms",
    "description": "A reusable Laravel package for querying table data by date and form testing.",
    "keywords": ["laravel", "forms", "table", "student-fee", "database"],
    "license": "MIT",
    "type": "library",
    "authors": [
        {
            "name": "Your Name",
            "email": "your.email@example.com"
        }
    ],
    "require": {
        "php": "^8.0|^8.1|^8.2|^8.3",
        "illuminate/support": "^9.0|^10.0|^11.0",
        "illuminate/validation": "^9.0|^10.0|^11.0",
        "illuminate/console": "^9.0|^10.0|^11.0",
        "illuminate/routing": "^9.0|^10.0|^11.0",
        "illuminate/view": "^9.0|^10.0|^11.0",
        "illuminate/database": "^9.0|^10.0|^11.0"
    },
    "require-dev": {
        "orchestra/testbench": "^7.0|^8.0|^9.0",
        "phpunit/phpunit": "^9.5|^10.0|^11.0"
    },
    "autoload": {
        "psr-4": {
            "Khan\\Forms\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Khan\\Forms\\Tests\\": "tests/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "Khan\\Forms\\FormsServiceProvider"
            ],
            "aliases": {
                "Forms": "Khan\\Forms\\Facades\\Forms"
            }
        }
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}
```

---

## Step 2: Pushing the Package to GitHub / GitLab

1. Open your terminal inside the package directory (`laracast-forms/`):
   ```bash
   cd laracast-forms
   ```

2. Initialize a Git repository:
   ```bash
   git init
   git add .
   git commit -m "feat: initial release of forms & table package"
   ```

3. Create a new public repository on GitHub (e.g. named `laracast-forms` or `forms`).

4. Link and push to GitHub:
   ```bash
   git branch -M main
   git remote add origin https://github.com/your-username/laracast-forms.git
   git push -u origin main
   ```

---

## Step 3: Creating a Release / Version Tag

Packagist determines package versions based on Git tags:

```bash
git tag v1.0.0
git push origin v1.0.0
```

*(You can also click **Releases -> Create a new release** on GitHub and set tag `v1.0.0`)*

---

## Step 4: Publishing on Packagist.org

1. Go to [https://packagist.org](https://packagist.org) and log in.
2. Click the **Submit** button in the top navigation bar.
3. Paste your GitHub repository URL:
   ```text
   https://github.com/your-username/laracast-forms
   ```
4. Click **Check** -> Packagist will validate your `composer.json`.
5. Click **Submit** -> Your package is now live on Packagist! 🎉

---

## Step 5: Setting Up Auto-Update Webhooks

To make Packagist automatically update whenever you push new Git commits or tags:

1. On Packagist, go to your package page and click **"Set up GitHub Webhook"**.
2. Copy your **API Token**.
3. Go to your GitHub repository -> **Settings** -> **Webhooks** -> **Add webhook**.
4. Set Payload URL to: `https://packagist.org/api/github?username=YOUR_PACKAGIST_USERNAME`
5. Content type: `application/json`
6. Secret: paste your Packagist API Token.
7. Click **Add webhook**.

---

## Step 6: Installing the Package in Any Laravel Project

Now, in **any** Laravel project around the world, developers can install your package with a single command:

```bash
composer require your-username/laracast-forms
```

### Publish Configuration & Views:

```bash
php artisan vendor:publish --tag=forms-config
# Use --force to overwrite existing published views with latest UI
php artisan vendor:publish --tag=forms-views --force
php artisan view:clear
```

---

## Step 7: Testing & Verification

1. **Access the Multi-Entity Explorer Dashboard**:
   Navigate to `http://your-domain/forms/test` in the browser.
   - Switch between **Student Fees**, **Students**, **Expenses**, and **Taskboards** tabs.
   - Filter by Date, Student, Status, or Keyword.
   - Use row checkboxes and the master checkbox for selection.
   - Click **Edit** to modify records dynamically via modal.
   - Click **Delete** or **Delete Selected (N)** to delete single/bulk records.

2. **Run Artisan Commands**:
   ```bash 
   php artisan make:form StudentAdmissionForm
   php artisan forms:test StudentAdmissionForm
   ```

3. **Call Programmatically**:
   ```php
   use Khan\Forms\Facades\Forms;

   // Query any entity: fees, students, expenses, taskboards
   $fees = Forms::getEntries('fees', '2026-06-15', 10);
   $students = Forms::getEntries('students', null, 50, null, ['search' => 'John']);
   $expenses = Forms::getEntries('expenses', '2026-06-15', 25);
   $tasks = Forms::getEntries('taskboards', null, 10);
   ```
