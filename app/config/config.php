<?php
// config.php
require_once __DIR__ . "/../../vendor/autoload.php";

define("DB_HOST", getenv("DB_HOST"));
define("DB_PORT", getenv("DB_PORT"));
define("DB_NAME", getenv("DB_NAME"));
define("DB_USER", getenv("DB_USER"));
define("DB_PASS", getenv("DB_PASS"));

define("CLOUD_NAME", getenv("CLOUDINARY_CLOUD_NAME"));
define("CLOUD_API_KEY", getenv("CLOUDINARY_API_KEY"));
define("CLOUD_API_SECRET", getenv("CLOUDINARY_API_SECRET"));

define("BASEURL", getenv("BASEURL"));
