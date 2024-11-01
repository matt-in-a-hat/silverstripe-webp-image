# silverstripe-webp-image

[![Build Status](https://travis-ci.org/loveduckie/silverstripe-webp-image.svg?branch=master)](https://travis-ci.org/loveduckie/silverstripe-webp-image)
[![License](https://poser.pugx.org/loveduckie/silverstripe-webp-image/license)](https://packagist.org/packages/loveduckie/silverstripe-webp-image)

## Overview

This SilverStripe module optimizes image assets by automatically converting them to the `.webp` format on request. Learn more about the benefits of WebP images [here](https://developers.google.com/speed/webp). For details on modifications in this fork and the reasoning behind them, refer to [Tips for Optimizing Page Speeds](https://lucshelton.com/blog/tips-for-optimizing-page-speeds/).

## NGINX Configuration

This module is designed to create `.webp` images with filenames structured as `file_name_goes_here.<original extension>.webp`. This enables NGINX to serve these `.webp` files in place of the original images, enhancing performance. Below is a sample NGINX configuration:

```nginx
map $http_accept $webp_suffix {
  default   "";
  "~*webp"  ".webp";
}

location ~* /assets/.+\.(?<extension>jpe?g|png|gif|webp)$ {
    gzip_static on;
    gzip_types image/png image/x-icon image/webp image/svg+xml image/jpeg image/gif;

    add_header Vary Accept;
    expires max;
    sendfile on;
    try_files "${request_uri}${webp_suffix}" $uri =404;
}
```

This configuration uses the `$webp_suffix` variable to serve `.webp` files if the client supports WebP in its `Accept` header. If the `.webp` file is unavailable, NGINX will serve the original image.

## Features

This module generates `.webp` versions of resized JPEG and PNG images, improving page load times with minimal configuration.

## Requirements

- SilverStripe > 4.2
- GD Library with WebP extension enabled

## Installation

Install the module using Composer:

```shell
composer require loveduckie/silverstripe-webp-image
```

## Usage

1. Run `dev/build?flush=1` to initialize.
2. Configure web server to prioritize `.webp` images if available.

### Browser Support Configuration

#### Option 1: `.htaccess` Configuration

To enable WebP support in compatible browsers, update your root `.htaccess` file with WebP-specific rules.

#### Option 2: HTML Implementation

For more information on WebP usage in HTML, visit [CSS-Tricks](https://css-tricks.com/using-webp-images/).

## Quick WebP Support Test

To check if WebP support is available via the GD Library, copy the following code into a `.php` file in your root directory and open it in a browser:

```php
<?php

if (function_exists('imagewebp')) {
    echo "WebP is available";
} else {
    echo "WebP is not available";
}
```

## Roadmap

- Enhanced documentation
- Support for Imagick
- PHP tests to verify WebP support
- Automatic deletion of WebP images
- WebP image flush functionality
