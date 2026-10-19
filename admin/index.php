<?php
session_start();
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
/*
|--------------------------------------------------------------------------
| Statistiques
|--------------------------------------------------------------------------
*/
$teamCount = 0;
$activitiesCount = 0;
$blogCount = 0;
$testimonialsCount = 0;
$opportunitiesCount = 0;
try {
    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM team_members
        WHERE status = 'active'
    ");
    $teamCount = (int) $stmt->fetchColumn();
    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM activities
        WHERE status = 'active'
    ");
    $activitiesCount = (int) $stmt->fetchColumn();
    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM blog_posts
        WHERE status = 'active'
    ");
    $blogCount = (int) $stmt->fetchColumn();
    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM testimonials
        WHERE status = 'active'
    ");
    $testimonialsCount = (int) $stmt->fetchColumn();
    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM opportunities
        WHERE status = 'active'
    ");
    $opportunitiesCount = (int) $stmt->fetchColumn();
} catch (PDOException $e) {
    // Certaines tables peuvent ne pas encore exister.
    // Le Dashboard continue à fonctionner.
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | ORPAIS</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >
    <link href="../assets/css/admin-common.css" rel="stylesheet">
    <style>
        :root {
            --orpaics-dark: #164e68;
            --orpaics-blue: #4da9c2;
            --orpaics-light: #dff3f5;
            --orpaics-soft: #f7fbfb;
            --orpaics-text: #163b4d;
            --orpaics-orange: #d9865f;
            --orpaics-orange-dark: #bd6846;
            --orpaics-line: #dcecef;
        }
        body {
            margin: 0;
            background: var(--orpaics-soft);
            color: var(--orpaics-text);
            font-family: Arial, Helvetica, sans-serif;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            background: linear-gradient(160deg, #164e68, #286f88);
            color: #fff;
            padding: 28px 15px;
            z-index: 1000;
        }
        .brand {
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 38px;
            letter-spacing: .04em;
        }
        .brand span {
            color: #f2b093;
        }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff;
            text-decoration: none;
            padding: 13px 15px;
            border-radius: 10px;
            margin-bottom: 6px;
            transition: background .2s ease, transform .2s ease;
        }
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.16);
            transform: translateX(2px);
        }
        .sidebar-menu i {
            font-size: 19px;
            color: #bce5e8;
        }
        .logout {
            position: absolute;
            bottom: 25px;
            left: 15px;
            right: 15px;
        }
        .main {
            margin-left: 250px;
            min-height: 100vh;
            padding: 32px;
        }
        .topbar {
            align-items: center;
            background: #fff;
            border: 1px solid var(--orpaics-line);
            border-radius: 18px;
            padding: 22px 26px;
            margin-bottom: 28px;
            box-shadow: 0 10px 28px rgba(22, 78, 104, .06);
        }
        .topbar h1 {
            margin: 0;
            font-size: 27px;
            font-weight: 700;
            color: var(--orpaics-dark);
            letter-spacing: -.02em;
        }
        .admin-info {
            font-size: 14px;
            color: #718096;
        }
        .stat-card {
            background: #fff;
            border: 1px solid var(--orpaics-line);
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 10px 28px rgba(22, 78, 104, .06);
            height: 100%;
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 32px rgba(22, 78, 104, .11);
        }
        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
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
            line-height: 1.1;
        }
        .stat-title {
            color: #718096;
            font-size: 14px;
        }
        .section-card {
            background: #fff;
            border: 1px solid var(--orpaics-line);
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 10px 28px rgba(22, 78, 104, .06);
            height: 100%;
        }
        .section-title {
            color: var(--orpaics-dark);
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .action-card {
            border: 1px solid var(--orpaics-line);
            border-radius: 14px;
            padding: 20px;
            height: 100%;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }
        .action-card:hover {
            transform: translateY(-4px);
            border-color: #a9d8df;
            box-shadow: 0 12px 25px rgba(22, 78, 104, .10);
        }
        .action-icon {
            font-size: 30px;
            color: var(--orpaics-orange);
            margin-bottom: 12px;
        }
        .action-card h5 {
            color: var(--orpaics-dark);
            font-weight: 700;
        }
        .action-card p {
            color: #718096;
            font-size: 14px;
            min-height: 42px;
        }
        .btn-orpaics {
            background: var(--orpaics-orange);
            color: #fff;
            border: none;
        }
        .btn-orpaics:hover {
            background: var(--orpaics-orange-dark);
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
        .topbar .btn-outline-orpaics {
            border-radius: 24px;
            padding: 10px 17px;
        }
        .topbar > a[href="profile.php"] {
            color: var(--orpaics-dark);
            font-size: 14px;
            text-decoration: none;
        }
        .topbar > a[href="profile.php"]:hover {
            color: var(--orpaics-orange-dark);
        }
        @media (max-width: 991px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                border-radius: 0 0 18px 18px;
            }
            .logout {
                position: static;
                margin-top: 20px;
            }
            .main {
                margin-left: 0;
            }
            .sidebar-menu {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 4px 10px;
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
            .sidebar-menu {
                display: block;
            }
            .topbar {
                align-items: flex-start !important;
            }
        }
    </style>
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
        <a href="index.php" class="active">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
        <!-- ACTIVITÉS -->
        <a href="activities/create.php">
            <i class="bi bi-plus-circle"></i>
            <span>Publier une activité</span>
        </a>
        <a href="activities/index.php">
            <i class="bi bi-calendar-event"></i>
            <span>Mes activités</span>
        </a>
        <!-- OPPORTUNITÉS -->
        <a href="opportunities/create.php">
            <i class="bi bi-briefcase"></i>
            <span>Publier une opportunité</span>
        </a>
        <a href="opportunities/index.php">
            <i class="bi bi-list-check"></i>
            <span>Mes opportunités</span>
        </a>
        <!-- ARTICLES -->
        <a href="blog/create.php">
            <i class="bi bi-file-earmark-plus"></i>
            <span>Publier un article</span>
        </a>
        <a href="blog/index.php">
            <i class="bi bi-newspaper"></i>
            <span>Mes articles</span>
        </a>
        <!-- ÉQUIPE -->
        <a href="team/create.php">
            <i class="bi bi-person-plus"></i>
            <span>Ajouter un membre</span>
        </a>
        <a href="team/index.php">
            <i class="bi bi-people"></i>
            <span>Équipe</span>
        </a>
    </div>
    <div class="logout">
        <a href="logout.php" class="btn btn-outline-light w-100">
            <i class="bi bi-box-arrow-right me-2"></i>
            Déconnexion
        </a>
    </div>
</aside>
<!-- =========================================================
     CONTENU PRINCIPAL
========================================================= -->
<main class="main">
    <!-- TOPBAR -->
    <div class="topbar d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1>Tableau de bord</h1>
            <div class="admin-info mt-1">
                Bienvenue,
                <strong><?= e($adminName) ?></strong>
                <?php if ($adminEmail): ?>
                    — <?= e($adminEmail) ?>
                <?php endif; ?>
            </div>
        </div>
        <a href="../index.php" target="_blank" class="btn btn-outline-orpaics">
            <i class="bi bi-globe me-2"></i>
            Voir le site
        </a>
        <a href="profile.php">
    <i class="bi bi-person-circle"></i>
    Mon profil
</a>
    </div>
    <!-- =====================================================
         STATISTIQUES
    ====================================================== -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <div class="stat-number">
                    <?= $activitiesCount ?>
                </div>
                <div class="stat-title">
                    Activités publiées
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-briefcase"></i>
                    </div>
                    <div class="stat-number">
                        <?= $opportunitiesCount ?>
                    </div>
                    <div class="stat-title">
                        Opportunités publiées
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-newspaper"></i>
                </div>
                <div class="stat-number">
                    <?= $blogCount ?>
                </div>
                <div class="stat-title">
                    Articles publiés
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-number">
                    <?= $teamCount ?>
                </div>
                <div class="stat-title">
                    Membres de l'équipe
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-chat-quote"></i>
                </div>
                <div class="stat-number">
                    <?= $testimonialsCount ?>
                </div>
                <div class="stat-title">
                    Témoignages
                </div>
            </div>
        </div>
    </div>
    <!-- =====================================================
         ACTIONS RAPIDES
    ====================================================== -->
    <div class="section-card">
        <div class="section-title">
            <i class="bi bi-lightning-charge me-2"></i>
            Actions rapides
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-xl-4">
                <div class="action-card">
                    <div class="action-icon"><i class="bi bi-briefcase"></i></div>
                    <h5>Publier une opportunité</h5>
                    <p>Ajoutez une offre d’emploi ou un appel aux dons.</p>
                    <a href="opportunities/create.php" class="btn btn-orpaics w-100">
                        <i class="bi bi-plus-lg me-2"></i>
                        Nouvelle opportunité
                    </a>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="action-card">
                    <div class="action-icon"><i class="bi bi-list-check"></i></div>
                    <h5>Gérer les opportunités</h5>
                    <p>Publiez, masquez ou supprimez les opportunités existantes.</p>
                    <a href="opportunities/index.php" class="btn btn-outline-orpaics w-100">
                        <i class="bi bi-arrow-right me-2"></i>
                        Voir les opportunités
                    </a>
                </div>
            </div>
            <!-- PUBLIER ACTIVITÉ -->
            <div class="col-md-6 col-xl-4">
                <div class="action-card">
                    <div class="action-icon">
                        <i class="bi bi-calendar2-plus"></i>
                    </div>
                    <h5>
                        Publier une activité
                    </h5>
                    <p>
                        Ajoutez une nouvelle activité ORPAIS avec ses informations,
                        images, bénéficiaires et détails.
                    </p>
                    <a
                        href="activities/create.php"
                        class="btn btn-orpaics w-100"
                    >
                        <i class="bi bi-plus-lg me-2"></i>
                        Nouvelle activité
                    </a>
                </div>
            </div>
            <!-- GÉRER ACTIVITÉS -->
            <div class="col-md-6 col-xl-4">
                <div class="action-card">
                    <div class="action-icon">
                        <i class="bi bi-list-check"></i>
                    </div>
                    <h5>
                        Gérer les activités
                    </h5>
                    <p>
                        Consultez, modifiez, publiez ou désactivez
                        les activités existantes.
                    </p>
                    <a
                        href="activities/index.php"
                        class="btn btn-outline-orpaics w-100"
                    >
                        <i class="bi bi-arrow-right me-2"></i>
                        Voir les activités
                    </a>
                </div>
            </div>
            <!-- PUBLIER ARTICLE -->
            <div class="col-md-6 col-xl-4">
                <div class="action-card">
                    <div class="action-icon">
                        <i class="bi bi-file-earmark-plus"></i>
                    </div>
                    <h5>
                        Publier un article
                    </h5>
                    <p>
                        Publiez une actualité, une information ou
                        un compte rendu sur le site ORPAIS.
                    </p>
                    <a
                        href="blog/create.php"
                        class="btn btn-orpaics w-100"
                    >
                        <i class="bi bi-plus-lg me-2"></i>
                        Nouvel article
                    </a>
                </div>
            </div>
            <!-- ÉQUIPE -->
            <div class="col-md-6 col-xl-4">
                <div class="action-card">
                    <div class="action-icon">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <h5>
                        Ajouter un membre
                    </h5>
                    <p>
                        Ajoutez un membre de l'équipe dirigeante
                        ou de l'organisation.
                    </p>
                    <a
                        href="team/create.php"
                        class="btn btn-outline-orpaics w-100"
                    >
                        <i class="bi bi-plus-lg me-2"></i>
                        Ajouter
                    </a>
                </div>
            </div>
            <!-- GÉRER ÉQUIPE -->
            <div class="col-md-6 col-xl-4">
                <div class="action-card">
                    <div class="action-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <h5>
                        Gérer l'équipe
                    </h5>
                    <p>
                        Consultez et modifiez les membres
                        affichés sur le site.
                    </p>
                    <a
                        href="team/index.php"
                        class="btn btn-outline-orpaics w-100"
                    >
                        <i class="bi bi-arrow-right me-2"></i>
                        Voir l'équipe
                    </a>
                </div>
            </div>
            <!-- SITE -->
            <div class="col-md-6 col-xl-4">
                <div class="action-card">
                    <div class="action-icon">
                        <i class="bi bi-globe2"></i>
                    </div>
                    <h5>
                        Voir le site
                    </h5>
                    <p>
                        Ouvrez le site public ORPAIS
                        dans une nouvelle fenêtre.
                    </p>
                    <a
                        href="../index.php"
                        target="_blank"
                        class="btn btn-outline-orpaics w-100"
                    >
                        <i class="bi bi-box-arrow-up-right me-2"></i>
                        Ouvrir le site
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>
</body>
</html>
