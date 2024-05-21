# Media Library
This package provides a set of functions for handling media files, including images, audio, video, and PDF files. 

- [Media Library](#media-library)
  - [Features](#features)
  - [Installation](#installation)
  - [Usage](#usage)
    - [Initialization](#initialization)
    - [Save Image](#save-image)
    - [Create Temporary Image](#create-temporary-image)
    - [Convert Temporary Image](#convert-temporary-image)
    - [Delete Temporary Image](#delete-temporary-image)
    - [Save Audio File](#save-audio-file)
    - [Save Video File](#save-video-file)
    - [Save Document File](#save-document-file)
  - [Media Object](#media-object)
  - [Rules](#rules)
    - [Square Image](#square-image)
  - [Troubleshooting and Collaboration](#troubleshooting-and-collaboration)

## Features
- **Versatile Storage Options**: Seamlessly store and manage your files in both public and private storage environments.
- **Image Conversion Made Easy**: Effortlessly convert and store images in multiple formats to suit all your needs.
- **Automatic Thumbnail Generation**: Instantly create and manage image thumbnails upon upload, saving you time and effort.
- **Universal File Storage**: Store and organize any file type, including documents, videos, and audio files.
- **Soft Delete Support**: Safeguard your data with soft delete functionality, allowing you to restore accidentally deleted files easily.
- **URL Generation**: Instantly generate shareable URLs for your files, making it simple to share and access your stored content.
- **Image Upload Rules**: Ensure optimal performance and quality with customizable image upload rules, including dimensions, size, and format restrictions.

## Installation

To install the `DoniaShaker\MediaLibrary` package, follow these steps:

1. Add the package to your Laravel project using Composer:
   ```shell
   composer require donia-shaker/media-library
   ```

2. Publish the Database migrations and config to your project:
   ```shell
   php artisan vendor:publish --tag=media-library-migrations
   php artisan vendor:publish --tag=media-library-config
   ```

3. If you wish to use a default format other than the default webp format, edit the following line in `config/media-library.php`
   ```php
   default_image_format = 'webp' // <-- change this value
   ```

4. Add `ENV` variables required by the package config
   ```env
   MEDIA_USE_STORAGE=true|false
   ```

5. Run the package's migrations to create the necessary database tables:
   ```shell
   php artisan migrate
   ```



## Usage
### Initialization

1. use the package 
	```php
	use DoniaShaker\MediaLibrary\MediaController;
	```
2. Call the Constructor
	```php
	$media_controller =  new MediaController();
	```
3. in the following examples, the `$format` field can be null, thus using the `default_image_format` value defined from the config.

### Save Image

This function saves an image file to the media library, generates a thumbnail, and associates it with the current model.
Usage:
```php
$media_controller->saveImage($model, $model_id, $file, $format);
```

### Create Temporary Image
This function creates a temporary image file in the media library. 

Usage:
```php
$media_controller->saveTempImage($model, $model_id, $file);
```

### Convert Temporary Image
This function converts a temporary image file to a normal image file in the media library, deletes the temporary file and record, and associates the new image with the current model. 

Usage:
```php
$media_controller->convertTempImage($model, $model_id, $media->id);
```

### Delete Temporary Image

This function deletes a temporary image file and record from the media library. 

Usage:
```php
$media_controller->deleteTemp();
```

### Save Audio File

This function saves an audio file to the media library and associates it with the current model.

Usage:
```php
$media_controller->audio($model, $model_id, $file);
```

### Save Video File

This function saves a video file to the media library and associates it with the current model. 
Usage:
```php
$media_controller->video($model, $model_id, $file);
```

### Save Document File

This function saves a document file to the media library and associates it with the current model.
Usage:
```php
$media_controller->uploadFile($model, $model_id, $file);
```

## Media Object

Explanation of the media object properties:

- `id`: The unique identifier of the media object.
- `model`: The model associated with the media object (in this case, "slider").
- `format`: The file format of the media object.
- `model_id`: The ID of the associated model (in this case, 2).
- `order`: The order of the media object (in case `model` has many `media`).
- `file_name`: The unique file name of the media object.
- `has_thumb`: Indicates whether the media object has a thumbnail (1 for true, 0 for false).
- `is_active`: Indicates whether the media object is active (1 for true, 0 for false).
- `is_temp`: Indicates whether the media object is temporary (1 for true, 0 for false).
- `deleted_at`: The timestamp indicating when the media object was deleted (null if not deleted).
- `created_at`: The timestamp indicating when the media object was created.
- `updated_at`: The timestamp indicating when the media object was last updated.
- `url`: The URL to access the original image file including temp.
- `thumb_url`: The URL to access the thumbnail image file (if available).

The media object represents a media file stored in the media library. It contains various properties providing information about the file, such as its associated model, file format, URLs for accessing the image and thumbnail, and timestamps for creation and updates.

## Rules
### Square Image


This function saves a document file to the media library and associates it with the current model. It accepts the `$file` parameter, which represents the uploaded document file.

Usage:
```php
use DoniaShaker\MediaLibrary\Rules\SquareImageRule;

'file' => [.., new  SquareImageRule],
```

## Troubleshooting and Collaboration

If you encounter any issues or have any suggestions, please feel free to [open an issue](https://github.com/donia-shaker/media-library/issues/new/choose) on GitHub.