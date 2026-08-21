# 🎨 Storage Cleaner - Visual Guide

## Page States Flowchart

```
┌─────────────────────────────────────────────────────────┐
│                    INITIAL STATE                        │
│                                                         │
│  ┌───────────────────────────────────────────────────┐ │
│  │  ℹ️  Info Banner                                  │ │
│  │  What does this do?                               │ │
│  │  This tool scans and removes unused thumbnails... │ │
│  └───────────────────────────────────────────────────┘ │
│                                                         │
│  ┌───────────────────────────────────────────────────┐ │
│  │              🗑️ Icon (Blue Circle)                │ │
│  │                                                   │ │
│  │         Ready to Clean Storage                    │ │
│  │                                                   │ │
│  │  Click the button below to scan and remove       │ │
│  │  unused thumbnail files from your storage.       │ │
│  │                                                   │ │
│  │         [ 🗑️ Start Cleanup ]                      │ │
│  └───────────────────────────────────────────────────┘ │
│                                                         │
│  ┌───────────────────────────────────────────────────┐ │
│  │  💡 Storage Management Tips                       │ │
│  │  ├─ Regular Cleanup                               │ │
│  │  ├─ Safe Operation                                │ │
│  │  ├─ No Downtime                                   │ │
│  │  └─ Free Space                                    │ │
│  └───────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘
                        │
                        │ [User clicks "Start Cleanup"]
                        ▼
┌─────────────────────────────────────────────────────────┐
│                   LOADING STATE                         │
│                                                         │
│  ┌───────────────────────────────────────────────────┐ │
│  │                                                   │ │
│  │            ⟳ Spinner (Animated)                   │ │
│  │         (Pulsing Blue Circle)                     │ │
│  │                                                   │ │
│  │         Cleaning Storage...                       │ │
│  │  Please wait while we scan and remove            │ │
│  │  unused files.                                    │ │
│  │                                                   │ │
│  └───────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘
                        │
                        │ [Cleanup completes]
                        ▼
┌─────────────────────────────────────────────────────────┐
│                   SUCCESS STATE                         │
│                                                         │
│  ┌───────────────────────────────────────────────────┐ │
│  │  ✅ Storage Cleaned Successfully!                 │ │
│  │  Unused files have been removed from storage.     │ │
│  └───────────────────────────────────────────────────┘ │
│                                                         │
│  ┌──────────────────────┐  ┌──────────────────────┐   │
│  │  Files Deleted       │  │  Space Freed         │   │
│  │  (Red Gradient)      │  │  (Green Gradient)    │   │
│  │                      │  │                      │   │
│  │  🗑️  22              │  │  📦  8.50 MB         │   │
│  │  Unused thumbnails   │  │  Storage reclaimed   │   │
│  └──────────────────────┘  └──────────────────────┘   │
│                                                         │
│  ┌───────────────────────────────────────────────────┐ │
│  │  📋 Cleanup Details                               │ │
│  │  Files Scanned:    22                             │ │
│  │  Files Removed:    22                             │ │
│  │  Space Recovered:  8.50 MB                        │ │
│  └───────────────────────────────────────────────────┘ │
│                                                         │
│  [ 🔄 Clean Again ]  [ 🏠 Back to Dashboard ]          │
└─────────────────────────────────────────────────────────┘
```

## Component Details

### 1. Info Banner (Blue)
```
┌─────────────────────────────────────────────────────┐
│  ℹ️  What does this do?                             │
│  ─────────────────────────────────────────────────  │
│  This tool scans your Browsershot directory and    │
│  removes unused thumbnail screenshots that are no  │
│  longer associated with any links in your database.│
│  This helps free up storage space without          │
│  affecting your active links.                      │
└─────────────────────────────────────────────────────┘
```
- Background: Blue-50
- Border: Blue-200
- Icon: Blue circle with "i"
- Text: Blue-800 (heading), Blue-700 (body)

### 2. Files Deleted Card (Success State)
```
┌────────────────────────────────────────┐
│  Files Deleted              🗑️         │
│                                        │
│  22                                    │
│  Unused thumbnails                     │
└────────────────────────────────────────┘
```
- Gradient: Red-50 to Red-100
- Border: Red-200
- Number: 3xl font, Red-900
- Icon: Red-200 circle, Red-700 icon
- Label: Red-600

### 3. Space Freed Card (Success State)
```
┌────────────────────────────────────────┐
│  Space Freed                📦         │
│                                        │
│  8.50 MB                               │
│  Storage reclaimed                     │
└────────────────────────────────────────┘
```
- Gradient: Green-50 to Green-100
- Border: Green-200
- Number: 3xl font, Green-900
- Icon: Green-200 circle, Green-700 icon
- Label: Green-600

### 4. Details Table
```
┌─────────────────────────────────────────┐
│  📋 Cleanup Details                     │
│  ─────────────────────────────────────  │
│  Files Scanned:      22                 │
│  ─────────────────────────────────────  │
│  Files Removed:      22                 │
│  ─────────────────────────────────────  │
│  Space Recovered:    8.50 MB            │
└─────────────────────────────────────────┘
```
- Background: Gray-50
- Labels: Gray-600
- Values: Gray-900, bold

### 5. Tips Grid
```
┌──────────────────────┐  ┌──────────────────────┐
│  ✓ Regular Cleanup   │  │  🛡️ Safe Operation   │
│  Run this tool       │  │  Only unused files   │
│  periodically...     │  │  are removed...      │
└──────────────────────┘  └──────────────────────┘

┌──────────────────────┐  ┌──────────────────────┐
│  ⚡ No Downtime      │  │  💰 Free Space       │
│  Cleanup runs        │  │  Recovered space     │
│  quickly without...  │  │  is immediately...   │
└──────────────────────┘  └──────────────────────┘
```
Each tip:
- 8x8 icon circle (colored background)
- 16x16 icon
- Bold title (Gray-900)
- Description (Gray-600)

## Button Styles

### Primary Button (Start Cleanup, Try Again)
```
┌─────────────────────────────┐
│  🗑️ Start Cleanup           │
└─────────────────────────────┘
```
- Background: Blue-600
- Text: White
- Hover: Blue-700
- Padding: 0.75rem 1.5rem
- Border Radius: 0.5rem
- Shadow: sm

### Secondary Button (Clean Again)
```
┌─────────────────────────────┐
│  🔄 Clean Again             │
└─────────────────────────────┘
```
- Background: White
- Border: Blue-600
- Text: Blue-600
- Hover: Blue-50
- Padding: 0.625rem 1rem
- Border Radius: 0.5rem

### Tertiary Button (Back to Dashboard)
```
┌─────────────────────────────┐
│  🏠 Back to Dashboard       │
└─────────────────────────────┘
```
- Background: White
- Border: Gray-300
- Text: Gray-700
- Hover: Gray-50
- Padding: 0.625rem 1rem
- Border Radius: 0.5rem

## Loading Animation

### Spinner
```
     ⟳
   Rotating
  Continuously
```
- Size: 32x32
- Color: Blue-600
- Animation: spin 1s linear infinite
- Outer circle: 25% opacity
- Inner arc: 75% opacity

### Pulsing Container
```
  ┌─────────┐
  │    ⟳    │  ← Pulsing in/out
  └─────────┘
```
- Animation: pulse 2s infinite
- Opacity: 1 → 0.5 → 1

## Color Scheme Summary

### State Colors
| State | Primary Color | Usage |
|-------|---------------|-------|
| Info | Blue (#3B82F6) | Banners, buttons |
| Success | Green (#10B981) | Success banner, space card |
| Warning | Yellow (#F59E0B) | Tips icons |
| Error | Red (#EF4444) | Error banner, deleted files card |
| Neutral | Gray (#6B7280) | Text, borders |

### Gradients
```
Red Card:    from-red-50 → to-red-100
Green Card:  from-green-50 → to-green-100
```

## Spacing & Layout

### Container
- Max Width: Full (with padding)
- Padding: 2rem (sm), 1.5rem (md), 1rem (lg)

### Cards
- Padding: 1.5rem
- Border Radius: 0.75rem
- Shadow: sm
- Gap Between: 1.5rem

### Grid Layout
```
Desktop (1024px+):
┌────────┬────────┐
│  Card  │  Card  │
└────────┴────────┘

Mobile (<768px):
┌────────┐
│  Card  │
├────────┤
│  Card  │
└────────┘
```

## Icon Set

### Used Icons (Lucide)
- 🗑️ Trash (Delete, Clean)
- 🔄 Refresh (Clean Again, Retry)
- 🏠 Home (Back to Dashboard)
- ◀️ Arrow Left (Back)
- ℹ️ Info (Information)
- ✅ Check Circle (Success)
- ❌ X Circle (Error)
- 📦 Package (Storage)
- 📋 Clipboard (Details)
- ⭐ Star (Tips)
- ⟳ Loader (Spinning)

All icons: 16-24px, stroke-width: 2

## Responsive Breakpoints

| Screen Size | Layout Changes |
|-------------|----------------|
| < 640px | Single column, stacked buttons |
| 640px - 768px | Two column stats |
| 768px - 1024px | Two column tips |
| > 1024px | Full grid layout |

## Accessibility

- All interactive elements have focus states
- Color contrast meets WCAG AA standards
- Buttons have descriptive labels
- Loading states announced
- Error messages clear and actionable

---

**Design System:** Tailwind CSS  
**Icons:** Lucide Icons  
**Animation:** CSS Transitions + Keyframes  
**Responsive:** Mobile-first approach
