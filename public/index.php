<?php
require_once '../app/init.php';


file_put_contents('/tmp/debug.log', "Reached index.php\n", FILE_APPEND);
error_log("Isi /var/www/html: " . implode(", ", scandir('/var/www/html')));
$app = new App;

