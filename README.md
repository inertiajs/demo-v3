# Inertia.js v3 Kitchen Sink

A demo app showcasing Inertia.js v3 features. Built on the Laravel starter kit with a mini CRM and feature showcase pages.

> Inertia.js v3 is currently in beta. See the [upgrade guide](https://inertiajs.com/docs/v3/getting-started/upgrade-guide) for more information.

## Tech Stack

- **Laravel 12** with PHP 8.5
- **Inertia.js v3** (`@inertiajs/vue3`)
- **Vue 3** with Composition API and TypeScript
- **Tailwind CSS 4** + shadcn-vue
- **Pest 4** with browser testing (Playwright)

## What's Inside

### Mini CRM

Dashboard, contacts, organizations, and notes demonstrating real-world Inertia patterns.

### Feature Showcase

Dedicated pages organized by category with interactive demos:

| Category | Pages |
|---|---|
| **Forms** | useForm, Form Component, File Uploads, Validation, Precognition, Optimistic Updates, useFormContext, Dotted Keys, Wayfinder |
| **Navigation** | Links, Preserve State, Preserve Scroll, View Transitions, History Management, Async Requests, Instant Visits, URL Fragments, Manual Visits, Redirects, Scroll Management |
| **Data Loading** | Deferred Props, Partial Reloads, Infinite Scroll, When Visible, Polling, Prop Merging, Optional Props, Once Props |
| **Prefetching** | Link Prefetch, Stale While Revalidate, Manual Prefetch, Cache Management |
| **State** | Remember, Flash Data, Shared Props |
| **Layouts** | Persistent Layouts, Nested Layouts, Head Component, Layout Props |
| **Events & Lifecycle** | Global Events, Visit Callbacks, Progress Bar |
| **Error Handling** | HTTP Exceptions, Network Errors |
| **HTTP** | useHttp |

## Setup

```bash
# Clone and install
git clone git@github.com:inertiajs/demo-v3.git
cd demo-v3
composer install
npm install

# Environment
cp .env.example .env
php artisan key:generate

# Database
touch database/database.sqlite
php artisan migrate --seed

# Generate Wayfinder routes
php artisan wayfinder:generate

# Development
composer run dev
# Or manually:
# php artisan serve & npm run dev

# For production builds, generate Wayfinder routes first:
# php artisan wayfinder:generate && npm run build
```

## Testing

```bash
# Run all tests
php artisan test

# Run browser tests only
php artisan test tests/Browser

# Run a specific category
php artisan test tests/Browser/Features/Forms

# Run with filter
php artisan test --filter="useForm"
```

Browser tests require Playwright:

```bash
npx playwright install
```

