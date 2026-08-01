# Phase 9 — Contact Architecture

## Existing Backend

- `POST /api/v1/contact` handled by `ContactController`.
- `ContactMessage` model with `ContactService`, `ContactRepository`, and Filament `ContactMessageResource`.

## Enhancements

### Enquiry Type

- New enum `App\Enums\ContactEnquiryType`:
  - general
  - sales
  - distributor
  - quote
  - technical_support
  - other

### CRM Fields Migration

Added to `contact_messages`:

- `company` — optional company name.
- `enquiry_type` — categorisation.
- `attachments` — JSON array of stored file paths.
- `assigned_to` — foreign key to `users` for staff assignment.
- `internal_notes` — private admin notes.
- `source` — default `website`.
- `ip_address` and `user_agent` — submission metadata.

### Validation

- `StoreContactRequest` validates new fields and optional attachments (max 5, PDF/JPG/PNG/DOC/DOCX, 5 MB each).

### Service Behaviour

- `ContactService::submit` stores metadata, saves attachments to `contact_attachments/{id}` on the public disk, sends a customer confirmation email, sends an admin notification email, and dispatches `ContactMessageSubmitted`.

### Mail

- `ContactReceivedMail` — customer confirmation.
- `ContactAdminNotificationMail` — internal alert.

### Filament

- `ContactMessageResource` updated to display company, enquiry type, attachments, assigned staff, and internal notes.
- New enquiry type filter added to the table.

## Frontend

- `ContactFormData` type extended with company, phone, enquiry_type, and attachments.
- `frontend/lib/api/contact.ts` switched to `apiPostFormData` for multipart uploads.
- `ContactForm` redesigned with enquiry type dropdown and file upload.
- `/contact` page rebuilt with hero, methods, map, social links, FAQ, resources, and final CTA.

## CRM Readiness

- Status, priority, assignment, internal notes, and source fields are in place.
- The `ContactMessageSubmitted` event can be wired to future ticket/CRM integrations.
