<?php

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/database.php';

/*
|--------------------------------------------------------------------------
| Fonction d'échappement
|--------------------------------------------------------------------------
*/
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/*
|--------------------------------------------------------------------------
| URL DE BASE DU PROJET
|--------------------------------------------------------------------------
| Ton projet est actuellement dans :
| C:\xampp\htdocs\ORPAIS\
|
| Donc dans le navigateur :
| http://localhost/ORPAIS/
|
| Si ton projet change de nom ou d'emplacement,
| modifie uniquement cette valeur.
|--------------------------------------------------------------------------
*/
if (!defined('BASE_URL')) {
    define('BASE_URL', '/ORPAICS');
}

/*
|--------------------------------------------------------------------------
| Photo par défaut
|--------------------------------------------------------------------------
*/
function defaultTeamPhoto()
{
    return BASE_URL . '/assets/img/logo.jpg';
}

/*
|--------------------------------------------------------------------------
| Conversion du chemin photo en URL navigateur
|--------------------------------------------------------------------------
*/
function teamPhotoUrl($photo)
{
    $photo = trim((string) $photo);

    /*
    |--------------------------------------------------------------
    | Aucune photo
    |--------------------------------------------------------------
    */
    if ($photo === '') {
        return defaultTeamPhoto();
    }

    /*
    |--------------------------------------------------------------
    | Nettoyage des chemins Windows
    |--------------------------------------------------------------
    */
    $photo = str_replace('\\', '/', $photo);

    /*
    |--------------------------------------------------------------
    | URL externe
    |--------------------------------------------------------------
    */
    if (filter_var($photo, FILTER_VALIDATE_URL)) {
        return $photo;
    }

    /*
    |--------------------------------------------------------------
    | Si la base contient déjà /ORPAIS/...
    |--------------------------------------------------------------
    */
    if (str_starts_with($photo, BASE_URL . '/')) {
        return $photo;
    }

    /*
    |--------------------------------------------------------------
    | Si la base contient une URL absolue commençant par /
    |--------------------------------------------------------------
    */
    if (str_starts_with($photo, '/')) {
        return $photo;
    }

    /*
    |--------------------------------------------------------------
    | Si la base contient déjà uploads/team/
    |--------------------------------------------------------------
    */
    if (str_starts_with($photo, 'uploads/team/')) {
        return BASE_URL . '/' . ltrim($photo, '/');
    }

    /*
    |--------------------------------------------------------------
    | Si la base contient assets/
    |--------------------------------------------------------------
    */
    if (str_starts_with($photo, 'assets/')) {
        return BASE_URL . '/' . ltrim($photo, '/');
    }

    /*
    |--------------------------------------------------------------
    | Si seule le nom du fichier est enregistré
    |
    | Exemple :
    | jean.jpg
    |
    | devient :
    | /ORPAIS/uploads/team/jean.jpg
    |--------------------------------------------------------------
    */
    return BASE_URL . '/uploads/team/' . rawurlencode(basename($photo));
}

/*
|--------------------------------------------------------------------------
| Récupération des membres
|--------------------------------------------------------------------------
*/
$stmt = $pdo->query("
    SELECT
        id,
        name,
        position,
        biography,
        photo,
        phone,
        email,
        facebook,
        instagram,
        linkedin,
        twitter,
        status,
        display_order,
        created_at
    FROM team_members
    ORDER BY display_order ASC, id ASC
");

$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Statistiques
|--------------------------------------------------------------------------
*/
$totalMembers = count($members);

$activeMembers = 0;
$inactiveMembers = 0;

foreach ($members as $member) {

    if (($member['status'] ?? '') === 'active') {
        $activeMembers++;
    } else {
        $inactiveMembers++;
    }

}

/*
|--------------------------------------------------------------------------
| Utilisateur connecté
|--------------------------------------------------------------------------
*/
$adminName = $_SESSION['admin_name'] ?? 'Administrateur';
$adminEmail = $_SESSION['admin_email'] ?? '';

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Équipe | Administration ORPAIS</title>

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

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--orpaics-soft);
            color: var(--orpaics-text);
            font-family: Arial, Helvetica, sans-serif;
        }

        /* =====================================================
           SIDEBAR
        ===================================================== */

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            background: var(--orpaics-dark);
            color: #fff;
            padding: 25px 15px;
            z-index: 1000;
            overflow-y: auto;
        }

        .brand {
            text-align: center;
            font-size: 25px;
            font-weight: 700;
            margin-bottom: 35px;
        }

        .brand span {
            color: #69aefc;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff;
            text-decoration: none;
            padding: 13px 15px;
            border-radius: 8px;
            margin-bottom: 7px;
            transition: 0.2s;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.13);
        }

        .sidebar-menu i {
            font-size: 19px;
        }

        .logout {
            position: absolute;
            bottom: 25px;
            left: 15px;
            right: 15px;
        }

        /* =====================================================
           MAIN
        ===================================================== */

        .main {
            margin-left: 250px;
            padding: 30px;
        }

        .topbar {
            background: #fff;
            border-radius: 14px;
            padding: 20px 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
        }

        .topbar h1 {
            margin: 0;
            font-size: 27px;
            font-weight: 700;
            color: var(--orpaics-dark);
        }

        .admin-info {
            font-size: 14px;
            color: #718096;
        }

        /* =====================================================
           STATISTIQUES
        ===================================================== */

        .stat-card {
            background: #fff;
            border: none;
            border-radius: 15px;
            padding: 22px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
            height: 100%;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            background: var(--orpaics-light);
            color: var(--orpaics-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 30px;
            font-weight: 700;
            color: var(--orpaics-dark);
        }

        .stat-title {
            color: #718096;
            font-size: 14px;
        }

        /* =====================================================
           CONTENT
        ===================================================== */

        .content-card {
            background: #fff;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
        }

        .section-title {
            color: var(--orpaics-dark);
            font-size: 20px;
            font-weight: 700;
        }

        /* =====================================================
           MEMBER CARD
        ===================================================== */

        .member-card {
            background: #fff;
            border: 1px solid #e7edf5;
            border-radius: 15px;
            overflow: hidden;
            height: 100%;
            transition: 0.25s ease;
        }

        .member-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(7, 59, 102, 0.10);
        }

        .member-photo {
            height: 260px;
            background: var(--orpaics-light);
            overflow: hidden;
            position: relative;
        }

        .member-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
        }

        .status-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 6px 11px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            z-index: 2;
        }

        .status-active {
            background: #d1fae5;
            color: #047857;
        }

        .status-inactive {
            background: #fee2e2;
            color: #b91c1c;
        }

        .member-body {
            padding: 20px;
        }

        .member-name {
            color: var(--orpaics-dark);
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .member-position {
            color: var(--orpaics-blue);
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .member-description {
            color: #718096;
            font-size: 14px;
            line-height: 1.6;
            min-height: 45px;
        }

        .member-contact {
            border-top: 1px solid #edf1f6;
            margin-top: 15px;
            padding-top: 15px;
        }

        .member-contact div {
            font-size: 13px;
            color: #718096;
            margin-bottom: 7px;
            word-break: break-word;
        }

        .member-contact i {
            color: var(--orpaics-blue);
            width: 20px;
        }

        /* =====================================================
           SOCIAL
        ===================================================== */

        .social-links {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .social-links a {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: var(--orpaics-light);
            color: var(--orpaics-blue);
            text-decoration: none;
            transition: 0.2s;
        }

        .social-links a:hover {
            background: var(--orpaics-blue);
            color: #fff;
        }

        /* =====================================================
           BUTTONS
        ===================================================== */

        .btn-orpaics {
            background: var(--orpaics-blue);
            color: #fff;
            border: none;
        }

        .btn-orpaics:hover {
            background: var(--orpaics-dark);
            color: #fff;
        }

        .btn-outline-orpaics {
            color: var(--orpaics-blue);
            border: 1px solid var(--orpaics-blue);
        }

        .btn-outline-orpaics:hover {
            background: var(--orpaics-blue);
            color: #fff;
        }

        /* =====================================================
           EMPTY
        ===================================================== */

        .empty-state {
            text-align: center;
            padding: 70px 20px;
        }

        .empty-state i {
            font-size: 60px;
            color: var(--orpaics-blue);
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: var(--orpaics-dark);
            font-weight: 700;
        }

        .empty-state p {
            color: #718096;
        }

        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 991px) {

            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                min-height: auto;
            }

            .logout {
                position: static;
                margin-top: 20px;
            }

            .main {
                margin-left: 0;
            }

        }

        @media (max-width: 576px) {

            .main {
                padding: 15px;
            }

            .topbar {
                padding: 18px;
            }

            .topbar h1 {
                font-size: 22px;
            }

            .member-photo {
                height: 230px;
            }

        }

    </style>

    <link href="../../assets/css/admin-common.css" rel="stylesheet">
</head>

<body>

<!-- =========================================================
     SIDEBAR
========================================================= -->

<aside class="sidebar">

    <div class="brand">
        ORPAIS<span>/RDC</span>
    </div>

    <div class="sidebar-menu">

        <a href="../index.php">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>

        <a href="../activities/create.php">
            <i class="bi bi-plus-circle"></i>
            <span>Publier une activité</span>
        </a>

        <a href="../activities/index.php">
            <i class="bi bi-calendar-event"></i>
            <span>Mes activités</span>
        </a>

        <a href="../blog/create.php">
            <i class="bi bi-file-earmark-plus"></i>
            <span>Publier un article</span>
        </a>

        <a href="../blog/index.php">
            <i class="bi bi-newspaper"></i>
            <span>Mes articles</span>
        </a>

        <a href="create.php">
            <i class="bi bi-person-plus"></i>
            <span>Ajouter un membre</span>
        </a>

        <a href="index.php" class="active">
            <i class="bi bi-people"></i>
            <span>Équipe</span>
        </a>

    </div>

    <div class="logout">

        <a href="../logout.php" class="btn btn-outline-light w-100">

            <i class="bi bi-box-arrow-right me-2"></i>

            Déconnexion

        </a>

    </div>

</aside>


<!-- =========================================================
     CONTENU
========================================================= -->

<main class="main">

    <!-- TOPBAR -->

    <div class="topbar d-flex justify-content-between align-items-center flex-wrap gap-3">

        <div>

            <h1>
                Notre équipe
            </h1>

            <div class="admin-info mt-1">

                Bienvenue,
                <strong><?= e($adminName) ?></strong>

                <?php if ($adminEmail): ?>

                    — <?= e($adminEmail) ?>

                <?php endif; ?>

            </div>

        </div>

        <div class="d-flex gap-2 flex-wrap">

            <a
                href="create.php"
                class="btn btn-orpaics"
            >
                <i class="bi bi-person-plus me-2"></i>
                Ajouter un membre
            </a>

            <a
                href="../index.php"
                class="btn btn-outline-orpaics"
            >
                <i class="bi bi-arrow-left me-2"></i>
                Dashboard
            </a>

        </div>

    </div>


    <!-- =====================================================
         STATISTIQUES
    ====================================================== -->

    <div class="row g-4 mb-4">

        <div class="col-md-4">

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="bi bi-people"></i>
                </div>

                <div class="stat-number">
                    <?= $totalMembers ?>
                </div>

                <div class="stat-title">
                    Total des membres
                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="bi bi-person-check"></i>
                </div>

                <div class="stat-number">
                    <?= $activeMembers ?>
                </div>

                <div class="stat-title">
                    Membres actifs
                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="bi bi-person-x"></i>
                </div>

                <div class="stat-number">
                    <?= $inactiveMembers ?>
                </div>

                <div class="stat-title">
                    Membres inactifs
                </div>

            </div>

        </div>

    </div>


    <!-- =====================================================
         LISTE DE L'ÉQUIPE
    ====================================================== -->

    <div class="content-card">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

            <div>

                <div class="section-title">
                    <i class="bi bi-people me-2"></i>
                    Membres de l'équipe
                </div>

                <div class="admin-info">
                    Les membres affichés ici peuvent être modifiés
                    ou désactivés depuis l'administration.
                </div>

            </div>

            <a
                href="create.php"
                class="btn btn-orpaics"
            >

                <i class="bi bi-plus-lg me-2"></i>

                Nouveau membre

            </a>

        </div>


        <?php if (empty($members)): ?>

            <div class="empty-state">

                <i class="bi bi-people"></i>

                <h3>
                    Aucun membre enregistré
                </h3>

                <p>
                    Commencez par ajouter un membre de l'équipe ORPAIS.
                </p>

                <a
                    href="create.php"
                    class="btn btn-orpaics"
                >

                    <i class="bi bi-person-plus me-2"></i>

                    Ajouter le premier membre

                </a>

            </div>

        <?php else: ?>

            <div class="row g-4">

                <?php foreach ($members as $member): ?>

                    <?php

                    $photo = teamPhotoUrl($member['photo'] ?? '');

                    $biography = trim(
                        (string) ($member['biography'] ?? '')
                    );

                    $status = $member['status'] ?? 'inactive';

                    ?>

                    <div class="col-md-6 col-xl-4">

                        <div class="member-card">

                            <!-- PHOTO -->

                            <div class="member-photo">

                                <img
                                    src="<?= e($photo) ?>"
                                    alt="<?= e($member['name'] ?? 'Membre ORPAIS') ?>"
                                    loading="lazy"
                                    onerror="this.onerror=null;this.src='<?= e(defaultTeamPhoto()) ?>';"
                                >

                                <?php if ($status === 'active'): ?>

                                    <span class="status-badge status-active">
                                        Actif
                                    </span>

                                <?php else: ?>

                                    <span class="status-badge status-inactive">
                                        Inactif
                                    </span>

                                <?php endif; ?>

                            </div>


                            <!-- INFORMATIONS -->

                            <div class="member-body">

                                <div class="member-name">
                                    <?= e($member['name'] ?? '') ?>
                                </div>


                                <div class="member-position">

                                    <?= e($member['position'] ?? '') ?>

                                </div>


                                <div class="member-description">

                                    <?php if ($biography): ?>

                                        <?= e(
                                            function_exists('mb_strimwidth')
                                                ? mb_strimwidth(
                                                    $biography,
                                                    0,
                                                    120,
                                                    '...',
                                                    'UTF-8'
                                                )
                                                : substr($biography, 0, 120) . '...'
                                        ) ?>

                                    <?php else: ?>

                                        Aucune biographie renseignée.

                                    <?php endif; ?>

                                </div>


                                <!-- CONTACT -->

                                <?php if (
                                    !empty($member['phone']) ||
                                    !empty($member['email'])
                                ): ?>

                                    <div class="member-contact">

                                        <?php if (!empty($member['phone'])): ?>

                                            <div>

                                                <i class="bi bi-telephone"></i>

                                                <?= e($member['phone']) ?>

                                            </div>

                                        <?php endif; ?>


                                        <?php if (!empty($member['email'])): ?>

                                            <div>

                                                <i class="bi bi-envelope"></i>

                                                <?= e($member['email']) ?>

                                            </div>

                                        <?php endif; ?>

                                    </div>

                                <?php endif; ?>


                                <!-- RÉSEAUX SOCIAUX -->

                                <?php

                                $hasSocial =
                                    !empty($member['facebook']) ||
                                    !empty($member['instagram']) ||
                                    !empty($member['linkedin']) ||
                                    !empty($member['twitter']);

                                ?>

                                <?php if ($hasSocial): ?>

                                    <div class="social-links">

                                        <?php if (!empty($member['facebook'])): ?>

                                            <a
                                                href="<?= e($member['facebook']) ?>"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                title="Facebook"
                                            >
                                                <i class="bi bi-facebook"></i>
                                            </a>

                                        <?php endif; ?>


                                        <?php if (!empty($member['instagram'])): ?>

                                            <a
                                                href="<?= e($member['instagram']) ?>"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                title="Instagram"
                                            >
                                                <i class="bi bi-instagram"></i>
                                            </a>

                                        <?php endif; ?>


                                        <?php if (!empty($member['linkedin'])): ?>

                                            <a
                                                href="<?= e($member['linkedin']) ?>"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                title="LinkedIn"
                                            >
                                                <i class="bi bi-linkedin"></i>
                                            </a>

                                        <?php endif; ?>


                                        <?php if (!empty($member['twitter'])): ?>

                                            <a
                                                href="<?= e($member['twitter']) ?>"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                title="Twitter / X"
                                            >
                                                <i class="bi bi-twitter-x"></i>
                                            </a>

                                        <?php endif; ?>

                                    </div>

                                <?php endif; ?>


                                <!-- ACTIONS -->

                                <div class="d-flex gap-2 mt-4">

                                    <a
                                        href="edit.php?id=<?= (int) $member['id'] ?>"
                                        class="btn btn-sm btn-outline-primary flex-grow-1"
                                    >

                                        <i class="bi bi-pencil me-1"></i>

                                        Modifier

                                    </a>


                                    <a
                                        href="toggle-status.php?id=<?= (int) $member['id'] ?>"
                                        class="btn btn-sm btn-outline-warning"
                                        title="<?= $status === 'active' ? 'Désactiver' : 'Activer' ?>"
                                    >

                                        <?php if ($status === 'active'): ?>

                                            <i class="bi bi-eye-slash"></i>

                                        <?php else: ?>

                                            <i class="bi bi-eye"></i>

                                        <?php endif; ?>

                                    </a>


                                    <a
                                        href="delete.php?id=<?= (int) $member['id'] ?>"
                                        class="btn btn-sm btn-outline-danger"
                                        title="Supprimer"
                                        onclick="return confirm('Voulez-vous vraiment supprimer ce membre ?');"
                                    >

                                        <i class="bi bi-trash"></i>

                                    </a>

                                </div>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

</main>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>



