# Smart Contact Hub

A lightweight WordPress contact form plugin with AJAX email delivery, optional WhatsApp follow-up, and practical anti-spam protection. It uses native WordPress APIs and has no frontend framework or third-party runtime dependency.

## Features

- **Shortcode:** add the form to any page or post with `[smart_contact_hub]`.
- **AJAX submission:** visitors submit without a page reload and receive clear success or error feedback.
- **Email delivery:** sends plain-text notifications through WordPress `wp_mail()`, with a validated Reply-To address.
- **WhatsApp follow-up:** optionally show a `wa.me` link after successful email delivery.
- **Settings screen:** configure the recipient, form heading, confirmation message, and WhatsApp option under **Settings → Smart Contact Hub**.
- **Spam controls:** nonce verification, a honeypot field, and a short per-network submission cooldown.
- **Input protection:** server-side validation, sanitization, field length limits, and safe email headers.
- **Accessible UI:** associated labels, required-field semantics, live status announcements, keyboard focus styles, and reduced-motion support.
- **Responsive styling:** scoped CSS that avoids leaking styles into the active WordPress theme.
- **No external runtime dependencies:** no external scripts, stylesheets, fonts, or API services are loaded.

## Requirements

- WordPress 5.8 or newer
- PHP 7.4 or newer
- A working WordPress mail transport (SMTP is recommended for production sites)

## Installation

1. Download or clone this repository.
2. Copy the plugin directory into `wp-content/plugins/smart-contact-hub/`.
3. Activate **Smart Contact Hub** from **Plugins** in the WordPress dashboard.
4. Open **Settings → Smart Contact Hub**.
5. Set the recipient email and customize the form title and success message.
6. Add `[smart_contact_hub]` to a page, post, or shortcode-enabled widget area.

## Configuration

| Setting | Purpose |
| --- | --- |
| Recipient Email | Destination for contact notifications. Defaults to the WordPress admin email. |
| Form Title | Heading displayed above the form. |
| Success Message | Confirmation shown after WordPress accepts the email. |
| WhatsApp Number | International number with country code, digits only (8–15 digits). |
| Enable WhatsApp Button | Displays a WhatsApp follow-up link after successful email delivery. |

The WhatsApp link is optional. When enabled, it opens a new tab with a short message containing the sender name and subject. The visitor must choose to open WhatsApp; the plugin does not redirect automatically.

## Form fields

- Name — required, maximum 120 characters
- Email — required, maximum 190 characters
- Phone — optional, maximum 40 characters
- Subject — required, maximum 160 characters
- Message — required, maximum 5,000 characters

The browser performs native validity checks, and the server repeats validation before sending. Server-side validation is authoritative.

## Security and data handling

- Requests are verified with a WordPress nonce.
- Text fields are sanitized and length-limited on the server.
- Email recipients and Reply-To addresses are validated before calling `wp_mail()`.
- Header values are normalized to reduce header-injection risk.
- A hidden honeypot field rejects basic automated submissions.
- A short transient cooldown reduces rapid repeat submissions. Its key is an HMAC of the remote address; the raw address is not stored by this plugin.
- Contact submissions are **not stored in the WordPress database** by this plugin. They are passed to `wp_mail()` for delivery.

The honeypot and cooldown are lightweight controls, not a replacement for a dedicated anti-spam service on high-volume public forms. Configure a reliable SMTP delivery method because `wp_mail()` acceptance does not guarantee inbox delivery.

## Developer workflow

Install development dependencies:

```bash
composer install
```

Run PHP syntax checks:

```bash
composer lint
```

Run WordPress and PHP compatibility coding standards:

```bash
composer cs
```

The project targets WordPress 5.8+ and PHP 7.4+. The compatibility helper provides Unicode-safe character counting and slicing even when the PHP `mbstring` extension is unavailable.

## Project structure

```text
SmartContactHub/
├── smart-contact-hub.php
├── uninstall.php
├── composer.json
├── phpcs.xml.dist
├── includes/
│   ├── class-sch-admin.php
│   ├── class-sch-compat.php
│   ├── class-sch-email.php
│   └── class-sch-frontend.php
├── assets/
│   ├── css/frontend.css
│   └── js/frontend.js
├── .github/workflows/
├── README.md
└── readme.txt
```

## Troubleshooting

**Messages are not arriving**

Check the configured recipient address, spam folder, and hosting mail configuration. For production, configure SMTP and verify delivery independently.

**The WhatsApp button does not appear**

Enter a valid international phone number using digits only, save the settings, and enable the WhatsApp button.

**The form reports an expired session**

Refresh the page to obtain a fresh nonce. Aggressive page caching can serve stale nonce values; exclude pages containing the form from long-lived full-page caching if needed.

## License

GPL-2.0-or-later. See [LICENSE](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html).
