<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/database.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id) { $stmt = $pdo->prepare("DELETE FROM opportunities WHERE id = ?"); $stmt->execute([$id]); }
header('Location: index.php?success=deleted');
exit;
