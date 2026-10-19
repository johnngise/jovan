<?php

session_start();

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/database.php';

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php');
    exit;
}


/*
 * Récupération de l'article
 */

$stmt = $pdo->prepare("
    SELECT *
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


$title = $post['title'];
$slug = $post['slug'];
$category = $post['category'];
$excerpt = $post['excerpt'];
$content = $post['content'];
$image = $post['image'];
$status = $post['status'];

$published_at = '';

if (!empty($post['published_at'])) {
    $published_at = date(
        'Y-m-d\TH:i',
        strtotime($post['published_at'])
    );
}

$errors = [];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $image = trim($_POST['image'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $published_at = trim($_POST['published_at'] ?? '');


    if ($title === '') {
        $errors[] = "Le titre est obligatoire.";
    }

    if ($content === '') {
        $errors[] = "Le contenu de l’article est obligatoire.";
    }

    if (!in_array($status, ['active', 'inactive'], true)) {
        $status = 'active';
    }


    /*
     * Génération du slug
     */

    if ($slug === '') {

        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $title);

        $slug = strtolower($slug);

        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        $slug = trim($slug, '-');
    }


    /*
     * Vérification du slug
     */

    $stmt = $pdo->prepare("
        SELECT id
        FROM blog_posts
        WHERE slug = ?
        AND id != ?
        LIMIT 1
    ");

    $stmt->execute([$slug, $id]);

    if ($stmt->fetch()) {
        $errors[] = "Ce slug est déjà utilisé par un autre article.";
    }


    if (empty($errors)) {

        $date = null;

        if ($published_at !== '') {

            $date = str_replace('T', ' ', $published_at);

            if (strlen($date) === 16) {
                $date .= ':00';
            }

        }


        $stmt = $pdo->prepare("
            UPDATE blog_posts
            SET
                title = ?,
                slug = ?,
                category = ?,
                excerpt = ?,
                content = ?,
                image = ?,
                status = ?,
                published_at = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $title,
            $slug,
            $category !== '' ? $category : null,
            $excerpt !== '' ? $excerpt : null,
            $content,
            $image !== '' ? $image : null,
            $status,
            $date,
            $id
        ]);

        header('Location: index.php?success=updated');
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Modifier l’article - ORPAIS</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >

    <style>

        body {
            background: #f5f9ff;
            font-family: Arial, sans-serif;
        }

        .admin-header {
            background: #073b66;
            color: white;
            padding: 25px;
        }

        .form-card {
            background: white;
            border-radius: 18px;
            padding: 30px;
            box-shadow: 0 5px 25px rgba(0,0,0,.06);
        }

        .form-label {
            font-weight: 600;
            color: #073b66;
        }

        .btn-orpaics {
            background: #0b5ed7;
            color: white;
        }

        .btn-orpaics:hover {
            background: #073b66;
            color: white;
        }

    </style>

    <link href="../../assets/css/admin-common.css" rel="stylesheet">
</head>

<body>


<header class="admin-header">

    <div class="container">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div>

                <h1 class="mb-1">

                    <i class="bi bi-pencil-square"></i>

                    Modifier l’article

                </h1>

                <p class="mb-0">
                    Modifier la publication ORPAIS
                </p>

            </div>

            <a
                href="index.php"
                class="btn btn-light"
            >
                <i class="bi bi-arrow-left"></i>
                Retour
            </a>

        </div>

    </div>

</header>


<main class="container py-4">

<?php if (!empty($errors)): ?>

    <div class="alert alert-danger">

        <strong>
            <i class="bi bi-exclamation-triangle"></i>
            Une erreur est survenue.
        </strong>

        <ul class="mb-0 mt-2">

            <?php foreach ($errors as $error): ?>

                <li><?= e($error) ?></li>

            <?php endforeach; ?>

        </ul>

    </div>

<?php endif; ?>


<div class="form-card">

<form method="POST">

<div class="row g-4">


    <div class="col-12">

        <label class="form-label">
            Titre *
        </label>

        <input
            type="text"
            name="title"
            class="form-control form-control-lg"
            value="<?= e($title) ?>"
            required
        >

    </div>


    <div class="col-md-8">

        <label class="form-label">
            Slug
        </label>

        <input
            type="text"
            name="slug"
            class="form-control"
            value="<?= e($slug) ?>"
        >

    </div>


    <div class="col-md-4">

        <label class="form-label">
            Catégorie
        </label>

        <input
            type="text"
            name="category"
            class="form-control"
            value="<?= e($category) ?>"
        >

    </div>


    <div class="col-12">

        <label class="form-label">
            Résumé
        </label>

        <textarea
            name="excerpt"
            class="form-control"
            rows="4"
        ><?= e($excerpt) ?></textarea>

    </div>


    <div class="col-12">

        <label class="form-label">
            Contenu *
        </label>

        <textarea
            name="content"
            class="form-control"
            rows="14"
            required
        ><?= e($content) ?></textarea>

    </div>


    <div class="col-md-8">

        <label class="form-label">
            Image
        </label>

        <input
            type="text"
            name="image"
            class="form-control"
            value="<?= e($image) ?>"
        >

    </div>


    <div class="col-md-4">

        <label class="form-label">
            Statut
        </label>

        <select
            name="status"
            class="form-select"
        >

            <option
                value="active"
                <?= $status === 'active' ? 'selected' : '' ?>
            >
                Actif / publié
            </option>

            <option
                value="inactive"
                <?= $status === 'inactive' ? 'selected' : '' ?>
            >
                Inactif / brouillon
            </option>

        </select>

    </div>


    <div class="col-md-6">

        <label class="form-label">
            Date de publication
        </label>

        <input
            type="datetime-local"
            name="published_at"
            class="form-control"
            value="<?= e($published_at) ?>"
        >

    </div>


    <div class="col-12">

        <hr>

        <div class="d-flex justify-content-between flex-wrap gap-2">

            <a
                href="index.php"
                class="btn btn-outline-secondary"
            >
                Annuler
            </a>

            <button
                type="submit"
                class="btn btn-orpaics px-4"
            >
                <i class="bi bi-save"></i>
                Enregistrer les modifications
            </button>

        </div>

    </div>

</div>

</form>

</div>

</main>

</body>
</html>



