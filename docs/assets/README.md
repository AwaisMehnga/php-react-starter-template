# Documentation Assets

This directory contains shared JavaScript files for the PHP React MVC Template documentation.

## sidebar.js

A dynamic sidebar generation system that eliminates code redundancy across documentation pages.

### Features

- **Dynamic Navigation Sidebar**: Automatically generates navigation links with active state detection
- **Auto-generated Table of Contents**: Scans page headings and creates a scrollable TOC
- **Smooth Scrolling**: Animated scrolling to page sections
- **Scroll Spy**: Highlights current section in TOC while scrolling
- **Mobile Responsive**: Hamburger menu for mobile devices
- **Zero Configuration**: Just include the script tag and it works

### Usage

Simply add this script tag to any documentation page:

```html
<script src="../assets/sidebar.js"></script>
```

The script will:
1. Inject necessary CSS styles
2. Wrap page content in the sidebar layout
3. Generate navigation and TOC sidebars
4. Attach all event handlers

### How it Works

1. **CSS Injection**: Adds all sidebar styles dynamically
2. **Layout Creation**: Wraps existing body content in sidebar structure
3. **Navigation Generation**: Creates links based on predefined page list
4. **TOC Generation**: Scans for `h1[id], h2[id], h3[id], h4[id]` elements
5. **Event Binding**: Adds smooth scrolling, scroll spy, and mobile menu

### Benefits

- **Zero Code Duplication**: Single source of truth for sidebar functionality
- **Easy Maintenance**: Update navigation in one place
- **Automatic TOC**: No manual TOC creation needed
- **Consistent UX**: Same behavior across all pages
- **Performance**: Lightweight and efficient

### Browser Support

- Modern browsers with ES6+ support
- Graceful degradation for older browsers
- Mobile-first responsive design
