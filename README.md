# Smart Contact Hub


A clean, modern contact form plugin for WordPress. It sends messages by email, can open a WhatsApp chat after submission, and includes basic spam protection. The form submits without reloading the page and works on mobile and desktop.


## Features


- **Shortcode:** place the form anywhere with `[smart_contact_hub]`.
- **AJAX submission:** no page reload, with a loading state and clear success or error messages.
- **Email notifications:** sent with `wp_mail()`, with the visitor set as the reply-to address.
- **WhatsApp redirect:** optional "Continue on WhatsApp" button after success, with the message pre-filled.
- **Admin settings page:** Settings > Smart Contact Hub, built on the WordPress Settings API.
- **Spam protection:** a hidden honeypot field catches simple bots.
- **Security:** nonces, input sanitization, output escaping, and capability checks.
- **Performance:** styles and scripts load only on pages that use the shortcode.
- **No dependencies:** no external CSS frameworks, no external requests.


## Requirements


- WordPress 5.8 or higher
- PHP 7.4 or higher


## Installation


1. Copy the `smart-contact-hub` folder to `wp-content/plugins/`, or zip it and upload it from **Plugins > Add New > Upload Plugin**.
2. Activate **Smart Contact Hub** from the Plugins screen.
3. Open **Settings > Smart Contact Hub** and configure the options below.
4. Add the shortcode `[smart_contact_hub]` to a page or post.


## Settings


| Setting | Description |
| --- | --- |
| Recipient Email | Address that receives new messages. Defaults to the site admin email. |
| Success Message | Text shown to the visitor after a successful submission. |
| WhatsApp Number | Number with country code and digits only, for example `919876543210`. |
| Enable WhatsApp Button | When set to Yes, shows a WhatsApp button after submission. |
| Form Title | Heading displayed above the form. |


## Usage


```
[smart_contact_hub]
```


The form includes Name, Email, Phone (optional), Subject, and Message fields. Name, Email, Subject, and Message are required.


## How It Works


1. The visitor fills in the form. The browser checks required fields first.
2. The form is sent to WordPress over AJAX, with a nonce for verification.
3. The server checks the honeypot, validates and sanitizes the data, and emails it to the recipient.
4. The visitor sees the success message. If WhatsApp is enabled, a button opens a chat with their message pre-filled.


## Project Structure


```
smart-contact-hub/
├── smart-contact-hub.php          Main plugin file, settings helpers, bootstrap
├── uninstall.php                  Removes settings when the plugin is deleted
├── includes/
│   ├── class-sch-admin.php        Settings page and settings API registration
│   ├── class-sch-frontend.php     Shortcode, asset loading, AJAX handler
│   └── class-sch-email.php        Email composition and wp_mail() delivery
├── assets/
│   ├── css/frontend.css           Form styles
│   └── js/frontend.js             AJAX submission and UI states
├── readme.txt                     WordPress.org plugin readme