=== KafiChat Lite ===
Contributors: kafgram
Tags: live chat, chat widget, customer support, bale messenger, online support
Requires at least: 6.0
Tested up to: 7.0.2
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Live chat widget for WordPress that connects your website visitors to Bale messenger. Fully optimized for Persian/Farsi websites.

== Description ==

KafiChat Lite is a free, lightweight live chat plugin that bridges your WordPress website with the Bale messenger platform. Your visitors can chat with you directly from your website, and you receive and respond to their messages through your Bale bot.

**Key Features:**

* **Real-time Chat:** Instant messaging between website visitors and your Bale bot.
* **Guest Form:** Collect visitor name and phone number before starting the chat.
* **Smart Adaptive Polling:** Efficient message checking using Page Visibility API (stops polling when the tab is inactive to save server resources).
* **RTL Support:** Fully optimized for Persian/Farsi websites with built-in Vazirmatn font.
* **Mobile Responsive:** Works perfectly on all screen sizes with a beautiful overlay.
* **Customizable:** Change position, header title, and welcome message.
* **Secure:** Encrypted PII (Name/Phone/IP) using Libsodium/OpenSSL, nonce verification, and advanced rate limiting.
* **CDN Compatible:** Correctly identifies real client IPs behind Cloudflare and ArvanCloud.
* **Lightweight:** No jQuery dependency, vanilla JavaScript, minimal footprint.
* **Webhook Integration:** Automatic message routing from Bale to your website.
* **Admin Dashboard:** Full settings panel with debug tools, system info, and conversation logs.

—

**برای کاربران فارسی‌زبان:**

این افزونه کاملاً برای سایت‌های فارسی‌زبان بهینه‌سازی شده است:

✅ **ویژگی‌های اختصاصی برای ایران:**
– پشتیبانی کامل از RTL (راست‌چین)
– فرم مهمان فارسی با اعتبارسنجی شماره موبایل ایرانی
– پنل ادمین کاملاً فارسی با فونت زیبای وزیر
– اتصال مستقیم به پیام‌رسان بله
– نمایش ساعت به وقت ایران
– سازگاری کامل با شبکه‌های توزیع محتوا (CDN) مانند کلودفلر و آروان

✅ **نصب آسان:**
1. افزونه را نصب و فعال کنید
2. از @BotFather در بله، توکن ربات بگیرید
3. توکن و شناسه چت ادمین را وارد کنید
4. وب‌هوک را تنظیم کنید
5. ویجت چت به‌صورت خودکار نمایش داده می‌شود

✅ **پشتیبانی از هاست‌های ایرانی:**
– سازگار با هاست‌های اشتراکی ایرانی
– پشتیبانی از هاست‌هایی که هدرهای امنیتی را حذف می‌کنند
– بهینه‌سازی شده برای سرورهای ایران

**How It Works:**

1. Install and activate KafiChat Lite.
2. Create a bot on Bale messenger via @BotFather.
3. Enter your bot token and admin chat ID in the plugin settings.
4. Configure the webhook to receive messages from Bale.
5. The chat widget appears on your website automatically.

**Privacy:**

KafiChat Lite stores conversation data and messages in your WordPress database. Sensitive guest data (Name, Phone, IP) is encrypted at rest. No data is sent to third-party servers except Bale messenger API. You can configure data retention periods in the settings.

== External services ==

This plugin connects to the Bale messenger platform to enable live chat between website visitors and site administrators.

== What the service is and what it is used for ==
Bale is an Iranian messaging platform. This plugin uses the Bale Bot API to:
- Send user messages from your website to the administrator's Bale account
- Receive administrator responses and display them to website visitors
- Set up webhooks for real-time message delivery

== What data is sent and when ==
- When a visitor sends a message through the chat widget, the message content, visitor name (if provided), and phone number (if provided) are sent to the Bale Bot API
- The administrator's Bale Bot Token and Chat ID are used to authenticate and route messages
- No data is sent unless the administrator has configured a Bale Bot Token in the plugin settings

== Links to service terms and policies ==
- Bale Terms  of  Service & Privacy Policy: https://bale.ai/terms
- Bale Bot API Documentation: https://docs.bale.ai

== Installation ==

1. Upload the `kafichat-lite` folder to the `/wp-content/plugins/` directory, or install through the WordPress plugins screen.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to 'KafiChat Lite' > 'Settings' in your WordPress admin.
4. Enter your Bale bot token and admin chat ID.
5. Set up the webhook by clicking "Set Webhook in Bale".
6. Customize appearance and messages as needed.
7. The chat widget will appear on your website.

== Frequently Asked Questions ==

= Does it work with any WordPress theme? =

Yes! KafiChat Lite uses isolated CSS with high z-index and !important where needed to ensure compatibility with any theme.

= Do I need a Bale account? =

Yes, you need a Bale messenger account and a bot created via @BotFather in Bale.

= Is it GDPR compliant? =

Yes. You can configure data retention periods, and all data stays on your server. The guest form collects only name and phone number with user consent.

= Can I use it with other messengers? =

The Lite version supports only Bale messenger. For Telegram and multi-platform support, upgrade to KafiChat Pro.

= Does it slow down my website? =

No. The widget uses vanilla JavaScript (no jQuery), loads asynchronously, and uses smart adaptive polling to minimize server requests.

= آیا با همه قالب‌ها کار می‌کند؟ =

بله! این افزونه با تمام قالب‌های وردپرس سازگار است.

= آیا نیاز به اکانت بله دارم؟ =

بله، باید یک اکانت در پیام‌رسان بله داشته باشید و از @BotFather یک ربات بسازید.

== Screenshots ==

1. Chat widget on website
2. Admin settings panel
3. Conversations list
4. Guest form

== Changelog ==

= 1.0.1 =
* New: Added Vazirmatn font via CDN for a better Persian typography experience.
* New: Implemented Adaptive Polling using the Page Visibility API to drastically reduce server load.
* Security: Enhanced RateLimiter to detect real client IPs behind CDNs (Cloudflare, ArvanCloud).
* Performance: Fixed N+1 query issue in the admin conversations list for much faster loading.
* DB Schema: Added `search_index` column to conversations table for future-proofing and Pro compatibility.
* Compatibility: Fixed a fatal error with the `%i` SQL placeholder for WordPress 6.0/6.1.
* UX: Logged-in users now bypass the guest form and enter the chat directly.
* UX: Fixed admin spinner alignment.
* Tweak: Extensive optimization and minification of frontend and backend CSS/JS.
* Tweak: Code refactoring (moved direct DB queries to repositories, removed duplicate code).

= 1.0.0 =
* Initial release
* Bale messenger integration
* Guest form with phone validation
* Real-time polling
* RTL support
* Mobile responsive design
* Admin dashboard with debug tools

== Upgrade Notice ==

= 1.0.1 =
Critical update for database compatibility, security enhancements, and performance improvements. Highly recommended before upgrading to KafiChat Pro.

= 1.0.0 =
Initial release of KafiChat Lite.

== License ==

This plugin is licensed under the GPLv2 or later.

== Credits ==

Developed by Kafgram.