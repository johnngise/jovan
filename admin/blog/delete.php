<?php

session_start();

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php');
    exit;
}


/*
 * Vérifier que l'article existe
 */

$stmt = $pdo->prepare("
    SELECT id
    FROM blog_posts
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$post = $stmt->fetch();

if (!$post) {
    header('Location: index.php');
    exit;
}


/*
 * Suppression
 */

try {

    $stmt = $pdo->prepare("
        DELETE FROM blog_posts
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    header('Location: index.php?success=deleted');
    exit;

} catch (PDOException $e) {

    die(
        "Erreur lors de la suppression : " .
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );

}
