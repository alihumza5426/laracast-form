# Khan Forms - Laravel Multi-Entity Database Explorer & Management Package

`khan/forms` is a reusable Laravel package for querying database records (Student Fees, Students, Expenses, and Taskboards), live filtering, editing, and deleting records with individual and bulk action controls.

---

## 🌟 Key Features

- **Multi-Entity Database Explorer**: Interactive dashboard (`/forms/test`) to seamlessly switch between:
  - 🎓 **Student Fees** (`student_fee_managers`)
  - 👨‍🎓 **Students** (`students`)
  - 💳 **Expenses** (`expenses` / `expense_managers`)
  - 📋 **Taskboards / Tasks** (`taskboards` / `tasks`)
- **Row Checkboxes & Bulk Delete**:
  - Individual row checkbox to select specific items.
  - Master "Select All" checkbox in table header.
  - Floating Bulk Action toolbar with `Delete Selected (N)` and `Deselect All`.
- **Edit & Delete Action Buttons**:
  - ✏️ **Edit**: Opens an interactive modal to edit any database column dynamically with instant AJAX update.
  - 🗑️ **Delete**: Single entry delete with confirmation dialog.
- **Comprehensive Dynamic Filters**:
  - Filter by **Date** & Date column (`created_at`, `due_date`, `paid_date`, `date`, `fee_month`, etc.).
  - Filter by **Student** via searchable student selector dropdown.
  - Filter by **Sub-type** (`all`, `challan`, `student`).
  - Filter by **Status** (`paid`, `unpaid`, `pending`, `active`, `completed`, etc.).
  - Live **Keyword Search** across title, name, roll no, challan no, remarks, and ID.
  - Configurable **Limit** (10, 25, 50, 100, 500/All).
- **Direct Query & CRUD API**:
  - `Forms::getEntries($entity, $date, $limit, $dateColumn, $filters)`
  - `Forms::getEntryById($entity, $id)`
  - `Forms::updateEntry($entity, $id, $data)`
  - `Forms::deleteEntry($entity, $id)`
  - `Forms::deleteBulkEntries($entity, $ids)`
- **Artisan Generators**: `php artisan make:form` and `php artisan forms:test`.

---

## 🌐 Web Interface (Database Explorer)

Navigate to:
👉 **`http://127.0.0.1:8000/forms/test`** (or `http://localhost/new_sls/public/forms/test`)

### How to Use:
1. **Switch Entity**: Click the top tabs for **Student Fees**, **Students**, **Expenses**, or **Taskboards**.
2. **Apply Filters**: Select a Date, pick a Student, choose a Status, or type in the Search box, then click **"Apply Filters"**.
3. **Edit a Record**: Click the **"Edit"** button on any row -> update values in the modal -> click **"Save Changes"**.
4. **Delete a Single Record**: Click **"Delete"** on a row and confirm.
5. **Bulk Delete**: Check multiple rows (or click the header checkbox to select all) -> click **"Delete Selected (N)"** in the top action bar.

---

## 🚀 Programmatic Usage (In PHP / Controllers)

```php
use Khan\Forms\Facades\Forms;

// 1. Get entries for any entity
$fees = Forms::getEntries('fees', '2026-06-15', 10);
$students = Forms::getEntries('students', null, 50, null, ['search' => 'John']);
$expenses = Forms::getEntries('expenses', '2026-06-15', 25);
$tasks = Forms::getEntries('taskboards', null, 10);

// 2. Update an entry
Forms::updateEntry('expenses', 1, [
    'title' => 'Updated Office Supplies',
    'amount' => 350.00,
]);

// 3. Delete single entry
Forms::deleteEntry('taskboards', 12);

// 4. Bulk delete
Forms::deleteBulkEntries('fees', [101, 102, 103]);
```

---

## 📦 Updating Published Views in Host Laravel Apps

If you installed this package in your Laravel project and your view hasn't updated, run:

```bash
# Force overwrite published views with latest version
php artisan vendor:publish --tag=forms-views --force

# Clear cached views & routes
php artisan view:clear
php artisan route:clear
php artisan config:clear
```

---

## 🧪 Running Automated Tests

```bash
php vendor/phpunit/phpunit/phpunit
```

**Results:**
```text
OK (28 tests, 100 assertions)
```
