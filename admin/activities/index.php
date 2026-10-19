<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
/*
|--------------------------------------------------------------------------
| Authentification
|--------------------------------------------------------------------------
*/
require_once __DIR__ . '/../auth.php';
/*
|--------------------------------------------------------------------------
| Base de données
|--------------------------------------------------------------------------
*/
require_once __DIR__ . '/../../config/database.php';
/*
|--------------------------------------------------------------------------
| Protection HTML
|--------------------------------------------------------------------------
*/
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
/*
|--------------------------------------------------------------------------
| Récupération des activités
|--------------------------------------------------------------------------
*/
$activities = [];
try {
    $stmt = $pdo->prepare("
        SELECT
            id,
            title,
            slug,
            category,
            category_label,
            description,
            icon,
            image,
            event_date,
            location,
            budget,
            beneficiaries,
            men,
            women,
            children,
            status,
            display_order,
            created_at
        FROM activities
        ORDER BY display_order ASC, id DESC
    ");
    $stmt->execute();
    $activities = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = $e->getMessage();
}
/*
|--------------------------------------------------------------------------
| Messages
|--------------------------------------------------------------------------
*/
$successMessage = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'created':
            $successMessage = "L'activité a été créée avec succès.";
            break;
        case 'updated':
            $successMessage = "L'activité a été modifiée avec succès.";
            break;
        case 'deleted':
            $successMessage = "L'activité a été supprimée.";
            break;
        case 'status':
            $successMessage = "Le statut de l'activité a été modifié.";
            break;
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
    <title>Gestion des activités | ORPAIS</title>
    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <!-- Bootstrap Icons -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >
    <style>
        :root {
            --orpaics-dark: #073b66;
            --orpaics-blue: #0b5ed7;
            --orpaics-light: #eaf3ff;
            --orpaics-soft: #f5f9ff;
            --orpaics-text: #263b50;
        }
        body {
            background: var(--orpaics-soft);
            color: var(--orpaics-text);
            font-family: Arial, Helvetica, sans-serif;
        }
        .page-wrapper {
            max-width: 1250px;
            margin: 35px auto;
            padding: 0 20px;
        }
        .page-header {
            background: var(--orpaics-dark);
            color: white;
            border-radius: 15px;
            padding: 25px 30px;
            margin-bottom: 20px;
        }
        .page-header h1 {
            margin: 0;
            font-size: 27px;
            font-weight: 700;
        }
        .page-header p {
            margin: 7px 0 0;
            opacity: .85;
        }
        .actions-bar {
            background: white;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,.05);
        }
        .btn-orpaics {
            background: var(--orpaics-blue);
            color: white;
            border: none;
        }
        .btn-orpaics:hover {
            background: var(--orpaics-dark);
            color: white;
        }
        .btn-outline-orpaics {
            color: var(--orpaics-blue);
            border: 1px solid var(--orpaics-blue);
        }
        .btn-outline-orpaics:hover {
            background: var(--orpaics-blue);
            color: white;
        }
        .table-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 18px rgba(0,0,0,.05);
            overflow: hidden;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .table {
            margin: 0;
            vertical-align: middle;
        }
        .table thead th {
            background: var(--orpaics-dark);
            color: white;
            border: none;
            padding: 15px 12px;
            white-space: nowrap;
        }
        .table tbody td {
            padding: 13px 12px;
            border-color: #edf1f5;
        }
        .activity-image {
            width: 75px;
            height: 55px;
            object-fit: cover;
            border-radius: 7px;
            background: #edf3f9;
        }
        .activity-title {
            font-weight: 700;
            color: var(--orpaics-dark);
            min-width: 220px;
        }
        .activity-description {
            font-size: 13px;
            color: #718096;
            max-width: 300px;
        }
        .badge-active {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
        }
        .category-badge {
            background: var(--orpaics-light);
            color: var(--orpaics-blue);
            font-weight: 600;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
            min-width: 190px;
        }
        .empty-state {
            text-align: center;
            padding: 70px 25px;
        }
        .empty-state i {
            font-size: 55px;
            color: #b8c9da;
        }
        .empty-state h3 {
            margin-top: 15px;
            color: var(--orpaics-dark);
        }
        .stat {
            background: white;
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 4px 15px rgba(0,0,0,.04);
        }
        .stat-number {
            font-size: 27px;
            font-weight: 700;
            color: var(--orpaics-dark);
        }
        .stat-label {
            color: #718096;
            font-size: 14px;
        }
        @media (max-width: 576px) {
            .page-wrapper {
                margin: 20px auto;
                padding: 0 12px;
            }
            .page-header {
                padding: 20px;
            }
            .page-header h1 {
                font-size: 22px;
            }
        }
    </style>
    <link href="../../assets/css/admin-common.css" rel="stylesheet">
</head>
<body>
<div class="page-wrapper">
    <!-- =====================================================
         EN-TÊTE
    ====================================================== -->
    <div class="page-header">
        <div class="d-flex align-items-center gap-3">
            <i
                class="bi bi-calendar-event"
                style="font-size: 38px;"
            ></i>
            <div>
                <h1>
                    Gestion des activités
                </h1>
                <p>
                    Gérez les activités publiées sur le site ORPAIS.
                </p>
            </div>
        </div>
    </div>
    <!-- =====================================================
         ACTIONS
    ====================================================== -->
    <div class="actions-bar">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <a
                    href="../index.php"
                    class="btn btn-outline-orpaics"
                >
                    <i class="bi bi-arrow-left me-2"></i>
                    Dashboard
                </a>
            </div>
            <div>
                <a
                    href="create.php"
                    class="btn btn-orpaics"
                >
                    <i class="bi bi-plus-lg me-2"></i>
                    Publier une activité
                </a>
            </div>
        </div>
    </div>
    <!-- =====================================================
         MESSAGE SUCCÈS
    ====================================================== -->
    <?php if ($successMessage): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-2"></i>
            <?= e($successMessage) ?>
        </div>
    <?php endif; ?>
    <!-- =====================================================
         ERREUR
    ====================================================== -->
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Une erreur est survenue :
            <?= e($error) ?>
        </div>
    <?php endif; ?>
    <!-- =====================================================
         STATISTIQUES
    ====================================================== -->
    <?php
    $totalActivities = count($activities);
    $activeActivities = 0;
    $inactiveActivities = 0;
    foreach ($activities as $activity) {
        if ($activity['status'] === 'active') {
            $activeActivities++;
        } else {
            $inactiveActivities++;
        }
    }
    ?>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat">
                <div class="stat-number">
                    <?= $totalActivities ?>
                </div>
                <div class="stat-label">
                    Total des activités
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat">
                <div class="stat-number text-success">
                    <?= $activeActivities ?>
                </div>
                <div class="stat-label">
                    Activités publiées
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat">
                <div class="stat-number text-danger">
                    <?= $inactiveActivities ?>
                </div>
                <div class="stat-label">
                    Activités désactivées
                </div>
            </div>
        </div>
    </div>
    <!-- =====================================================
         LISTE
    ====================================================== -->
    <div class="table-card">
        <?php if (empty($activities)): ?>
            <div class="empty-state">
                <i class="bi bi-calendar-x"></i>
                <h3>
                    Aucune activité
                </h3>
                <p class="text-muted">
                    Vous n'avez encore publié aucune activité.
                </p>
                <a
                    href="create.php"
                    class="btn btn-orpaics"
                >
                    <i class="bi bi-plus-lg me-2"></i>
                    Créer la première activité
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>
                                Image
                            </th>
                            <th>
                                Activité
                            </th>
                            <th>
                                Catégorie
                            </th>
                            <th>
                                Date
                            </th>
                            <th>
                                Lieu
                            </th>
                            <th>
                                Bénéficiaires
                            </th>
                            <th>
                                Statut
                            </th>
                            <th>
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($activities as $activity): ?>
                        <tr>
                            <!-- IMAGE -->
                            <td>
                                <?php
                                $image = !empty($activity['image'])
                                    ? '../../' . ltrim($activity['image'], '/')
                                    : '../../assets/img/orpais.jpeg';
                                ?>
                                <img
                                    src="<?= e($image) ?>"
                                    alt="<?= e($activity['title']) ?>"
                                    class="activity-image"
                                    onerror="this.src='../../assets/img/logo.jpg';"
                                >
                            </td>
                            <!-- TITRE -->
                            <td>
                                <div class="activity-title">
                                    <?= e($activity['title']) ?>
                                </div>
                                <?php if (!empty($activity['description'])): ?>
                                    <div class="activity-description mt-1">
                                        <?= e(
                                            mb_strimwidth(
                                                $activity['description'],
                                                0,
                                                120,
                                                '...'
                                            )
                                        ) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <!-- CATÉGORIE -->
                            <td>
                                <span class="badge category-badge">
                                    <?= e(
                                        !empty($activity['category_label'])
                                            ? $activity['category_label']
                                            : $activity['category']
                                    ) ?>
                                </span>
                            </td>
                            <!-- DATE -->
                            <td>
                                <?php if (!empty($activity['event_date'])): ?>
                                    <?= e(
                                        date(
                                            'd/m/Y',
                                            strtotime($activity['event_date'])
                                        )
                                    ) ?>
                                <?php else: ?>
                                    <span class="text-muted">
                                        —
                                    </span>
                                <?php endif; ?>
                            </td>
                            <!-- LIEU -->
                            <td>
                                <?php if (!empty($activity['location'])): ?>
                                    <i class="bi bi-geo-alt me-1"></i>
                                    <?= e($activity['location']) ?>
                                <?php else: ?>
                                    <span class="text-muted">
                                        —
                                    </span>
                                <?php endif; ?>
                            </td>
                            <!-- BÉNÉFICIAIRES -->
                            <td>
                                <?php if ($activity['beneficiaries'] !== null): ?>
                                    <?= number_format(
                                        (int)$activity['beneficiaries'],
                                        0,
                                        ',',
                                        ' '
                                    ) ?>
                                <?php else: ?>
                                    <span class="text-muted">
                                        —
                                    </span>
                                <?php endif; ?>
                            </td>
                            <!-- STATUT -->
                            <td>
                                <?php if ($activity['status'] === 'active'): ?>
                                    <span class="badge badge-active">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Publiée
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-inactive">
                                        <i class="bi bi-eye-slash me-1"></i>
                                        Désactivée
                                    </span>
                                <?php endif; ?>
                            </td>
                            <!-- ACTIONS -->
                            <td>
                                <div class="action-buttons">
                                    <!-- VOIR -->
                                    <a
                                        href="../../activity-single.php?id=<?= (int)$activity['id'] ?>"
                                        target="_blank"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Voir sur le site"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <!-- MODIFIER -->
                                    <a
                                        href="edit.php?id=<?= (int)$activity['id'] ?>"
                                        class="btn btn-sm btn-outline-warning"
                                        title="Modifier"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <!-- STATUT -->
                                    <?php if ($activity['status'] === 'active'): ?>
                                        <a
                                            href="toggle-status.php?id=<?= (int)$activity['id'] ?>&status=inactive"
                                            class="btn btn-sm btn-outline-secondary"
                                            title="Désactiver"
                                            onclick="return confirm('Voulez-vous désactiver cette activité ?');"
                                        >
                                            <i class="bi bi-eye-slash"></i>
                                        </a>
                                    <?php else: ?>
                                        <a
                                            href="toggle-status.php?id=<?= (int)$activity['id'] ?>&status=active"
                                            class="btn btn-sm btn-outline-success"
                                            title="Publier"
                                            onclick="return confirm('Voulez-vous publier cette activité ?');"
                                        >
                                            <i class="bi bi-check-circle"></i>
                                        </a>
                                    <?php endif; ?>
                                    <!-- SUPPRIMER -->
                                    <a
                                        href="delete.php?id=<?= (int)$activity['id'] ?>"
                                        class="btn btn-sm btn-outline-danger"
                                        title="Supprimer"
                                        onclick="return confirm('ATTENTION : cette activité sera définitivement supprimée. Continuer ?');"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>
</body>
</html>





