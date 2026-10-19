<?php

session_start();

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/database.php';

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$stmt = $pdo->query("
    SELECT
        id,
        title,
        slug,
        category,
        excerpt,
        image,
        status,
        published_at,
        created_at
    FROM blog_posts
    ORDER BY
        COALESCE(published_at, created_at) DESC,
        id DESC
");

$posts = $stmt->fetchAll();

$total = count($posts);
$active = 0;
$inactive = 0;

foreach ($posts as $post) {
    if ($post['status'] === 'active') {
        $active++;
    } else {
        $inactive++;
    }
}

$success = $_GET['success'] ?? '';

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mes articles - Administration ORPAIS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: #f5f9ff;
            font-family: Arial, sans-serif;
        }

        .admin-header {
            background: #073b66;
            color: white;
            padding: 25px;
            border-radius: 0 0 20px 20px;
        }

        .admin-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }

        .admin-container {
            padding: 30px 15px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,.06);
            height: 100%;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eaf3ff;
            color: #0b5ed7;
            font-size: 23px;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 700;
            color: #073b66;
        }

        .content-card {
            background: white;
            border-radius: 18px;
            box-shadow: 0 5px 25px rgba(0,0,0,.06);
            overflow: hidden;
        }

        .content-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }

        .post-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }

        .badge-active {
            background: #198754;
        }

        .badge-inactive {
            background: #6c757d;
        }

        .btn-orpaics {
            background: #0b5ed7;
            color: white;
            border: none;
        }

        .btn-orpaics:hover {
            background: #073b66;
            color: white;
        }

        .action-btn {
            margin: 2px;
        }
    </style>
    <link href="../../assets/css/admin-common.css" rel="stylesheet">
</head>

<body>

<header class="admin-header">
    <div class="container">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div>
                <h1>
                    <i class="bi bi-newspaper"></i>
                    Gestion des articles
                </h1>

                <p class="mb-0 mt-1">
                    Administration du blog ORPAIS
                </p>
            </div>

            <div>
                <a href="../index.php" class="btn btn-light me-2">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>

                <a href="create.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    Nouvel article
                </a>
            </div>

        </div>

    </div>
</header>


<main class="admin-container">

<div class="container">

    <?php if ($success === 'created'): ?>

        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i>
            L’article a été publié avec succès.
        </div>

    <?php elseif ($success === 'updated'): ?>

        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i>
            L’article a été modifié avec succès.
        </div>

    <?php elseif ($success === 'deleted'): ?>

        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i>
            L’article a été supprimé avec succès.
        </div>

    <?php elseif ($success === 'status'): ?>

        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i>
            Le statut de l’article a été modifié.
        </div>

    <?php endif; ?>


    <!-- STATISTIQUES -->

    <div class="row g-4 mb-4">

        <div class="col-md-4">

            <div class="stat-card">

                <div class="d-flex align-items-center gap-3">

                    <div class="stat-icon">
                        <i class="bi bi-newspaper"></i>
                    </div>

                    <div>
                        <div class="stat-number">
                            <?= $total ?>
                        </div>

                        <div>
                            Total des articles
                        </div>
                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="stat-card">

                <div class="d-flex align-items-center gap-3">

                    <div class="stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>

                    <div>
                        <div class="stat-number">
                            <?= $active ?>
                        </div>

                        <div>
                            Articles actifs
                        </div>
                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="stat-card">

                <div class="d-flex align-items-center gap-3">

                    <div class="stat-icon">
                        <i class="bi bi-eye-slash"></i>
                    </div>

                    <div>
                        <div class="stat-number">
                            <?= $inactive ?>
                        </div>

                        <div>
                            Articles masqués
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- ARTICLES -->

    <div class="content-card">

        <div class="content-header">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                <div>
                    <h4 class="mb-1">
                        <i class="bi bi-list-ul"></i>
                        Mes articles
                    </h4>

                    <small class="text-muted">
                        Gérez les publications du site ORPAIS.
                    </small>
                </div>

                <a href="create.php" class="btn btn-orpaics">
                    <i class="bi bi-plus-circle"></i>
                    Publier un article
                </a>

            </div>

        </div>


        <div class="table-responsive">

            <table class="table table-hover mb-0">

                <thead class="table-light">

                    <tr>

                        <th>Image</th>

                        <th>Article</th>

                        <th>Catégorie</th>

                        <th>Date</th>

                        <th>Statut</th>

                        <th class="text-center">Actions</th>

                    </tr>

                </thead>

                <tbody>

                <?php if (empty($posts)): ?>

                    <tr>

                        <td colspan="6" class="text-center py-5">

                            <i class="bi bi-newspaper fs-1 text-muted"></i>

                            <p class="mt-3 mb-2">
                                Aucun article n’a encore été publié.
                            </p>

                            <a href="create.php" class="btn btn-primary">
                                Créer le premier article
                            </a>

                        </td>

                    </tr>

                <?php else: ?>

                    <?php foreach ($posts as $post): ?>

                        <tr>

                            <td>

                                <?php
                                $image = !empty($post['image'])
                                    ? $post['image']
                                    : '../../assets/img/logo.jpg';
                                ?>

                                <img
                                    src="<?= e($image) ?>"
                                    alt="<?= e($post['title']) ?>"
                                    class="post-image"
                                    onerror="this.src='../../assets/img/logo.jpg'"
                                >

                            </td>


                            <td>

                                <strong>
                                    <?= e($post['title']) ?>
                                </strong>

                                <?php if (!empty($post['excerpt'])): ?>

                                    <div class="small text-muted mt-1">
                                        <?= e(mb_strimwidth($post['excerpt'], 0, 90, '...')) ?>
                                    </div>

                                <?php endif; ?>

                            </td>


                            <td>

                                <?php if (!empty($post['category'])): ?>

                                    <span class="badge bg-primary">
                                        <?= e($post['category']) ?>
                                    </span>

                                <?php else: ?>

                                    <span class="text-muted">
                                        —
                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>

                                <?php

                                $date = $post['published_at'] ?: $post['created_at'];

                                echo date('d/m/Y', strtotime($date));

                                ?>

                            </td>


                            <td>

                                <?php if ($post['status'] === 'active'): ?>

                                    <span class="badge badge-active">
                                        Actif
                                    </span>

                                <?php else: ?>

                                    <span class="badge badge-inactive">
                                        Inactif
                                    </span>

                                <?php endif; ?>

                            </td>


                            <td class="text-center">

                                <!-- VOIR -->

                                <a
                                    href="../../blog-single.php?id=<?= (int)$post['id'] ?>"
                                    class="btn btn-sm btn-outline-primary action-btn"
                                    target="_blank"
                                    title="Voir"
                                >
                                    <i class="bi bi-eye"></i>
                                </a>


                                <!-- MODIFIER -->

                                <a
                                    href="edit.php?id=<?= (int)$post['id'] ?>"
                                    class="btn btn-sm btn-outline-warning action-btn"
                                    title="Modifier"
                                >
                                    <i class="bi bi-pencil"></i>
                                </a>


                                <!-- STATUT -->

                                <?php if ($post['status'] === 'active'): ?>

                                    <a
                                        href="toggle-status.php?id=<?= (int)$post['id'] ?>&status=inactive"
                                        class="btn btn-sm btn-outline-secondary action-btn"
                                        title="Masquer"
                                        onclick="return confirm('Voulez-vous masquer cet article ?');"
                                    >
                                        <i class="bi bi-eye-slash"></i>
                                    </a>

                                <?php else: ?>

                                    <a
                                        href="toggle-status.php?id=<?= (int)$post['id'] ?>&status=active"
                                        class="btn btn-sm btn-outline-success action-btn"
                                        title="Activer"
                                        onclick="return confirm('Voulez-vous publier cet article ?');"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>

                                <?php endif; ?>


                                <!-- SUPPRIMER -->

                                <a
                                    href="delete.php?id=<?= (int)$post['id'] ?>"
                                    class="btn btn-sm btn-outline-danger action-btn"
                                    title="Supprimer"
                                    onclick="return confirm('ATTENTION : voulez-vous vraiment supprimer cet article ?');"
                                >
                                    <i class="bi bi-trash"></i>
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</main>

</body>
</html>



