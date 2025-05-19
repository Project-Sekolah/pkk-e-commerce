<?php
// Load environment and dependencies
require_once "config/config.php";
require_once "core/App.php";
require_once "core/Controller.php";
require_once "core/Database.php";
require_once "core/Flasher.php";

use Cloudinary\Cloudinary;

// Start session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

echo "<pre>";
echo "CLOUDINARY_CLOUD_NAME: " . getenv("CLOUDINARY_CLOUD_NAME") . PHP_EOL;
echo "CLOUDINARY_API_KEY: " . getenv("CLOUDINARY_API_KEY") . PHP_EOL;
echo "CLOUDINARY_API_SECRET: " . getenv("CLOUDINARY_API_SECRET") . PHP_EOL;
echo "</pre>";

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
