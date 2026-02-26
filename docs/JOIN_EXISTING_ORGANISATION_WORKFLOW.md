# Join Existing Organisation — Workflow & Database Guide

This document describes the **Join Existing Organisation** flow: tables affected, status handling, and how to verify join requests in the database.

---

## 1. Workflow Overview

```
User (requester) → POST /api/v2/organisations/join
    ↓
1. Authorisation check (requestJoinExisting policy)
2. Find organisation by organisation_uuid
3. Attach user to organisation via pivot with status='requested'
4. Dispatch OrganisationUserJoinRequestEvent
    ↓
5a. OrganisationUserJoinRequestNotification → creates in-app notification for each owner
5b. OrganisationUserJoinRequestSendEmail → sends email to each owner
```

---

## 2. Tables Involved

### 2.1 Primary: `organisation_user` (pivot table)

**Purpose:** Stores the link between users and organisations and the join-request status.

| Column          | Type             | Description                                      |
|-----------------|------------------|--------------------------------------------------|
| `id`            | bigint unsigned  | Primary key                                      |
| `user_id`       | bigint unsigned  | FK → `users.id` (the user requesting to join)    |
| `organisation_id` | bigint unsigned | FK → `organisations.id`                          |
| `status`        | varchar(30)      | `'requested'` (pending), `'approved'`, `'rejected'` |

**How the join request is stored:**

When a user requests to join, a row is created or updated:

- **INSERT:** New row with `status = 'requested'` if none exists.
- **UPDATE:** Existing row’s `status` is set to `'requested'` if the user had previously joined/been rejected.

```sql
-- Check join requests for an organisation
SELECT ou.*, u.email_address, u.first_name, u.last_name
FROM organisation_user ou
JOIN users u ON u.id = ou.user_id
WHERE ou.organisation_id = <organisation_id>
  AND ou.status = 'requested';
```

---

### 2.2 Secondary: `notifications`

**Purpose:** In-app notifications for organisation owners.

| Column               | Description                                         |
|----------------------|-----------------------------------------------------|
| `user_id`            | Owner who receives the notification                 |
| `title`              | "A user has requested to join your organization"    |
| `body`               | "A user has requested to join your organization. Please go to the 'Meet the Team' page to review this request." |
| `action`             | `'user_join_organisation_requested'`                |
| `referenced_model`   | `App\Models\V2\Organisation`                        |
| `referenced_model_id`| Organisation ID                                     |

```sql
-- Check notifications for a join request
SELECT * FROM notifications
WHERE action = 'user_join_organisation_requested'
  AND referenced_model_id = <organisation_id>
ORDER BY created_at DESC;
```

---

### 2.3 Tables NOT Modified

- **`organisations`** — Organisation status (draft, pending, approved, rejected) is **not** changed by join requests.
- **`users`** — No changes.

---

## 3. Status Flow

### Organisation entity vs. user–organisation link

- **Organisation status** (`organisations` / `organisation_versions`): `draft`, `pending`, `approved`, `rejected` — for org approval. **Unchanged by join requests.**
- **User–organisation link status** (`organisation_user.status`): `requested`, `approved`, `rejected` — for join requests.

### Lifecycle of `organisation_user.status`

```
User requests join  →  status = 'requested'
        ↓
Owner approves     →  status = 'approved'  (OrganisationUserRequestApprovedEvent)
Owner rejects      →  status = 'rejected'  (OrganisationUserRequestRejectedEvent)
```

---

## 4. Email Recipients

**Who receives the email when someone requests to join?**

All **organisation owners**.

Owners are users whose primary organisation is this one: `users.organisation_id = organisations.id`.

```php
// From OrganisationUserJoinRequestSendEmail listener
$emailAddressList = $organisation->owners;
foreach ($emailAddressList as $user) {
    Mail::to($user->email_address)->send(new OrganisationUserJoinRequested($user));
}
```

```sql
-- List owners (email recipients) for an organisation
SELECT u.id, u.email_address, u.first_name, u.last_name
FROM users u
WHERE u.organisation_id = '872ed2e2-d88a-42e0-8564-bebd7d49eb51';
```

---

## 5. Quick DB Checks

### See pending join requests for an organisation

```sql
SELECT
    ou.id AS pivot_id,
    ou.user_id,
    ou.organisation_id,
    ou.status,
    ou.created_at,
    u.email_address AS requester_email,
    u.first_name,
    u.last_name
FROM organisation_user ou
JOIN users u ON u.id = ou.user_id
WHERE ou.organisation_id = (
    SELECT id FROM organisations WHERE uuid = '<organisation_uuid>'
)
  AND ou.status = 'requested';
```

### See who receives the notification/email

```sql
SELECT u.id, u.email_address, u.first_name, u.last_name
FROM users u
WHERE u.organisation_id = (
    SELECT id FROM organisations WHERE uuid = '<organisation_uuid>'
);
```

---

## 6. Summary

| Aspect                | Details                                                                 |
|-----------------------|-------------------------------------------------------------------------|
| **Main table**        | `organisation_user` — stores `user_id`, `organisation_id`, `status`     |
| **Org status changed?** | No — `organisations` / `organisation_versions` are unchanged           |
| **Join request storage** | `organisation_user` row with `status = 'requested'`                   |
| **Email recipients**  | All organisation owners (`users.organisation_id = organisations.id`)    |
| **In-app notifications** | One per owner in `notifications` with `action = 'user_join_organisation_requested'` |
