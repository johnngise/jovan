<?php

/*
|--------------------------------------------------------------------------
| ORPAIS - Profil administrateur
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';


/*
|--------------------------------------------------------------------------
| Fonction d'échappement
|--------------------------------------------------------------------------
*/

function e($value)
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}


/*
|--------------------------------------------------------------------------
| Vérifier l'administrateur connecté
|--------------------------------------------------------------------------
*/

$adminId = $_SESSION['admin_id'] ?? null;

if (!$adminId) {
    header('Location: login.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Protection CSRF
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];


/*
|--------------------------------------------------------------------------
| Récupérer l'administrateur
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        name,
        email,
        status,
        created_at
    FROM admin_users
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$adminId]);

$admin = $stmt->fetch();


if (!$admin) {
    session_destroy();

    header('Location: login.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Variables
|--------------------------------------------------------------------------
*/

$name = $admin['name'];
$email = $admin['email'];

$error = '';
$success = '';


/*
|--------------------------------------------------------------------------
| Traitement du formulaire
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | Vérification CSRF
    |--------------------------------------------------------------------------
    */

    $submittedToken = $_POST['csrf_token'] ?? '';

    if (
        empty($submittedToken) ||
        !hash_equals($_SESSION['csrf_token'], $submittedToken)
    ) {

        $error = "La session de sécurité a expiré. Veuillez réessayer.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Récupération des données
        |--------------------------------------------------------------------------
        */

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';


        /*
        |--------------------------------------------------------------------------
        | Validation du nom
        |--------------------------------------------------------------------------
        */

        if ($name === '') {

            $error = "Le nom est obligatoire.";

        }


        /*
        |--------------------------------------------------------------------------
        | Validation de l'e-mail
        |--------------------------------------------------------------------------
        */

        elseif ($email === '') {

            $error = "L'adresse e-mail est obligatoire.";

        }

        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $error = "Veuillez saisir une adresse e-mail valide.";

        }


        /*
        |--------------------------------------------------------------------------
        | Vérifier l'unicité de l'e-mail
        |--------------------------------------------------------------------------
        */

        else {

            $stmt = $pdo->prepare("
                SELECT id
                FROM admin_users
                WHERE email = ?
                AND id != ?
                LIMIT 1
            ");

            $stmt->execute([
                $email,
                $adminId
            ]);

            $existingAdmin = $stmt->fetch();


            if ($existingAdmin) {

                $error = "Cette adresse e-mail est déjà utilisée par un autre administrateur.";

            }


            /*
            |--------------------------------------------------------------------------
            | Changement du mot de passe
            |--------------------------------------------------------------------------
            */

            elseif (
                $currentPassword !== '' ||
                $newPassword !== '' ||
                $confirmPassword !== ''
            ) {

                if ($currentPassword === '') {

                    $error = "Veuillez saisir votre mot de passe actuel.";

                }

                elseif ($newPassword === '') {

                    $error = "Veuillez saisir votre nouveau mot de passe.";

                }

                elseif (strlen($newPassword) < 8) {

                    $error = "Le nouveau mot de passe doit contenir au moins 8 caractères.";

                }

                elseif ($newPassword !== $confirmPassword) {

                    $error = "Les deux nouveaux mots de passe ne correspondent pas.";

                }

                else {

                    /*
                    |--------------------------------------------------------------------------
                    | Vérifier le mot de passe actuel
                    |--------------------------------------------------------------------------
                    */

                    $stmt = $pdo->prepare("
                        SELECT password
                        FROM admin_users
                        WHERE id = ?
                        LIMIT 1
                    ");

                    $stmt->execute([$adminId]);

                    $passwordData = $stmt->fetch();


                    if (
                        !$passwordData ||
                        !password_verify(
                            $currentPassword,
                            $passwordData['password']
                        )
                    ) {

                        $error = "Le mot de passe actuel est incorrect.";

                    }

                }

            }


            /*
            |--------------------------------------------------------------------------
            | Mise à jour
            |--------------------------------------------------------------------------
            */

            if ($error === '') {

                try {

                    $pdo->beginTransaction();


                    /*
                    |--------------------------------------------------------------------------
                    | Avec nouveau mot de passe
                    |--------------------------------------------------------------------------
                    */

                    if ($newPassword !== '') {

                        $hashedPassword = password_hash(
                            $newPassword,
                            PASSWORD_DEFAULT
                        );

                        $stmt = $pdo->prepare("
                            UPDATE admin_users
                            SET
                                name = ?,
                                email = ?,
                                password = ?
                            WHERE id = ?
                        ");

                        $stmt->execute([
                            $name,
                            $email,
                            $hashedPassword,
                            $adminId
                        ]);

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Sans changement de mot de passe
                    |--------------------------------------------------------------------------
                    */

                    else {

                        $stmt = $pdo->prepare("
                            UPDATE admin_users
                            SET
                                name = ?,
                                email = ?
                            WHERE id = ?
                        ");

                        $stmt->execute([
                            $name,
                            $email,
                            $adminId
                        ]);

                    }


                    $pdo->commit();


                    /*
                    |--------------------------------------------------------------------------
                    | Mise à jour de la session
                    |--------------------------------------------------------------------------
                    */

                    $_SESSION['admin_id'] = $adminId;
                    $_SESSION['admin_name'] = $name;
                    $_SESSION['admin_email'] = $email;


                    /*
                    |--------------------------------------------------------------------------
                    | Nouveau token CSRF
                    |--------------------------------------------------------------------------
                    */

                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                    $csrfToken = $_SESSION['csrf_token'];


                    /*
                    |--------------------------------------------------------------------------
                    | Message
                    |--------------------------------------------------------------------------
                    */

                    $success = "Votre profil a été mis à jour avec succès.";


                    /*
                    |--------------------------------------------------------------------------
                    | Nettoyer les champs de mot de passe
                    |--------------------------------------------------------------------------
                    */

                    $currentPassword = '';
                    $newPassword = '';
                    $confirmPassword = '';


                    /*
                    |--------------------------------------------------------------------------
                    | Actualiser les informations affichées
                    |--------------------------------------------------------------------------
                    */

                    $admin['name'] = $name;
                    $admin['email'] = $email;


                } catch (PDOException $e) {

                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }

                    $error = "Une erreur est survenue lors de la mise à jour du profil.";

                }

            }

        }

    }

}


/*
|--------------------------------------------------------------------------
| Initiales de l'administrateur
|--------------------------------------------------------------------------
*/

$nameParts = preg_split('/\s+/', trim($name));

$initials = '';

foreach ($nameParts as $part) {

    if ($part !== '') {
        $initials .= strtoupper(substr($part, 0, 1));
    }

    if (strlen($initials) >= 2) {
        break;
    }
}

if ($initials === '') {
    $initials = 'AD';
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

    <title>Mon profil | ORPAIS Administration</title>


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

            --orpaics-dark: #164e68;
            --orpaics-blue: #4da9c2;
            --orpaics-light: #dff3f5;
            --orpaics-soft: #f7fbfb;
            --orpaics-text: #163b4d;

        }


        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            background: #f7fbfb;

            color: var(--orpaics-text);

            font-family:
                Inter,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Arial,
                sans-serif;

        }


        /*
        |--------------------------------------------------------------------------
        | SIDEBAR
        |--------------------------------------------------------------------------
        */

        .sidebar {

            position: fixed;

            top: 0;
            left: 0;

            width: 255px;

            height: 100vh;

            background:
                linear-gradient(
                    180deg,
                    #164e68 0%,
                    #286f88 100%
                );

            padding: 25px 16px;

            z-index: 1000;

            overflow-y: auto;

        }


        .sidebar-brand {

            display: flex;

            align-items: center;

            gap: 12px;

            color: white;

            text-decoration: none;

            padding: 10px 12px 25px;

            margin-bottom: 15px;

            border-bottom:
                1px solid
                rgba(255,255,255,.12);

        }


        .brand-icon {

            width: 43px;
            height: 43px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 12px;

            background: rgba(255,255,255,.12);

            font-size: 21px;

        }


        .brand-text strong {

            display: block;

            font-size: 20px;

            letter-spacing: .5px;

        }


        .brand-text span {

            display: block;

            font-size: 10px;

            color: rgba(255,255,255,.65);

            margin-top: 2px;

        }


        .sidebar-section {

            color: rgba(255,255,255,.4);

            text-transform: uppercase;

            font-size: 10px;

            font-weight: 700;

            letter-spacing: 1.2px;

            padding: 18px 12px 8px;

        }


        .sidebar a {

            display: flex;

            align-items: center;

            gap: 11px;

            color: rgba(255,255,255,.78);

            text-decoration: none;

            padding: 12px 14px;

            border-radius: 10px;

            margin-bottom: 4px;

            font-size: 14px;

            transition: all .2s ease;

        }


        .sidebar a i {

            font-size: 17px;

            width: 21px;

        }


        .sidebar a:hover {

            background: rgba(255,255,255,.09);

            color: white;

        }


        .sidebar a.active {

            background: white;

            color: var(--orpaics-dark);

            font-weight: 600;

            box-shadow:
                0 5px 15px
                rgba(0,0,0,.08);

        }


        .sidebar-divider {

            border-color: rgba(255,255,255,.12);

            margin: 20px 8px;

        }


        /*
        |--------------------------------------------------------------------------
        | MAIN
        |--------------------------------------------------------------------------
        */

        .main {

            margin-left: 255px;

            min-height: 100vh;

            padding: 30px 35px 50px;

        }


        /*
        |--------------------------------------------------------------------------
        | TOP HEADER
        |--------------------------------------------------------------------------
        */

        .page-header {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            margin-bottom: 25px;

        }


        .page-header h1 {

            margin: 0;

            color: var(--orpaics-dark);

            font-size: 27px;

            font-weight: 750;

        }


        .page-header p {

            margin: 6px 0 0;

            color: #7b8998;

            font-size: 14px;

        }


        .back-btn {

            display: inline-flex;

            align-items: center;

            gap: 7px;

            padding: 10px 15px;

            background: white;

            color: var(--orpaics-dark);

            border: 1px solid #e4eaf0;

            border-radius: 9px;

            text-decoration: none;

            font-size: 13px;

            font-weight: 600;

            transition: .2s;

        }


        .back-btn:hover {

            border-color: var(--orpaics-blue);

            color: var(--orpaics-blue);

        }


        /*
        |--------------------------------------------------------------------------
        | PROFILE HERO
        |--------------------------------------------------------------------------
        */

        .profile-hero {

            position: relative;

            overflow: hidden;

            background:
                linear-gradient(
                    115deg,
                    #073b66,
                    #0b5ed7
                );

            border-radius: 18px;

            padding: 30px 35px;

            color: white;

            margin-bottom: 25px;

            box-shadow:
                0 12px 35px
                rgba(7,59,102,.18);

        }


        .profile-hero::before {

            content: "";

            position: absolute;

            width: 260px;
            height: 260px;

            border-radius: 50%;

            background: rgba(255,255,255,.06);

            right: -80px;
            top: -120px;

        }


        .profile-hero::after {

            content: "";

            position: absolute;

            width: 180px;
            height: 180px;

            border-radius: 50%;

            background: rgba(255,255,255,.05);

            right: 150px;
            bottom: -130px;

        }


        .profile-hero-content {

            position: relative;

            z-index: 2;

            display: flex;

            align-items: center;

            gap: 22px;

        }


        .avatar {

            flex: 0 0 auto;

            width: 86px;
            height: 86px;

            border-radius: 22px;

            display: flex;

            align-items: center;
            justify-content: center;

            background: white;

            color: var(--orpaics-blue);

            font-size: 27px;

            font-weight: 800;

            box-shadow:
                0 8px 20px
                rgba(0,0,0,.15);

        }


        .profile-hero h2 {

            margin: 0 0 5px;

            font-size: 24px;

            font-weight: 750;

        }


        .profile-hero-email {

            color: rgba(255,255,255,.78);

            font-size: 14px;

            margin-bottom: 11px;

        }


        .account-status {

            display: inline-flex;

            align-items: center;

            gap: 7px;

            padding: 6px 11px;

            background: rgba(255,255,255,.13);

            border:
                1px solid
                rgba(255,255,255,.15);

            border-radius: 50px;

            font-size: 12px;

        }


        .status-dot {

            width: 7px;
            height: 7px;

            border-radius: 50%;

            background: #61d98b;

            box-shadow:
                0 0 0 4px
                rgba(97,217,139,.15);

        }


        /*
        |--------------------------------------------------------------------------
        | CONTENT GRID
        |--------------------------------------------------------------------------
        */

        .content-grid {

            display: grid;

            grid-template-columns:
                minmax(0, 1fr)
                minmax(0, 1fr);

            gap: 25px;

        }


        .panel {

            background: white;

            border:
                1px solid
                #e8edf3;

            border-radius: 16px;

            box-shadow:
                0 5px 20px
                rgba(20,45,70,.045);

            overflow: hidden;

        }


        .panel-header {

            display: flex;

            align-items: center;

            gap: 13px;

            padding: 22px 25px;

            border-bottom:
                1px solid
                #edf0f4;

        }


        .panel-icon {

            width: 40px;
            height: 40px;

            display: flex;

            align-items: center;
            justify-content: center;

            background: var(--orpaics-light);

            color: var(--orpaics-blue);

            border-radius: 10px;

            font-size: 19px;

        }


        .panel-header h3 {

            margin: 0;

            color: var(--orpaics-dark);

            font-size: 17px;

            font-weight: 700;

        }


        .panel-header p {

            margin: 3px 0 0;

            color: #8794a3;

            font-size: 12px;

        }


        .panel-body {

            padding: 25px;

        }


        /*
        |--------------------------------------------------------------------------
        | FORM
        |--------------------------------------------------------------------------
        */

        .form-group {

            margin-bottom: 20px;

        }


        .form-label {

            display: block;

            color: var(--orpaics-dark);

            font-size: 13px;

            font-weight: 650;

            margin-bottom: 8px;

        }


        .input-wrapper {

            position: relative;

        }


        .input-icon {

            position: absolute;

            left: 14px;

            top: 50%;

            transform: translateY(-50%);

            color: #9aa8b6;

            font-size: 16px;

            pointer-events: none;

        }


        .form-control {

            width: 100%;

            min-height: 46px;

            border:
                1px solid
                #dfe6ed;

            border-radius: 9px;

            padding: 10px 14px 10px 43px;

            color: var(--orpaics-text);

            font-size: 14px;

            background: white;

            transition: all .2s ease;

        }


        .password-input {

            padding-right: 48px;

        }


        .form-control:focus {

            border-color: var(--orpaics-blue);

            box-shadow:
                0 0 0 3px
                rgba(11,94,215,.09);

            outline: none;

        }


        .toggle-password {

            position: absolute;

            right: 13px;

            top: 50%;

            transform: translateY(-50%);

            border: 0;

            background: transparent;

            color: #8b99a8;

            cursor: pointer;

            padding: 4px;

        }


        .toggle-password:hover {

            color: var(--orpaics-blue);

        }


        .field-help {

            color: #8996a4;

            font-size: 11px;

            margin-top: 7px;

        }


        /*
        |--------------------------------------------------------------------------
        | SECURITY INFO
        |--------------------------------------------------------------------------
        */

        .security-note {

            display: flex;

            gap: 12px;

            background: #f5f9ff;

            border:
                1px solid
                #dcecff;

            border-radius: 10px;

            padding: 14px;

            margin-bottom: 22px;

        }


        .security-note i {

            color: var(--orpaics-blue);

            font-size: 18px;

            margin-top: 1px;

        }


        .security-note strong {

            display: block;

            color: var(--orpaics-dark);

            font-size: 12px;

            margin-bottom: 3px;

        }


        .security-note span {

            color: #738395;

            font-size: 11px;

            line-height: 1.5;

        }


        /*
        |--------------------------------------------------------------------------
        | FOOTER ACTION
        |--------------------------------------------------------------------------
        */

        .action-bar {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            margin-top: 25px;

            background: white;

            border:
                1px solid
                #e8edf3;

            border-radius: 14px;

            padding: 17px 20px;

        }


        .action-info {

            color: #8492a0;

            font-size: 12px;

        }


        .btn-orpaics {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            border: 0;

            border-radius: 9px;

            padding: 12px 20px;

            background: var(--orpaics-blue);

            color: white;

            font-size: 13px;

            font-weight: 650;

            cursor: pointer;

            transition: all .2s ease;

        }


        .btn-orpaics:hover {

            background: var(--orpaics-dark);

            transform: translateY(-1px);

        }


        .btn-cancel {

            display: inline-flex;

            align-items: center;

            gap: 7px;

            padding: 11px 16px;

            border-radius: 9px;

            background: #f2f5f8;

            color: #596979;

            text-decoration: none;

            font-size: 13px;

            font-weight: 600;

        }


        .btn-cancel:hover {

            background: #e8edf2;

            color: var(--orpaics-dark);

        }


        /*
        |--------------------------------------------------------------------------
        | ALERTS
        |--------------------------------------------------------------------------
        */

        .alert-custom {

            display: flex;

            align-items: flex-start;

            gap: 12px;

            border-radius: 11px;

            padding: 14px 16px;

            margin-bottom: 22px;

            font-size: 13px;

        }


        .alert-success-custom {

            background: #edf9f2;

            border:
                1px solid
                #cdeedb;

            color: #207344;

        }


        .alert-danger-custom {

            background: #fff1f1;

            border:
                1px solid
                #f3d0d0;

            color: #a63232;

        }


        .alert-custom i {

            font-size: 17px;

        }


        /*
        |--------------------------------------------------------------------------
        | MOBILE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 1100px) {

            .content-grid {

                grid-template-columns: 1fr;

            }

        }


        @media (max-width: 850px) {

            .sidebar {

                position: relative;

                width: 100%;

                height: auto;

                min-height: auto;

            }


            .main {

                margin-left: 0;

                padding: 22px 18px 40px;

            }


            .page-header {

                align-items: flex-start;

                flex-direction: column;

            }

        }


        @media (max-width: 600px) {

            .profile-hero {

                padding: 25px 20px;

            }


            .profile-hero-content {

                align-items: flex-start;

            }


            .avatar {

                width: 68px;
                height: 68px;

                border-radius: 18px;

                font-size: 21px;

            }


            .profile-hero h2 {

                font-size: 20px;

            }


            .panel-body {

                padding: 20px;

            }


            .action-bar {

                align-items: stretch;

                flex-direction: column;

            }


            .action-bar > div:last-child {

                display: flex;

                flex-direction: column;

                gap: 8px;

            }


            .btn-orpaics,
            .btn-cancel {

                width: 100%;

            }

        }

    </style>

    <link href="../assets/css/admin-common.css" rel="stylesheet">
</head>


<body>


<!-- =========================================================
     SIDEBAR
========================================================= -->

<aside class="sidebar">


    <a href="index.php" class="sidebar-brand">

        <div class="brand-icon">

            <i class="bi bi-building"></i>

        </div>

        <div class="brand-text">

            <strong>ORPAIS</strong>

            <span>ADMINISTRATION</span>

        </div>

    </a>


    <div class="sidebar-section">
        Principal
    </div>


    <a href="index.php">

        <i class="bi bi-grid-1x2-fill"></i>

        <span>Tableau de bord</span>

    </a>


    <a href="activities/index.php">

        <i class="bi bi-activity"></i>

        <span>Activités</span>

    </a>


    <a href="blog/index.php">

        <i class="bi bi-newspaper"></i>

        <span>Blog</span>

    </a>


    <a href="team/index.php">

        <i class="bi bi-people"></i>

        <span>Équipe</span>

    </a>


    <a href="testimonials/index.php">

        <i class="bi bi-chat-quote"></i>

        <span>Témoignages</span>

    </a>


    <div class="sidebar-section">
        Compte
    </div>


    <a href="profile.php" class="active">

        <i class="bi bi-person-circle"></i>

        <span>Mon profil</span>

    </a>


    <hr class="sidebar-divider">


    <a href="logout.php">

        <i class="bi bi-box-arrow-right"></i>

        <span>Déconnexion</span>

    </a>

</aside>



<!-- =========================================================
     MAIN
========================================================= -->

<main class="main">


    <!-- HEADER -->

    <div class="page-header">

        <div>

            <h1>
                Mon profil
            </h1>

            <p>
                Gérez vos informations et la sécurité de votre compte.
            </p>

        </div>


        <a href="index.php" class="back-btn">

            <i class="bi bi-arrow-left"></i>

            Retour au tableau de bord

        </a>

    </div>



    <!-- =====================================================
         PROFILE HERO
    ====================================================== -->

    <section class="profile-hero">

        <div class="profile-hero-content">


            <div class="avatar">

                <?= e($initials) ?>

            </div>


            <div>

                <h2>
                    <?= e($name) ?>
                </h2>


                <div class="profile-hero-email">

                    <i class="bi bi-envelope me-1"></i>

                    <?= e($email) ?>

                </div>


                <div class="account-status">

                    <span class="status-dot"></span>

                    <?php if ($admin['status'] === 'active'): ?>

                        Compte administrateur actif

                    <?php else: ?>

                        Compte administrateur inactif

                    <?php endif; ?>

                </div>

            </div>


        </div>

    </section>



    <!-- =====================================================
         MESSAGES
    ====================================================== -->

    <?php if ($success): ?>

        <div class="alert-custom alert-success-custom">

            <i class="bi bi-check-circle-fill"></i>

            <div>

                <strong>Modification enregistrée</strong>

                <div>
                    <?= e($success) ?>
                </div>

            </div>

        </div>

    <?php endif; ?>


    <?php if ($error): ?>

        <div class="alert-custom alert-danger-custom">

            <i class="bi bi-exclamation-triangle-fill"></i>

            <div>

                <strong>Attention</strong>

                <div>
                    <?= e($error) ?>
                </div>

            </div>

        </div>

    <?php endif; ?>



    <!-- =====================================================
         FORM
    ====================================================== -->

    <form method="POST" autocomplete="off">


        <input
            type="hidden"
            name="csrf_token"
            value="<?= e($csrfToken) ?>"
        >


        <div class="content-grid">


            <!-- =================================================
                 INFORMATIONS
            ================================================== -->

            <section class="panel">


                <div class="panel-header">

                    <div class="panel-icon">

                        <i class="bi bi-person-vcard"></i>

                    </div>

                    <div>

                        <h3>
                            Informations du compte
                        </h3>

                        <p>
                            Vos informations administratives
                        </p>

                    </div>

                </div>


                <div class="panel-body">


                    <!-- NOM -->

                    <div class="form-group">

                        <label
                            for="name"
                            class="form-label"
                        >
                            Nom
                        </label>


                        <div class="input-wrapper">

                            <i class="bi bi-person input-icon"></i>


                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-control"
                                value="<?= e($name) ?>"
                                placeholder="Votre nom"
                                required
                            >

                        </div>

                    </div>



                    <!-- EMAIL -->

                    <div class="form-group">

                        <label
                            for="email"
                            class="form-label"
                        >
                            Adresse e-mail
                        </label>


                        <div class="input-wrapper">

                            <i class="bi bi-envelope input-icon"></i>


                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                value="<?= e($email) ?>"
                                placeholder="admin@orpaics.org"
                                required
                            >

                        </div>


                        <div class="field-help">

                            Cette adresse est utilisée pour votre compte administrateur.

                        </div>

                    </div>



                    <!-- STATUT -->

                    <div class="form-group mb-0">

                        <label class="form-label">
                            Statut du compte
                        </label>


                        <div class="input-wrapper">

                            <i class="bi bi-shield-check input-icon"></i>


                            <input
                                type="text"
                                class="form-control"
                                value="<?= $admin['status'] === 'active' ? 'Compte actif' : 'Compte inactif' ?>"
                                readonly
                            >

                        </div>

                    </div>

                </div>

            </section>



            <!-- =================================================
                 SECURITE
            ================================================== -->

            <section class="panel">


                <div class="panel-header">

                    <div class="panel-icon">

                        <i class="bi bi-shield-lock"></i>

                    </div>

                    <div>

                        <h3>
                            Sécurité
                        </h3>

                        <p>
                            Modifier votre mot de passe
                        </p>

                    </div>

                </div>


                <div class="panel-body">


                    <div class="security-note">

                        <i class="bi bi-lock-fill"></i>

                        <div>

                            <strong>
                                Protégez votre compte
                            </strong>

                            <span>
                                Laissez les champs vides si vous ne souhaitez
                                pas modifier votre mot de passe.
                            </span>

                        </div>

                    </div>



                    <!-- MOT DE PASSE ACTUEL -->

                    <div class="form-group">

                        <label
                            for="current_password"
                            class="form-label"
                        >
                            Mot de passe actuel
                        </label>


                        <div class="input-wrapper">

                            <i class="bi bi-key input-icon"></i>


                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                class="form-control password-input"
                                autocomplete="current-password"
                                placeholder="Votre mot de passe actuel"
                            >


                            <button
                                type="button"
                                class="toggle-password"
                                data-target="current_password"
                                aria-label="Afficher le mot de passe"
                            >

                                <i class="bi bi-eye"></i>

                            </button>

                        </div>

                    </div>



                    <!-- NOUVEAU MOT DE PASSE -->

                    <div class="form-group">

                        <label
                            for="new_password"
                            class="form-label"
                        >
                            Nouveau mot de passe
                        </label>


                        <div class="input-wrapper">

                            <i class="bi bi-lock input-icon"></i>


                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                class="form-control password-input"
                                autocomplete="new-password"
                                minlength="8"
                                placeholder="Minimum 8 caractères"
                            >


                            <button
                                type="button"
                                class="toggle-password"
                                data-target="new_password"
                                aria-label="Afficher le mot de passe"
                            >

                                <i class="bi bi-eye"></i>

                            </button>

                        </div>


                        <div class="field-help">

                            Minimum 8 caractères.

                        </div>

                    </div>



                    <!-- CONFIRMATION -->

                    <div class="form-group mb-0">

                        <label
                            for="confirm_password"
                            class="form-label"
                        >
                            Confirmer le nouveau mot de passe
                        </label>


                        <div class="input-wrapper">

                            <i class="bi bi-lock-fill input-icon"></i>


                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="form-control password-input"
                                autocomplete="new-password"
                                minlength="8"
                                placeholder="Répétez le nouveau mot de passe"
                            >


                            <button
                                type="button"
                                class="toggle-password"
                                data-target="confirm_password"
                                aria-label="Afficher le mot de passe"
                            >

                                <i class="bi bi-eye"></i>

                            </button>

                        </div>

                    </div>

                </div>

            </section>


        </div>



        <!-- =====================================================
             ACTION BAR
        ====================================================== -->

        <div class="action-bar">


            <div class="action-info">

                <i class="bi bi-info-circle me-1"></i>

                Vos modifications seront enregistrées immédiatement.

            </div>


            <div class="d-flex gap-2">

                <a
                    href="index.php"
                    class="btn-cancel"
                >

                    <i class="bi bi-x-lg"></i>

                    Annuler

                </a>


                <button
                    type="submit"
                    class="btn-orpaics"
                >

                    <i class="bi bi-check-lg"></i>

                    Enregistrer les modifications

                </button>

            </div>


        </div>


    </form>


</main>



<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script>

document.addEventListener('DOMContentLoaded', function () {


    /*
    |--------------------------------------------------------------------------
    | Afficher / masquer les mots de passe
    |--------------------------------------------------------------------------
    */

    const toggleButtons =
        document.querySelectorAll('.toggle-password');


    toggleButtons.forEach(function (button) {

        button.addEventListener('click', function () {

            const targetId =
                button.getAttribute('data-target');

            const input =
                document.getElementById(targetId);

            const icon =
                button.querySelector('i');


            if (!input) {
                return;
            }


            if (input.type === 'password') {

                input.type = 'text';

                icon.classList.remove('bi-eye');

                icon.classList.add('bi-eye-slash');

                button.setAttribute(
                    'aria-label',
                    'Masquer le mot de passe'
                );

            } else {

                input.type = 'password';

                icon.classList.remove('bi-eye-slash');

                icon.classList.add('bi-eye');

                button.setAttribute(
                    'aria-label',
                    'Afficher le mot de passe'
                );

            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | Vérification côté navigateur
    |--------------------------------------------------------------------------
    */

    const form =
        document.querySelector('form');

    const newPassword =
        document.getElementById('new_password');

    const confirmPassword =
        document.getElementById('confirm_password');


    form.addEventListener('submit', function (event) {

        if (
            newPassword.value !== '' ||
            confirmPassword.value !== ''
        ) {

            if (newPassword.value.length < 8) {

                event.preventDefault();

                alert(
                    'Le nouveau mot de passe doit contenir au moins 8 caractères.'
                );

                newPassword.focus();

                return;

            }


            if (
                newPassword.value !==
                confirmPassword.value
            ) {

                event.preventDefault();

                alert(
                    'Les deux nouveaux mots de passe ne correspondent pas.'
                );

                confirmPassword.focus();

                return;

            }

        }

    });

});

</script>


</body>

</html>


