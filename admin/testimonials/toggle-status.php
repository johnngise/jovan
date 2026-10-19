<?php

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php');
    exit;
}


$stmt = $pdo->prepare("
    SELECT status
    FROM testimonials
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$testimonial = $stmt->fetch();


if (!$testimonial) {
    header('Location: index.php');
    exit;
}


$newStatus = $testimonial['status'] === 'active'
    ? 'inactive'
    : 'active';


$stmt = $pdo->prepare("
    UPDATE testimonials
    SET status = ?
    WHERE id = ?
");

$stmt->execute([
    $newStatus,
    $id
]);


header('Location: index.php?success=status');
exit;