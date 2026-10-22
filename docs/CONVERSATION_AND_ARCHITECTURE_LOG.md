# Dars-e-Nizami SEO & Deployment Architecture Log

> **Repository:** [darsenizami-seo](https://github.com/livevirtuecom-sudo/darsenizami-seo)  
> **Production Domain:** [https://darsenizami.net](https://darsenizami.net)  
> **Hosting Platform:** StackCP (Shared Cloud / Apache behind StackCDN)  
> **Session / Conversation ID:** `fa2f5b66-8afb-4aed-b624-9eff00787b47`  
> **Last Updated:** October 2026

---

## 1. Executive Summary & Objectives

The goal of this project was to establish a dedicated, clean, production-grade Git repository for the public web root (`public_html/`) of `darsenizami.net`. This included implementing full Technical & On-Page SEO enhancements, resolving keyword cannibalization, configuring structured schema markup, integrating Google AdSense, and setting up automated zero-downtime deployment through StackCP Git Version Control while rigorously protecting sub-applications (`/lms/` and `/online/`).

---

## 2. Server & Infrastructure Architecture

### StackCP & StackCDN Nuance
- **Edge Reverse Proxy:** StackCP routes traffic through **StackCDN**. SSL termination happens at the CDN edge, which forwards requests internally to Apache on port 80.
- **HTTPS & DirectoryIndex Resolution:**
  - Standard Apache `RewriteCond %{HTTPS} !=on` rules create infinite 301 redirect loops behind StackCDN. HTTPS redirection is handled natively by StackCDN at the edge.
  - Apache's internal `DirectoryIndex` rewrites `/` to `index.html`. Generic `RewriteRule ^index\.html$` rules trigger infinite loops unless guarded by `RewriteCond %{THE_REQUEST}`.
- **Isolation Protection:**
  - `/lms/`: Custom PHP 8.3 student portal with database integration and strict Content Security Policies.
  - `/online/`: Standalone WordPress installation.
  - Both directories are excluded from deployment overwrites to avoid downtime or data corruption.

---

## 3. Implemented Technical SEO Enhancements

### 3.1. Crawl Budget Optimization (`robots.txt`)
- Disallows backend and utility endpoints: `/lms/`, `/cms/`, `/alimah-course/enroll.html`, `/dn-lms-hosting-upload.zip`.
- Eliminates 20+ WordPress feed crawl traps (`/online/*/feed/`, `/*/feed/`, `/wp-admin/`, `/wp-login.php`).
- Sitemaps declared:
  - `https://darsenizami.net/sitemap.xml`
  - `https://darsenizami.net/online/sitemap_index.xml`

### 3.2. Server Rewrites & Core Web Vitals (`deploy.php`)
- **Non-WWW Enforced:** 301 redirects from `www.darsenizami.net` to `darsenizami.net`.
- **DirectoryIndex Safe Canonicalization:**
  ```apache
  RewriteCond %{THE_REQUEST} ^[A-Z]{3,}\s/+index\.html[\s?] [NC]
  RewriteRule ^index\.html$ https://darsenizami.net/ [R=301,L,NE]
  ```
- **Compression & Caching:**
  - `mod_deflate` enabled for text/html, json, xml, css, js, svg, and web fonts.
  - `mod_expires` enabled: 1-hour cache for HTML, 1-year immutable cache for static assets (`.jpg`, `.png`, `.webp`, `.css`, `.js`, `.woff2`).

### 3.3. Structured Data (JSON-LD)
- **EducationalOrganization Schema:** Embedded site-wide and on the homepage.
- **Course Schema:** Implemented on course landing pages detailing curriculum duration, admission credentials, and tuition structure.

### 3.4. On-Page Deduplication & Meta Directives
- **Homepage (`index.html`):** `<title>Dars-e-Nizami Online Institute | Alim & Alimah Courses</title>`
- **Alim Program (`alim-course.html`):** `<title>Online Alim Course (Baneen) — 8-Year Dars-e-Nizami</title>`
- **Alimah Program (`alimah-course.html`):** `<title>Online Alimah Course for Sisters — Female Scholar Program</title>`
- **UK Sisters Program (`alimah-course/alimah-course-uk.html`):** `<title>Online Alimah Course UK — Sisters Islamic Studies</title>`
- **Digital Library (`books/index.html`):** `<title>Dars-e-Nizami Books & Shuruhaat PDF Download Library</title>`
- Self-referencing canonical links verified across all pages.
- Thin WhatsApp enrollment forms tagged with `<meta name="robots" content="noindex, follow">`.

### 3.5. Google AdSense Integration
- **Publisher ID:** `ca-pub-5450259987891922`
- **Ownership Verification:** `<meta name="google-adsense-account" content="ca-pub-5450259987891922">`
- **Ad Loader Script:** Injected site-wide across all 25 public HTML pages:
  ```html
  <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-5450259987891922"
       crossorigin="anonymous"></script>
  ```
- **Analytics & Tracking Intact:** Google Tag Manager container `GT-WRDMH29V`.

---

## 4. Git Commit History & Changelog

| Commit Hash | Conventional Commit Message | Description |
| :--- | :--- | :--- |
| `a6beec0` | `feat(safety): add automated pre-deployment backup and isolation locks` | Created pre-deploy backup script and safety exclusions. |
| `76e22fa` | `feat(deploy): add StackCP absolute path and .cpanel.yml task` | Configured absolute path targeting for StackCP web root. |
| `b8b221d` | `fix(deploy): strip UTF-8 BOM and enforce LF line endings on shebang` | Resolved StackCP shebang interpreter error. |
| `ae66fd6` | `fix(deploy): preserve host-managed .htaccess rules and restore original config` | Protected native PHP version handlers from overwrite. |
| `ead4090` | `fix(htaccess): remove HTTPS rewrite to prevent StackCDN reverse-proxy loop` | Removed origin HTTPS rewrite to align with StackCDN edge SSL. |
| `f173b90` | `fix(deploy): use THE_REQUEST for index.html canonical redirects and enable caching` | Fixed DirectoryIndex loop, added Gzip, and sorted pristine backups. |
| `4d02320` | `feat(ads): add Google AdSense script tag site-wide across all 25 public pages` | Injected Google AdSense loader across all public pages. |

---

## 5. Deployment Workflow & Hosting Management

### StackCP Deployment Steps
1. Navigate to **StackCP** > **Web Files** > **Git Version Control**.
2. Select repository `darsenizami-seo`.
3. Click **Update from Remote** to fetch the latest commit from `origin/main`.
4. Click **Deploy Head Commit** to trigger `deploy.php`.
5. Automated process:
   - Reads pristine original `.htaccess`.
   - Prepends CDN-safe rewrite rules and Core Web Vitals caching.
   - Preserves `/lms/` and `/online/`.
   - Copies updated HTML and assets to `public_html/`.

### Edge Cache Invalidation
If headers or redirects are cached at the edge, purge the CDN cache via **StackCP** > **StackCDN** / **Edge Caching** > **Purge Cache**.
