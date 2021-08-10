# PHP Video probe

A PHP wrapper for [ffprobe](https://ffmpeg.org/ffprobe.html) to view video file information, metadata and details.

ffprobe gathers information from multimedia streams (video files). With this class you can get this information
outputted in json format for further analysis.

[![PHP 8](https://img.shields.io/badge/PHP-8.0-blue.svg)](https://shields.io/)
[![psr-4 auto loading](https://img.shields.io/badge/autoloading-psr4-green.svg)](https://shields.io/)

# Usage

Install ```video-probe``` class with:

```shell
composer require corbpie/video-probe
```

If [ffprobe](https://ffmpeg.org/ffprobe.html) is not installed:

```shell
sudo apt install ffmpeg
```

Now call the class before use

```php
<?php
require_once('vendor/autoload.php');

$v = new videoProbe();
```


**As of now view examples calls in ```test.php```**