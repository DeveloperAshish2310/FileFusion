# 🧹 Storage Cleaner - Feature Documentation

## Overview

The Storage Cleaner is a dedicated page that helps users clean up unused thumbnail files from their Browsershot directory, freeing up storage space.

## 📍 Access

**Route:** `/panel/storage-cleaner`  
**Named Route:** `panel.cleanStoragePage`  
**URL Example:** `https://yoursite.com/panel/storage-cleaner`

## 🎯 What It Does

The Storage Cleaner scans the `public/Browsershot` directory and:
1. Lists all thumbnail files
2. Checks which files are linked to active/trashed links in the database
3. Identifies unused files (orphaned thumbnails)
4. Deletes unused files
5. Reports space freed and files removed

## 🎨 Page Design

### Layout States

#### 1. **Before Cleanup (Initial State)**
- Clean, centered design with icon
- Blue button to start cleanup
- Info banner explaining what the tool does
- "Back to Dashboard" link

#### 2. **During Cleanup (Loading State)**
- Animated spinner
- "Cleaning Storage..." message
- Pulsing animation effect
- Prevents user interaction

#### 3. **After Cleanup (Success State)**
- Green success banner
- Two statistics cards:
  - **Files Deleted** (Red gradient card)
  - **Space Freed** (Green gradient card)
- Detailed breakdown table
- "Clean Again" and "Back to Dashboard" buttons

#### 4. **Error State**
- Red error banner
- Error message display
- "Try Again" button

### Color Scheme

- **Primary:** Blue (#3B82F6)
- **Success:** Green (#10B981)
- **Warning:** Yellow (#F59E0B)
- **Danger:** Red (#EF4444)
- **Neutral:** Gray scale

## 🔧 Technical Implementation

### Controller Methods

#### `cleanStoragePage()` - View Method
```php
public function cleanStoragePage()
{
    return view('panel.storage-cleaner');
}
```
**Purpose:** Returns the Storage Cleaner page view

#### `cleanStorage()` - API Method
```php
public function cleanStorage()
{
    // Scans directory
    // Compares with database
    // Deletes unused files
    // Returns JSON response
}
```
**Purpose:** Performs the actual cleanup operation

### Routes

```php
// Page view
Route::get('/storage-cleaner', [WebsiteController::class, 'cleanStoragePage'])
    ->name('cleanStoragePage');

// Cleanup API
Route::get('/cleanStorage', [WebsiteController::class, 'cleanStorage'])
    ->name('cleanStorage');
```

### AJAX Request

```javascript
$.ajax({
    url: '{{ route("panel.cleanStorage") }}',
    type: 'GET',
    success: function(response) {
        // Handle success
        // Update UI with stats
    },
    error: function(xhr, status, error) {
        // Handle error
        // Show error message
    }
});
```

### JSON Response Format

```json
{
  "status": "success",
  "message": "Storage Cleaned Successfully",
  "deleted_files_count": 22,
  "freed_space_bytes": 8912255,
  "freed_space_human_readable": "8.50 MB"
}
```

## 📊 UI Components

### Statistics Cards

#### Files Deleted Card
- **Color:** Red gradient (from-red-50 to-red-100)
- **Icon:** Trash can
- **Data:** Number of files removed
- **Label:** "Unused thumbnails"

#### Space Freed Card
- **Color:** Green gradient (from-green-50 to-green-100)
- **Icon:** Storage box
- **Data:** Human-readable size (MB, KB, etc.)
- **Label:** "Storage reclaimed"

### Details Table

| Label | Value |
|-------|-------|
| Files Scanned | Total count |
| Files Removed | Deleted count |
| Space Recovered | Human-readable size |

### Buttons

#### Start Cleanup
- **Style:** Primary blue button
- **Icon:** Trash can
- **Action:** Triggers cleanup process

#### Clean Again
- **Style:** Blue outlined button
- **Icon:** Refresh arrows
- **Action:** Runs cleanup again

#### Back to Dashboard
- **Style:** Gray outlined button
- **Icon:** Home
- **Action:** Navigates to dashboard

#### Try Again (Error State)
- **Style:** Primary blue button
- **Icon:** Refresh arrows
- **Action:** Retries cleanup after error

## 📱 Responsive Design

### Desktop (1024px+)
- Two-column statistics grid
- Four-column tips grid
- Full-width content area

### Tablet (768px)
- Two-column statistics grid
- Two-column tips grid
- Adjusted padding

### Mobile (<768px)
- Single column statistics
- Stacked tips
- Full-width buttons
- Increased vertical spacing

## 🎭 Animations

### Spinner (During Cleanup)
```css
.animate-spin {
    animation: spin 1s linear infinite;
}
```

### Pulse (Loading State)
```css
.cleanup-animation {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
```

### Number Animation (Success State)
- Statistics numbers pulse briefly on load
- Duration: 2 seconds
- Creates visual impact

### Card Hover
```css
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}
```

## 💡 Tips Section

Four helpful tips displayed:

1. **Regular Cleanup** - Run periodically for optimization
2. **Safe Operation** - Only unused files are removed
3. **No Downtime** - Quick operation without disruption
4. **Free Space** - Space immediately available after cleanup

Each tip has:
- Colored icon background
- Icon
- Title
- Description

## 🔒 Security

- Route protected by authentication middleware
- Only deletes files from Browsershot directory
- Cross-references with database before deletion
- No user input required (prevents injection attacks)

## 🎯 User Flow

1. **Navigate** → User clicks "Storage Cleaner" in menu/dashboard
2. **Read Info** → User sees explanation of what will happen
3. **Initiate** → User clicks "Start Cleanup"
4. **Wait** → Loading spinner shows (usually 1-3 seconds)
5. **Review** → Success screen shows stats
6. **Repeat or Exit** → User can clean again or go back

## 📈 Use Cases

### When to Use
- After deleting many links
- After editing links and changing thumbnails
- Regular maintenance (weekly/monthly)
- Before backing up
- When storage quota is limited

### Expected Results
- Typical cleanup: 10-50 files
- Average space saved: 5-20 MB
- Execution time: 1-5 seconds
- No impact on active links

## 🐛 Error Handling

### Possible Errors
1. **Directory not found** - Browsershot folder missing
2. **Permission issues** - Can't delete files
3. **Database connection** - Can't query links
4. **Network timeout** - AJAX request fails

### Error Display
- Red banner with error icon
- Clear error message
- "Try Again" button
- Logs error in console (dev mode)

## 🔄 Future Enhancements

### Potential Features
- [ ] Schedule automatic cleanup
- [ ] Preview files before deletion
- [ ] Restore deleted files (trash system)
- [ ] Email notification after cleanup
- [ ] Cleanup history log
- [ ] Filter by file age
- [ ] Batch operations for multiple directories

## 📝 Code Structure

```
resources/views/panel/storage-cleaner.blade.php
├── Header Section
├── Info Banner
├── Main Content Card
│   ├── Before Cleanup State
│   ├── During Cleanup State
│   ├── After Cleanup State
│   └── Error State
├── Tips Section
└── Scripts
```

## 🧪 Testing Checklist

- [ ] Page loads correctly
- [ ] Info banner displays
- [ ] "Start Cleanup" button works
- [ ] Loading state shows during cleanup
- [ ] Statistics update correctly
- [ ] "Clean Again" works
- [ ] "Back to Dashboard" navigates correctly
- [ ] Error handling works
- [ ] Responsive on mobile
- [ ] Animations work smoothly
- [ ] No console errors

## 📚 Related Files

- **View:** `resources/views/panel/storage-cleaner.blade.php`
- **Controller:** `app/Http/Controllers/WebsiteController.php`
- **Routes:** `routes/web.php`
- **Helper:** `app/helpers/helpers.php` (BytetoSize function)

---

**Status:** ✅ Production Ready  
**Version:** 1.0  
**Last Updated:** October 19, 2025
