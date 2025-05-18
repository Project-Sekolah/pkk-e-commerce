<?php
// Error reporting
//error_reporting(E_ALL);
//ini_set('display_errors', 1);


require_once 'config/config.php';

require_once 'core/App.php';
require_once 'core/Controller.php';
require_once 'core/Database.php';
require_once 'core/Flasher.php';
require_once '../vendor/autoload.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}



// Cloudinary instance using defined constants
use Cloudinary\Cloudinary;

if (defined('CLOUD_NAME') && defined('CLOUD_API_KEY') && defined('CLOUD_API_SECRET')) {
    $cloudinary = new Cloudinary([
        'cloud' => [
            'cloud_name' => CLOUD_NAME,
            'api_key'    => CLOUD_API_KEY,
            'api_secret' => CLOUD_API_SECRET,
        ],
        'url' => [
            'secure' => true
        ]
    ]);

    $GLOBALS['cloudinary'] = $cloudinary;
}





/*

require_once '../vendor/autoload.php'; // Pastikan ini sudah ada

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
echo getenv('BASEURL');
*/
