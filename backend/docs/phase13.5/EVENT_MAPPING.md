# Event Mapping

The Activity Centre classifies every `AuditLog` and `LoginActivity` row into a single `ActivityCategory` and `ActivityStatus`. Mapping is centralised in `App\Services\Admin\ActivityService` so adding new actions only requires updating one location.

## LoginActivity mapping

| Source column | Value | Category | Status | Title | Icon |
|---------------|-------|----------|--------|-------|------|
| `successful` | `true` | Authentication | Success | User login | `heroicon-o-arrow-right-end-on-rectangle` |
| `successful` | `false` | Security | Error | Failed login attempt | `heroicon-o-shield-exclamation` |

The description for login rows includes the actor name or email and, for failures, the originating IP address.

## AuditLog category mapping

Categories are resolved from the `action` string (case-insensitive) using the following priority:

| Keywords | Category |
|----------|----------|
| `failed`, `unauthorized`, `bypass`, `locked`, `security` | Security |
| `login`, `logout`, `password`, `authenticated` | Authentication |
| `quote`, `order`, `sale` | Sales |
| `contact`, `customer` | CRM |
| `distributor` | Distributors |
| `support`, `ticket` | Support |
| `product`, `inventory` | Products |
| `blog`, `campaign`, `marketing` | Marketing |
| `user`, `role`, `permission`, `setting` | Administration |
| everything else | System |

## AuditLog status mapping

| Keywords | Status |
|----------|--------|
| `failed`, `error`, `denied`, `unauthorized`, `exception`, `bypass` | Error |
| `warning`, `deleted`, `removed`, `rejected` | Warning |
| `created`, `submitted`, `success`, `approved`, `completed`, `login` | Success |
| everything else | Information |

## AuditLog icon mapping

Icons are chosen from the Heroicons set based on action keywords:

| Keywords | Icon |
|----------|------|
| `quote` | `heroicon-o-document-text` |
| `distributor` | `heroicon-o-user-group` |
| `contact` | `heroicon-o-envelope` |
| `customer` | `heroicon-o-users` |
| `support`, `ticket` | `heroicon-o-ticket` |
| `blog` | `heroicon-o-newspaper` |
| `product` | `heroicon-o-cube` |
| `order` | `heroicon-o-shopping-cart` |
| `setting` | `heroicon-o-cog-6-tooth` |
| `task` | `heroicon-o-check-circle` |
| `login`, `logout` | `heroicon-o-arrow-right-end-on-rectangle` |
| `security`, `failed` | `heroicon-o-shield-exclamation` |
| default | `heroicon-o-bolt` |

## Module derivation

For `AuditLog` rows the module is the basename of `subject_type` when a subject exists (e.g. `QuoteRequest`, `Product`). When no subject is present, the module falls back to the category label. `LoginActivity` rows always report `Authentication` as the module.

## Related record URLs

When an `AuditLog` subject exists, `ActivityService` resolves a view URL using a class-basename map:

- `QuoteRequest` → `/quote-requests/{id}`
- `DistributorRequest` → `/distributor-requests/{id}`
- `ContactMessage` → `/contact-messages/{id}`
- `CustomerFeedback` → `/customer-feedback/{id}`
- `BlogPost` → `/blog-posts/{id}`
- `Product` → `/products/{id}`
- `User` → `/users/{id}`
- `SupportTicket` → `/support-tickets/{id}`
- `Order` → `/orders/{id}`
- `Task` → `/tasks/{id}`
