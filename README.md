# Flarum MailGuard
![Flarum 2.0 Compatible](https://img.shields.io/badge/Flarum-2.0-blue.svg) [![License](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)
[![Total Downloads](https://poser.pugx.org/anto0102/mailguard/downloads)](https://packagist.org/packages/anto0102/mailguard)
[![Latest Stable Version](https://poser.pugx.org/anto0102/mailguard/v)](https://packagist.org/packages/anto0102/mailguard)
[![Monthly Downloads](https://poser.pugx.org/anto0102/mailguard/d/monthly)](https://packagist.org/packages/anto0102/mailguard)

MailGuard is a lightweight, ultra-optimized Flarum 2.0 extension to securely guard and validate email domains during user registration. Built entirely without external GUI frameworks to guarantee **zero impact on server memory** and provide instant, real-time blocking of unwanted users.

---

## ✨ Features & Configuration Guide

### 🛡️ Alias Anti-Fraud (Plus Strategy)
Gmail and many providers allow users to add a `+` (e.g., `user+spam@gmail.com`) to create "infinite" addresses for the same account.
* **Action: Block**: Rejects any registration containing a `+` alias.
* **Action: Sanitize**: Automatically removes the alias part (e.g., `user+1@gmail.com` becomes `user@gmail.com`) before saving.

### 🎯 Gmail Dot Strategy (Anti-Clone)
Gmail ignores dots (e.g., `m.a.r.i.o@gmail.com` is the same as `mario@gmail.com`). This is often used for account cloning.
* **Disabled (Standard)**: Dots are ignored by the filter; Flarum treats them as separate emails.
* **Check for Duplicates (Anti-Clone)**: **[Recommended]** The user can register with dots (maintaining their login UX), but MailGuard blocks the registration if the root version (without dots) already exists in the database. 
* **Strict Block**: Rejects any email containing dots in the local part for target providers.
* **Silent Clean**: Automatically removes dots from the email before saving. **Note**: The user will then need to log in *without* dots.

### 🌐 DNS/MX Live Validation
Performs a real-time server check to verify if the domain has valid Mail Exchange (MX) records. Blocks fake domains like `@nonexistent-provider.web` instantly.

### ⚡ Domain Shield (Allow/Deny)
* **Allowlist**: Only the listed domains can register. Perfect for corporate or invitation-only forums.
* **Denylist**: All domains allowed except those listed (e.g., disposable email providers).

---

## 🚀 Installation & CLI

Install via Composer:
```sh
composer require anto0102/mailguard
php flarum cache:clear
```

### Powerful CLI Commands:
```sh
# Audit users against current blocked domains
php flarum emailguard:audit

# Perform a deep audit and permanently remove non-compliant users
php flarum emailguard:audit --delete

# Export all current forum domains to CSV or JSON
php flarum emailguard:export --format=json
```

---

## 🖼️ Screenshots
![Admin Settings Dashboard](https://github.com/anto0102/MailGuard/raw/main/screenshots/admin.png)
![Registration Error Example](https://github.com/anto0102/MailGuard/raw/main/screenshots/error.png)

---

## 📄 License
This project is licensed under the **MIT License**.

## 🤝 Support & Issues
If you experience any bugs, have questions, or want to suggest new features, please [Open an Issue](https://github.com/anto0102/MailGuard/issues) directly on this GitHub repository.
