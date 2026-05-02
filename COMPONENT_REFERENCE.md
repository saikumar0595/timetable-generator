# ChronoGen UI Components - Quick Reference

## Quick Start

Include components in your PHP files:
```php
<?php
include('components/header.php');
include('components/sidebar.php');
include('components/cards.php');
include('components/table.php');
include('components/modal.php');
?>
```

## Components Overview

### 1. Header Component
**File:** `components/header.php`

```php
renderHeader($page_title, $user, $role, $show_actions = true);
```

**Example:**
```php
<?php renderHeader('Dashboard', $_SESSION['user'], 'admin', true); ?>
```

**Features:**
- Title display
- Theme toggle button
- Notifications bell
- User profile
- Mobile menu button

---

### 2. Sidebar Component
**File:** `components/sidebar.php`

```php
renderSidebar($current_page_id, $role);
```

**Example:**
```php
<?php renderSidebar('dashboard', 'admin'); ?>
```

**Supported Pages:**
- `dashboard`
- `teachers`
- `subjects`
- `classrooms`
- `groups`
- `timetable`

**Supported Roles:**
- `admin`
- `faculty`
- `student`

---

### 3. Data Table Component
**File:** `components/table.php`

```php
renderTable($headers, $rows, $table_id, $enable_search, $enable_sort);
```

**Example:**
```php
<?php
$headers = ['Name', 'Email', 'Department'];
$rows = [
    ['John Doe', 'john@uni.edu', 'Computer Science'],
    ['Jane Smith', 'jane@uni.edu', 'Electronics'],
];
renderTable($headers, $rows, 'faculty-table', true, true);
?>
```

**Features:**
- Search functionality
- Column sorting
- CSV export
- Pagination info
- Responsive design

---

### 4. Stat Card Component
**File:** `components/cards.php`

```php
renderStatCard($icon, $icon_color, $label, $value, $trend, $trend_value);
```

**Example:**
```php
<?php
renderStatCard(
    'fas fa-users',
    'indigo',
    'Total Teachers',
    125,
    'up',
    '+5 this month'
);
?>
```

**Icon Colors:**
- `indigo`
- `pink`
- `emerald`
- `amber`
- `blue`
- `purple`

---

### 5. Action Card Component
**File:** `components/cards.php`

```php
renderActionCard($icon, $title, $description, $onclick, $href);
```

**Example:**
```php
<?php
renderActionCard(
    'fas fa-book',
    'Manage Subjects',
    'Add and organize course subjects',
    '',
    'manage_subjects.php'
);
?>
```

---

### 6. Modal Component
**File:** `components/modal.php`

```php
renderModal($id, $title, $content, $buttons);
```

**Example:**
```php
<?php
renderModal(
    'confirm-modal',
    'Confirm Action',
    '<p>Are you sure you want to delete?</p>',
    [
        ['label' => 'Delete', 'onclick' => 'confirmDelete()', 'class' => 'px-4 py-2 bg-red-600 text-white rounded-lg'],
        ['label' => 'Cancel', 'onclick' => 'closeModal("confirm-modal")']
    ]
);
?>
```

---

### 7. Form Components
**File:** `components/modal.php`

#### Text Input
```php
<?php renderFormInput('Full Name', 'name', 'text', 'Enter name...', true, 'John Doe'); ?>
```

#### Select Dropdown
```php
<?php
renderFormSelect(
    'Department',
    'dept',
    ['cs' => 'Computer Science', 'ec' => 'Electronics'],
    'cs',
    true
);
?>
```

#### Textarea
```php
<?php renderFormTextarea('Bio', 'bio', 'Enter bio...', false, 'My bio', 4); ?>
```

---

### 8. Alert Component
**File:** `components/modal.php`

```php
renderAlert($type, $message, $dismissible = true);
```

**Types:**
- `success`
- `error`
- `warning`
- `info`

**Example:**
```php
<?php renderAlert('success', 'Teacher added successfully!', true); ?>
```

---

### 9. Badge Component
**File:** `components/cards.php`

```php
renderBadge($text, $type = 'primary', $size = 'md');
```

**Types:**
- `primary`
- `success`
- `warning`
- `danger`
- `info`

**Sizes:**
- `sm`
- `md`
- `lg`

**Example:**
```php
<?php renderBadge('Active', 'success', 'md'); ?>
```

---

### 10. Empty State Component
**File:** `components/cards.php`

```php
renderEmptyState($icon, $title, $message, $button_label, $button_action);
```

**Example:**
```php
<?php
renderEmptyState(
    'fas fa-inbox',
    'No Results',
    'No teachers found matching your search.',
    'Add Teacher',
    'openModal("add-teacher")'
);
?>
```

---

## Tailwind CSS Classes Used

### Commonly Used Classes

**Buttons:**
```css
.btn-primary     - px-4 py-2 bg-indigo-600 text-white rounded-lg...
.btn-secondary   - px-4 py-2 bg-slate-200 text-slate-800 rounded-lg...
.btn-danger      - px-4 py-2 bg-red-600 text-white rounded-lg...
.btn-outline     - px-4 py-2 border-2 border-indigo-600 text-indigo-600...
.btn-small       - px-3 py-1.5 text-sm bg-indigo-600...
```

**Cards:**
```css
.card            - bg-white rounded-2xl shadow-sm border border-slate-100 p-6
.card-hover      - Same as above with hover effects
```

**Inputs:**
```css
.input           - Full width, slate colors, focus ring
.label           - Block, bold, slate-700
```

**Tables:**
```css
.table-header    - bg-slate-50, text-xs, uppercase
.table-cell      - border-b, slate colors
```

---

## Color Palette

```css
Primary:   #4f46e5  (Indigo)
Secondary: #1e293b  (Slate)
Success:   #10b981  (Emerald)
Warning:   #f59e0b  (Amber)
Danger:    #ef4444  (Red)
Info:      #3b82f6  (Blue)
```

---

## JavaScript Functions

### Modal Functions
```javascript
openModal(id)       // Open modal by ID
closeModal(id)      // Close modal by ID
```

### Table Functions
```javascript
filterTable(tableId)         // Search/filter table
sortTable(tableId, header)   // Sort by column
exportTableCSV(tableId)      // Export to CSV
```

### Theme Functions
```javascript
toggleTheme()       // Toggle dark/light mode
```

---

## Full Page Example

```php
<?php
session_start();
include('db.php');
include('components/header.php');
include('components/sidebar.php');
include('components/cards.php');
include('components/table.php');

if (!isset($_SESSION['user'])) { 
    header("Location: login.php"); 
    exit(); 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Title</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-50 text-slate-800 flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <?php renderSidebar('teachers', 'admin'); ?>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden">
        <!-- Header -->
        <?php renderHeader('Teachers', $_SESSION['user'], 'admin', true); ?>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <?php 
                renderStatCard('fas fa-users', 'indigo', 'Total Teachers', 125);
                renderStatCard('fas fa-book', 'pink', 'Subjects Taught', 450);
                renderStatCard('fas fa-award', 'emerald', 'Qualifications', 180);
                ?>
            </div>

            <?php
            $headers = ['Name', 'Email', 'Department'];
            $rows = [
                ['John Doe', 'john@uni.edu', 'CS'],
                ['Jane Smith', 'jane@uni.edu', 'EC'],
            ];
            renderTable($headers, $rows, 'teachers-table');
            ?>
        </div>
    </main>
</body>
</html>
```

---

## Best Practices

1. **Always include session check:**
   ```php
   if (!isset($_SESSION['user'])) { 
       header("Location: login.php"); 
       exit(); 
   }
   ```

2. **Escape user input:**
   ```php
   <?= htmlspecialchars($user_data) ?>
   ```

3. **Use meaningful IDs:**
   ```php
   renderModal('confirm-delete-teacher', 'Confirm Delete', ...)
   ```

4. **Organize imports:**
   ```php
   include('db.php');
   include('components/header.php');
   include('components/sidebar.php');
   // ... more imports
   ```

5. **Structure pages with grid:**
   ```html
   <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
       <!-- Content here -->
   </div>
   ```

---

## Common Issues & Solutions

**Problem:** Modal not showing  
**Solution:** Ensure ID matches `onclick="openModal('id')"`

**Problem:** Sidebar not responding  
**Solution:** Check if `toggle-sidebar()` is called on mobile

**Problem:** Table search not working  
**Solution:** Verify `filterTable()` function and table ID match

**Problem:** Styling looks broken  
**Solution:** Ensure Tailwind CDN is loaded in `<head>`

---

## Performance Tips

1. Minimize re-renders
2. Use CSS animations over JavaScript
3. Lazy load modals
4. Optimize images
5. Cache component includes

---

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers (iOS 14+, Android 11+)

---

**Last Updated:** May 1, 2026  
**Version:** 1.0
