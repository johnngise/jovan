<?php

session_start();

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/database.php';

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$title = '';
$slug = '';
$category = '';
$excerpt = '';
$content = '';
$image = '';
$status = 'active';
$published_at = '';

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
     * Génération automatique du slug
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
        LIMIT 1
    ");

    $stmt->execute([$slug]);

    if ($stmt->fetch()) {
        $errors[] = "Ce slug existe déjà. Veuillez en choisir un autre.";
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
            INSERT INTO blog_posts
            (
                title,
                slug,
                category,
                excerpt,
                content,
                image,
                status,
                published_at
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            )
        ");

        $stmt->execute([
            $title,
            $slug,
            $category !== '' ? $category : null,
            $excerpt !== '' ? $excerpt : null,
            $content,
            $image !== '' ? $image : null,
            $status,
            $date
        ]);

        header('Location: index.php?success=created');
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Publier un article - ORPAIS</title>

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

        textarea {
            min-height: 180px;
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
                    Publier un article
                </h1>

                <p class="mb-0">
                    Créer une nouvelle publication ORPAIS
                </p>

            </div>

            <div>

                <a href="index.php" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i>
                    Retour aux articles
                </a>

            </div>

        </div>

    </div>

</header>


<main class="container py-4">

    <?php if (!empty($errors)): ?>

        <div class="alert alert-danger">

            <strong>
                <i class="bi bi-exclamation-triangle"></i>
                Impossible de publier l’article.
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


                <!-- TITRE -->

                <div class="col-12">

                    <label class="form-label">
                        Titre de l’article *
                    </label>

                    <input
                        type="text"
                        name="title"
                        id="title"
                        class="form-control form-control-lg"
                        value="<?= e($title) ?>"
                        required
                    >

                </div>


                <!-- SLUG -->

                <div class="col-md-8">

                    <label class="form-label">
                        Slug
                    </label>

                    <input
                        type="text"
                        name="slug"
                        id="slug"
                        class="form-control"
                        value="<?= e($slug) ?>"
                        placeholder="exemple-de-mon-article"
                    >

                    <small class="text-muted">
                        Laissez vide pour générer automatiquement.
                    </small>

                </div>


                <!-- CATEGORIE -->

                <div class="col-md-4">

                    <label class="form-label">
                        Catégorie
                    </label>

                    <input
                        type="text"
                        name="category"
                        class="form-control"
                        value="<?= e($category) ?>"
                        placeholder="Paix, Autonomisation..."
                    >

                </div>


                <!-- EXCERPT -->

                <div class="col-12">

                    <label class="form-label">
                        Résumé
                    </label>

                    <textarea
                        name="excerpt"
                        class="form-control"
                        rows="4"
                        placeholder="Court résumé de l’article..."
                    ><?= e($excerpt) ?></textarea>

                </div>


                <!-- CONTENU -->

                <div class="col-12">

                    <label class="form-label">
                        Contenu de l’article *
                    </label>

                    <textarea
                        name="content"
                        class="form-control"
                        rows="12"
                        required
                        placeholder="Rédigez ici le contenu complet de votre article..."
                    ><?= e($content) ?></textarea>

                </div>


                <!-- IMAGE -->

                <div class="col-md-8">

                    <label class="form-label">
                        Image
                    </label>

                    <input
                        type="text"
                        name="image"
                        class="form-control"
                        value="<?= e($image) ?>"
                        placeholder="assets/img/orpais.jpeg"
                    >

                    <small class="text-muted">
                        Exemple : assets/img/orpais.jpeg
                    </small>

                </div>


                <!-- STATUT -->

                <div class="col-md-4">

                    <label class="form-label">
                        Statut
                    </label>

                    <select name="status" class="form-select">

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


                <!-- DATE -->

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


                <!-- BOUTONS -->

                <div class="col-12">

                    <hr>

                    <div class="d-flex justify-content-between flex-wrap gap-2">

                        <a href="index.php" class="btn btn-outline-secondary">
                            Annuler
                        </a>

                        <button
                            type="submit"
                            class="btn btn-orpaics px-4"
                        >
                            <i class="bi bi-check-circle"></i>
                            Publier l’article
                        </button>

                    </div>

                </div>

            </div>

        </form>

    </div>

</main>


<script>

const titleInput = document.getElementById('title');
const slugInput = document.getElementById('slug');

titleInput.addEventListener('input', function () {

    if (slugInput.dataset.manual === 'true') {
        return;
    }

    let slug = this.value
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

    slugInput.value = slug;

});

slugInput.addEventListener('input', function () {
    this.dataset.manual = 'true';
});

</script>

</body>
</html>



