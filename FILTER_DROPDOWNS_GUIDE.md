# Property Filter Dropdowns - Multi-Select Guide

## Overview
The property filter system now supports **multi-select filtering** for property types, allowing users to search for properties matching multiple types simultaneously.

---

## How Filter Dropdowns Work

### **Property Type Filter (Multi-Select)**

#### **User Experience**

1. **Initial State**
   - Shows "All Property Types" placeholder
   - Clean, minimal appearance

2. **Opening Dropdown**
   - Click the filter button
   - Dropdown expands with all available property types
   - Checkboxes for each type
   - Search bar (if more than 8 options)

3. **Selecting Types**
   - Check/uncheck property types
   - Selected count updates in real-time
   - "Select All" / "Clear" quick actions available

4. **Viewing Selection**
   - Selected types shown as badges below the button
   - Shows up to 3 badges, then "+X more"
   - Example: `[Commercial Lot] [Residential Lot] +1 more`

5. **Applying Filters**
   - Click "Apply" button
   - Or click outside dropdown (auto-applies)
   - Page updates with filtered results

6. **Active Filter Indication**
   - Button shows "X selected" when types are chosen
   - Selected badges visible below dropdown
   - Can remove individual types by clicking badge X

---

## Filter Behavior

### **OR Logic (Default)**
When multiple types are selected, properties matching **ANY** of the selected types appear in results.

**Example:**
```
Selected: [Commercial Lot, Residential Lot]

Results include:
✅ Property A: Commercial Lot only
✅ Property B: Residential Lot only
✅ Property C: Both Commercial + Residential (mixed-use)
❌ Property D: Agricultural Land only
```

### **URL Parameters**
Filters are reflected in the URL for bookmarking and sharing:
```
/properties?types=commercial_lot,residential_lot&municipality=Tagbilaran+City
```

### **Persistence**
- Filters persist across page navigation
- URL can be bookmarked
- Browser back/forward maintains filter state

---

## Filter Components

### **1. MultiSelectFilter.vue**
**Purpose:** Reusable multi-select dropdown component

**Features:**
- ✅ Checkbox-based selection
- ✅ Search within options (for long lists)
- ✅ Select All / Clear All
- ✅ Selected count display
- ✅ Badge preview below button
- ✅ Auto-apply on click outside
- ✅ Smooth animations

**Props:**
```javascript
{
  modelValue: Array,      // Selected values
  options: Array,         // Available options [{value, label}]
  placeholder: String     // Placeholder text
}
```

**Events:**
```javascript
@change  // Emitted when selection changes
```

### **2. UnifiedSearchFilter.vue**
**Purpose:** Main filter container with search and multiple filter types

**Supported Filter Types:**
- `multiselect` - Multi-select dropdown (NEW)
- `select` - Single-select dropdown
- `number` - Number input
- `date` - Date picker
- `text` - Text input

**Configuration Example:**
```javascript
{
  key: "types",
  label: "Property Type",
  type: "multiselect",
  span: 2,
  placeholder: "All Property Types",
  options: [
    { value: "commercial_lot", label: "Commercial Lot" },
    { value: "residential_lot", label: "Residential Lot" },
    // ...
  ]
}
```

---

## Implementation Details

### **Frontend (Vue.js)**

#### **Filter State Management**
```javascript
const filters = ref({
  types: [],  // Array of selected types
  municipality: "",
  status: "",
  // ... other filters
});
```

#### **Handling Filter Changes**
```javascript
const handleFilterChange = (key, value) => {
  filters.value[key] = value;
  filterProperties();
};
```

#### **Converting to URL Parameters**
```javascript
// Convert types array to comma-separated string
if (cleanFilters.types && Array.isArray(cleanFilters.types)) {
  cleanFilters.types = cleanFilters.types.join(',');
}
// Result: types=commercial_lot,residential_lot
```

### **Backend (Laravel)**

#### **Controller Processing**
```php
->when($request->types, function ($query, $types) {
    // Support both array and comma-separated string
    if (is_string($types)) {
        $types = explode(',', $types);
    }
    $query->byTypes($types);
})
```

#### **Database Query**
```php
// Property Model scope
public function scopeByTypes($query, $types)
{
    return $query->where(function ($q) use ($types) {
        foreach ($types as $type) {
            $q->orWhereJsonContains('types', $type);
        }
    });
}
```

---

## User Scenarios

### **Scenario 1: Finding Mixed-Use Properties**
**Goal:** Find properties suitable for both commercial and residential use

**Steps:**
1. Open Property Type filter
2. Select "Commercial Lot"
3. Select "Residential Lot"
4. Click "Apply"

**Result:** Shows all properties that are:
- Commercial only
- Residential only
- **Mixed-use (both commercial and residential)**

### **Scenario 2: Agricultural Land Search**
**Goal:** Find any agricultural property

**Steps:**
1. Open Property Type filter
2. Select "Agricultural Land"
3. Select "Rice Field"
4. Select "Coconut Plantation"
5. Click "Apply"

**Result:** Shows all agricultural properties regardless of specific sub-type

### **Scenario 3: Beachfront Properties**
**Goal:** Find beachfront properties of any type

**Steps:**
1. Open Property Type filter
2. Select "Beachfront"
3. Click "Apply"

**Result:** Shows all beachfront properties

### **Scenario 4: Clearing Filters**
**Goal:** See all properties again

**Steps:**
1. Click "Clear filters" button in header
2. Or open dropdown and click "Clear"

**Result:** All filters reset, shows all properties

---

## Visual Design

### **Filter Button States**

**Empty State:**
```
┌─────────────────────────┐
│ All Property Types    ▼ │
└─────────────────────────┘
```

**With Selection:**
```
┌─────────────────────────┐
│ 2 selected            ▼ │
└─────────────────────────┘
[Commercial Lot] [Residential Lot]
```

**Dropdown Open:**
```
┌─────────────────────────┐
│ 2 selected            ▲ │
└─────────────────────────┘
┌─────────────────────────┐
│ Select All    Clear     │
├─────────────────────────┤
│ ☑ Commercial Lot        │
│ ☑ Residential Lot       │
│ ☐ Agricultural Land     │
│ ☐ Industrial Lot        │
│ ☐ Beachfront            │
│ ...                     │
├─────────────────────────┤
│     Apply (2)           │
└─────────────────────────┘
```

### **Color Coding**
- **Selected badges:** Blue (`bg-blue-100 text-blue-800`)
- **Overflow badge:** Gray (`bg-gray-100 text-gray-600`)
- **Apply button:** Blue (`bg-blue-600 text-white`)
- **Hover states:** Subtle gray background

---

## Mobile Responsiveness

### **Desktop (≥768px)**
- Filters displayed in horizontal row
- Multi-select dropdowns full-width within grid column
- Badge display below button

### **Tablet (≥640px)**
- Filters stack in 2-column grid
- Dropdowns adjust to container width

### **Mobile (<640px)**
- Filters stack vertically
- Full-width dropdowns
- Touch-optimized tap targets (min 44px)
- Scrollable dropdown list

---

## Accessibility

### **Keyboard Navigation**
- ✅ Tab to focus filter button
- ✅ Enter/Space to open dropdown
- ✅ Arrow keys to navigate options
- ✅ Space to toggle checkboxes
- ✅ Escape to close dropdown

### **Screen Readers**
- ✅ Proper ARIA labels
- ✅ Announces selected count
- ✅ Checkbox states announced
- ✅ Filter changes announced

### **Visual Indicators**
- ✅ Focus rings on interactive elements
- ✅ Clear selected state
- ✅ Hover feedback
- ✅ Active filter badges

---

## Performance Considerations

### **Debouncing**
Filter changes are debounced by 300ms to prevent excessive API calls:
```javascript
const filterProperties = debounce(() => {
  // Apply filters
}, 300);
```

### **Optimizations**
- ✅ Only sends non-empty filters to backend
- ✅ Uses `preserveState` for smooth updates
- ✅ `preserveScroll` maintains scroll position
- ✅ Minimal re-renders with computed properties

### **URL Management**
- ✅ Uses `replace: true` to avoid cluttering browser history
- ✅ Clean URL parameters (no empty values)
- ✅ Array converted to comma-separated string

---

## Future Enhancements (Phase 2)

### **Filter Mode Toggle**
Add option to switch between OR and AND logic:
```
Match: ○ ANY selected type  ● ALL selected types
```

**Use Case:** Find properties that are BOTH commercial AND residential (true mixed-use only)

### **Property Count per Type**
Show how many properties match each type:
```
☑ Commercial Lot (45)
☑ Residential Lot (123)
☐ Agricultural Land (67)
```

### **Saved Filters**
Allow users to save common filter combinations:
```
My Saved Filters:
- Beachfront Properties in Panglao
- Commercial Lots Under ₱5M
- Agricultural Land > 1 Hectare
```

### **Filter Presets**
Quick-access preset filters:
```
Quick Filters:
[Beachfront] [Budget-Friendly] [Large Lots] [With Utilities]
```

---

## Troubleshooting

### **Issue: Filter not working**
**Check:**
1. Browser console for errors
2. Network tab - verify `types` parameter in URL
3. Backend logs - verify query execution

**Solution:**
```javascript
// Ensure types is array before filtering
if (!Array.isArray(filters.value.types)) {
  filters.value.types = [];
}
```

### **Issue: Selected types not showing**
**Check:**
1. Verify `formatted_types` exists in property data
2. Check PropertyTypeBadges component is imported
3. Verify property has `types` array populated

**Solution:**
```php
// Ensure types are appended to JSON
protected $appends = ['formatted_types'];
```

### **Issue: URL parameters not working**
**Check:**
1. Verify comma-separated format: `types=type1,type2`
2. Check controller splits string correctly
3. Verify route accepts query parameters

**Solution:**
```php
// Controller should handle both formats
if (is_string($types)) {
    $types = explode(',', $types);
}
```

---

## Testing Checklist

### **Functional Tests**
- [ ] Select single type - verify results
- [ ] Select multiple types - verify OR logic
- [ ] Clear individual type badge
- [ ] Clear all filters
- [ ] Search within dropdown (if >8 options)
- [ ] Select All / Clear All buttons
- [ ] Apply button
- [ ] Click outside to close
- [ ] URL parameters update correctly
- [ ] Browser back/forward works
- [ ] Bookmark URL and reload

### **UI/UX Tests**
- [ ] Dropdown opens/closes smoothly
- [ ] Selected count updates
- [ ] Badges display correctly
- [ ] Overflow "+X more" works
- [ ] Mobile responsive
- [ ] Touch targets adequate
- [ ] Animations smooth
- [ ] No layout shifts

### **Edge Cases**
- [ ] No types selected (shows all)
- [ ] All types selected
- [ ] Very long type names
- [ ] Many types selected (>10)
- [ ] Slow network (debouncing works)
- [ ] Empty results

---

## Summary

The multi-select filter system provides:

✅ **Flexible Search** - Find properties matching multiple criteria
✅ **Better UX** - Intuitive checkbox interface
✅ **Visual Feedback** - Clear indication of active filters
✅ **Performance** - Optimized queries and debouncing
✅ **Accessibility** - Keyboard and screen reader support
✅ **Mobile-Friendly** - Responsive design
✅ **Shareable** - URL-based filter state

**Status:** ✅ PRODUCTION READY
