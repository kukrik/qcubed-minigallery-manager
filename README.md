# QCubed MiniGallery Plugin

## MiniGallery for QCubed-4

The **MiniGallery plugin** is a reusable QCubed-4 component designed to simplify the insertion of a small set of images (typically two to five), with one image selected as a **cover image**, into content managed via the CKEditor 4 HTML editor.

It provides a clean and structured way to embed, display, store, and remove images, while keeping all business logic fully under the developer’s control.

The plugin is intended for use in articles, news, pages, or any other content type that requires rich media support.

![MiniGallery manager screenshot](screenshot/minigallery_manager_screenhots_1.png?raw=true)
![MiniGallery manager screenshot](screenshot/minigallery_manager_screenhots_2.png?raw=true)
![MiniGallery manager screenshot](screenshot/minigallery_manager_screenhots_3.png?raw=true)

---

## Features

- Image selection and embedding via an external mini gallery manager
- Clear separation between UI interactions and application logic
- Reusable and extensible architecture
- Custom QCubed event handling (e.g. delete actions)
- Compatible with **PHP 8.3+**

---

## How It Works

The MiniGallery plugin appears on the **right side of the CKEditor interface** and allows editors to:

- Select images
- Preview embedded images
- Add a description and author name to each image
- Remove or replace existing images

The plugin focuses solely on **UI interaction and data transport**.  
All validation, permission checks, confirmation dialogs, and database operations are intentionally left to the developer.

---

## Database Requirements

The MiniGallery and FileManager plugins already provide all required database tables via their `database` directories.

To integrate the MiniGallery plugin with your own content types, your content table must include at least the following columns:

- `media_type_id`
- `content_cover_media_id`

In addition, the following tables are expected to exist (provided by the plugins):

- `content_cover_media`
- `mini_gallery`
- `mini_gallery_register`

How these fields and tables are validated, stored, or processed is entirely application-specific.

---

## Usage Notes

It is the responsibility of the developer to implement:

- User permission checks
- Confirmation dialogs (if required)
- Business logic and database consistency
- Additional UI or workflow constraints

---

### Related Tools

For a more advanced media workflow, you may also refer to:

- `mediafinder.php` (from the QCubed-4 FileManager plugin)

---

## Requirements

- **PHP 8.3 or newer**
- **QCubed-4**
- **QCubed FileManager plugin** (required)

---

## Installation

This plugin is installed via Composer:

```bash
composer require kukrik/qcubed-minigallery-manager
```

### Required dependency

The MiniGallery plugin depends on the QCubed-4 FileManager plugin, which must be installed or updated in your project:

```bash
composer require kukrik/qcubed-filemanager
```

### Optional but recommended dependencies

```bash
composer require qcubed-4/plugin-bootstrap
composer require kukrik/qcubed-videomanager
