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
| Connexion à la base de données
|--------------------------------------------------------------------------
*/
require_once __DIR__ . '/../../config/database.php';
/*
|--------------------------------------------------------------------------
| Fonction de protection HTML
|--------------------------------------------------------------------------
*/
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
/*
|--------------------------------------------------------------------------
| Génération du slug
|--------------------------------------------------------------------------
*/
function generateSlug($text)
{
    $text = trim($text);
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text !== '' ? $text : 'activite-' . time();
}
/*
|--------------------------------------------------------------------------
| Dossiers d'upload
|--------------------------------------------------------------------------
*/
$uploadDirectory = __DIR__ . '/../../uploads/activities/';
$uploadWebPath = 'uploads/activities/';
/*
|--------------------------------------------------------------------------
| Création du dossier s'il n'existe pas
|--------------------------------------------------------------------------
*/
if (!is_dir($uploadDirectory)) {
    mkdir($uploadDirectory, 0775, true);
}
/*
|--------------------------------------------------------------------------
| Variables
|--------------------------------------------------------------------------
*/
$errors = [];
$success = '';
$title = '';
$category = '';
$description = '';
$eventDate = '';
$location = '';
$budget = '';
$beneficiaries = '';
$men = '';
$women = '';
$children = '';
$detailedDescription = '';
$supplies = '';
$status = 'active';
/*
|--------------------------------------------------------------------------
| Catégories
|--------------------------------------------------------------------------
*/
$categories = [
    'social' => 'Social',
    'developpement' => 'Développement',
    'paix' => 'Paix',
    'education' => 'Éducation',
    'humanitaire' => 'Humanitaire'
];
/*
|--------------------------------------------------------------------------
| Upload d'un fichier
|--------------------------------------------------------------------------
*/
function uploadFile($file, $uploadDirectory, $uploadWebPath, $allowedTypes, $maxSize, &$errors, $fieldName)
{
    if (!isset($file) || !isset($file['error'])) {
        return null;
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Une erreur est survenue lors de l'envoi du fichier : " . $fieldName;
        return null;
    }
    if ($file['size'] > $maxSize) {
        $errors[] = "Le fichier {$fieldName} est trop volumineux.";
        return null;
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if (!$finfo) {
        $errors[] = "Impossible de vérifier le type du fichier {$fieldName}.";
        return null;
    }
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mimeType, $allowedTypes, true)) {
        $errors[] = "Le type de fichier {$fieldName} n'est pas autorisé.";
        return null;
    }
    $extension = strtolower(
        pathinfo($file['name'], PATHINFO_EXTENSION)
    );
    $filename = uniqid('orpaics_', true) . '.' . $extension;
    $destination = $uploadDirectory . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        $errors[] = "Impossible d'enregistrer le fichier {$fieldName}.";
        return null;
    }
    return $uploadWebPath . $filename;
}
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
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $eventDate = trim($_POST['event_date'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $budget = trim($_POST['budget'] ?? '');
    $beneficiaries = trim($_POST['beneficiaries'] ?? '');
    $men = trim($_POST['men'] ?? '');
    $women = trim($_POST['women'] ?? '');
    $children = trim($_POST['children'] ?? '');
    $detailedDescription = trim($_POST['detailed_description'] ?? '');
    $supplies = trim($_POST['supplies'] ?? '');
    $status = $_POST['status'] ?? 'active';
    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */
    if ($title === '') {
        $errors[] = "Le titre de l'activité est obligatoire.";
    }
    if ($category === '') {
        $errors[] = "Veuillez sélectionner une catégorie.";
    }
    if (!array_key_exists($category, $categories)) {
        $errors[] = "La catégorie sélectionnée est invalide.";
    }
    if ($description === '') {
        $errors[] = "La description courte est obligatoire.";
    }
    if ($status !== 'active' && $status !== 'inactive') {
        $status = 'active';
    }
    /*
    |--------------------------------------------------------------------------
    | Validation des nombres
    |--------------------------------------------------------------------------
    */
    if ($budget !== '' && !is_numeric($budget)) {
        $errors[] = "Le budget doit être un nombre valide.";
    }
    if ($beneficiaries !== '' && (!ctype_digit($beneficiaries) || (int)$beneficiaries < 0)) {
        $errors[] = "Le nombre de bénéficiaires est invalide.";
    }
    if ($men !== '' && (!ctype_digit($men) || (int)$men < 0)) {
        $errors[] = "Le nombre d'hommes est invalide.";
    }
    if ($women !== '' && (!ctype_digit($women) || (int)$women < 0)) {
        $errors[] = "Le nombre de femmes est invalide.";
    }
    if ($children !== '' && (!ctype_digit($children) || (int)$children < 0)) {
        $errors[] = "Le nombre d'enfants est invalide.";
    }
    /*
    |--------------------------------------------------------------------------
    | Date
    |--------------------------------------------------------------------------
    */
    if ($eventDate !== '') {
        $dateObject = DateTime::createFromFormat('Y-m-d', $eventDate);
        if (!$dateObject || $dateObject->format('Y-m-d') !== $eventDate) {
            $errors[] = "La date de l'activité est invalide.";
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Slug
    |--------------------------------------------------------------------------
    */
    $slug = generateSlug($title);
    /*
    |--------------------------------------------------------------------------
    | Vérification du slug
    |--------------------------------------------------------------------------
    */
    if (empty($errors)) {
        $checkSlug = $pdo->prepare("
            SELECT id
            FROM activities
            WHERE slug = ?
            LIMIT 1
        ");
        $checkSlug->execute([$slug]);
        if ($checkSlug->fetch()) {
            $slug = $slug . '-' . time();
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Upload des fichiers
    |--------------------------------------------------------------------------
    */
    $image = null;
    $image2 = null;
    $video = null;
    $planImage = null;
    if (empty($errors)) {
        /*
        |--------------------------------------------------------------------------
        | Image principale
        |--------------------------------------------------------------------------
        */
        $image = uploadFile(
            $_FILES['image'] ?? null,
            $uploadDirectory,
            $uploadWebPath,
            [
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif'
            ],
            8 * 1024 * 1024,
            $errors,
            'image principale'
        );
        /*
        |--------------------------------------------------------------------------
        | Deuxième image
        |--------------------------------------------------------------------------
        */
        $image2 = uploadFile(
            $_FILES['image_2'] ?? null,
            $uploadDirectory,
            $uploadWebPath,
            [
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif'
            ],
            8 * 1024 * 1024,
            $errors,
            'deuxième image'
        );
        /*
        |--------------------------------------------------------------------------
        | Vidéo
        |--------------------------------------------------------------------------
        */
        $video = uploadFile(
            $_FILES['video'] ?? null,
            $uploadDirectory,
            $uploadWebPath,
            [
                'video/mp4',
                'video/webm',
                'video/ogg',
                'video/quicktime'
            ],
            100 * 1024 * 1024,
            $errors,
            'vidéo'
        );
        /*
        |--------------------------------------------------------------------------
        | Plan / carte
        |--------------------------------------------------------------------------
        */
        $planImage = uploadFile(
            $_FILES['plan_image'] ?? null,
            $uploadDirectory,
            $uploadWebPath,
            [
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif'
            ],
            8 * 1024 * 1024,
            $errors,
            'plan / carte'
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Enregistrement en base de données
    |--------------------------------------------------------------------------
    */
    if (empty($errors)) {
        try {
            $categoryLabel = $categories[$category];
            $icon = 'bi bi-activity';
            /*
            |--------------------------------------------------------------------------
            | Icônes automatiques
            |--------------------------------------------------------------------------
            */
            switch ($category) {
                case 'social':
                    $icon = 'bi bi-shield-check';
                    break;
                case 'developpement':
                    $icon = 'bi bi-graph-up-arrow';
                    break;
                case 'paix':
                    $icon = 'bi bi-people';
                    break;
                case 'education':
                    $icon = 'bi bi-mortarboard';
                    break;
                case 'humanitaire':
                    $icon = 'bi bi-heart-pulse';
                    break;
            }
            /*
            |--------------------------------------------------------------------------
            | Ordre d'affichage
            |--------------------------------------------------------------------------
            */
            $orderStatement = $pdo->query("
                SELECT COALESCE(MAX(display_order), 0) + 1
                FROM activities
            ");
            $displayOrder = (int) $orderStatement->fetchColumn();
            /*
            |--------------------------------------------------------------------------
            | INSERT
            |--------------------------------------------------------------------------
            */
            $stmt = $pdo->prepare("
                INSERT INTO activities (
                    title,
                    slug,
                    category,
                    category_label,
                    description,
                    icon,
                    image,
                    image_2,
                    event_date,
                    location,
                    budget,
                    beneficiaries,
                    men,
                    women,
                    children,
                    detailed_description,
                    supplies,
                    video,
                    plan_image,
                    status,
                    display_order
                )
                VALUES (
                    :title,
                    :slug,
                    :category,
                    :category_label,
                    :description,
                    :icon,
                    :image,
                    :image_2,
                    :event_date,
                    :location,
                    :budget,
                    :beneficiaries,
                    :men,
                    :women,
                    :children,
                    :detailed_description,
                    :supplies,
                    :video,
                    :plan_image,
                    :status,
                    :display_order
                )
            ");
            $stmt->execute([
                ':title' => $title,
                ':slug' => $slug,
                ':category' => $category,
                ':category_label' => $categoryLabel,
                ':description' => $description,
                ':icon' => $icon,
                ':image' => $image,
                ':image_2' => $image2,
                ':event_date' => $eventDate !== ''
                    ? $eventDate
                    : null,
                ':location' => $location !== ''
                    ? $location
                    : null,
                ':budget' => $budget !== ''
                    ? $budget
                    : null,
                ':beneficiaries' => $beneficiaries !== ''
                    ? (int)$beneficiaries
                    : null,
                ':men' => $men !== ''
                    ? (int)$men
                    : null,
                ':women' => $women !== ''
                    ? (int)$women
                    : null,
                ':children' => $children !== ''
                    ? (int)$children
                    : null,
                ':detailed_description' => $detailedDescription !== ''
                    ? $detailedDescription
                    : null,
                ':supplies' => $supplies !== ''
                    ? $supplies
                    : null,
                ':video' => $video,
                ':plan_image' => $planImage,
                ':status' => $status,
                ':display_order' => $displayOrder
            ]);
            /*
            |--------------------------------------------------------------------------
            | Redirection après succès
            |--------------------------------------------------------------------------
            */
            header('Location: index.php?success=created');
            exit;
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'enregistrement de l'activité : "
                . $e->getMessage();
        }
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
    <title>Nouvelle activité | ORPAIS</title>
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
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .page-header {
            background: var(--orpaics-dark);
            color: #fff;
            border-radius: 15px;
            padding: 25px 30px;
            margin-bottom: 25px;
        }
        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
        }
        .page-header p {
            margin: 8px 0 0;
            opacity: 0.85;
        }
        .form-card {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.06);
            margin-bottom: 25px;
        }
        .section-title {
            color: var(--orpaics-dark);
            font-weight: 700;
            font-size: 20px;
            border-bottom: 1px solid #e8eef5;
            padding-bottom: 12px;
            margin-bottom: 22px;
        }
        .form-label {
            font-weight: 600;
            color: var(--orpaics-dark);
        }
        .form-control,
        .form-select {
            border-radius: 8px;
            padding: 11px 13px;
            border-color: #dbe5ef;
        }
        .form-control:focus,
        .form-select:focus {
            border-color: var(--orpaics-blue);
            box-shadow: 0 0 0 0.2rem rgba(11, 94, 215, 0.10);
        }
        textarea.form-control {
            min-height: 130px;
        }
        .small-help {
            font-size: 13px;
            color: #718096;
            margin-top: 5px;
        }
        .btn-orpaics {
            background: var(--orpaics-blue);
            color: #fff;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-orpaics:hover {
            background: var(--orpaics-dark);
            color: #fff;
        }
        .btn-back {
            background: #fff;
            color: var(--orpaics-dark);
            border: 1px solid #dbe5ef;
            padding: 11px 20px;
            border-radius: 8px;
        }
        .btn-back:hover {
            background: var(--orpaics-light);
            color: var(--orpaics-dark);
        }
        .required {
            color: #dc3545;
        }
        .file-box {
            background: var(--orpaics-soft);
            border: 1px dashed #b9cbe0;
            border-radius: 10px;
            padding: 18px;
        }
        .file-box i {
            color: var(--orpaics-blue);
            font-size: 25px;
        }
        .top-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
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
                font-size: 23px;
            }
            .form-card {
                padding: 20px;
            }
            .top-actions {
                flex-direction: column;
                align-items: stretch;
            }
            .top-actions a {
                width: 100%;
                text-align: center;
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
                class="bi bi-calendar2-plus"
                style="font-size: 40px;"
            ></i>
            <div>
                <h1>
                    Publier une activité
                </h1>
                <p>
                    Ajoutez une nouvelle activité au site ORPAIS.
                </p>
            </div>
        </div>
    </div>
    <!-- =====================================================
         ACTIONS
    ====================================================== -->
    <div class="top-actions">
        <a
            href="../index.php"
            class="btn btn-back"
        >
            <i class="bi bi-arrow-left me-2"></i>
            Retour au Dashboard
        </a>
        <a
            href="index.php"
            class="btn btn-back"
        >
            <i class="bi bi-list me-2"></i>
            Gérer les activités
        </a>
    </div>
    <!-- =====================================================
         ERREURS
    ====================================================== -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <div class="fw-bold mb-2">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Veuillez corriger les erreurs suivantes :
            </div>
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li>
                        <?= e($error) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <!-- =====================================================
         FORMULAIRE
    ====================================================== -->
    <form
        method="POST"
        enctype="multipart/form-data"
    >
        <!-- =================================================
             INFORMATIONS GÉNÉRALES
        ================================================== -->
        <div class="form-card">
            <div class="section-title">
                <i class="bi bi-info-circle me-2"></i>
                Informations générales
            </div>
            <div class="row g-4">
                <!-- TITRE -->
                <div class="col-12">
                    <label
                        for="title"
                        class="form-label"
                    >
                        Titre de l'activité
                        <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        name="title"
                        id="title"
                        class="form-control"
                        value="<?= e($title) ?>"
                        placeholder="Exemple : Sensibilisation pour la paix à Goma"
                        required
                    >
                </div>
                <!-- CATÉGORIE -->
                <div class="col-md-6">
                    <label
                        for="category"
                        class="form-label"
                    >
                        Catégorie
                        <span class="required">*</span>
                    </label>
                    <select
                        name="category"
                        id="category"
                        class="form-select"
                        required
                    >
                        <option value="">
                            -- Sélectionner une catégorie --
                        </option>
                        <?php foreach ($categories as $key => $label): ?>
                            <option
                                value="<?= e($key) ?>"
                                <?= $category === $key ? 'selected' : '' ?>
                            >
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- DATE -->
                <div class="col-md-6">
                    <label
                        for="event_date"
                        class="form-label"
                    >
                        Date de l'activité
                    </label>
                    <input
                        type="date"
                        name="event_date"
                        id="event_date"
                        class="form-control"
                        value="<?= e($eventDate) ?>"
                    >
                </div>
                <!-- LIEU -->
                <div class="col-12">
                    <label
                        for="location"
                        class="form-label"
                    >
                        Lieu
                    </label>
                    <input
                        type="text"
                        name="location"
                        id="location"
                        class="form-control"
                        value="<?= e($location) ?>"
                        placeholder="Exemple : Goma, Nord-Kivu, RDC"
                    >
                </div>
                <!-- DESCRIPTION COURTE -->
                <div class="col-12">
                    <label
                        for="description"
                        class="form-label"
                    >
                        Description courte
                        <span class="required">*</span>
                    </label>
                    <textarea
                        name="description"
                        id="description"
                        class="form-control"
                        placeholder="Présentez brièvement l'activité..."
                        required
                    ><?= e($description) ?></textarea>
                    <div class="small-help">
                        Cette description sera utilisée sur la page des activités.
                    </div>
                </div>
                <!-- DESCRIPTION D’AILLÉE -->
                <div class="col-12">
                    <label
                        for="detailed_description"
                        class="form-label"
                    >
                        Description détaillée
                    </label>
                    <textarea
                        name="detailed_description"
                        id="detailed_description"
                        class="form-control"
                        placeholder="Décrivez en détail l'activité, son contexte, ses objectifs et ses résultats..."
                    ><?= e($detailedDescription) ?></textarea>
                </div>
            </div>
        </div>
        <!-- =================================================
             STATISTIQUES
        ================================================== -->
        <div class="form-card">
            <div class="section-title">
                <i class="bi bi-bar-chart me-2"></i>
                Bénéficiaires et statistiques
            </div>
            <div class="row g-4">
                <div class="col-md-6">
                    <label
                        for="beneficiaries"
                        class="form-label"
                    >
                        Nombre total de bénéficiaires
                    </label>
                    <input
                        type="number"
                        name="beneficiaries"
                        id="beneficiaries"
                        class="form-control"
                        min="0"
                        value="<?= e($beneficiaries) ?>"
                        placeholder="Exemple : 250"
                    >
                </div>
                <div class="col-md-6">
                    <label
                        for="budget"
                        class="form-label"
                    >
                        Budget
                    </label>
                    <input
                        type="number"
                        name="budget"
                        id="budget"
                        class="form-control"
                        min="0"
                        step="0.01"
                        value="<?= e($budget) ?>"
                        placeholder="Exemple : 5000"
                    >
                    <div class="small-help">
                        Montant selon la devise utilisée par votre organisation.
                    </div>
                </div>
                <div class="col-md-4">
                    <label
                        for="men"
                        class="form-label"
                    >
                        Hommes
                    </label>
                    <input
                        type="number"
                        name="men"
                        id="men"
                        class="form-control"
                        min="0"
                        value="<?= e($men) ?>"
                        placeholder="0"
                    >
                </div>
                <div class="col-md-4">
                    <label
                        for="women"
                        class="form-label"
                    >
                        Femmes
                    </label>
                    <input
                        type="number"
                        name="women"
                        id="women"
                        class="form-control"
                        min="0"
                        value="<?= e($women) ?>"
                        placeholder="0"
                    >
                </div>
                <div class="col-md-4">
                    <label
                        for="children"
                        class="form-label"
                    >
                        Enfants
                    </label>
                    <input
                        type="number"
                        name="children"
                        id="children"
                        class="form-control"
                        min="0"
                        value="<?= e($children) ?>"
                        placeholder="0"
                    >
                </div>
            </div>
        </div>
        <!-- =================================================
             FOURNITURES
        ================================================== -->
        <div class="form-card">
            <div class="section-title">
                <i class="bi bi-box-seam me-2"></i>
                Fournitures et ressources
            </div>
            <label
                for="supplies"
                class="form-label"
            >
                Fournitures utilisées
            </label>
            <textarea
                name="supplies"
                id="supplies"
                class="form-control"
                placeholder="Exemple : kits scolaires, affiches, matériel de sensibilisation, vivres..."
            ><?= e($supplies) ?></textarea>
            <div class="small-help">
                Vous pouvez séparer les différents éléments par des virgules ou des retours à la ligne.
            </div>
        </div>
        <!-- =================================================
             IMAGES
        ================================================== -->
        <div class="form-card">
            <div class="section-title">
                <i class="bi bi-images me-2"></i>
                Photos de l'activité
            </div>
            <div class="row g-4">
                <!-- IMAGE 1 -->
                <div class="col-md-6">
                    <div class="file-box">
                        <div class="mb-3">
                            <i class="bi bi-image"></i>
                        </div>
                        <label
                            for="image"
                            class="form-label"
                        >
                            Image principale
                        </label>
                        <input
                            type="file"
                            name="image"
                            id="image"
                            class="form-control"
                            accept="image/jpeg,image/png,image/webp,image/gif"
                        >
                        <div class="small-help">
                            JPG, PNG, WEBP ou GIF — maximum 8 Mo.
                        </div>
                    </div>
                </div>
                <!-- IMAGE 2 -->
                <div class="col-md-6">
                    <div class="file-box">
                        <div class="mb-3">
                            <i class="bi bi-images"></i>
                        </div>
                        <label
                            for="image_2"
                            class="form-label"
                        >
                            Deuxième image
                        </label>
                        <input
                            type="file"
                            name="image_2"
                            id="image_2"
                            class="form-control"
                            accept="image/jpeg,image/png,image/webp,image/gif"
                        >
                        <div class="small-help">
                            JPG, PNG, WEBP ou GIF — maximum 8 Mo.
                        </div>
                    </div>
                </div>
                <!-- PLAN -->
                <div class="col-12">
                    <div class="file-box">
                        <div class="mb-3">
                            <i class="bi bi-map"></i>
                        </div>
                        <label
                            for="plan_image"
                            class="form-label"
                        >
                            Plan / carte de l'activité
                        </label>
                        <input
                            type="file"
                            name="plan_image"
                            id="plan_image"
                            class="form-control"
                            accept="image/jpeg,image/png,image/webp,image/gif"
                        >
                        <div class="small-help">
                            Vous pouvez ajouter une carte, un plan de localisation ou un schéma.
                            Maximum 8 Mo.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- =================================================
             VIDÉO
        ================================================== -->
        <div class="form-card">
            <div class="section-title">
                <i class="bi bi-camera-video me-2"></i>
                Vidéo
            </div>
            <div class="file-box">
                <div class="mb-3">
                    <i class="bi bi-play-btn"></i>
                </div>
                <label
                    for="video"
                    class="form-label"
                >
                    Vidéo de l'activité
                </label>
                <input
                    type="file"
                    name="video"
                    id="video"
                    class="form-control"
                    accept="video/mp4,video/webm,video/ogg,video/quicktime"
                >
                <div class="small-help">
                    MP4, WEBM, OGG ou MOV — maximum 100 Mo.
                </div>
            </div>
        </div>
        <!-- =================================================
             PUBLICATION
        ================================================== -->
        <div class="form-card">
            <div class="section-title">
                <i class="bi bi-send me-2"></i>
                Publication
            </div>
            <div class="row g-4 align-items-end">
                <div class="col-md-6">
                    <label
                        for="status"
                        class="form-label"
                    >
                        Statut
                    </label>
                    <select
                        name="status"
                        id="status"
                        class="form-select"
                    >
                        <option
                            value="active"
                            <?= $status === 'active' ? 'selected' : '' ?>
                        >
                            Publié
                        </option>
                        <option
                            value="inactive"
                            <?= $status === 'inactive' ? 'selected' : '' ?>
                        >
                            Brouillon / Désactivé
                        </option>
                    </select>
                </div>
                <div class="col-md-6">
                    <button
                        type="submit"
                        class="btn btn-orpaics w-100"
                    >
                        <i class="bi bi-cloud-arrow-up me-2"></i>
                        Publier l'activité
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>
</body>
</html>





