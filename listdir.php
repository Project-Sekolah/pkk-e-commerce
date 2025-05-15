<?php
header('Content-Type: application/json');
$files = scandir(__DIR__);
echo json_encode($files);
