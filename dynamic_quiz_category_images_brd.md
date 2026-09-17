# BRD — Dynamic Quiz Category Images from Admin Panel

## 1. Business Requirement

Currently, the Flutter application is loading **quiz category images from hardcoded/local data**.

This must be changed.

Category images must **not** be hardcoded inside the Flutter application. The Laravel backend and Admin Panel must become the source of truth for category images.

---

## 2. Required Admin Panel Modification

Inside:

**Admin Panel → Quiz Management → Categories**

the admin must have an option to upload/manage an image for every quiz category.

Example fields:

- Category Name
- Category Slug / ID
- Category Image
- Status
- Existing category fields

Admin must be able to:

- Upload a category image
- Preview the uploaded image
- Replace/update the image
- Remove the image if required
- Save the category

---

## 3. Backend Requirement

The category image path/URL must be stored against the corresponding category in the database.

Example:

```text
Category:
ID: 5
Name: General Knowledge
Image: https://domain.com/uploads/categories/general-knowledge.webp
```

The category API must return the image URL together with the other category information.

Example:

```json
{
  "id": 5,
  "name": "General Knowledge",
  "slug": "general-knowledge",
  "image_url": "https://domain.com/uploads/categories/general-knowledge.webp"
}
```

---

## 4. Flutter Application Requirement

Remove the existing logic where category images are selected from hardcoded/local category data.

The Flutter application must use:

```text
Admin Panel
      ↓
Category Image Upload
      ↓
Laravel Backend / Database
      ↓
Category API
      ↓
image_url
      ↓
Flutter Category UI
```

Whenever the admin changes a category image, the application should show the updated image from backend data without requiring a Flutter code change.

---

## 5. Shared Hosting / Image Storage

Because the Laravel project is running on shared hosting, category images should be stored in a location that is publicly accessible through the website.

Recommended structure:

```text
public/uploads/categories/
```

or another publicly accessible category-image directory configured by the project.

The API must return a complete publicly accessible HTTPS image URL.

Do not make the Flutter application depend on a server-side/private storage path that cannot be accessed publicly.

---

## 6. Existing Categories

Existing categories must remain intact.

During implementation:

- Do not delete existing categories.
- Add image support to existing category records.
- Existing hardcoded images can temporarily act as migration references if needed.
- Once backend images are configured, remove the hardcoded category-image mapping from Flutter.

---

## 7. Fallback

If a category does not yet have an uploaded image, Flutter may show one generic placeholder image.

The fallback must **not** contain separate hardcoded mappings such as:

```text
GK → image_1
Science → image_2
Math → image_3
```

Only one generic fallback/placeholder is acceptable.

---

## 8. Acceptance Criteria

This requirement is complete when:

1. Every quiz category can have an image managed from the Admin Panel.
2. Admin can upload, preview, update, and remove category images.
3. The image is associated with the correct category in the backend.
4. Category API returns a publicly accessible image URL.
5. Flutter loads category images from API/backend data.
6. Existing hardcoded category-image mapping is removed.
7. Changing an image from the Admin Panel does not require a new Flutter code change.
8. Existing categories and their quiz relationships are not affected.
9. Missing images use only a generic fallback.
10. Category images work correctly on the shared-hosting production environment.

---

## Critical Instruction for Development / AI Agent

> **Do not fetch quiz category images from hardcoded Flutter data.**

The source of truth must be:

**Admin Panel → Quiz Category → Category Image → Laravel API → Flutter App**

Category names, IDs/slugs, images, and other server-managed category information should remain backend-driven wherever applicable.
