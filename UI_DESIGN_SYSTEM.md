# GeoCasa Bohol - UI Design System

## Overview

This document outlines the unified design system for GeoCasa Bohol, ensuring consistency across all components and pages.

## Core Design Principles

### 1. Consistency

-   All components follow the same design patterns
-   Unified color scheme and typography
-   Consistent spacing and layout principles

### 2. Accessibility

-   WCAG 2.1 AA compliance
-   Keyboard navigation support
-   Screen reader friendly
-   High contrast ratios

### 3. Professional Aesthetics

-   Clean, modern design
-   Subtle shadows and gradients
-   Professional color palette
-   Elegant typography

## Color Palette

### Primary Colors

-   **Primary Blue**: Trust and reliability
    -   `primary-50`: #f0f9ff
    -   `primary-500`: #0ea5e9
    -   `primary-600`: #0284c7
    -   `primary-700`: #0369a1

### Accent Colors

-   **Growth Green**: Success and growth
    -   `accent-500`: #22c55e
    -   `accent-600`: #16a34a

### Status Colors

-   **Success**: `success-600` (#16a34a)
-   **Warning**: `warning-600` (#d97706)
-   **Error**: `error-600` (#dc2626)
-   **Info**: `info-600` (#2563eb)

### Neutral Colors

-   **Neutral-50**: #fafafa (Background)
-   **Neutral-100**: #f5f5f5 (Subtle backgrounds)
-   **Neutral-600**: #525252 (Secondary text)
-   **Neutral-900**: #171717 (Primary text)

## Typography

### Font Families

-   **Primary**: Inter (Sans-serif) - For UI elements and body text
-   **Display**: DM Serif Display (Serif) - For headings and emphasis

### Font Scales

-   **Display**: 48px/56px - Hero headings
-   **Headline**: 36px/44px - Page headings
-   **Subheadline**: 24px/32px - Section headings
-   **Body Large**: 18px/28px - Important body text
-   **Body**: 16px/24px - Default body text
-   **Body Small**: 14px/20px - Secondary text
-   **Caption**: 12px/16px - Captions and labels

## Component System

### 1. Button Component

**File**: `resources/js/Components/Button.vue`

**Variants**:

-   `primary` - Main actions
-   `secondary` - Secondary actions
-   `accent` - Accent actions
-   `warning` - Warning actions
-   `danger` - Destructive actions
-   `ghost` - Subtle actions
-   `outline` - Outlined actions

**Sizes**:

-   `xs` - Extra small
-   `sm` - Small
-   `md` - Medium (default)
-   `lg` - Large
-   `xl` - Extra large

**Usage**:

```vue
<Button variant="primary" size="md" :icon="PlusIcon">
    Add Property
</Button>
```

### 2. Card Component

**File**: `resources/js/Components/Card.vue`

**Variants**:

-   `default` - Standard card with shadow
-   `elevated` - Enhanced shadow
-   `flat` - No shadow
-   `outlined` - Border only
-   `gradient` - Gradient background

**Usage**:

```vue
<Card variant="default" title="Property Details">
    <template #headerActions>
        <Button variant="ghost" size="sm">Edit</Button>
    </template>
    
    Content goes here
    
    <template #footer>
        <Button variant="primary">Save</Button>
    </template>
</Card>
```

### 3. Input Component

**File**: `resources/js/Components/Input.vue`

**Features**:

-   Built-in validation states
-   Icon support (left/right)
-   Clear button
-   Help text
-   Error/success messages

**Usage**:

```vue
<Input
    v-model="email"
    type="email"
    label="Email Address"
    placeholder="Enter your email"
    :left-icon="EnvelopeIcon"
    :required="true"
    help-text="We'll never share your email"
/>
```

### 4. Badge Component

**File**: `resources/js/Components/Badge.vue`

**Variants**:

-   `default`, `primary`, `secondary`
-   `success`, `warning`, `error`, `info`
-   `accent`

**Styles**:

-   `solid` - Filled background
-   `outline` - Border only
-   `soft` - Light background

**Usage**:

```vue
<Badge variant="success" style="solid" :icon="CheckIcon">
    Active
</Badge>
```

## Layout System

### Grid System

-   **Mobile**: 1 column
-   **Tablet**: 2 columns
-   **Desktop**: 3-4 columns
-   **Large Desktop**: 4+ columns

### Spacing Scale

-   `xs`: 4px
-   `sm`: 8px
-   `md`: 16px
-   `lg`: 24px
-   `xl`: 32px
-   `2xl`: 48px
-   `3xl`: 64px

### Container Sizes

-   **Small**: 640px
-   **Medium**: 768px
-   **Large**: 1024px
-   **Extra Large**: 1280px

## Shadows and Elevation

### Shadow Levels

-   `shadow-card`: Subtle card shadow
-   `shadow-card-hover`: Enhanced hover shadow
-   `shadow-professional-md`: Professional medium shadow
-   `shadow-professional-lg`: Professional large shadow

## Animation and Transitions

### Transition Durations

-   **Fast**: 150ms - Micro-interactions
-   **Normal**: 200ms - Standard transitions
-   **Slow**: 300ms - Complex animations

### Easing Functions

-   `ease-in-out` - Standard easing
-   `ease-out` - Entrances
-   `ease-in` - Exits

## Responsive Design

### Breakpoints

-   **Mobile**: < 640px
-   **Tablet**: 640px - 1024px
-   **Desktop**: 1024px - 1280px
-   **Large Desktop**: > 1280px

### Mobile-First Approach

-   Design for mobile first
-   Progressive enhancement for larger screens
-   Touch-friendly interface elements

## Accessibility Guidelines

### Color Contrast

-   Minimum 4.5:1 for normal text
-   Minimum 3:1 for large text
-   Minimum 3:1 for UI components

### Focus States

-   Visible focus indicators
-   Consistent focus styling
-   Logical tab order

### Screen Readers

-   Semantic HTML structure
-   ARIA labels where needed
-   Descriptive alt text for images

## Implementation Guidelines

### 1. Component Usage

-   Always use the unified components
-   Avoid creating custom styles that bypass the design system
-   Use Tailwind utility classes for spacing and layout

### 2. Consistency Checks

-   Ensure all buttons use the Button component
-   Verify all cards use the Card component
-   Check input fields use the Input component

### 3. Color Usage

-   Use semantic color names (primary, success, error)
-   Avoid hardcoded color values
-   Test with different color schemes

### 4. Typography

-   Use the defined font scales
-   Maintain consistent line heights
-   Ensure proper font weights

## Migration Strategy

### Phase 1: Core Components

1. ✅ Create unified Button component
2. ✅ Create unified Card component
3. ✅ Create unified Input component
4. ✅ Create unified Badge component

### Phase 2: Layout Components

1. Update all layouts to use ModernDashboardLayout
2. Standardize page headers and navigation
3. Implement consistent spacing

### Phase 3: Page Updates

1. Update all pages to use unified components
2. Remove old component files
3. Test for consistency

### Phase 4: Polish

1. Add micro-interactions
2. Optimize animations
3. Final accessibility audit

## Best Practices

### Do's

-   ✅ Use semantic component names
-   ✅ Follow the established color palette
-   ✅ Maintain consistent spacing
-   ✅ Test on multiple screen sizes
-   ✅ Ensure accessibility compliance

### Don'ts

-   ❌ Create one-off component styles
-   ❌ Use hardcoded colors
-   ❌ Ignore responsive design
-   ❌ Skip accessibility testing
-   ❌ Mix different design patterns

## Resources

### Design Tokens

-   Colors: Defined in `resources/css/design-system.css`
-   Typography: Defined in `resources/css/app.css`
-   Spacing: Tailwind's default scale
-   Shadows: Custom shadow utilities

### Component Documentation

-   Each component includes comprehensive props documentation
-   Examples provided in component files
-   TypeScript definitions for better IDE support

### Testing

-   Visual regression testing for components
-   Accessibility testing with axe-core
-   Cross-browser compatibility testing
-   Mobile device testing
