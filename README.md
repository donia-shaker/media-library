# Media Library

This package provides a set of functions for handling media files, including images, audio, video, PDF files, and other documents.

- [Media Library](#media-library)
  - [Features](#features)
  - [Installation](#installation)
    - [1. Install the package](#1-install-the-package)
    - [2. Publish migrations and config](#2-publish-migrations-and-config)
    - [3. Configure the default image format](#3-configure-the-default-image-format)
    - [4. Configure environment variables](#4-configure-environment-variables)
    - [5. Run migrations](#5-run-migrations)
  - [Usage](#usage)
    - [Initialization](#initialization)
    - [Save Image](#save-image)
    - [Create Temporary Image](#create-temporary-image)
    - [Convert Temporary Image](#convert-temporary-image)
    - [Delete Temporary Image](#delete-temporary-image)
    - [Save Audio File](#save-audio-file)
    - [Save Video File](#save-video-file)
    - [Save Document File](#save-document-file)
  - [Private Media](#private-media)
    - [Save Private Image](#save-private-image)
    - [Save Private Document](#save-private-document)
    - [Save Private Audio](#save-private-audio)
    - [Save Private Video](#save-private-video)
  - [Private Media Authentication Guard](#private-media-authentication-guard)
  - [Private Media URL](#private-media-url)
  - [Access Private Media From Frontend](#access-private-media-from-frontend)
    - [Bearer Token Authentication](#bearer-token-authentication)
    - [JavaScript Fetch](#javascript-fetch)
    - [Vue](#vue)
    - [React](#react)
    - [Axios](#axios)
    - [Authenticated API Client](#authenticated-api-client)
  - [Private Thumbnail](#private-thumbnail)
  - [Public vs Private Media](#public-vs-private-media)
    - [Public Media](#public-media)
    - [Private Media](#private-media-1)
  - [Media Object](#media-object)
  - [Rules](#rules)
    - [Square Image](#square-image)
  - [Troubleshooting and Collaboration](#troubleshooting-and-collaboration)

## Features

- **Public and Private Storage**: Store media in publicly accessible storage or protected private storage.
- **Image Conversion**: Convert and store images in multiple formats.
- **Automatic Thumbnail Generation**: Automatically generate thumbnails when enabled.
- **Universal File Storage**: Store images, documents, PDFs, videos, audio files, and other file types.
- **Soft Delete Support**: Restore accidentally deleted media records when needed.
- **Public and Protected URL Generation**: Generate direct URLs for public media and signed authenticated URLs for private media.
- **Image Upload Rules**: Apply customizable dimensions, size, and format validation rules.
- **Authentication Support**: Protect private media using configurable Laravel authentication guards.
- **Frontend Independent**: Private media can be consumed from Vue, React, Angular, mobile applications, or any client capable of sending authenticated HTTP requests.

## Installation

To install the `DoniaShaker\MediaLibrary` package, follow these steps.

### 1. Install the package

```shell
composer require donia-shaker/media-library
```

### 2. Publish migrations and config

```shell
php artisan vendor:publish --tag=media-library-migrations
php artisan vendor:publish --tag=media-library-config
```

### 3. Configure the default image format

Edit:

```text
config/media.php
```

Example:

```php
'default_image_format' => 'webp',
```

### 4. Configure environment variables

```env
MEDIA_USE_STORAGE=true
MEDIA_CREATE_THUMBNAILS=true
```

Private media uses the `sanctum` authentication guard by default.

If your project uses another authentication guard, such as JWT with an `api` guard:

```env
MEDIA_PRIVATE_AUTH_GUARD=api
```

### 5. Run migrations

```shell
php artisan migrate
```

## Usage

### Initialization

Import the controller:

```php
use DoniaShaker\MediaLibrary\MediaController;
```

Create an instance:

```php
$media_controller = new MediaController();
```

The `$format` parameter can be `null`. In this case, the package uses the configured `default_image_format`.

### Save Image

Saves an image, optionally resizes it, controls image quality, and generates a thumbnail when enabled.

```php
$media_controller->saveImage($model, $model_id, $file, $format, $maxWidth, $maxHeight, $quality);
```

`$format`, `$maxWidth`, `$maxHeight`, and `$quality` are optional.

### Create Temporary Image

Creates a temporary image.

```php
$media_controller->saveTempImage($model, $model_id, $file);
```

### Convert Temporary Image

Converts a temporary image into a normal media file and associates it with a model.

```php
$media_controller->convertTempImage($model, $model_id, $media->id);
```

### Delete Temporary Image

Deletes a temporary image and its media record.

```php
$media_controller->deleteTemp();
```

### Save Audio File

Saves an audio file and associates it with a model.

```php
$media_controller->audio($model, $model_id, $file);
```

### Save Video File

Saves a video file and associates it with a model.

```php
$media_controller->video($model, $model_id, $file);
```

### Save Document File

Saves a document or other uploaded file and associates it with a model.

```php
$media_controller->uploadFile($model, $model_id, $file);
```

## Private Media

Media can be stored as either:

```text
public
```

or:

```text
private
```

The default visibility is:

```text
public
```

Existing package usage therefore remains backward compatible.

Private files are stored outside the publicly accessible storage directory and are served through an authenticated signed route.

To save private media, pass:

```php
'private'
```

as the visibility parameter.

### Save Private Image

```php
$media_controller->saveImage($model, $model_id, $file, $format, $maxWidth, $maxHeight, $quality, 'private');
```

Example:

```php
$media_controller->saveImage('order', $order->id, $file, null, 1600, 1600, 75, 'private');
```

### Save Private Document

```php
$media_controller->uploadFile($model, $model_id, $file, 'private');
```

Example:

```php
$media_controller->uploadFile('order_receipt_invoice', $order->id, $file, 'private');
```

### Save Private Audio

```php
$media_controller->audio($model, $model_id, $file, 'private');
```

### Save Private Video

```php
$media_controller->video($model, $model_id, $file, 'private');
```

## Private Media Authentication Guard

Private media authentication is controlled by:

```php
'privateAuthGuard' => env('MEDIA_PRIVATE_AUTH_GUARD', 'sanctum'),
```

The default guard is:

```text
sanctum
```

If the application already uses Sanctum, no additional configuration is required.

For a JWT project using the `api` guard:

```env
MEDIA_PRIVATE_AUTH_GUARD=api
```

For another custom guard:

```env
MEDIA_PRIVATE_AUTH_GUARD=delegate
```

The package internally authenticates the private media request using the configured guard.

Conceptually:

```php
Auth::guard(config('media.privateAuthGuard', 'sanctum'))->user();
```

The frontend does not need to know the guard name.

The frontend only needs to send the authentication credentials required by the application.

## Private Media URL

Private media URLs are generated automatically from the `url` attribute.

```php
$media->url;
```

Example response:

```text
https://example.com/api/media/private/10?auth_id=1&signature=...
```

The package automatically generates:

- Media ID
- Authenticated user ID
- Signed URL signature

The application should not manually create or modify `auth_id` or `signature`.

For example:

```php
'image' => $media->url,
```

may return:

```json
{
    "image": "https://example.com/api/media/private/10?auth_id=1&signature=..."
}
```

The signed URL is bound to the authenticated user for whom it was generated.

When the private media endpoint is requested, the package verifies:

1. The signed URL is valid.
2. The media record is private.
3. The request contains a valid authenticated user.
4. The authenticated user's ID matches the signed `auth_id`.
5. The physical private file exists.

If any verification fails, the endpoint returns:

```http
404 Not Found
```

## Access Private Media From Frontend

### Bearer Token Authentication

When using JWT, Sanctum API tokens, or another Bearer Token authentication system, private media must be requested with:

```http
Authorization: Bearer YOUR_TOKEN
```

Do not directly use the private URL like this with JWT:

```html
<img src="PRIVATE_MEDIA_URL">
```

A normal `<img>` request does not automatically attach a Bearer Token.

Instead, request the private URL through an authenticated HTTP request.

### JavaScript Fetch

```js
const response = await fetch(media.url, { headers: { Authorization: `Bearer ${token}` } });
```

Convert the response to a Blob:

```js
const blob = await response.blob();
```

Create a browser URL:

```js
const imageUrl = URL.createObjectURL(blob);
```

Display it:

```html
<img src="BLOB_URL">
```

Complete example:

```js
const response = await fetch(media.url, { headers: { Authorization: `Bearer ${token}` } });
const blob = await response.blob();
const imageUrl = URL.createObjectURL(blob);
```

### Vue

```js
const response = await fetch(media.url, { headers: { Authorization: `Bearer ${token}` } });
const blob = await response.blob();
imageUrl.value = URL.createObjectURL(blob);
```

Then:

```vue
<img :src="imageUrl">
```

### React

```js
const response = await fetch(media.url, { headers: { Authorization: `Bearer ${token}` } });
const blob = await response.blob();
const imageUrl = URL.createObjectURL(blob);
```

Then:

```jsx
<img src={imageUrl} alt="" />
```

### Axios

```js
const response = await axios.get(media.url, { headers: { Authorization: `Bearer ${token}` }, responseType: 'blob' });
```

Then:

```js
const imageUrl = URL.createObjectURL(response.data);
```

### Authenticated API Client

If the application already has an HTTP client that automatically sends the authentication token, the token does not need to be manually added again.

Example:

```js
const blob = await $api(media.url, { responseType: "blob" });
```

Then:

```js
const imageUrl = URL.createObjectURL(blob);
```

The important requirement is that the request reaching the private media endpoint contains the application's authentication credentials.

For JWT or Bearer Token authentication, the request must contain:

```http
Authorization: Bearer YOUR_TOKEN
```

## Private Thumbnail

Private thumbnails are also returned as protected signed URLs.

Example:

```php
$media->thumb_url;
```

A private thumbnail may return:

```text
https://example.com/api/media/private/10?auth_id=1&thumb=1&signature=...
```

It should be requested using the same authenticated process as the original private media file.

Example:

```js
const response = await fetch(media.thumb_url, { headers: { Authorization: `Bearer ${token}` } });
const blob = await response.blob();
const thumbnailUrl = URL.createObjectURL(blob);
```

## Public vs Private Media

### Public Media

Public media can be displayed directly.

```vue
<img :src="media.url">
```

Example URL:

```text
https://example.com/storage/media/images/order/10-image.webp
```

No authenticated media request is required.

### Private Media

Private media is accessed through a protected API endpoint.

```text
media.url
    ↓
Authenticated HTTP Request
    ↓
Authorization: Bearer TOKEN
    ↓
Signed URL Validation
    ↓
User Authentication
    ↓
User Identity Validation
    ↓
Private File
    ↓
Blob
    ↓
<img>
```

In short:

```text
Public  -> Use media.url directly
Private -> Request media.url with authentication
```

## Media Object

Explanation of the media object properties:

- `id`: The unique identifier of the media object.
- `model`: The model associated with the media object.
- `format`: The file format.
- `model_id`: The ID of the associated model.
- `order`: The order of the media object when a model has multiple media files.
- `file_name`: The unique file name.
- `has_thumb`: Indicates whether a thumbnail exists.
- `is_active`: Indicates whether the media is active.
- `is_temp`: Indicates whether the media is temporary.
- `visibility`: Defines whether the media is `public` or `private`.
- `deleted_at`: The media deletion timestamp.
- `created_at`: The media creation timestamp.
- `updated_at`: The media last update timestamp.
- `url`: Public URL for public media or signed authenticated URL for private media.
- `thumb_url`: Public thumbnail URL or protected signed thumbnail URL when available.

Example:

```json
{
    "id": 10,
    "model": "order",
    "model_id": 500,
    "format": "webp",
    "visibility": "private",
    "url": "https://example.com/api/media/private/10?auth_id=1&signature=...",
    "thumb_url": "https://example.com/api/media/private/10?auth_id=1&thumb=1&signature=..."
}
```

## Rules

### Square Image

Use `SquareImageRule` to validate that an uploaded image is square.

```php
use DoniaShaker\MediaLibrary\Rules\SquareImageRule;
```

Usage:

```php
'file' => [..., new SquareImageRule],
```

## Troubleshooting and Collaboration

If you encounter any issues or have suggestions, please feel free to [open an issue](https://github.com/donia-shaker/media-library/issues/new/choose) on GitHub.