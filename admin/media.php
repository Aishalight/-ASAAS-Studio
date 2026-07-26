<?php
require_once __DIR__ . '/../config/functions.php';
startSession();
header('Location: ' . BASE_URL . 'admin-files');
exit;
