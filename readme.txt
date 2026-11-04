=== Smart Contact Hub ===
Contributors: ashutoshpalhare
Tags: contact form, contact, email, whatsapp, shortcode
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html


A clean, modern AJAX contact form with email notifications, an optional WhatsApp redirect, and honeypot spam protection.


== Description ==


Smart Contact Hub adds a responsive contact form to your site with a single shortcode.


* Place the form anywhere with `[smart_contact_hub]`.
* Submissions are sent with no page reload, with a loading state and clear success or error messages.
* Messages are emailed to the address you choose, with the visitor set as the reply-to address.
* Optionally show a "Continue on WhatsApp" button after a successful submission, with the visitor's message pre-filled.
* Basic spam protection using a hidden honeypot field.
* Nonces, input sanitization, and output escaping throughout.
* No external CSS frameworks and no external requests. Styles and scripts load only on pages that use the shortcode.


== Installation ==


1. Upload the `smart-contact-hub` folder to the `/wp-content/plugins/` directory, or install the plugin zip through the Plugins screen in WordPress.
2. Activate the plugin through the Plugins screen.
3. Go to Settings > Smart Contact Hub and configure the recipient email, success message, WhatsApp options, and form title.
4. Add `[smart_contact_hub]` to any page or post.


== Frequently Asked Questions ==


= Where are messages sent? =


To the Recipient Email set under Settings > Smart Contact Hub. It defaults to the site admin email.


= How do I enable the WhatsApp button? =


Enter your WhatsApp number with the country code and digits only (for example 919876543210), set "Enable WhatsApp Button" to Yes, and save. After a visitor submits the form, a button opens WhatsApp with their message pre-filled.


= Does the plugin store submissions in the database? =


No. Submissions are emailed using wp_mail(). Use a reliable mail delivery plugin (SMTP) if your host does not deliver mail well.


= Is the form protected against spam? =


It includes a hidden honeypot field that catches most simple bots. For heavier spam, combine it with a CAPTCHA or anti-spam service.


== Changelog ==


= 1.0.0 =
* Initial release.
* Contact form shortcode with Name, Email, Phone, Subject, and Message fields.
* AJAX submission with loading and feedback states.
* Email notifications through wp_mail().
* Optional WhatsApp redirect after success.
* Admin settings page under Settings > Smart Contact Hub.
* Honeypot spam protection.


== Upgrade Notice ==


= 1.0.0 =
First release of Smart Contact Hub.

