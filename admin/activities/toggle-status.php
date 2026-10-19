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

$status = $_GET['status'] ?? '';


if (!$id || !in_array($status, ['active', 'inactive'], true)) {

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Modifier le statut
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        UPDATE activities
        SET status = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $status,
        $id
    ]);


    header('Location: index.php?success=status');
    exit;


} catch (PDOException $e) {

    die(
        "Erreur lors de la modification du statut : "
        . htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );

}
