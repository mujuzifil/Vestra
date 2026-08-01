# Phase 5 — Distributor Application Workflow

## Public Submission Flow

```
Visitor lands on /distributor
↓
Reviews partner value, benefits, process
↓
Clicks Apply Now
↓
Completes expanded application form
↓
Frontend validates fields client-side
↓
Frontend submits multipart/form-data to POST /api/v1/distributor
↓
Backend validates StoreDistributorRequest
↓
DistributorService maps payload to DistributorRequest columns
↓
Supporting documents stored in storage/app/public/distributor_documents/{id}
↓
DistributorApplicationSubmitted event fired
↓
DispatchNotificationListener sends:
  • Applicant confirmation (email + in-app)
  • Admin notification to all is_admin users (email + in-app)
↓
API returns success response with request id
↓
Frontend redirects to /distributor/success?ref=VESTRA-DIST-{id}
```

## Field Mapping

| Frontend Field            | Backend Field          | Model Column            |
|---------------------------|------------------------|-------------------------|
| fullName                  | contact_person         | contact_person          |
| businessName              | company_name           | company_name            |
| position                  | position               | business_description    |
| email                     | email                  | email                   |
| phone                     | phone                  | phone                   |
| district                  | district               | region                  |
| physicalAddress           | physical_address       | address                 |
| yearsInBusiness           | years_in_business      | years_in_operation      |
| businessType              | business_type          | business_type           |
| regionsCovered            | regions_covered        | target_region           |
| existingBrands            | existing_brands        | business_description    |
| warehouseAvailability     | warehouse_availability | business_description    |
| deliveryCapability        | delivery_capability    | business_description    |
| additionalInformation     | additional_information | business_description    |
| documents                 | documents[]            | documents (JSON array)  |

## Admin Workflow
- Filament `DistributorRequestResource` lists all applications.
- Admins can view, edit, update status, assign administrator, add internal notes.
- Existing approve / reject / request-info actions remain functional.
- `DistributorOnboardingService::approve()` creates the distributor record and user role.

## Notification Templates
- `distributor.application_submitted` — sent to applicant if a matching user exists.
- `distributor.application_admin_notification` — sent to all admins with reference, contact, and business details.
