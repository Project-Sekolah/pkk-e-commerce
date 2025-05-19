<?php

// Load environment and dependencies
require_once __DIR__ . "/../vendor/autoload.php";

use Dotenv\Dotenv;

// Pakai createMutable
$dotenv = Dotenv::createMutable(__DIR__ . "/../");
$dotenv->load();

// Ini yang penting: push semua $_ENV ke getenv()
foreach ($_ENV as $key => $value) {
  putenv("$key=$value");
}



use Cloudinary\Cloudinary;
require_once "config/config.php";
require_once "core/App.php";
require_once "core/Controller.php";
require_once "core/Database.php";
require_once "core/Flasher.php";

// Inisialisasi Cloudinary jika konstanta tersedia

if (
  defined("CLOUD_NAME") &&
  defined("CLOUD_API_KEY") &&
  defined("CLOUD_API_SECRET")
) {
  $cloudinary = new Cloudinary([
    "cloud" => [
      "cloud_name" => CLOUD_NAME,
      "api_key" => CLOUD_API_KEY,
      "api_secret" => CLOUD_API_SECRET,
    ],
    "url" => [
      "secure" => true,
    ],
  ]);

  // Simpan instance ke variabel global
  $GLOBALS["cloudinary"] = $cloudinary;
}
