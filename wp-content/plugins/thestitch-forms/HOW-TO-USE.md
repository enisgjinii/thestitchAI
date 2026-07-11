# The Stitch - Forms How-To Guide

> A simple, step-by-step guide for managing your website forms.
> No coding required. Just follow the steps.

---

## Table of Contents

1. [What Are These Forms?](#1-what-are-these-forms)
2. [Where Are the Forms on My Website?](#2-where-are-the-forms-on-my-website)
3. [How to View Form Submissions](#3-how-to-view-form-submissions)
4. [How to Customize the Forms (Colors, Text, Size)](#4-how-to-customize-the-forms)
5. [How to Change Email Notifications](#5-how-to-change-email-notifications)
6. [How to Add a Form to a New Page](#6-how-to-add-a-form-to-a-new-page)
7. [Form Fields Explained](#7-form-fields-explained)
8. [Troubleshooting](#8-troubleshooting)

---

## 1. What Are These Forms?

Your website has **two forms**:

### Form 1: Recreate Form
- For customers who want you to **recreate an outfit** they love
- They upload photos, add notes, choose their size, and submit
- Lives on the **Recreate** page (`/recreate/`)
- Shortcode: `[recreate_form]`

### Form 2: Bridal Consultation Form
- For brides who want to **book a consultation**
- They fill in their details, pick a date, and submit
- Lives on the **Bridal** page (`/bridal/`)
- Shortcode: `[bridal_consultation_form]`

---

## 2. Where Are the Forms on My Website?

| Page | URL | What's on it |
|------|-----|-------------|
| **Recreate** | `yourdomain.com/recreate/` | Recreate Form |
| **Bridal** | `yourdomain.com/bridal/` | Bridal Consultation Form |

Both forms are already placed and working on these pages. You don't need to do anything to activate them.

---

## 3. How to View Form Submissions

When someone fills out a form, it gets saved in your WordPress admin.

### Step-by-step:

1. **Log in** to your WordPress admin (`yourdomain.com/wp-admin`)
2. Look at the **left sidebar menu**
3. Click **"Form Submissions"** (the email icon)
4. You'll see a list of all submissions

### What you'll see in the list:

| Column | Meaning |
|--------|---------|
| **Subject** | The title (e.g., "Bridal Consultation: John Smith") |
| **Type** | Either "Bridal Consultation" or "Recreate" |
| **Email** | The customer's email address |
| **Phone** | The customer's phone number (Bridal form only) |
| **Date** | When they submitted the form |

### To view full details:

1. Click on **any submission** in the list
2. Scroll down to see the **"Submission Details"** box
3. You'll see all the information they entered:
   - Email, phone, dates (Bridal)
   - Sizing type, measurements, notes, uploaded images (Recreate)

### To view uploaded images (Recreate form):

1. Open a Recreate submission
2. Look for **"Uploaded Images"** in the details
3. Click on any image thumbnail to open it full size

---

## 4. How to Customize the Forms

You can change how your forms look **without touching any code**.

### Getting there:

1. Log in to WordPress admin
2. Click **"Form Submissions"** in the left sidebar
3. Click **"Customize"** underneath it
4. You'll see the customization panel with **4 tabs**

---

### Tab 1: Colors & Styling

Change the colors to match your brand.

| Setting | What it changes | Default |
|---------|----------------|---------|
| Primary Button Color | The submit/next button color | Brown `#8b7355` |
| Button Hover Color | Button color when mouse hovers over it | Dark Brown `#6d5a47` |
| Input Border Color | Border around text fields | Light Gray `#e0e0e0` |
| Input Focus Color | Border color when you click a field | Brown `#8b7355` |
| Success Message Color | Green "success" message | Green `#4caf50` |
| Error Message Color | Red "error" message | Red `#f44336` |
| Form Background Color | Background of the form box | White `#ffffff` |
| Text Color | All text in the form | Dark Gray `#333333` |

**How to change a color:**
1. Click the **color box** next to any setting
2. A color picker will pop up
3. Pick your color or type a hex code (like `#FF5733`)
4. Click **"Save All Settings"** at the bottom

---

### Tab 2: Branding

Change the size, shape, and spacing of your forms.

| Setting | What it changes | Default |
|---------|----------------|---------|
| Form Container Width | How wide the form is (in pixels) | 600px |
| Border Radius | How rounded the corners are | 8px |
| Button Border Radius | How rounded the buttons are | 8px |
| Form Padding | Space inside the form border | 30px |
| Button Text | The default text on submit buttons | "Submit" |
| Enable Form Shadow | Drop shadow behind the form | Yes |
| Custom CSS | Advanced styling (leave empty if unsure) | Empty |

---

### Tab 3: Labels & Text

Change the words on your forms.

**Bridal Consultation Form:**

| Setting | What it changes | Default |
|---------|----------------|---------|
| Form Title | The big heading at the top | "Bridal Consultation" |
| Full Name Placeholder | Hint text in the name field | "Full Name" |
| Email Placeholder | Hint text in the email field | "Email Address" |
| Submit Button Text | Text on the submit button | "Request Consultation" |

**Recreate Form:**

| Setting | What it changes | Default |
|---------|----------------|---------|
| Form Title | The big heading at the top | "Recreate Form" |
| Submit Button Text | Text on the submit button | "Send My Recreate Request" |

---

### Tab 4: Email Settings

Control what happens when someone submits a form.

| Setting | What it changes | Default |
|---------|----------------|---------|
| Recipient Email Address | Where you get notified | Your admin email |
| Email Subject (Bridal) | Subject line for bridal emails | "New Bridal Consultation Request" |
| Email Subject (Recreate) | Subject line for recreate emails | "New Recreate Form Submission" |
| Send confirmation to customer | Auto-reply to the customer | Yes |
| Customer Email Message | What the auto-reply says | "Thank you for your submission!..." |

**Don't forget to click "Save All Settings" after making changes!**

---

## 5. How to Change Email Notifications

### To change where notifications are sent:

1. Go to **Form Submissions → Customize**
2. Click the **"Email Settings"** tab
3. Change the **"Recipient Email Address"** to your preferred email
4. Click **"Save All Settings"**

### To turn off customer auto-replies:

1. Go to **Email Settings** tab
2. Set **"Send confirmation to customer"** to **"No"**
3. Save

### To customize the auto-reply message:

1. Go to **Email Settings** tab
2. Edit the **"Customer Email Message"** text
3. Save

---

## 6. How to Add a Form to a New Page

If you want a form on a different page, here's how:

### Using the WordPress Block Editor:

1. Go to **Pages** in the left sidebar
2. Click **"Add New"** or edit an existing page
3. Click the **+** button to add a block
4. Search for **"Shortcode"**
5. Add one of these shortcodes:

```
[recreate_form]
```
or
```
[bridal_consultation_form]
```

6. Click **"Publish"** or **"Update"**

### Using Elementor:

1. Open the page in Elementor
2. Search for **"Shortcode"** in the sidebar widgets
3. Drag it to where you want the form
4. Type the shortcode in the box:
   - `[recreate_form]` for the Recreate form
   - `[bridal_consultation_form]` for the Bridal form
5. Click **"Update"**

---

## 7. Form Fields Explained

### Recreate Form (3 Steps)

**Step 1: Show Us The Look**
| Field | Required? | What to enter |
|-------|-----------|--------------|
| Inspiration Images | Yes | Upload 1 or more photos of the outfit to recreate |

**Step 2: Add Your Flair**
| Field | Required? | What to enter |
|-------|-----------|--------------|
| Extra Reference Images | No | Additional angles or close-up photos |
| Your Notes | No | Special requests ("add longer sleeves", "make it emerald green") |
| Color/Pattern References | No | Color swatches or pattern images |

**Step 3: Contact & Fit**
| Field | Required? | What to enter |
|-------|-----------|--------------|
| Email Address | Yes | Customer's email |
| Sizing Type | Yes | "Standard Size" or "Custom Measurements" |
| Standard Size (if chosen) | Yes | XS, S, M, L, XL, 2XL-5XL |
| Bust, Waist, Hips (if custom) | Yes | Measurements in inches |

### Bridal Consultation Form

| Field | Required? | What to enter |
|-------|-----------|--------------|
| Full Name | Yes | Customer's full name |
| Country Code | Yes | e.g., +1, +44, +91 |
| Mobile Number | Yes | Phone number |
| Email Address | Yes | Email |
| Message / Inquiry | No | Any message |
| Preferred Date | Yes | When they want the consultation |
| Preferred Time | Yes | What time |
| Wedding Date | No | When is the wedding |

### File Upload Rules

- **Accepted formats:** JPG, PNG, GIF, WebP
- **Max size:** 5MB per image
- **Multiple files:** Yes, you can upload more than one at a time

---

## 8. Troubleshooting

### Form not showing on the page?

1. Make sure the plugin is active: **Plugins → find "The Stitch Custom Forms" → should say "Active"**
2. Clear your browser cache: **Ctrl+Shift+Delete** (Windows) or **Cmd+Shift+Delete** (Mac)
3. Check if the shortcode is on the page: edit the page and look for `[recreate_form]` or `[bridal_consultation_form]`

### Not receiving email notifications?

1. Check your **spam/junk folder**
2. Go to **Form Submissions → Customize → Email Settings** and verify the recipient email
3. Make sure your hosting provider allows sending emails from WordPress

### Form submissions not appearing in admin?

1. Go to **Form Submissions** in the left sidebar
2. Check if the submissions are in the **Trash** (click "Trash" at the top)
3. Try submitting a test form yourself

### Changes not showing on the website?

1. Make sure you clicked **"Save All Settings"**
2. **Hard refresh** the page: Ctrl+F5 (Windows) or Cmd+Shift+R (Mac)
3. Clear any caching plugin: go to **LiteSpeed Cache → Toolbox → Purge All**

### Images not uploading?

1. Check the file is under **5MB**
2. Make sure it's an image file (JPG, PNG, GIF, or WebP)
3. Check that `/wp-content/uploads/` has write permissions

### Form looks broken or weird?

1. Check if there's **Custom CSS** in **Branding** tab that might be conflicting
2. Try resetting colors to defaults in the **Colors & Styling** tab
3. Test on a different browser

---

## Quick Reference

### Shortcodes

| Form | Shortcode |
|------|-----------|
| Recreate Form | `[recreate_form]` |
| Bridal Consultation Form | `[bridal_consultation_form]` |
| Recreate Form (old) | `[dream_outfit_form]` (still works) |

### Admin Pages

| Page | Path |
|------|------|
| View Submissions | Form Submissions (left sidebar) |
| Customize Forms | Form Submissions → Customize |
| Help Page | Form Submissions → How to Use |

### Key URLs

| Page | URL |
|------|-----|
| Recreate Page | `yourdomain.com/recreate/` |
| Bridal Page | `yourdomain.com/bridal/` |
| Admin Login | `yourdomain.com/wp-admin/` |

---

*Last updated: March 2026*
*Plugin: The Stitch Custom Forms v1.0.0*
