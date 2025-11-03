# Inquiry Flow

This document describes the end-to-end flow of a property inquiry across Client, Broker, and Admin roles, including real-time updates.

## Actors

-   Client: a user submitting an inquiry for a property
-   Broker: the assigned or property broker managing the inquiry
-   Admin: platform administrator with full visibility

## Lifecycle Stages

1. New

    - How it happens: A client submits an inquiry from a property page or the client portal.
    - System behavior:
        - Inquiry is saved with status "new".
        - If the client has an assigned broker, the inquiry is auto-assigned.
        - Event broadcast: `inquiry.new` on `private-inquiries` (+ `private-broker.{id}` when available).
        - Client is redirected to the inquiry detail page with a confirmation flash.

2. Contacted

    - How it happens: Broker responds to the inquiry and sets status to "contacted".
    - System behavior:
        - Inquiry is updated with `broker_response`, `responded_at`, optional `contacted_at`.
        - Event broadcast: `inquiry.status.updated` on:
            - `private-inquiries` (broker/admin list)
            - `private-client.{client_id}` (client list/detail)
            - `private-broker.{broker_id}` (assigned broker)
            - `private-inquiry.{inquiry_id}` (fine-grained channel)

3. Scheduled (optional)

    - How it happens: Broker proposes/sets a schedule for viewing or follow-up.
    - System behavior:
        - Inquiry is updated with `scheduled_at`.
        - Event broadcast: `inquiry.status.updated` on the same channels as above.

4. Completed / Closed

    - How it happens: After the engagement is done, broker marks the inquiry as completed or closed.
    - System behavior:
        - Inquiry status changes to `completed` or `closed`.
        - Event broadcast: `inquiry.status.updated` on the same channels as above.

5. Transaction (optional, when converting)
    - How it happens: Broker accepts/creates a Transaction from the inquiry.
    - System behavior:
        - A `Transaction` is created and linked to the inquiry.
        - Conversation (if any) is transitioned to the transaction context.

## Real-time Behavior

-   Brokers/Admins subscribe to `private-inquiries` and receive:
    -   `inquiry.new` for newly created inquiries (toast notification + list refresh)
    -   `inquiry.status.updated` for updates (toast notification + inline list update)
-   Clients subscribe to `private-client.{client_id}` and receive:
    -   `inquiry.status.updated` and refresh the relevant list row or the detail view
-   Optional per-inquiry channel `private-inquiry.{inquiry_id}` is authorized for client, assigned/property broker, or admin.

## Auto-normalization and soft guard (transaction creation)

-   Creating a Transaction is allowed at any time (no hard block).
-   When a Transaction is created from an Inquiry, the system auto-normalizes:
    -   Sets `status = in transaction`
    -   Sets `responded_at = now()` if it was null
    -   If moving from `new`, sets `contacted_at = now()` (keeps any existing `scheduled_at`)
    -   Broadcasts `inquiry.status.updated` so UIs update instantly
-   If the Inquiry already has a Transaction, the user is redirected to it (prevents duplicates).
-   Soft guard: If the Inquiry is `new` with no `broker_response`, a small confirm prompts the broker to proceed or add a quick response first.

## UX Notes

-   Broker UI includes a streamlined Respond/Update panel with quick status buttons and message templates.
-   Client UI includes a "How inquiries work" panel on index and a real-time detail page update when broker changes status.
-   Broker/Admin index shows a dismissible toast stack for real-time events.

## Event Contracts

-   `inquiry.new` payload:
    -   `inquiry`: { id, name, email, phone, inquiry_type, status, created_at, property: { id, title, municipality, price } }
-   `inquiry.status.updated` payload:
    -   `inquiry_id`, `previous_status`, `new_status`, `updated_by`, `updated_at`
    -   `inquiry`: { id, name, email, status, property: { id, title } }

## Edge Cases

-   If a client does not yet have a linked Client record, it's created/linked on first use.
-   If a list view doesn’t include the updated inquiry (due to filters or pagination), the UI triggers a partial reload.
-   Channel authorization ensures only participants (client, assigned/property broker) or admin can access per-inquiry updates.

## Troubleshooting

-   Ensure broadcasting is configured (Pusher or Laravel WebSockets) and Echo connects.
-   Verify channel auth routes in `routes/channels.php` are correct and roles are accurate.
-   Check the browser console for Echo connection status and events.
