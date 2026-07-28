# Plan: Contact Entries & Product-Based Voucher Selection

## 1. Database & Model Updates

### `Product` Model (New)
*   **Table `products`**:
    *   `id` (PK)
    *   `event_id` (FK to `events`)
    *   `name` (string)
    *   `image_path` (string, for the portrait image)
    *   `timestamps`
*   **Relationships**: Belongs to `Event`, Has Many `Voucher`s.
*   **Traits**: `HasFactory`.

### `Voucher` Model (Update)
*   **Add field**: `product_id` (FK to `products`, nullable) to link the voucher to a specific product.

### Contact & Event Pivot Table (New)
*   **Table `contact_event`** (Intermediate table):
    *   `id` (PK)
    *   `contact_id` (FK to `contacts`)
    *   `event_id` (FK to `events`)
    *   `entries` (integer, default: 1) - Dictates how many times the contact can pick a product for the given event.
    *   `timestamps`
*   **Relationships**: `Contact` belongsToMany `Event` (withPivot `entries`), `Event` belongsToMany `Contact`.

## 2. Admin Interface Updates

### Products Management
*   Add a new section under Event management to create/edit/delete `Products`.
*   Fields: Name, Image Upload (no specific dimension constraints required).

### Voucher Bulk Upload
*   Update the `VoucherUploadController` and `admin/vouchers/upload.blade.php`.
*   Add a "Product" dropdown to the upload form so the admin can specify which product all the uploaded vouchers belong to (setting their `product_id`).

### Contact Management & Import
*   Update `Admin/ContactController` and the contact edit views.
*   Add the ability to set `entries` per event for a contact (saving to the `contact_event` pivot table).
*   **CRITICAL CHANGE**: Disable the auto-assignment of vouchers during the Contact Import process (`ContactImporter::importMany`). Users must now choose their products manually via the frontend.

## 3. Frontend / User Flow

### Voucher Selection Page
*   Update the page where users see their assigned vouchers.
*   **Logic**:
    1. Count how many vouchers the contact currently has assigned to them for this event (`$claimedCount`).
    2. Number of remaining entries = `ContactEvent->entries` - `$claimedCount`.
    3. If they have remaining entries, display the list of `Products` for the event as clickable cards (showing the portrait image and name).
    4. Provide a mechanism (e.g., a form POST) to "Select this Product".
    5. Show their already-claimed vouchers underneath.

### Claim Endpoint
*   **Logic**:
    1. Validate the user has remaining entries for the given event.
    2. Find the `Product` they selected.
    3. Find an available `Voucher` for the Event where `product_id` matches the Product's ID, `contact_id` is null, and `status` is active.
    4. Assign that `Voucher` to the `Contact`.
    5. Redirect back to the selection page, where they will see the assigned voucher and 1 less remaining entry.
