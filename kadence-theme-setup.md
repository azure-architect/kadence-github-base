# Kadence Theme Setup Guide

This guide provides instructions for setting up the Kadence theme for client websites using our standard configuration.

## Required Plugins

Before starting the Kadence setup, ensure these plugins are installed and activated:

1. **Kadence Blocks** - Extends Gutenberg with additional blocks
2. **Kadence Starter Templates** - Provides pre-built templates
3. **Kadence Pro** (optional) - Adds premium features to the theme

## Theme Setup Steps

### 1. Import Starter Template

1. Go to **Appearance → Kadence → Starter Templates**
2. Browse available templates or search for a specific industry
3. For our standard client setup, we recommend:
   - **Business Pro** for service businesses
   - **WooCommerce Pro** for e-commerce sites
   - **Professional Services** for corporate/professional clients
4. Click on your chosen template, then select **Import Complete Template**
5. Select the following import options:
   - ✅ **Import Customizer Settings**
   - ✅ **Import Content**
   - ✅ **Import Plugins**
   - ❌ **Import Performance Settings** (we'll set these manually)
6. Click **Import** and wait for the process to complete

### 2. Theme Customization

After importing the template, customize the theme settings:

1. Go to **Appearance → Customize**

2. **Site Identity**

   - Upload client logo (recommended size: 240px × 80px)
   - Set site title and tagline
   - Upload favicon (site icon)

3. **Colors**

   - Primary Color: Client's main brand color
   - Secondary Color: Client's secondary brand color
   - Background Color: Usually white (#FFFFFF)
   - Link Color: Usually primary or secondary color

4. **Typography**

   - Headings: Use client's brand font or our default (Montserrat)
   - Body: Use a readable font (Open Sans, Roboto, or Source Sans Pro)
   - Set base font size to 16px or 18px

5. **Header**

   - Layout: Usually "Logo Left, Navigation Right"
   - Sticky: Enable for desktop, disable for mobile
   - Transparent: Only enable for specific designs with hero images
   - Mobile Navigation: Set to "Popup Drawer"

6. **Footer**
   - Update copyright text to include client name and current year
   - Add client contact information
   - Set columns based on content needs (usually 3 or 4)

### 3. Kadence Blocks Setup

Configure the default settings for Kadence Blocks:

1. Go to **Kadence Blocks → Settings**

2. Set default colors to match client brand colors:

   - Add client's brand colors to the color palette
   - Set default button styles
   - Configure default spacing values

3. Create reusable blocks for common elements:
   - Call-to-action sections
   - Contact information
   - Team member layouts
   - Testimonial displays

### 4. Page Templates

Set up standard pages with consistent layouts:

1. **Homepage**

   - Hero section with primary CTA
   - Features/benefits section
   - About/intro section
   - Testimonials
   - Call-to-action

2. **About Page**

   - Company history/mission
   - Team section
   - Values/approach

3. **Services/Products Page**

   - Overview section
   - Individual service/product listings
   - Benefits
   - Testimonials specific to services

4. **Contact Page**
   - Contact form
   - Map
   - Hours of operation
   - Additional contact methods

### 5. Performance Optimization

1. Go to **Kadence → Performance**

2. Enable the following optimizations:

   - ✅ Remove Global Styles
   - ✅ Preload Featured Images
   - ✅ Include Scroll To ID JS
   - ✅ Load Scripts Conditionally
   - ✅ Swap Google Fonts Display

3. CSS Optimization:

   - Set "CSS Print Method" to "Separate Files"

4. For caching, we recommend using a separate caching plugin (e.g., WP Rocket, LiteSpeed Cache)

### 6. Mobile Responsiveness

1. Test the site on multiple device sizes using browser developer tools
2. Adjust any layouts that don't look optimal on mobile
3. Check and optimize:
   - Font sizes on mobile
   - Button sizes
   - Spacing/padding
   - Image sizes and positioning

## Common Kadence Settings for Clients

| Setting                | Recommended Value | Notes                           |
| ---------------------- | ----------------- | ------------------------------- |
| Container Width        | 1290px            | Good balance for most sites     |
| Content Width          | 1200px            | For readable content areas      |
| Sidebar Width          | 30%               | When using sidebars             |
| Mobile Menu Breakpoint | 1024px            | Works well for most sites       |
| H1 Font Size           | 48px/36px/32px    | Desktop/tablet/mobile           |
| Body Font Size         | 18px/16px/16px    | Desktop/tablet/mobile           |
| Button Style           | Rounded (5px)     | Modern, clean look              |
| Header Height          | 90px              | Good balance of space           |
| Sticky Header Height   | 70px              | Slightly smaller when scrolling |

## Troubleshooting

### Common Issues and Solutions

1. **Template Import Fails**

   - Increase PHP memory limit in wp-config.php
   - Check for plugin conflicts
   - Import pieces separately (customizer settings first, then content)

2. **CSS/JS Not Loading**

   - Check for caching plugin issues
   - Purge all caches
   - Verify Kadence Performance settings

3. **Layout Issues**
   - Check for custom CSS that might conflict
   - Ensure container settings are appropriate
   - Verify block margin/padding settings

## Kadence Pro Features

If using Kadence Pro, these features are particularly useful:

1. **Header Addons** - For sticky headers and transparent headers
2. **Hooked Elements** - For adding custom content to specific areas
3. **Custom Fonts** - For loading client brand fonts
4. **Mega Menu** - For complex navigation structures
5. **WooCommerce Addons** - For enhanced product pages

Remember to document any custom configurations made for the specific client for future reference.
