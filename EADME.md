# GP DentaShop

> قالب فروشگاهی فوق سریع بر پایه GeneratePress + WooCommerce  
> با الهام از دیجی‌کالا | بهینه برای سرعت، SEO و تجربه کاربری

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-6.0+-21759b.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-777bb4.svg)
![WooCommerce](https://img.shields.io/badge/WooCommerce-7.0+-96588a.svg)
![License](https://img.shields.io/badge/license-GPL--2.0+-green.svg)

---

## 📋 فهرست مطالب

- [ویژگی‌ها](#-ویژگیها)
- [پیش‌نیازها](#-پیشنیازها)
- [نصب](#-نصب)
- [راه‌اندازی اولیه](#-راهاندازی-اولیه)
- [ساختار قالب](#-ساختار-قالب)
- [تنظیمات](#-تنظیمات)
- [استوری‌ها](#-استوریها)
- [شگفت‌انگیزها](#-شگفتانگیزها)
- [بهینه‌سازی سرعت](#-بهینهسازی-سرعت)
- [توسعه](#-توسعه)
- [سوالات متداول](#-سوالات-متداول)

---

## ✨ ویژگی‌ها

### 🏠 صفحه اصلی
- **استوری‌ها** — با پنل ادمین کامل + Modal شبیه اینستاگرام (پشتیبانی از تصویر و ویدیو)
- **اسلایدر** — از محصولات ویژه WooCommerce
- **آیکون‌های خدمات** — ۸ آیکون قابل تنظیم
- **شگفت‌انگیزها** — با تایمر از سرور (نه کلاینت)
- **دسته‌بندی‌های محصولات**
- **پرفروش‌ترین‌ها** — کاروسل با Swiper
- **خواندنی‌ها** — آخرین نوشته‌های بلاگ

### 🛒 فروشگاهی
- **صفحه فروشگاه** — با فیلترهای پیشرفته
- **صفحه محصول** — گالری + تب‌ها + مشخصات
- **Mini Cart** — با Ajax
- **جستجوی Ajax** — با Debounce
- **افزودن سریع به سبد** — بدون رفرش

### ⚡ عملکرد
- **بدون Elementor** — سرعت بالا
- **بدون jQuery Migrate**
- **CSS/JS حداقلی** — فقط در صفحات لازم
- **Lazy Loading** — تصاویر و iframe
- **Critical CSS inline**
- **Defer JS** — غیرحیاتی
- **Transient Cache** — برای کوئری‌های سنگین
- **Browser Cache** — بهینه

### 🎨 طراحی
- **RTL کامل**
- **Dana Font** — لوکال (بدون Google Fonts)
- **SVG Icons** — بدون FontAwesome
- **CSS Variables** — قابل سفارشی‌سازی
- **Responsive** — موبایل، تبلت، دسکتاپ
- **Dark Mode** — آماده (آینده)

---

## 📦 پیش‌نیازها

| مورد | حداقل |
|------|-------|
| **WordPress** | 6.0+ |
| **PHP** | 7.4+ (بهترین: 8.0+) |
| **MySQL** | 5.7+ / MariaDB 10.3+ |
| **WooCommerce** | 7.0+ |
| **GeneratePress** | آخرین نسخه (رایگان) |

### افزونه‌های پیشنهادی

- **[Converter for Media](https://wordpress.org/plugins/webp-converter-for-media/)** — تبدیل WebP/AVIF
- **[LiteSpeed Cache](https://wordpress.org/plugins/litespeed-cache/)** یا **[WP Rocket](https://wp-rocket.me)** — کش
- **[Asset CleanUp](https://wordpress.org/plugins/wp-asset-clean-up/)** — حذف Assets اضافی

---

## 🚀 نصب

### مرحله ۱: دانلود

```bash
cd wp-content/themes/
git clone https://github.com/your-username/gp-dentashop.git