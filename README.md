# darsenizami.net — Production Technical SEO & Web Root Repository

Production Git repository for `https://darsenizami.net` web root (`public_html/`) containing all critical Technical & On-Page SEO fixes optimized for Apache / StackCP hosting.

## Features & Implementation
- **Canonical Redirects & Server Routing (`.htaccess`)**:
  - Enforces canonical HTTPS and non-www (`https://darsenizami.net`).
  - Passes through `/online/` and `/lms/` subdirectories.
  - Redirects legacy WordPress AMP URLs and naked slug paths to `/online/`.
  - Normalizes canonical page aliases (e.g. `/courses/` -> `/dars-e-nizami-course.html`).
- **Performance & Core Web Vitals**:
  - Gzip / Deflate compression via `mod_deflate`.
  - Long-term 1-year immutable caching for static assets (images, fonts, CSS, JS).
  - Proper security headers (`X-Content-Type-Options`, `X-Frame-Options`, `Strict-Transport-Security`).
- **Crawl Budget Optimization (`robots.txt`)**:
  - Disallows student portal (`/lms/`, `/cms/`) and utility enrollment form (`/alimah-course/enroll.html`).
  - Blocks crawl traps like WordPress feeds (`/online/*/feed/`, `/*/feed/`).
  - Declares XML sitemaps (`/sitemap.xml`, `/online/sitemap_index.xml`).
- **Structured Data (JSON-LD)**:
  - `EducationalOrganization` schema on the homepage.
  - `Course` schema on course landing pages with tuition and curriculum details.
- **On-Page SEO & Deduplication**:
  - Deduplicated course title tags to eliminate keyword cannibalization.
  - Self-referencing canonical tags on every page.
  - `noindex, follow` on thin WhatsApp submission forms.

## StackCP Deployment Instructions
1. Log into your **StackCP Control Panel**.
2. Go to **Web Files** > **Git Version Control**.
3. Add or configure the repository:
   - **Clone URL**: `https://github.com/livevirtuecom-sudo/darsenizami-seo.git` (or your repository URL)
   - **Branch**: `main`
   - **Target Directory**: `public_html`
4. Trigger Deploy to sync files directly into `public_html`.
