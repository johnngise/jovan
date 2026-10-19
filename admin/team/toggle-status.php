<?php

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/database.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("
    UPDATE team_members

    SET status =
        CASE
            WHEN status = 'active'
            THEN 'inactive'
            ELSE 'active'
        END

    WHERE id = :id
");

$stmt->execute([
    'id' => $id
]);

header("Location: index.php");
exit;