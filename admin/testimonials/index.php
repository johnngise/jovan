<?php

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/database.php';

function e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$stmt = $pdo->query("
    SELECT
        id,
        name,
        role,
        content,
        photo,
        status,
        display_order,
        created_at
    FROM testimonials
    ORDER BY display_order ASC, id DESC
");

$testimonials = $stmt->fetchAll();

$total = count($testimonials);

$active = 0;
$inactive = 0;

foreach ($testimonials as $testimonial) {
    if ($testimonial['status'] === 'active') {
        $active++;
    } else {
        $inactive++;
    }
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Témoignages - Administration ORPAIS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --orpaics-dark: #073b66;
            --orpaics-blue: #0b5ed7;
            --orpaics-light: #eaf3ff;
            --orpaics-soft: #f5f9ff;
        }

        body {
            background: #f5f7fb;
            font-family: Arial, sans-serif;
        }

        .sidebar {
            width: 250px;
            min-height: 100vh;
            background: var(--orpaics-dark);
            position: fixed;
            left: 0;
            top: 0;
            padding: 25px 15px;
        }

        .sidebar-logo {
            color: white;
            font-size: 24px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            color: rgba(255,255,255,.85);
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 5px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: rgba(255,255,255,.15);
            color: white;
        }

        .main {
            margin-left: 250px;
            padding: 30px;
        }

        .topbar {
            background: white;
            padding: 20px 25px;
            border-radius: 14px;
            margin-bottom: 25px;
            box-shadow: 0 3px 15px rgba(0,0,0,.05);
        }

        .stat-card {
            background: white;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 3px 15px rgba(0,0,0,.05);
            height: 100%;
        }

        .stat-number {
            font-size: 30px;
            font-weight: 700;
            color: var(--orpaics-dark);
        }

        .testimonial-card {
            background: white;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 3px 15px rgba(0,0,0,.05);
            height: 100%;
        }

        .testimonial-photo {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--orpaics-light);
        }

        .testimonial-content {
            color: #555;
            line-height: 1.7;
            margin-top: 15px;
        }

        .action-buttons a {
            margin-right: 5px;
        }

        @media (max-width: 991px) {
            .sidebar {
                position: relative;
                width: 100%;
                min-height: auto;
            }

            .main {
                margin-left: 0;
            }
        }
    </style>
    <link href="../../assets/css/admin-common.css" rel="stylesheet">
</head>

<body>

<div class="sidebar">

    <div class="sidebar-logo">
        ORPAIS
    </div>

    <a href="../index.php">
        <i class="bi bi-speedometer2"></i>
        Tableau de bord
    </a>

    <a href="../activities/index.php">
        <i class="bi bi-activity"></i>
        Activités
    </a>

    <a href="../blog/index.php">
        <i class="bi bi-newspaper"></i>
        Blog
    </a>

    <a href="../team/index.php">
        <i class="bi bi-people"></i>
        Équipe
    </a>

    <a href="index.php" class="active">
        <i class="bi bi-chat-quote"></i>
        Témoignages
    </a>

    <hr class="border-light opacity-25">

    <a href="../logout.php">
        <i class="bi bi-box-arrow-right"></i>
        Déconnexion
    </a>

</div>

<div class="main">

    <div class="topbar d-flex justify-content-between align-items-center flex-wrap gap-3">

        <div>
            <h2 class="mb-1">Témoignages</h2>
            <p class="text-muted mb-0">
                Gérez les témoignages affichés sur le site ORPAIS.
            </p>
        </div>

        <a href="create.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i>
            Ajouter un témoignage
        </a>

    </div>

    <!-- STATISTIQUES -->

    <div class="row g-4 mb-4">

        <div class="col-md-4">

            <div class="stat-card">

                <div class="text-muted">
                    Total témoignages
                </div>

                <div class="stat-number">
                    <?= $total ?>
                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="stat-card">

                <div class="text-muted">
                    Publiés
                </div>

                <div class="stat-number text-success">
                    <?= $active ?>
                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="stat-card">

                <div class="text-muted">
                    Non publiés
                </div>

                <div class="stat-number text-secondary">
                    <?= $inactive ?>
                </div>

            </div>

        </div>

    </div>


    <!-- LISTE -->

    <div class="row g-4">

        <?php if (!empty($testimonials)): ?>

            <?php foreach ($testimonials as $testimonial): ?>

                <?php
                $photo = !empty($testimonial['photo'])
                    ? $testimonial['photo']
                    : '../../assets/img/logo.jpg';
                ?>

                <div class="col-md-6 col-xl-4">

                    <div class="testimonial-card">

                        <div class="d-flex align-items-center gap-3">

                            <img
                                src="<?= e($photo) ?>"
                                class="testimonial-photo"
                                alt="<?= e($testimonial['name']) ?>"
                                onerror="this.onerror=null;this.src='../../assets/img/logo.jpg';"
                            >

                            <div>

                                <h5 class="mb-1">
                                    <?= e($testimonial['name']) ?>
                                </h5>

                                <?php if (!empty($testimonial['role'])): ?>

                                    <div class="text-muted small">
                                        <?= e($testimonial['role']) ?>
                                    </div>

                                <?php endif; ?>

                            </div>

                        </div>


                        <div class="testimonial-content">

                            «<?= e($testimonial['content']) ?>É

                        </div>


                        <div class="mt-3">

                            <?php if ($testimonial['status'] === 'active'): ?>

                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i>
                                    Publié
                                </span>

                            <?php else: ?>

                                <span class="badge bg-secondary">
                                    <i class="bi bi-eye-slash"></i>
                                    Non publié
                                </span>

                            <?php endif; ?>

                        </div>


                        <hr>


                        <div class="action-buttons">

                            <a
                                href="edit.php?id=<?= (int)$testimonial['id'] ?>"
                                class="btn btn-sm btn-outline-primary"
                            >
                                <i class="bi bi-pencil"></i>
                                Modifier
                            </a>


                            <a
                                href="toggle-status.php?id=<?= (int)$testimonial['id'] ?>"
                                class="btn btn-sm btn-outline-success"
                                onclick="return confirm('Voulez-vous modifier le statut de ce témoignage ?');"
                            >

                                <?php if ($testimonial['status'] === 'active'): ?>

                                    <i class="bi bi-eye-slash"></i>
                                    Masquer

                                <?php else: ?>

                                    <i class="bi bi-eye"></i>
                                    Publier

                                <?php endif; ?>

                            </a>


                            <a
                                href="delete.php?id=<?= (int)$testimonial['id'] ?>"
                                class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce témoignage ?');"
                            >
                                <i class="bi bi-trash"></i>
                                Supprimer
                            </a>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="col-12">

                <div class="alert alert-info">

                    <i class="bi bi-info-circle"></i>

                    Aucun témoignage n'est encore enregistré.

                    <a href="create.php">
                        Ajouter le premier témoignage
                    </a>

                </div>

            </div>

        <?php endif; ?>

    </div>

</div>

</body>
</html>




