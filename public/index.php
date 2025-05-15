<?php
require_once '../app/init.php';

$app = new App;
file_put_contents('/tmp/debug.log', "Reached index.php\n", FILE_APPEND);
