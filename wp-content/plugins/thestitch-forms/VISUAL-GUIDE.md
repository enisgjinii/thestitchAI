# 🎯 ADDING FORMS TO YOUR PAGES - VISUAL GUIDE

## STEP 1: Activate the Plugin

```
WordPress Dashboard
  ↓
Plugins
  ↓
Installed Plugins
  ↓
Find "The Stitch Custom Forms"
  ↓
Click "Activate" Button
  ↓
✅ Done! Plugin is now active
```

**What you'll see after activation:**
- "Form Submissions" appears in admin sidebar
- "How to Use Forms" link appears under Form Submissions
- Plugin is ready to use

---

## STEP 2: Copy the Shortcodes

You have TWO forms ready to use:

### Form 1 - Bridal Consultation
```
[bridal_consultation_form]
```
👉 Copy and paste this into your pages

### Form 2 - Dream Outfit Submission
```
[dream_outfit_form]
```
👉 Copy and paste this into your pages

---

## STEP 3: Add Forms to Pages

### OPTION A: Gutenberg Block Editor (Standard WordPress)

```
1. Go to: Pages → Edit Page
   
2. Click: + Button (top left)
   
3. Search: "Custom HTML"
   
4. Drag: "Custom HTML" block where you want the form
   
5. Click: The HTML block you just added
   
6. Paste: [bridal_consultation_form]
   
7. Click: Update or Publish
   
✅ Form now appears on your page!
```

**Visual Layout:**
```
┌─────────────────────────────────┐
│  Page Title                     │
├─────────────────────────────────┤
│  [Your existing content here]   │
├─────────────────────────────────┤
│  [FORM WILL APPEAR HERE]        │
│  ┌───────────────────────────┐  │
│  │ Bridal Consultation       │  │
│  │ Full Name: [_________]    │  │
│  │ Email: [_____________]    │  │
│  │ [Submit Button]           │  │
│  └───────────────────────────┘  │
├─────────────────────────────────┤
│  [Your footer content]          │
└─────────────────────────────────┘
```

---

### OPTION B: Elementor Page Builder

```
1. Open: Page in Elementor editor
   
2. Find: Elementor toolbar (left side)
   
3. Search: "HTML" widget
   
4. Drag: HTML widget to desired location
   
5. Click: The HTML widget to select it
   
6. Find: "HTML Code" field in right panel
   
7. Paste: [dream_outfit_form]
   
8. Click: "Save & Publish" button
   
✅ Form now appears on your page with Elementor design!
```

**Where to drag widget:**
```
Elementor Canvas
┌──────────────────────────────┐
│  Section 1                   │
│  ┌────────────────────────┐  │
│  │  Existing Text/Images  │  │
│  └────────────────────────┘  │
└──────────────────────────────┘

  ↓ Click + to add new section

┌──────────────────────────────┐
│  Section 2                   │
│  ┌────────────────────────┐  │
│  │  [DRAG HTML HERE ↓]    │  │
│  │  [Paste shortcode]     │  │
│  └────────────────────────┘  │
└──────────────────────────────┘
```

---

### OPTION C: Divi Page Builder

```
1. Open: Page with Divi builder
   
2. Click: "+" to add module
   
3. Search: "Text" module
   
4. Drag: Text module to page
   
5. Click: Edit module
   
6. Your content editor appears
   
7. Paste: [bridal_consultation_form]
   
8. Click: "Save & Publish"
   
✅ Form appears with Divi styling!
```

---

### OPTION D: WPBakery / Visual Composer

```
1. Edit: Page with VC
   
2. Click: "Add Element"
   
3. Search: "Raw HTML"
   
4. Click: Raw HTML element
   
5. Click: Edit element
   
6. Paste: [dream_outfit_form]
   
7. Click: "Save"
   
✅ Form is now on your page!
```

---

### OPTION E: Text/Code Editor

```
1. Edit: Any page
   
2. Click: Switch to "Text" or "Code" mode
   
3. Place cursor: Where you want the form
   
4. Paste: [bridal_consultation_form]
   
5. Click: Update/Publish
   
✅ Form appears in that location!
```

**Example:**
```html
<h1>Contact Us</h1>
<p>Have questions? Send us a message below!</p>

[bridal_consultation_form]

<p>We'll get back to you within 24 hours.</p>
```

---

## STEP 4: Recommended Placements

### Homepage Hero Section
```
HOMEPAGE
┌──────────────────────────────┐
│  "WELCOME TO THE STITCH"     │
│  [Hero background image]     │
│  [Logo]                      │
│  BUTTON: "Book Now" ←────┐   │
└──────────────────────────┤───┘
                           ↓
                    [PLACE FORM HERE
                     when clicked]
```

### Bridal Services Page
```
BRIDAL SERVICES
┌──────────────────────────────┐
│  Our Bridal Collection       │
├──────────────────────────────┤
│  [Images of dresses]         │
│  Service description...      │
│  [FORM BELOW]                │
│  ┌────────────────────────┐  │
│  │ [bridal_consultation]  │  │
│  └────────────────────────┘  │
└──────────────────────────────┘
```

### Custom Design Page
```
CUSTOM DESIGN
┌──────────────────────────────┐
│  Design Your Dream Outfit    │
│  [Feature images]            │
│  How it works...             │
│  [START FORM BUTTON]         │
│  ┌────────────────────────┐  │
│  │ [dream_outfit_form]    │  │
│  │ Step 1: Upload images  │  │
│  │ Step 2: Add details    │  │
│  │ Step 3: Select sizing  │  │
│  └────────────────────────┘  │
│  What happens next...        │
└──────────────────────────────┘
```

### Contact Page
```
CONTACT US
┌──────────────────────────────┐
│  Get in Touch                │
│  Email: info@thestitch.com   │
│  Phone: (555) 123-4567       │
│                              │
│  OR Book a Consultation:     │
│  ┌────────────────────────┐  │
│  │ [bridal_consultation]  │  │
│  └────────────────────────┘  │
└──────────────────────────────┘
```

---

## STEP 5: Test Your Forms

### Testing Checklist:

```
DESKTOP:
□ Does form display correctly?
□ Can you fill all fields?
□ Does submit button work?
□ Do you get success message?
□ Is styling aligned properly?

MOBILE:
□ Does form fit screen width?
□ Are buttons easy to tap?
□ Can you see all fields?
□ Does form scroll smoothly?
□ Are file uploads working?

FUNCTIONALITY:
□ Try submitting the bridal form
□ Check for email notification
□ Login to admin, go to Form Submissions
□ Do you see your test submission?
□ Try dream outfit form if applicable
□ Check file uploads work
```

---

## STEP 6: View Submissions

```
WordPress Dashboard
  ↓
Left Sidebar → "Form Submissions"
  ↓
See all customer submissions
  ↓
Click any submission to see:
  • Customer name & email
  • Contact details
  • Preferred dates
  • Wedding date
  • Uploaded images (if dream outfit)
  • Body measurements (if dream outfit)
  • All notes and messages
```

---

## QUICK REFERENCE: WHERE TO PASTE SHORTCODES

| Page Type | Best Form | Shortcode |
|-----------|-----------|-----------|
| **Homepage** | Bridal | `[bridal_consultation_form]` |
| **Bridal Services** | Bridal | `[bridal_consultation_form]` |
| **Custom Design** | Dream Outfit | `[dream_outfit_form]` |
| **Contact** | Bridal | `[bridal_consultation_form]` |
| **Services** | Both | Use both |
| **About** | Bridal (optional) | `[bridal_consultation_form]` |

---

## COMMON QUESTIONS

**Q: Can I use both forms on same page?**
A: Yes! Just paste both shortcodes:
```
[bridal_consultation_form]

[dream_outfit_form]
```

**Q: Will forms match my theme styling?**
A: Yes! Forms have professional styling that works with any WordPress theme. They're responsive and mobile-friendly.

**Q: Can I customize the form colors?**
A: Yes! Go to Appearance → Customize → Additional CSS and add your custom styles. (Advanced customization available upon request)

**Q: What happens when someone submits?**
A: You receive an email notification AND the submission is saved in WordPress admin under "Form Submissions"

**Q: Can users upload multiple images?**
A: Yes! Both forms support multiple file uploads. Users can upload as many images as needed (max 5MB each).

---

## TROUBLESHOOTING

**Problem: Form not showing**
- Check: Is shortcode exactly `[bridal_consultation_form]`?
- Check: Did you paste in right place?
- Check: Is plugin activated?

**Problem: Form shows broken**
- Clear your browser cache (Ctrl+Shift+Del)
- Try a different browser
- Check plugin is activated

**Problem: Submissions not saving**
- Enable Debug mode or contact hosting support
- Verify write permissions on `/wp-content/uploads/`

**Problem: No email received**
- Check: Admin email in Settings → General
- Check: Spam folder
- Check: Submission exists in Form Submissions admin menu

---

## YOU'RE ALL SET! 🎉

Your forms are now:
✅ Installed
✅ Activated
✅ Ready to display

**Next step:** Add the shortcodes to your pages using one of the methods above!

**Questions?** Check the "How to Use Forms" page in WordPress admin:
```
Dashboard → Form Submissions → How to Use
```

Your customers can now easily:
📅 Book consultations
📸 Upload dream outfit images
📋 Submit custom design requests
💌 Request consultations

Let your brand shine! 🎀✨
