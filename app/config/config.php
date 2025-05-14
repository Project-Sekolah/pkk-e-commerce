<?php




// DB configuration constants
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'my_database');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Cloudinary configuration constants
define('CLOUD_NAME', getenv('CLOUDINARY_CLOUD_NAME') ?: 'dripo32vz');
define('CLOUD_API_KEY', getenv('CLOUDINARY_API_KEY') ?: '165341413734398');
define('CLOUD_API_SECRET', getenv('CLOUDINARY_API_SECRET') ?: 'F7FDCO3Bvp0UKKZLLtfGCuvqG38');

