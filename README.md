# Flarum MailGuard

![Flarum 2.0 Compatible](https://img.shields.io/badge/Flarum-2.0-blue.svg) ![License](https://img.shields.io/badge/License-MIT-green.svg)

MailGuard is a lightweight, ultra-optimized Flarum 2.0 extension to securely guard and validate email domains during user registration. Built entirely without external GUI frameworks to guarantee 0 impact on server memory and instant real-time blocks.

## Features

* **Allowlist / Denylist**: Block specific disposable email domains or restrict your forum strictly to internal company emails.
* **DNS/MX Live Validation**: Even if an email looks valid, MailGuard does a live server intercept to verify if the MX records exist. Fake domains are instantly nuked.
* **Database Exporter**: Instantly retrieve and export the email domains of all users currently on your forum.
* **CLI Auditing**: Run terminal commands to discover or remove users belonging to bad email domains.

## Installation

Install manually or via composer:

```sh
composer require anto0102/mailguard
```

*(Ensure you rebuild your flarum extensions cache after installation)*

```sh
php flarum cache:clear
```

## CLI Commands

To extract domains from your current userbase to a CSV log:
```sh
php flarum emailguard:export
```

To list all existing users matching your denied domains:
```sh
php flarum emailguard:audit
```

To **permanently delete** users with blocked domains:
```sh
php flarum emailguard:audit --delete
```
