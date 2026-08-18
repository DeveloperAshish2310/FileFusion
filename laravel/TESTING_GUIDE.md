# 🧪 Testing Guide - Search & Filter Features

## ✅ Manual Testing Checklist

### 1. **Search Functionality**

#### Test Search by Title
- [ ] Navigate to Links page
- [ ] Type "github" in search box
- [ ] Wait 500ms (debounce)
- [ ] Verify only links with "github" in title appear
- [ ] Verify results count updates
- [ ] Verify search badge shows "Search: github"

#### Test Search by URL
- [ ] Clear previous search
- [ ] Type a domain (e.g., "youtube.com")
- [ ] Verify links with that domain in URL appear
- [ ] Verify results count is accurate

#### Test Empty Search Results
- [ ] Search for non-existent term (e.g., "xyzabc123")
- [ ] Verify empty state shows
- [ ] Verify message: "No links found"
- [ ] Verify "Add New Link" button appears

#### Test Search Clearing
- [ ] Type search term
- [ ] Click Clear Filters button (trash icon)
- [ ] Verify search input is empty
- [ ] Verify all links reappear

### 2. **Category Filter**

#### Test Single Category
- [ ] Click category dropdown
- [ ] Select a category (e.g., "Work")
- [ ] Verify only links in that category show
- [ ] Verify category badge appears
- [ ] Verify results count updates

#### Test "All Categories"
- [ ] Select a specific category
- [ ] Change back to "All Categories"
- [ ] Verify all links (regardless of category) appear

#### Test Empty Category
- [ ] Create a category with no links
- [ ] Select that category
- [ ] Verify empty state shows

### 3. **Starred Filter**

#### Test Starred Toggle ON
- [ ] Click star button (should turn yellow)
- [ ] Verify only starred links appear
- [ ] Verify [Starred] badge shows
- [ ] Verify star icon is filled (yellow)

#### Test Starred Toggle OFF
- [ ] With starred filter active
- [ ] Click star button again
- [ ] Verify all links (starred + unstarred) appear
- [ ] Verify badge disappears
- [ ] Verify star icon is hollow (gray)

#### Test No Starred Links
- [ ] Unstar all your links
- [ ] Activate starred filter
- [ ] Verify empty state shows

### 4. **Combined Filters**

#### Test Search + Category
- [ ] Type "tutorial" in search
- [ ] Select "Development" category
- [ ] Verify only Development links with "tutorial" appear
- [ ] Verify both badges show
- [ ] Verify results count is accurate

#### Test Search + Starred
- [ ] Type search term
- [ ] Activate starred filter
- [ ] Verify only starred links matching search appear
- [ ] Verify both badges show

#### Test Category + Starred
- [ ] Select category
- [ ] Activate starred filter
- [ ] Verify only starred links in that category appear
- [ ] Verify both badges show

#### Test All Three Filters
- [ ] Type search term
- [ ] Select category
- [ ] Activate starred filter
- [ ] Verify all filters apply simultaneously
- [ ] Verify all three badges show
- [ ] Verify results are accurate

### 5. **Clear Filters Button**

#### Test Clearing All Filters
- [ ] Apply search, category, and starred filters
- [ ] Click Clear Filters button (trash icon)
- [ ] Verify search input is cleared
- [ ] Verify category resets to "All Categories"
- [ ] Verify starred button is inactive (gray)
- [ ] Verify all badges disappear
- [ ] Verify all links reappear
- [ ] Verify URL has no filter parameters

### 6. **UI/UX Elements**

#### Test Loading Indicator
- [ ] Apply a filter
- [ ] Observe spinner appears briefly
- [ ] Verify content dims (50% opacity)
- [ ] Verify smooth transition to results

#### Test Results Count
- [ ] Apply any filter
- [ ] Verify "Found X links" displays correctly
- [ ] Verify number matches actual card count
- [ ] Verify singular/plural ("link" vs "links") is correct

#### Test Filter Badges
- [ ] Apply search filter
- [ ] Verify blue badge appears with search term
- [ ] Apply category filter
- [ ] Verify purple badge appears with category name
- [ ] Activate starred
- [ ] Verify yellow badge appears with "Starred"
- [ ] Verify all badges can show simultaneously

#### Test Button Hover States
- [ ] Hover over search input
- [ ] Verify focus ring appears when clicked
- [ ] Hover over starred button
- [ ] Verify background changes to light gray (inactive) or darker yellow (active)
- [ ] Hover over clear button
- [ ] Verify background changes to light gray

### 7. **Keyboard Shortcuts**

#### Test Ctrl+K (Cmd+K on Mac)
- [ ] Press Ctrl+K (or Cmd+K)
- [ ] Verify search input receives focus
- [ ] Verify cursor appears in input field
- [ ] Verify works from anywhere on page

#### Test Tab Navigation
- [ ] Press Tab from search input
- [ ] Verify focus moves to category dropdown
- [ ] Press Tab again
- [ ] Verify focus moves through filter buttons

### 8. **Browser History & URLs**

#### Test URL Parameters
- [ ] Apply search filter
- [ ] Verify URL contains `?search=your-term`
- [ ] Apply category filter
- [ ] Verify URL contains `&category=encrypted-id`
- [ ] Activate starred
- [ ] Verify URL contains `&starred=1`

#### Test Direct URL Access
- [ ] Copy URL with filters applied
- [ ] Open in new tab
- [ ] Verify filters are preserved
- [ ] Verify results match

#### Test Browser Back Button
- [ ] Apply several different filters
- [ ] Click browser back button
- [ ] Verify previous filter state is restored
- [ ] Click forward button
- [ ] Verify next filter state is restored

### 9. **AJAX & Performance**

#### Test Debounce
- [ ] Type quickly in search box ("g", "gi", "git", "github")
- [ ] Verify only ONE AJAX request fires (after 500ms)
- [ ] Check browser Network tab to confirm

#### Test Rapid Filter Changes
- [ ] Quickly change category 3-4 times
- [ ] Verify no race conditions
- [ ] Verify final results match last selection

#### Test Filter with Pagination
- [ ] Apply filter that returns >12 results
- [ ] Verify pagination appears
- [ ] Click page 2
- [ ] Verify filter persists
- [ ] Verify correct page 2 results show

### 10. **Integration with Existing Features**

#### Test Star Toggle During Filter
- [ ] Apply starred filter
- [ ] Unstar a visible link
- [ ] Verify link disappears from view
- [ ] Verify results count decrements

#### Test Hide Toggle During Filter
- [ ] Apply any filter
- [ ] Hide a visible link
- [ ] Verify link disappears
- [ ] Verify results count decrements

#### Test View Toggle (Grid/List)
- [ ] Apply filters
- [ ] Toggle to list view
- [ ] Verify filters still work
- [ ] Verify search still functions
- [ ] Toggle back to grid view
- [ ] Verify filters persist

#### Test Delete During Filter
- [ ] Apply filter
- [ ] Delete a link
- [ ] Verify link removed from view
- [ ] Verify results count updates
- [ ] Verify filter remains active

### 11. **Responsive Design**

#### Test on Desktop (1920px)
- [ ] Verify all filters in one row
- [ ] Verify search input takes remaining space
- [ ] Verify proper spacing between elements

#### Test on Tablet (768px)
- [ ] Verify search input full width
- [ ] Verify category and buttons on second row
- [ ] Verify touch-friendly button sizes

#### Test on Mobile (375px)
- [ ] Verify all elements stack vertically
- [ ] Verify search input full width
- [ ] Verify category dropdown full width
- [ ] Verify buttons maintain usable size
- [ ] Verify scrolling works properly

### 12. **Edge Cases**

#### Test with No Links
- [ ] Delete all links (or use fresh account)
- [ ] Visit links page
- [ ] Verify empty state shows
- [ ] Verify filters don't error
- [ ] Verify "Add New Link" button works

#### Test with Special Characters in Search
- [ ] Search with quotes: `"test"`
- [ ] Search with symbols: `@#$%`
- [ ] Search with spaces: `my test link`
- [ ] Verify no errors occur
- [ ] Verify results are accurate

#### Test with Very Long Search Term
- [ ] Paste 200+ character string
- [ ] Verify input handles it
- [ ] Verify search works
- [ ] Verify no UI breaking

#### Test with Encrypted ID Manipulation
- [ ] Apply category filter
- [ ] Manually modify category parameter in URL
- [ ] Verify graceful error handling
- [ ] Verify no security issues

## 🐛 Known Issues to Watch For

- [ ] Debounce interfering with fast typing
- [ ] Race conditions in AJAX requests
- [ ] URL encoding of special characters
- [ ] Category decryption errors
- [ ] Pagination not preserving filters
- [ ] View toggle state lost after filter
- [ ] Star/hide events not reattaching after AJAX
- [ ] Memory leaks from repeated AJAX calls

## ✨ Expected Behavior Summary

### Performance
- Search debounce: 500ms
- AJAX request time: < 1 second
- Loading indicator: Visible during AJAX
- Smooth transitions: All state changes

### Visual
- Blue focus rings on inputs
- Yellow active state for starred
- Purple badges for categories
- Gray neutral elements
- Smooth hover effects

### Functionality
- All filters work independently
- All filters work together
- Filters preserve on pagination
- Filters save in URL
- Clear button resets everything

## 📊 Test Results Template

```
Date: ___________
Tester: ___________
Browser: ___________
Screen Size: ___________

| Test Case | Pass | Fail | Notes |
|-----------|------|------|-------|
| Search by Title | ☐ | ☐ | |
| Search by URL | ☐ | ☐ | |
| Category Filter | ☐ | ☐ | |
| Starred Filter | ☐ | ☐ | |
| Combined Filters | ☐ | ☐ | |
| Clear Filters | ☐ | ☐ | |
| Keyboard Shortcuts | ☐ | ☐ | |
| URL Parameters | ☐ | ☐ | |
| AJAX Performance | ☐ | ☐ | |
| Responsive Design | ☐ | ☐ | |
| Edge Cases | ☐ | ☐ | |

Overall Status: ____________
```

---

**Testing Tip**: Test in multiple browsers (Chrome, Firefox, Safari, Edge) and on different devices (Desktop, Tablet, Mobile) for comprehensive coverage!
