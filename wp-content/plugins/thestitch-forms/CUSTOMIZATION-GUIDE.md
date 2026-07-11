# 🎨 Form Customization Panel - Complete Guide

## Overview

The advanced customization panel lets you fully control how your forms look and feel without touching any code. Customize colors to match your brand, change all text labels, adjust sizing and spacing, and manage email notifications—all from the WordPress admin dashboard.

## Accessing the Customization Panel

1. Go to **WordPress Admin Dashboard**
2. Navigate to **Form Submissions → Customize** in the left sidebar
3. You'll see 5 tabs: **Colors & Styling**, **Branding**, **Labels & Text**, **Email Settings**, and **Live Preview**

## Tab 1: Colors & Styling 🎨

Change all colors used in your forms to match your brand identity.

### Available Color Settings

| Setting | Purpose | Default |
|---------|---------|---------|
| **Primary Button Color** | Main submit/next button color | #8b7355 (Brown) |
| **Button Hover Color** | Button color when hovering over it | #6d5a47 (Dark Brown) |
| **Input Border Color** | Border color of form input fields | #e0e0e0 (Light Gray) |
| **Input Focus Color** | Border color when input is focused/clicked | #8b7355 (Brown) |
| **Success Message Color** | Color of success messages | #4caf50 (Green) |
| **Error Message Color** | Color of error messages | #f44336 (Red) |
| **Form Background Color** | Background of the entire form container | #ffffff (White) |
| **Text Color** | Text/font color throughout the form | #333333 (Dark Gray) |

### How to Change Colors

1. Click on any color field to open the **WordPress Color Picker**
2. Choose your color visually or enter a **hex color code** (e.g., #FF5733)
3. The **Live Preview** tab will update in real-time to show your changes
4. Click **"Save All Settings"** to apply changes to your live forms

### Color Picker Tips

- **Click the color preview box** to open an interactive color selector
- **Enter hex codes** directly (e.g., #8b7355)
- **Use common colors**: Red (#FF0000), Blue (#0000FF), Green (#00AA00), etc.
- **Test your brand colors** using online color pickers like Coolors.co

---

## Tab 2: Branding 🏢

Control the overall appearance, sizing, and spacing of your forms.

### Available Branding Settings

| Setting | Purpose | Default | Range |
|---------|---------|---------|-------|
| **Form Container Width (px)** | How wide the form displays | 600px | 300-1200px |
| **Border Radius (px)** | Roundness of form corners | 8px | 0-50px |
| **Button Border Radius (px)** | Roundness of buttons | 8px | 0-50px |
| **Form Padding (px)** | Internal spacing inside form | 30px | 0-100px |
| **Button Text** | Label on submit buttons | "Submit" | Any text |
| **Enable Form Shadow** | Drop shadow behind form | Yes | Yes/No |
| **Custom CSS** | Advanced CSS for power users | (empty) | Any valid CSS |

### Form Width

- **Narrow (300-400px)** — Mobile-friendly, compact design
- **Medium (500-700px)** — Balanced, recommended for most sites
- **Wide (800-1200px)** — Grand, spacious feel

### Border Radius Examples

- **0px** — Sharp, angular corners
- **8-12px** — Modern, slightly rounded
- **20-30px** — Organic, very rounded

### Button Text

The "Button Text" field changes the label on ALL submit/next buttons in your forms:
- Bridal form submit button
- Dream Outfit form next/submit buttons

**Examples:**
- "Submit Request"
- "Send Inquiry"
- "Get Started"
- "Apply Now"

### Custom CSS (Advanced)

Add custom CSS rules for complete styling control. Example:

```css
.thestitch-form-container {
    font-family: 'Georgia', serif;
    letter-spacing: 1px;
}

.btn-submit {
    font-size: 18px;
    font-weight: bold;
}
```

---

## Tab 3: Labels & Text 📝

Customize all text throughout your forms.

### Bridal Consultation Form

| Setting | Purpose | Default |
|---------|---------|---------|
| **Form Title** | Large heading at top of form | "Bridal Consultation" |
| **Full Name Placeholder** | Hint text in name field | "Full Name" |
| **Email Placeholder** | Hint text in email field | "Email Address" |
| **Submit Button Text** | Bridal form submit button | "Request Consultation" |

### Dream Outfit Form

| Setting | Purpose | Default |
|---------|---------|---------|
| **Form Title** | Large heading at top of form | "Dream Outfit Submission" |
| **Submit Button Text** | Dream outfit form submit button | "Submit Request" |

### Tips for Custom Labels

- Keep labels **short and clear** (under 50 characters)
- Use **action-oriented language**: "Send", "Submit", "Request", "Apply"
- Match your **brand voice** and tone
- Be **specific** about what the button does

**Example Custom Labels:**
- "Request My Consultation" instead of "Submit"
- "Upload My Dream Outfit" instead of "Next"
- "Send My Vision" instead of "Submit Request"

---

## Tab 4: Email Settings 📧

Configure how notifications are sent when forms are submitted.

### Available Settings

| Setting | Purpose | Default |
|---------|---------|---------|
| **Recipient Email Address** | Where form submissions are sent | Your admin email |
| **Email Subject (Bridal)** | Subject line for bridal form emails | "New Bridal Consultation Request" |
| **Email Subject (Dream Outfit)** | Subject line for dream outfit emails | "New Dream Outfit Submission" |
| **Send Customer Email** | Auto-reply to customers after submission | Yes |
| **Customer Message** | Auto-reply message content | "Thank you for your submission..." |

### Email Flow

1. **Customer submits form** via your website
2. **You receive notification email** with all their details
3. **Customer receives auto-reply** (if enabled) confirming receipt

### Customizing Recipient Email

- Make sure the email address is **valid and active**
- Check your **spam folder** if you don't receive emails
- Use **Gmail, Outlook, or your business email** for best delivery

### Auto-Reply Message Tips

- Keep it **professional but friendly**
- Include **expected response time** (e.g., "We'll respond within 24 hours")
- Add **contact information** if helpful
- Use **line breaks** for readability

**Example Auto-Reply:**
```
Thank you for reaching out! 

We've received your submission and appreciate your interest. 
Our team will review your information and get back to you 
within 24 business hours.

Best regards,
The Stitch Team
📞 (555) 123-4567
```

---

## Tab 5: Live Preview 👁️

See exactly how your forms will look with all your customizations applied.

### How Live Preview Works

- Shows a **real-time preview** of your Bridal Consultation form
- Updates **automatically** as you change colors, fonts, and text
- Displays **sample form fields** with your chosen styling
- Shows **hover effects** when you move your mouse over buttons

### Using the Preview

1. Make a change in any other tab
2. The preview updates **instantly** (no saving needed)
3. Switch to the **Preview tab** to see the full effect
4. Test **button hover** by moving your mouse over the button
5. Once satisfied, **click "Save All Settings"** to apply to live forms

### What the Preview Shows

- ✓ Custom colors applied
- ✓ Button text changes
- ✓ Form width and spacing
- ✓ Border radius effects
- ✓ Custom label text
- ✓ Font colors and styling

---

## Saving Your Changes

### Step-by-Step

1. Make your desired changes in any tab
2. Click the blue **"Save All Settings"** button at the bottom
3. Button will show **"✓ Saved!"** confirmation (2 seconds)
4. Changes apply **instantly** to your live website

### What Gets Saved

✓ All color choices  
✓ Form dimensions and spacing  
✓ All custom text labels  
✓ Email notification settings  
✓ Custom CSS code  

---

## Common Customizations

### Match Your Wedding/Brand Colors

1. Go to **Colors & Styling** tab
2. Use your **brand's primary color** for "Primary Button Color"
3. Use a **darker shade** for "Button Hover Color"
4. Preview changes in the **Live Preview** tab
5. Save when satisfied

### Create a Mobile-Friendly Form

1. Go to **Branding** tab
2. Set **Form Container Width** to 400px
3. Set **Button Border Radius** to 5px for modern look
4. Keep padding at 20-25px for mobile space
5. Test on mobile devices

### Match Your Site's Font Style

1. Go to **Branding** tab
2. Use **Custom CSS** section
3. Add: `font-family: 'Your Font Name', sans-serif;`
4. Adjust font-size if needed
5. Save and preview

### Luxury/Elegant Look

```css
.thestitch-form-container h3 {
    font-family: 'Georgia', serif;
    font-size: 28px;
    letter-spacing: 2px;
}
```

### Modern/Minimal Look

```css
.thestitch-form-container {
    border: 1px solid #ddd;
    box-shadow: none;
}

.btn-submit {
    border-radius: 4px;
    letter-spacing: 0;
    text-transform: none;
}
```

---

## Troubleshooting

### Changes Not Showing on Website

1. **Clear your browser cache** (Ctrl+Shift+Delete on Windows, Cmd+Shift+Del on Mac)
2. **Hard refresh** the page (Ctrl+F5 on Windows, Cmd+Shift+R on Mac)
3. Verify changes are **saved** (button should say "✓ Saved!")
4. Check that **plugin is activated** in Plugins menu

### Color Picker Not Working

1. Make sure **WordPress admin area is fully loaded**
2. Try a different browser
3. Check browser console for errors (F12 → Console)
4. Disable any conflicting plugins temporarily

### Email Not Sending

1. Verify **recipient email** is valid in Email Settings
2. Check **spam/junk folder** for notifications
3. Contact your **hosting provider** about email limits
4. Use a **transactional email service** if issues persist

### Forms Look Different Than Preview

1. Check **Custom CSS** for conflicting rules
2. Verify **browser zoom** is at 100%
3. **Compare on mobile** vs desktop
4. Check that **correct color values** were saved

---

## Best Practices

✓ **Keep it on-brand** — Match your existing website colors  
✓ **Test thoroughly** — Preview on desktop, tablet, and mobile  
✓ **Keep it simple** — Don't use more than 3-4 main colors  
✓ **Ensure readability** — Make sure text has good contrast  
✓ **Test with customers** — Get feedback on form appearance  
✓ **Back up settings** — Take screenshots of your configuration  
✓ **Update regularly** — Keep labels and messages fresh and relevant  

---

## Advanced Features

### Using CSS Variables

The forms use CSS variables that update automatically:

```css
:root {
    --thestitch-primary: #8b7355;   /* Button color */
    --thestitch-hover: #6d5a47;     /* Button hover */
    --thestitch-text: #333333;       /* Text color */
    --thestitch-success: #4caf50;    /* Success messages */
    --thestitch-error: #f44336;      /* Error messages */
}
```

### Custom CSS Rules

You can target specific elements:

```css
/* Form title */
.thestitch-form-container h3 { ... }

/* Input fields */
.thestitch-form input { ... }

/* Buttons */
.btn-submit, .btn-next { ... }

/* Form responses */
.form-response { ... }
```

---

## Getting Help

If you need additional customizations:

1. Review this guide for common solutions
2. Check the **Live Preview** to test changes
3. Use the **Custom CSS** field for advanced styling
4. Contact support with specific design requests

---

## Keyboard Shortcuts

| Action | Shortcut |
|--------|----------|
| Save Settings | Ctrl+Enter (Windows) or Cmd+Return (Mac) |
| Jump to Preview | Ctrl+Shift+P (in some browsers) |
| Reload Customization | Ctrl+Shift+R (hard refresh) |

---

**Version**: 1.0.0  
**Last Updated**: 2024  
**Plugin**: The Stitch Custom Forms
