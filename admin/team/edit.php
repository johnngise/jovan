<?php
session_start();

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/database.php';

/*
|--------------------------------------------------------------------------
| Fonction de protection HTML
|--------------------------------------------------------------------------
*/
function e($value)
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/*
|--------------------------------------------------------------------------
| Vérification de l'ID
|--------------------------------------------------------------------------
*/
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php?error=invalid_id');
    exit;
}

/*
|--------------------------------------------------------------------------
| Récupération du membre
|--------------------------------------------------------------------------
*/
try {

    $stmt = $pdo->prepare("
        SELECT
            id,
            name,
            position,
            photo,
            status,
            display_order
        FROM team_members
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $id
    ]);

    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
        header('Location: index.php?error=member_not_found');
        exit;
    }

} catch (PDOException $e) {

    die(
        'Erreur lors de la récupération du membre : '
        . e($e->getMessage())
    );
}

/*
|--------------------------------------------------------------------------
| Variables
|--------------------------------------------------------------------------
*/
$error = '';
$success = '';

$name = $member['name'];
$position = $member['position'];
$status = $member['status'];
$displayOrder = $member['display_order'];
$currentPhoto = $member['photo'];

/*
|--------------------------------------------------------------------------
| Traitement du formulaire
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | Récupération des données
    |--------------------------------------------------------------------------
    */
    $name = trim($_POST['name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $displayOrder = filter_input(
        INPUT_POST,
        'display_order',
        FILTER_VALIDATE_INT
    );

    if ($displayOrder === false || $displayOrder === null) {
        $displayOrder = 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Validation du nom
    |--------------------------------------------------------------------------
    */
    if ($name === '') {

        $error = 'Veuillez saisir le nom du membre.';

    } elseif (mb_strlen($name) > 150) {

        $error = 'Le nom ne peut pas dépasser 150 caractères.';

    }

    /*
    |--------------------------------------------------------------------------
    | Validation de la fonction
    |--------------------------------------------------------------------------
    */
    if ($error === '' && mb_strlen($position) > 200) {

        $error = 'La fonction ne peut pas dépasser 200 caractères.';

    }

    /*
    |--------------------------------------------------------------------------
    | Validation du statut
    |--------------------------------------------------------------------------
    */
    if (
        $error === '' &&
        !in_array($status, ['active', 'inactive'], true)
    ) {

        $error = 'Le statut sélectionné est invalide.';

    }

    /*
    |--------------------------------------------------------------------------
    | Gestion de la photo
    |--------------------------------------------------------------------------
    */
    $newPhotoPath = $currentPhoto;
    $uploadedNewPhoto = false;
    $newPhotoPhysicalPath = null;

    if ($error === '' && isset($_FILES['photo'])) {

        $file = $_FILES['photo'];

        /*
        |--------------------------------------------------------------------------
        | Aucun fichier sélectionné
        |--------------------------------------------------------------------------
        */
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {

            // On conserve la photo actuelle.

        } elseif ($file['error'] !== UPLOAD_ERR_OK) {

            $error = 'Une erreur est survenue lors du téléchargement de la photo.';

        } else {

            /*
            |--------------------------------------------------------------------------
            | Taille maximale : 5 MB
            |--------------------------------------------------------------------------
            */
            $maxSize = 5 * 1024 * 1024;

            if ($file['size'] > $maxSize) {

                $error = 'La photo est trop volumineuse. Taille maximale : 5 MB.';

            } else {

                /*
                |--------------------------------------------------------------------------
                | Vérification MIME réelle
                |--------------------------------------------------------------------------
                */
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($file['tmp_name']);

                $allowedTypes = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp'
                ];

                if (!isset($allowedTypes[$mimeType])) {

                    $error = 'Format non autorisé. Utilisez JPG, PNG ou WEBP.';

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Vérification que le fichier est réellement une image
                    |--------------------------------------------------------------------------
                    */
                    $imageInfo = @getimagesize($file['tmp_name']);

                    if ($imageInfo === false) {

                        $error = 'Le fichier sélectionné n’est pas une image valide.';

                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | Dossier de destination
                        |--------------------------------------------------------------------------
                        |
                        | admin/team/edit.php
                        |        — ../../
                        | ORPAIS/
                        |        —
                        | assets/img/team/
                        |
                        */
                        $uploadDirectory =
                            __DIR__ . '/../../assets/img/team/';

                        if (!is_dir($uploadDirectory)) {

                            if (!mkdir(
                                $uploadDirectory,
                                0755,
                                true
                            )) {

                                $error =
                                    'Impossible de créer le dossier des photos.';
                            }
                        }

                        if ($error === '') {

                            /*
                            |--------------------------------------------------------------------------
                            | Nom de fichier sécurisé et unique
                            |--------------------------------------------------------------------------
                            */
                            $extension = $allowedTypes[$mimeType];

                            $newFileName =
                                'team_' .
                                $id .
                                '_' .
                                bin2hex(random_bytes(8)) .
                                '.' .
                                $extension;

                            $destination =
                                $uploadDirectory . $newFileName;

                            /*
                            |--------------------------------------------------------------------------
                            | Déplacement du fichier
                            |--------------------------------------------------------------------------
                            */
                            if (
                                !move_uploaded_file(
                                    $file['tmp_name'],
                                    $destination
                                )
                            ) {

                                $error =
                                    'Impossible d’enregistrer la nouvelle photo.';

                            } else {

                                /*
                                |--------------------------------------------------------------------------
                                | Chemin enregistré en base de données
                                |--------------------------------------------------------------------------
                                */
                                $newPhotoPath =
                                    'assets/img/team/' . $newFileName;

                                $uploadedNewPhoto = true;
                                $newPhotoPhysicalPath = $destination;
                            }
                        }
                    }
                }
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Mise à jour MySQL
    |--------------------------------------------------------------------------
    */
    if ($error === '') {

        try {

            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                UPDATE team_members
                SET
                    name = :name,
                    position = :position,
                    photo = :photo,
                    status = :status,
                    display_order = :display_order
                WHERE id = :id
            ");

            $stmt->execute([
                ':name' => $name,
                ':position' => $position,
                ':photo' => $newPhotoPath,
                ':status' => $status,
                ':display_order' => $displayOrder,
                ':id' => $id
            ]);

            $pdo->commit();

            /*
            |--------------------------------------------------------------------------
            | Suppression de l'ancienne photo
            |--------------------------------------------------------------------------
            */
            if (
                $uploadedNewPhoto &&
                !empty($currentPhoto)
            ) {

                /*
                | On ne supprime que les photos stockées
                | dans assets/img/team/
                */
                if (
                    str_starts_with(
                        $currentPhoto,
                        'assets/img/team/'
                    )
                ) {

                    $oldPhoto =
                        __DIR__ .
                        '/../../' .
                        $currentPhoto;

                    if (
                        is_file($oldPhoto) &&
                        $oldPhoto !== $newPhotoPhysicalPath
                    ) {

                        @unlink($oldPhoto);
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Message de succès
            |--------------------------------------------------------------------------
            */
            $success =
                'Le profil de ' .
                $name .
                ' a été modifié avec succès.';

            /*
            |--------------------------------------------------------------------------
            | Actualisation des données affichées
            |--------------------------------------------------------------------------
            */
            $currentPhoto = $newPhotoPath;

            $member['name'] = $name;
            $member['position'] = $position;
            $member['photo'] = $newPhotoPath;
            $member['status'] = $status;
            $member['display_order'] = $displayOrder;

        } catch (PDOException $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            /*
            |--------------------------------------------------------------------------
            | Si la nouvelle photo a déjà été déplacée,
            | on la supprime si la sauvegarde DB échoue.
            |--------------------------------------------------------------------------
            */
            if (
                $uploadedNewPhoto &&
                $newPhotoPhysicalPath &&
                is_file($newPhotoPhysicalPath)
            ) {

                @unlink($newPhotoPhysicalPath);
            }

            $error =
                'Erreur lors de la modification du membre : ' .
                $e->getMessage();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | En cas d'erreur après upload
    |--------------------------------------------------------------------------
    */
    if (
        $error !== '' &&
        $uploadedNewPhoto &&
        $newPhotoPhysicalPath &&
        is_file($newPhotoPhysicalPath)
    ) {

        @unlink($newPhotoPhysicalPath);
    }
}

/*
|--------------------------------------------------------------------------
| URL de la photo actuelle
|--------------------------------------------------------------------------
*/
$photoUrl = '';

if (!empty($currentPhoto)) {

    if (str_starts_with($currentPhoto, 'http://') ||
        str_starts_with($currentPhoto, 'https://')) {

        $photoUrl = $currentPhoto;

    } else {

        $photoUrl = '../../' . ltrim($currentPhoto, '/');
    }
}

/*
|--------------------------------------------------------------------------
| Initiales
|--------------------------------------------------------------------------
*/
$initials = 'OR';

$nameParts = preg_split('/\s+/', trim($name));

if (count($nameParts) >= 2) {

    $initials =
        strtoupper(
            mb_substr($nameParts[0], 0, 1) .
            mb_substr($nameParts[count($nameParts) - 1], 0, 1)
        );

} elseif (!empty($name)) {

    $initials =
        strtoupper(
            mb_substr($name, 0, 2)
        );
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

    <title>Modifier le membre | ORPAIS</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

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
            --orpaics-muted: #718096;
            --white: #ffffff;
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

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            background: linear-gradient(
                180deg,
                #073b66,
                #052d4e
            );
            color: #fff;
            padding: 25px 15px;
            z-index: 1000;
        }

        .brand {
            text-align: center;
            font-size: 25px;
            font-weight: 800;
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
            border-radius: 9px;
            margin-bottom: 7px;
            transition: .2s ease;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,.13);
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

        .main {
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
        }

        .topbar {
            background: #fff;
            border-radius: 18px;
            padding: 22px 25px;
            margin-bottom: 25px;
            box-shadow: 0 6px 25px rgba(7,59,102,.06);
        }

        .topbar h1 {
            margin: 0;
            color: var(--orpaics-dark);
            font-size: 27px;
            font-weight: 800;
        }

        .topbar p {
            margin: 6px 0 0;
            color: var(--orpaics-muted);
            font-size: 14px;
        }

        .content-card {
            background: #fff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 30px rgba(7,59,102,.06);
        }

        .profile-preview {
            text-align: center;
            background: linear-gradient(
                145deg,
                #f5f9ff,
                #eaf3ff
            );
            border-radius: 18px;
            padding: 30px 20px;
            height: 100%;
        }

        .photo-container {
            position: relative;
            width: 180px;
            height: 180px;
            margin: 0 auto 20px;
        }

        .profile-photo {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            object-fit: cover;
            border: 6px solid #fff;
            box-shadow: 0 12px 30px rgba(7,59,102,.18);
            background: #dceafa;
        }

        .photo-placeholder {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            border: 6px solid #fff;
            background: linear-gradient(
                135deg,
                #0b5ed7,
                #073b66
            );
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 52px;
            font-weight: 800;
            margin: auto;
            box-shadow: 0 12px 30px rgba(7,59,102,.18);
        }

        .photo-edit-button {
            position: absolute;
            right: 0;
            bottom: 8px;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--orpaics-blue);
            color: #fff;
            border: 4px solid #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: .2s;
        }

        .photo-edit-button:hover {
            transform: scale(1.08);
            background: var(--orpaics-dark);
        }

        .preview-name {
            color: var(--orpaics-dark);
            font-size: 21px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .preview-position {
            color: var(--orpaics-blue);
            font-size: 14px;
            font-weight: 600;
        }

        .preview-status {
            display: inline-block;
            margin-top: 15px;
            padding: 6px 14px;
            border-radius: 30px;
            background: #dff7e8;
            color: #18864b;
            font-size: 12px;
            font-weight: 700;
        }

        .form-section-title {
            color: var(--orpaics-dark);
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 25px;
        }

        .form-label {
            color: var(--orpaics-dark);
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            border: 1px solid #dce7f2;
            border-radius: 11px;
            padding: 12px 14px;
            min-height: 48px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--orpaics-blue);
            box-shadow: 0 0 0 4px rgba(11,94,215,.10);
        }

        .help-text {
            color: var(--orpaics-muted);
            font-size: 12px;
            margin-top: 7px;
        }

        .upload-zone {
            border: 2px dashed #cbdbea;
            border-radius: 14px;
            padding: 20px;
            text-align: center;
            background: #f9fbfe;
            transition: .2s;
            cursor: pointer;
        }

        .upload-zone:hover {
            border-color: var(--orpaics-blue);
            background: var(--orpaics-light);
        }

        .upload-zone i {
            font-size: 32px;
            color: var(--orpaics-blue);
        }

        .upload-zone strong {
            display: block;
            margin-top: 8px;
            color: var(--orpaics-dark);
        }

        .upload-zone small {
            display: block;
            color: var(--orpaics-muted);
            margin-top: 5px;
        }

        #photo {
            display: none;
        }

        .btn-orpaics {
            background: var(--orpaics-blue);
            border: none;
            color: #fff;
            border-radius: 10px;
            padding: 12px 20px;
            font-weight: 700;
        }

        .btn-orpaics:hover {
            background: var(--orpaics-dark);
            color: #fff;
        }

        .btn-back {
            border: 1px solid #dce7f2;
            background: #fff;
            color: var(--orpaics-dark);
            border-radius: 10px;
            padding: 12px 20px;
            font-weight: 700;
        }

        .btn-back:hover {
            background: var(--orpaics-soft);
        }

        .alert {
            border: none;
            border-radius: 12px;
        }

        @media (max-width: 991px) {

            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
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

            .content-card {
                padding: 20px;
            }

            .topbar {
                padding: 18px;
            }

            .topbar h1 {
                font-size: 22px;
            }

            .photo-container,
            .profile-photo,
            .photo-placeholder {
                width: 150px;
                height: 150px;
            }

            .photo-placeholder {
                font-size: 42px;
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

        <a href="index.php" class="active">
            <i class="bi bi-people"></i>
            <span>Équipe</span>
        </a>

        <a href="create.php">
            <i class="bi bi-person-plus"></i>
            <span>Ajouter un membre</span>
        </a>

    </div>

    <div class="logout">

        <a
            href="../logout.php"
            class="btn btn-outline-light w-100"
        >
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
                <i class="bi bi-person-gear me-2"></i>
                Modifier le membre
            </h1>

            <p>
                Modifiez les informations et la photo affichées sur le site ORPAIS.
            </p>

        </div>

        <a
            href="index.php"
            class="btn btn-back"
        >
            <i class="bi bi-arrow-left me-2"></i>
            Retour à l'équipe
        </a>

    </div>


    <!-- ALERTES -->

    <?php if ($success): ?>

        <div
            class="alert alert-success d-flex align-items-center gap-2"
            role="alert"
        >

            <i class="bi bi-check-circle-fill"></i>

            <div>
                <?= e($success) ?>
            </div>

        </div>

    <?php endif; ?>


    <?php if ($error): ?>

        <div
            class="alert alert-danger d-flex align-items-center gap-2"
            role="alert"
        >

            <i class="bi bi-exclamation-triangle-fill"></i>

            <div>
                <?= e($error) ?>
            </div>

        </div>

    <?php endif; ?>


    <!-- FORMULAIRE -->

    <div class="content-card">

        <form
            method="POST"
            enctype="multipart/form-data"
            id="editTeamForm"
        >

            <div class="row g-4">

                <!-- =================================================
                     APERÉU
                ================================================== -->

                <div class="col-lg-4">

                    <div class="profile-preview">

                        <div class="photo-container">

                            <?php if ($photoUrl): ?>

                                <img
                                    src="<?= e($photoUrl) ?>"
                                    id="previewImage"
                                    class="profile-photo"
                                    alt="Photo de <?= e($name) ?>"
                                >

                                <div
                                    id="previewInitials"
                                    class="photo-placeholder d-none"
                                >
                                    <?= e($initials) ?>
                                </div>

                            <?php else: ?>

                                <div
                                    id="previewInitials"
                                    class="photo-placeholder"
                                >
                                    <?= e($initials) ?>
                                </div>

                                <img
                                    src=""
                                    id="previewImage"
                                    class="profile-photo d-none"
                                    alt="Aperçu"
                                >

                            <?php endif; ?>


                            <label
                                for="photo"
                                class="photo-edit-button"
                                title="Choisir une photo"
                            >

                                <i class="bi bi-camera-fill"></i>

                            </label>

                        </div>


                        <div
                            class="preview-name"
                            id="previewName"
                        >
                            <?= e($name) ?>
                        </div>

                        <div
                            class="preview-position"
                            id="previewPosition"
                        >
                            <?= e($position ?: 'Fonction non renseignée') ?>
                        </div>

                        <span
                            class="preview-status"
                            id="previewStatus"
                        >
                            <?= $status === 'active' ? 'Profil actif' : 'Profil inactif' ?>
                        </span>

                    </div>

                </div>


                <!-- =================================================
                     FORMULAIRE
                ================================================== -->

                <div class="col-lg-8">

                    <div class="form-section-title">
                        Informations du membre
                    </div>


                    <!-- NOM -->

                    <div class="mb-4">

                        <label
                            for="name"
                            class="form-label"
                        >
                            Nom complet
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="name"
                            name="name"
                            value="<?= e($name) ?>"
                            maxlength="150"
                            required
                        >

                        <div class="help-text">
                            Maximum 150 caractères.
                        </div>

                    </div>


                    <!-- FONCTION -->

                    <div class="mb-4">

                        <label
                            for="position"
                            class="form-label"
                        >
                            Fonction / Poste
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="position"
                            name="position"
                            value="<?= e($position) ?>"
                            maxlength="200"
                            placeholder="Ex. Président du Conseil d'Administration"
                        >

                        <div class="help-text">
                            Cette fonction sera affichée sous le nom sur le site.
                        </div>

                    </div>


                    <div class="row g-3">

                        <!-- STATUT -->

                        <div class="col-md-6">

                            <label
                                for="status"
                                class="form-label"
                            >
                                Statut
                            </label>

                            <select
                                class="form-select"
                                id="status"
                                name="status"
                            >

                                <option
                                    value="active"
                                    <?= $status === 'active' ? 'selected' : '' ?>
                                >
                                    Actif — affiché sur le site
                                </option>

                                <option
                                    value="inactive"
                                    <?= $status === 'inactive' ? 'selected' : '' ?>
                                >
                                    Inactif — masqué du site
                                </option>

                            </select>

                        </div>


                        <!-- ORDRE -->

                        <div class="col-md-6">

                            <label
                                for="display_order"
                                class="form-label"
                            >
                                Ordre d'affichage
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                id="display_order"
                                name="display_order"
                                value="<?= e($displayOrder) ?>"
                                min="0"
                                max="999"
                            >

                            <div class="help-text">
                                0 = affiché en premier.
                            </div>

                        </div>

                    </div>


                    <!-- PHOTO -->

                    <div class="mt-4">

                        <label class="form-label">
                            Photo du membre
                        </label>

                        <label
                            for="photo"
                            class="upload-zone d-block"
                            id="uploadZone"
                        >

                            <i class="bi bi-cloud-arrow-up"></i>

                            <strong id="fileName">
                                Choisir une photo
                            </strong>

                            <small>
                                JPG, PNG ou WEBP — maximum 5 MB
                            </small>

                        </label>

                        <input
                            type="file"
                            id="photo"
                            name="photo"
                            accept="image/jpeg,image/png,image/webp"
                        >

                        <div
                            class="help-text"
                            id="imageValidation"
                        >
                            Vous pouvez sélectionner une nouvelle photo depuis votre téléphone ou votre ordinateur.
                        </div>

                    </div>


                    <!-- BOUTONS -->

                    <div class="d-flex flex-wrap gap-3 mt-4">

                        <button
                            type="submit"
                            class="btn btn-orpaics"
                            id="submitButton"
                        >

                            <i class="bi bi-check2-circle me-2"></i>

                            Enregistrer les modifications

                        </button>


                        <a
                            href="index.php"
                            class="btn btn-back"
                        >

                            Annuler

                        </a>

                    </div>

                </div>

            </div>

        </form>

    </div>

</main>


<script>

    /*
    |--------------------------------------------------------------------------
    | Aperçu du nom
    |--------------------------------------------------------------------------
    */

    const nameInput = document.getElementById('name');

    const positionInput = document.getElementById('position');

    const statusInput = document.getElementById('status');

    const previewName = document.getElementById('previewName');

    const previewPosition =
        document.getElementById('previewPosition');

    const previewStatus =
        document.getElementById('previewStatus');


    nameInput.addEventListener('input', function () {

        previewName.textContent =
            this.value.trim() || 'Nom du membre';

    });


    positionInput.addEventListener('input', function () {

        previewPosition.textContent =
            this.value.trim() || 'Fonction non renseignée';

    });


    statusInput.addEventListener('change', function () {

        if (this.value === 'active') {

            previewStatus.textContent = 'Profil actif';

            previewStatus.style.background = '#dff7e8';

            previewStatus.style.color = '#18864b';

        } else {

            previewStatus.textContent = 'Profil inactif';

            previewStatus.style.background = '#fce4e4';

            previewStatus.style.color = '#b42318';

        }

    });


    /*
    |--------------------------------------------------------------------------
    | Gestion de la photo
    |--------------------------------------------------------------------------
    */

    const photoInput =
        document.getElementById('photo');

    const previewImage =
        document.getElementById('previewImage');

    const previewInitials =
        document.getElementById('previewInitials');

    const fileName =
        document.getElementById('fileName');

    const imageValidation =
        document.getElementById('imageValidation');

    const uploadZone =
        document.getElementById('uploadZone');


    photoInput.addEventListener('change', function () {

        const file = this.files[0];

        if (!file) {

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Taille maximale
        |--------------------------------------------------------------------------
        */

        const maxSize = 5 * 1024 * 1024;

        if (file.size > maxSize) {

            alert(
                'La photo est trop volumineuse. Taille maximale : 5 MB.'
            );

            this.value = '';

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Formats autorisés
        |--------------------------------------------------------------------------
        */

        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (!allowedTypes.includes(file.type)) {

            alert(
                'Format non autorisé. Utilisez JPG, PNG ou WEBP.'
            );

            this.value = '';

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Affichage du nom
        |--------------------------------------------------------------------------
        */

        fileName.textContent = file.name;

        imageValidation.textContent =
            'Photo valide. Aperçu ci-dessous avant enregistrement.';

        imageValidation.style.color = '#18864b';


        /*
        |--------------------------------------------------------------------------
        | Aperçu
        |--------------------------------------------------------------------------
        */

        const reader = new FileReader();

        reader.onload = function (event) {

            previewImage.src =
                event.target.result;

            previewImage.classList.remove('d-none');

            previewInitials.classList.add('d-none');

        };

        reader.readAsDataURL(file);

    });


    /*
    |--------------------------------------------------------------------------
    | Empêcher l'envoi accidentel pendant la validation
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('editTeamForm')
        .addEventListener('submit', function (event) {

            const name =
                nameInput.value.trim();

            if (!name) {

                event.preventDefault();

                alert(
                    'Veuillez saisir le nom du membre.'
                );

                nameInput.focus();

                return;

            }

            const file =
                photoInput.files[0];

            if (file) {

                const maxSize =
                    5 * 1024 * 1024;

                const allowedTypes = [
                    'image/jpeg',
                    'image/png',
                    'image/webp'
                ];

                if (file.size > maxSize) {

                    event.preventDefault();

                    alert(
                        'La photo dépasse la taille maximale de 5 MB.'
                    );

                    return;

                }

                if (!allowedTypes.includes(file.type)) {

                    event.preventDefault();

                    alert(
                        'Le format de la photo n’est pas autorisé.'
                    );

                    return;

                }

            }


            const submitButton =
                document.getElementById('submitButton');

            submitButton.disabled = true;

            submitButton.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2"></span>' +
                'Enregistrement...';

        });

</script>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>
</html>




