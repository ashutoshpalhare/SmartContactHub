=== Smart Contact Hub ===
Contributors: ashutoshpalhare
Tags: contact form, contact, email, whatsapp, shortcode
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A responsive WordPress contact form with AJAX email delivery, optional WhatsApp follow-up, and lightweight anti-spam controls.

== Description ==

Smart Contact Hub adds a configurable contact form with the [smart_contact_hub] shortcode.

* AJAX submission with accessible loading, error, and success states.
* Email notifications through WordPress wp_mail().
* Optional WhatsApp follow-up link after successful email delivery.
* Settings page under Settings > Smart Contact Hub.
* Nonce verification, a honeypot field, and a short submission cooldown.
* Server-side sanitization, validation, length limits, and safe email headers.
* Responsive, scoped frontend styling with no external runtime dependencies.
* Contact submissions are not stored in the WordPress database by this plugin.

== Installation ==

1. Upload the smart-contact-hub directory to /wp-content/plugins/.
2. Activate Smart Contact Hub from the Plugins screen.
3. Open Settings > Smart Contact Hub and configure the recipient and form options.
4. Add [smart_contact_hub] to a page or post.

== Frequently Asked Questions ==

= Where are messages sent? =

Messages are sent to the Recipient Email configured under Settings > Smart Contact Hub. It defaults to the WordPress admin email.

= How do I enable WhatsApp follow-up? =

Enter the international number with country code and digits only (8 to 15 digits), enable the WhatsApp button, and save. A follow-up link appears after email delivery succeeds. Visitors choose whether to open it.

= Does the plugin store submissions? =

No. The plugin sends submissions through wp_mail() and does not save them as WordPress records. A short transient is used for submission cooldown; its key is an HMAC of the remote address.

= Why are messages not arriving? =

Check the recipient address and hosting mail configuration. Configure a reliable SMTP provider for production and verify inbox delivery.

= Is the form protected against spam? =

The plugin includes nonce verification, a hidden honeypot, and a short per-network cooldown. These are basic controls and may need to be supplemented on high-volume sites.

== Changelog ==

= 1.1.0 =
* Restored complete frontend and admin settings implementations.
* Added server-side validation, field length limits, and submission cooldown.
* Hardened recipient validation and email headers.
* Added a multibyte-safe compatibility helper that does not require mbstring.
* Improved responsive styling, accessibility, and AJAX error handling.
* Added live message length feedback and developer quality tooling.

= 1.0.0 =
* Initial contact form shortcode and settings page.
* AJAX submission and email notifications.
* Optional WhatsApp follow-up and honeypot protection.

== Upgrade Notice ==

= 1.1.0 =
Includes restored form functionality, improved validation, and frontend accessibility updates.
