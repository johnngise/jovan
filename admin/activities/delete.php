<?php

session_start();

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/database.php';


/*
|--------------------------------------------------------------------------
| Récupérer l'ID
|--------------------------------------------------------------------------
*/

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);


if (!$id) {

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Vérifier que l'activité existe
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM activities
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$activity = $stmt->fetch();


if (!$activity) {

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Suppression
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        DELETE FROM activities
        WHERE id = ?
    ");

    $stmt->execute([$id]);


    header('Location: index.php?success=deleted');
    exit;


} catch (PDOException $e) {

    die(
        "Erreur lors de la suppression : "
        . htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );

}
