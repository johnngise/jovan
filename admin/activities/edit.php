<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../auth.php';
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
| ID de l'activité
|--------------------------------------------------------------------------
*/

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Récupération de l'activité
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM activities
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$activity = $stmt->fetch();

if (!$activity) {
    header('Location: index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Variables du formulaire
|--------------------------------------------------------------------------
*/

$title = $activity['title'] ?? '';
$slug = $activity['slug'] ?? '';
$category = $activity['category'] ?? '';
$category_label = $activity['category_label'] ?? '';
$description = $activity['description'] ?? '';
$icon = $activity['icon'] ?? 'bi bi-activity';

$event_date = $activity['event_date'] ?? '';
$location = $activity['location'] ?? '';
$budget = $activity['budget'] ?? '';
$beneficiaries = $activity['beneficiaries'] ?? '';
$men = $activity['men'] ?? '';
$women = $activity['women'] ?? '';
$children = $activity['children'] ?? '';

$detailed_description = $activity['detailed_description'] ?? '';
$supplies = $activity['supplies'] ?? '';
$video = $activity['video'] ?? '';
$map_url = $activity['map_url'] ?? '';

$image = $activity['image'] ?? '';
$image_2 = $activity['image_2'] ?? '';
$plan_image = $activity['plan_image'] ?? '';

$status = $activity['status'] ?? 'active';
$display_order = $activity['display_order'] ?? 0;

$error = '';


/*
|--------------------------------------------------------------------------
| Génération du slug
|--------------------------------------------------------------------------
*/

function generateSlug($text)
{
    $text = trim($text);

    $text = iconv(
        'UTF-8',
        'ASCII//TRANSLIT//IGNORE',
        $text
    );

    $text = strtolower($text);

    $text = preg_replace('/[^a-z0-9]+/', '-', $text);

    $text = trim($text, '-');

    return $text;
}


/*
|--------------------------------------------------------------------------
| Traitement du formulaire
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');

    $slug = trim($_POST['slug'] ?? '');

    $category = trim($_POST['category'] ?? '');

    $category_label = trim($_POST['category_label'] ?? '');

    $description = trim($_POST['description'] ?? '');

    $icon = trim($_POST['icon'] ?? 'bi bi-activity');

    $event_date = trim($_POST['event_date'] ?? '');

    $location = trim($_POST['location'] ?? '');

    $budget = trim($_POST['budget'] ?? '');

    $beneficiaries = trim($_POST['beneficiaries'] ?? '');

    $men = trim($_POST['men'] ?? '');

    $women = trim($_POST['women'] ?? '');

    $children = trim($_POST['children'] ?? '');

    $detailed_description = trim(
        $_POST['detailed_description'] ?? ''
    );

    $supplies = trim($_POST['supplies'] ?? '');

    $video = trim($_POST['video'] ?? '');

    $map_url = trim($_POST['map_url'] ?? '');

    $status = $_POST['status'] ?? 'active';

    $display_order = (int)(
        $_POST['display_order'] ?? 0
    );


    /*
    |--------------------------------------------------------------------------
    | Vérification
    |--------------------------------------------------------------------------
    */

    if ($title === '') {

        $error = "Le titre de l'activité est obligatoire.";

    } elseif ($category === '') {

        $error = "La catégorie est obligatoire.";

    } elseif ($description === '') {

        $error = "La description courte est obligatoire.";

    }


    /*
    |--------------------------------------------------------------------------
    | Slug automatique
    |--------------------------------------------------------------------------
    */

    if ($slug === '') {

        $slug = generateSlug($title);

    } else {

        $slug = generateSlug($slug);

    }


    /*
    |--------------------------------------------------------------------------
    | Vérification du slug
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        $check = $pdo->prepare("
            SELECT id
            FROM activities
            WHERE slug = ?
            AND id != ?
            LIMIT 1
        ");

        $check->execute([
            $slug,
            $id
        ]);

        if ($check->fetch()) {

            $error = "Ce slug existe déjà. Choisissez un autre slug.";

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Nettoyage des valeurs numériques
    |--------------------------------------------------------------------------
    */

    $budgetValue = null;

    if ($budget !== '') {

        $budgetValue = (float)str_replace(
            ',',
            '.',
            $budget
        );

    }


    $beneficiariesValue = null;

    if ($beneficiaries !== '') {

        $beneficiariesValue = max(
            0,
            (int)$beneficiaries
        );

    }


    $menValue = null;

    if ($men !== '') {

        $menValue = max(
            0,
            (int)$men
        );

    }


    $womenValue = null;

    if ($women !== '') {

        $womenValue = max(
            0,
            (int)$women
        );

    }


    $childrenValue = null;

    if ($children !== '') {

        $childrenValue = max(
            0,
            (int)$children
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Mise à jour
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        try {

            $stmt = $pdo->prepare("
                UPDATE activities
                SET
                    title = ?,
                    slug = ?,
                    category = ?,
                    category_label = ?,
                    description = ?,
                    icon = ?,
                    event_date = ?,
                    location = ?,
                    budget = ?,
                    beneficiaries = ?,
                    men = ?,
                    women = ?,
                    children = ?,
                    detailed_description = ?,
                    supplies = ?,
                    video = ?,
                    map_url = ?,
                    status = ?,
                    display_order = ?
                WHERE id = ?
            ");


            $stmt->execute([

                $title,

                $slug,

                $category,

                $category_label,

                $description,

                $icon,

                ($event_date !== ''
                    ? $event_date
                    : null
                ),

                ($location !== ''
                    ? $location
                    : null
                ),

                $budgetValue,

                $beneficiariesValue,

                $menValue,

                $womenValue,

                $childrenValue,

                ($detailed_description !== ''
                    ? $detailed_description
                    : null
                ),

                ($supplies !== ''
                    ? $supplies
                    : null
                ),

                ($video !== ''
                    ? $video
                    : null
                ),

                ($map_url !== ''
                    ? $map_url
                    : null
                ),

                $status,

                $display_order,

                $id

            ]);


            /*
            |--------------------------------------------------------------------------
            | Redirection
            |--------------------------------------------------------------------------
            */

            header(
                'Location: index.php?success=updated'
            );

            exit;


        } catch (PDOException $e) {

            $error =
                "Erreur lors de la modification : "
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

    <title>
        Modifier l'activité | ORPAIS
    </title>


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
        }


        body {
            background: var(--orpaics-soft);
            color: var(--orpaics-text);
            font-family: Arial, Helvetica, sans-serif;
        }


        .page-wrapper {
            max-width: 1100px;
            margin: 35px auto;
            padding: 0 20px;
        }


        .header {
            background: var(--orpaics-dark);
            color: white;
            border-radius: 15px;
            padding: 25px 30px;
            margin-bottom: 25px;
        }


        .header h1 {
            margin: 0;
            font-size: 27px;
            font-weight: 700;
        }


        .header p {
            margin: 7px 0 0;
            opacity: .85;
        }


        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 18px rgba(0,0,0,.05);
            margin-bottom: 20px;
        }


        .card-header {
            background: white;
            border-bottom: 1px solid #edf1f5;
            padding: 18px 22px;
            font-weight: 700;
            color: var(--orpaics-dark);
        }


        .card-body {
            padding: 22px;
        }


        .form-label {
            font-weight: 600;
        }


        .form-control,
        .form-select {
            border-radius: 8px;
            padding: 11px 13px;
        }


        .form-control:focus,
        .form-select:focus {
            border-color: var(--orpaics-blue);
            box-shadow: 0 0 0 .2rem rgba(11,94,215,.12);
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


        .current-image {
            max-width: 220px;
            max-height: 150px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #ddd;
        }


        .help-text {
            font-size: 13px;
            color: #718096;
        }


        textarea {
            min-height: 130px;
        }

    </style>

    <link href="../../assets/css/admin-common.css" rel="stylesheet">
</head>


<body>


<div class="page-wrapper">


    <!-- HEADER -->

    <div class="header">

        <div class="d-flex align-items-center gap-3">

            <i
                class="bi bi-pencil-square"
                style="font-size: 38px;"
            ></i>

            <div>

                <h1>
                    Modifier l'activité
                </h1>

                <p>
                    Modifiez les informations de cette activité ORPAIS.
                </p>

            </div>

        </div>

    </div>


    <!-- ERREUR -->

    <?php if ($error !== ''): ?>

        <div class="alert alert-danger">

            <i class="bi bi-exclamation-triangle me-2"></i>

            <?= e($error) ?>

        </div>

    <?php endif; ?>


    <!-- FORMULAIRE -->

    <form
        method="POST"
        action=""
    >


        <!-- INFORMATIONS PRINCIPALES -->

        <div class="card">

            <div class="card-header">

                <i class="bi bi-info-circle me-2"></i>

                Informations principales

            </div>


            <div class="card-body">

                <div class="row g-3">


                    <div class="col-md-8">

                        <label class="form-label">
                            Titre de l'activité *
                        </label>

                        <input
                            type="text"
                            name="title"
                            class="form-control"
                            value="<?= e($title) ?>"
                            required
                        >

                    </div>


                    <div class="col-md-4">

                        <label class="form-label">
                            Ordre d'affichage
                        </label>

                        <input
                            type="number"
                            name="display_order"
                            class="form-control"
                            value="<?= e($display_order) ?>"
                            min="0"
                        >

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Catégorie *
                        </label>

                        <select
                            name="category"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Sélectionner
                            </option>

                            <option
                                value="social"
                                <?= $category === 'social' ? 'selected' : '' ?>
                            >
                                Social
                            </option>

                            <option
                                value="paix"
                                <?= $category === 'paix' ? 'selected' : '' ?>
                            >
                                Paix
                            </option>

                            <option
                                value="developpement"
                                <?= $category === 'developpement' ? 'selected' : '' ?>
                            >
                                Développement
                            </option>

                            <option
                                value="education"
                                <?= $category === 'education' ? 'selected' : '' ?>
                            >
                                Éducation
                            </option>

                            <option
                                value="humanitaire"
                                <?= $category === 'humanitaire' ? 'selected' : '' ?>
                            >
                                Humanitaire
                            </option>

                            <option
                                value="autre"
                                <?= $category === 'autre' ? 'selected' : '' ?>
                            >
                                Autre
                            </option>

                        </select>

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Nom affiché de la catégorie
                        </label>

                        <input
                            type="text"
                            name="category_label"
                            class="form-control"
                            value="<?= e($category_label) ?>"
                            placeholder="Ex : Cohésion sociale"
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

                        <div class="help-text mt-1">

                            Exemple :
                            protection-sociale-droits-humains

                        </div>

                    </div>


                    <div class="col-md-4">

                        <label class="form-label">
                            Icône Bootstrap
                        </label>

                        <input
                            type="text"
                            name="icon"
                            class="form-control"
                            value="<?= e($icon) ?>"
                            placeholder="bi bi-people"
                        >

                    </div>


                    <div class="col-12">

                        <label class="form-label">
                            Description courte *
                        </label>

                        <textarea
                            name="description"
                            class="form-control"
                            required
                        ><?= e($description) ?></textarea>

                    </div>


                </div>

            </div>

        </div>


        <!-- INFORMATIONS TERRAIN -->

        <div class="card">

            <div class="card-header">

                <i class="bi bi-geo-alt me-2"></i>

                Informations sur l'activité

            </div>


            <div class="card-body">

                <div class="row g-3">


                    <div class="col-md-4">

                        <label class="form-label">
                            Date de l'activité
                        </label>

                        <input
                            type="date"
                            name="event_date"
                            class="form-control"
                            value="<?= e($event_date) ?>"
                        >

                    </div>


                    <div class="col-md-8">

                        <label class="form-label">
                            Lieu
                        </label>

                        <input
                            type="text"
                            name="location"
                            class="form-control"
                            value="<?= e($location) ?>"
                            placeholder="Ex : Goma, Nord-Kivu"
                        >

                    </div>


                    <div class="col-md-4">

                        <label class="form-label">
                            Budget
                        </label>

                        <input
                            type="number"
                            name="budget"
                            class="form-control"
                            value="<?= e($budget) ?>"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                        >

                    </div>


                    <div class="col-md-4">

                        <label class="form-label">
                            Bénéficiaires
                        </label>

                        <input
                            type="number"
                            name="beneficiaries"
                            class="form-control"
                            value="<?= e($beneficiaries) ?>"
                            min="0"
                        >

                    </div>


                    <div class="col-md-4">

                        <label class="form-label">
                            Hommes
                        </label>

                        <input
                            type="number"
                            name="men"
                            class="form-control"
                            value="<?= e($men) ?>"
                            min="0"
                        >

                    </div>


                    <div class="col-md-4">

                        <label class="form-label">
                            Femmes
                        </label>

                        <input
                            type="number"
                            name="women"
                            class="form-control"
                            value="<?= e($women) ?>"
                            min="0"
                        >

                    </div>


                    <div class="col-md-4">

                        <label class="form-label">
                            Enfants
                        </label>

                        <input
                            type="number"
                            name="children"
                            class="form-control"
                            value="<?= e($children) ?>"
                            min="0"
                        >

                    </div>


                </div>

            </div>

        </div>


        <!-- CONTENU -->

        <div class="card">

            <div class="card-header">

                <i class="bi bi-file-text me-2"></i>

                Contenu détaillé

            </div>


            <div class="card-body">


                <div class="mb-3">

                    <label class="form-label">
                        Description détaillée
                    </label>

                    <textarea
                        name="detailed_description"
                        class="form-control"
                        style="min-height:220px;"
                    ><?= e($detailed_description) ?></textarea>

                    <div class="help-text mt-1">

                        Décrivez en détail l'activité, son contexte,
                        ses objectifs et ses résultats.

                    </div>

                </div>


                <div class="mb-3">

                    <label class="form-label">
                        Matériel / fournitures
                    </label>

                    <textarea
                        name="supplies"
                        class="form-control"
                    ><?= e($supplies) ?></textarea>

                    <div class="help-text mt-1">

                        Vous pouvez séparer les éléments par des virgules
                        ou par des lignes.

                    </div>

                </div>


            </div>

        </div>


        <!-- MÉDIAS -->

        <div class="card">

            <div class="card-header">

                <i class="bi bi-images me-2"></i>

                Images et médias

            </div>


            <div class="card-body">

                <div class="row g-4">


                    <div class="col-md-4">

                        <label class="form-label">
                            Image principale
                        </label>

                        <?php if (!empty($image)): ?>

                            <div class="mb-2">

                                <img
                                    src="../../<?= e(ltrim($image, '/')) ?>"
                                    class="current-image"
                                    alt=""
                                    onerror="this.src='../../assets/img/logo.jpg';"
                                >

                            </div>

                        <?php endif; ?>


                        <input
                            type="text"
                            name="image"
                            class="form-control"
                            value="<?= e($image) ?>"
                            placeholder="assets/img/photo.jpg"
                        >

                    </div>


                    <div class="col-md-4">

                        <label class="form-label">
                            Deuxième image
                        </label>

                        <?php if (!empty($image_2)): ?>

                            <div class="mb-2">

                                <img
                                    src="../../<?= e(ltrim($image_2, '/')) ?>"
                                    class="current-image"
                                    alt=""
                                    onerror="this.src='../../assets/img/logo.jpg';"
                                >

                            </div>

                        <?php endif; ?>


                        <input
                            type="text"
                            name="image_2"
                            class="form-control"
                            value="<?= e($image_2) ?>"
                            placeholder="assets/img/photo2.jpg"
                        >

                    </div>


                    <div class="col-md-4">

                        <label class="form-label">
                            Image du plan
                        </label>

                        <?php if (!empty($plan_image)): ?>

                            <div class="mb-2">

                                <img
                                    src="../../<?= e(ltrim($plan_image, '/')) ?>"
                                    class="current-image"
                                    alt=""
                                    onerror="this.src='../../assets/img/logo.jpg';"
                                >

                            </div>

                        <?php endif; ?>


                        <input
                            type="text"
                            name="plan_image"
                            class="form-control"
                            value="<?= e($plan_image) ?>"
                            placeholder="assets/img/plan.jpg"
                        >

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Vidéo
                        </label>

                        <input
                            type="text"
                            name="video"
                            class="form-control"
                            value="<?= e($video) ?>"
                            placeholder="assets/img/video.mp4 ou URL YouTube"
                        >

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Lien Google Maps
                        </label>

                        <input
                            type="text"
                            name="map_url"
                            class="form-control"
                            value="<?= e($map_url) ?>"
                            placeholder="https://maps.google.com/..."
                        >

                    </div>


                </div>

            </div>

        </div>


        <!-- PUBLICATION -->

        <div class="card">

            <div class="card-header">

                <i class="bi bi-globe me-2"></i>

                Publication

            </div>


            <div class="card-body">

                <div class="row g-3">


                    <div class="col-md-6">

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
                                Publiée
                            </option>

                            <option
                                value="inactive"
                                <?= $status === 'inactive' ? 'selected' : '' ?>
                            >
                                Désactivée
                            </option>

                        </select>

                    </div>


                </div>

            </div>

        </div>


        <!-- BOUTONS -->

        <div class="d-flex justify-content-between flex-wrap gap-2 mb-5">


            <a
                href="index.php"
                class="btn btn-outline-secondary"
            >

                <i class="bi bi-arrow-left me-2"></i>

                Annuler

            </a>


            <button
                type="submit"
                class="btn btn-orpaics px-4"
            >

                <i class="bi bi-save me-2"></i>

                Enregistrer les modifications

            </button>


        </div>


    </form>


</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>



