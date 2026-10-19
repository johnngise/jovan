<?php

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/database.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT photo
    FROM team_members
    WHERE id = :id
");

$stmt->execute([
    'id' => $id
]);

$member = $stmt->fetch();

if ($member) {

    if (
        !empty($member['photo']) &&
        file_exists(
            __DIR__ . '/../../' . $member['photo']
        )
    ) {

        unlink(
            __DIR__ . '/../../' . $member['photo']
        );
    }

    $stmt = $pdo->prepare("
        DELETE FROM team_members
        WHERE id = :id
    ");

    $stmt->execute([
        'id' => $id
    ]);
}

header("Location: index.php");
exit;