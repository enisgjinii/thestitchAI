# The Stitch Custom Forms Plugin

A powerful WordPress plugin that provides two professionally designed, responsive forms for The Stitch bridal business.

## Features

✨ **Form 1: Bridal Consultation Form**
- Clean, elegant design perfect for booking consultations
- Fields: Full Name, Mobile Number, Email, Message, Preferred Date/Time, Wedding Date
- Automatic email notifications to admin
- Data saved to WordPress custom post type

✨ **Form 2: Dream Outfit Submission Form**
- Multi-step interactive form with smooth navigation
- Step 1: Upload dream outfit images
- Step 2: Upload reference images, add notes, upload color/pattern references
- Step 3: Select sizing (Standard XS-5XL or Custom measurements)
- File preview with image gallery
- Advanced validation and error handling

💾 **Data Management**
- All submissions stored in WordPress admin (Form Submissions)
- Email notifications for each submission
- Custom metadata for easy filtering and review

📱 **Responsive Design**
- Mobile-first design
- Optimized for all screen sizes
- Smooth animations and transitions
- Professional branding colors

## Installation

1. **Upload the plugin** to `/wp-content/plugins/thestitch-forms/`
2. **Activate** the plugin from WordPress Admin → Plugins
3. You'll see "Form Submissions" in the admin menu for managing submissions

## Usage

### Adding Forms to Your Website

#### Option 1: Using Shortcodes in Pages/Posts

**For Bridal Consultation Form:**
```
[bridal_consultation_form]
```

**For Dream Outfit Submission Form:**
```
[dream_outfit_form]
```

#### Option 2: Using Elementor or Page Builders

1. Open any page in your editor
2. Add a **Custom HTML** or **Code** block
3. Paste the shortcode:
   ```
   [bridal_consultation_form]
   ```
   or
   ```
   [dream_outfit_form]
   ```

#### Option 3: Recommended Placements

**Bridal Consultation Form:**
- Homepage hero section
- Bridal Services page
- Contact page
- Navigation footer

**Dream Outfit Submission Form:**
- Custom Design/Consultation page
- Services page
- Dedicated custom design landing page

### Where to Add Forms

1. **Log in to WordPress Admin**
2. Navigate to **Pages** or **Posts**
3. Edit the page where you want the form
4. Click where you want to add the form
5. Paste the shortcode: `[bridal_consultation_form]` or `[dream_outfit_form]`
6. Update/Publish the page

## Managing Submissions

### Viewing Form Submissions

1. Go to **WordPress Admin → Form Submissions**
2. View all customer submissions
3. Click on any submission to see full details
4. View attached files and metadata

### Email Notifications

- Admins receive email notifications for every new submission
- Email sent to the WordPress site admin email address
- Can be customized by editing the plugin code

## Form Fields & Requirements

### Bridal Consultation Form
- **Full Name** (Required)
- **Country Code** (Required) - e.g., +1, +44
- **Mobile Number** (Required)
- **Email** (Required)
- **Message/Inquiry** (Optional)
- **Preferred Date** (Required)
- **Preferred Time** (Required)
- **Wedding Date** (Optional)

### Dream Outfit Submission Form

**Step 1:**
- Dream outfit images (Required, multiple uploads)

**Step 2:**
- Reference images (Optional, multiple uploads)
- Notes/Description (Optional, text)
- Color/Pattern reference images (Optional, multiple uploads)

**Step 3:**
- Email (Required)
- Sizing Type (Required):
  - **Standard Size:** XS, S, M, L, XL, 2XL, 3XL, 4XL, 5XL
  - **Custom Measurements:** Bust, Waist, Hips (in inches)

## File Uploads

- **Supported formats:** JPG, PNG, GIF, WebP
- **Max file size:** 5MB per image
- **Multiple uploads:** Yes
- **Storage location:** `/wp-content/uploads/thestitch-forms/`

## Styling & Customization

### Colors
The forms use a professional color scheme:
- Primary: `#8b7355` (Brown)
- Hover: `#6d5a47` (Darker Brown)
- Success: `#4caf50` (Green)
- Error: `#f44336` (Red)

### Custom CSS
To customize the styling, add CSS to your theme's `custom.css` or **Appearance → Customize → Additional CSS**:

```css
.thestitch-form-container {
    /* Your custom styles */
}

.btn-submit {
    /* Button styling */
}
```

## Troubleshooting

### Forms not displaying
- Ensure the plugin is activated
- Check shortcode spelling: `[bridal_consultation_form]` or `[dream_outfit_form]`
- Clear browser cache and WordPress cache

### Form submissions not working
- Check WordPress AJAX is enabled
- Verify admin email is set in Settings → General
- Check browser console for JavaScript errors (F12 → Console)

### File uploads not working
- Ensure `/wp-content/uploads/` directory has write permissions
- Check max upload size in Settings → Media
- Verify file size is under 5MB

### Submissions not appearing in admin
- Check "Form Submissions" in WordPress admin menu
- Refresh the page
- Check if submissions are in spam/trash

## Support

For issues or customization needs, contact the developer.

## Changelog

### Version 1.0.0
- Initial release
- Bridal Consultation Form
- Dream Outfit Multi-Step Form
- File upload support
- Email notifications
- Responsive design
- Mobile optimization
