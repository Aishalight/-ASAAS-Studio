<?php
require_once __DIR__ . '/../config/functions.php';
startSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('error', 'Invalid request method.');
    redirect(BASE_URL . 'home');
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Invalid security token.');
    redirect(BASE_URL . 'home');
}

if (isLoggedIn()) {
    logActivity('logout', 'User logged out', [], 'info');
}

destroySession();
redirect(BASE_URL . 'home');