
<?php

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/database.php';

function e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php');
    exit;
}


$stmt = $pdo->prepare("
    SELECT *
    FROM testimonials
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$testimonial = $stmt->fetch();

if (!$testimonial) {
    header('Location: index.php');
    exit;
}


$name = $testimonial['name'];
$role = $testimonial['role'];
$content = $testimonial['content'];
$photo = $testimonial['photo'];
$status = $testimonial['status'];
$display_order = $testimonial['display_order'];

$error = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $photo = trim($_POST['photo'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $display_order = (int)($_POST['display_order'] ?? 0);


    if ($name === '') {

        $error = "Le nom est obligatoire.";

    } elseif ($content === '') {

        $error = "Le contenu du témoignage est obligatoire.";

    } elseif (!in_array($status, ['active', 'inactive'], true)) {

        $error = "Statut invalide.";

    } else {

        $stmt = $pdo->prepare("
            UPDATE testimonials
            SET
                name = ?,
                role = ?,
                content = ?,
                photo = ?,
                status = ?,
                display_order = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $name,
            $role !== '' ? $role : null,
            $content,
            $photo !== '' ? $photo : null,
            $status,
            $display_order,
            $id
        ]);

        header('Location: index.php?success=updated');
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Modifier le témoignage - ORPAIS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        :root {
            --orpaics-dark: #073b66;
        }

        body {
            background: #f5f7fb;
        }

        .main {
            max-width: 900px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,.07);
        }

        .card-header {
            background: var(--orpaics-dark);
            color: white;
            border-radius: 16px 16px 0 0 !important;
            padding: 20px;
        }

    </style>

    <link href="../../assets/css/admin-common.css" rel="stylesheet">
</head>

<body>

<div class="main">

    <div class="card">

        <div class="card-header">

            <h3 class="mb-1">
                Modifier le témoignage
            </h3>

            <small>
                <?= e($testimonial['name']) ?>
            </small>

        </div>


        <div class="card-body p-4">

            <?php if ($error): ?>

                <div class="alert alert-danger">
                    <?= e($error) ?>
                </div>

            <?php endif; ?>


            <form method="POST">


                <div class="mb-3">

                    <label class="form-label">
                        Nom *
                    </label>

                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        value="<?= e($name) ?>"
                        required
                    >

                </div>


                <div class="mb-3">

                    <label class="form-label">
                        Fonction / rôle
                    </label>

                    <input
                        type="text"
                        name="role"
                        class="form-control"
                        value="<?= e($role) ?>"
                    >

                </div>


                <div class="mb-3">

                    <label class="form-label">
                        Témoignage *
                    </label>

                    <textarea
                        name="content"
                        class="form-control"
                        rows="8"
                        required
                    ><?= e($content) ?></textarea>

                </div>


                <div class="mb-3">

                    <label class="form-label">
                        Photo
                    </label>

                    <input
                        type="text"
                        name="photo"
                        class="form-control"
                        value="<?= e($photo) ?>"
                    >

                </div>


                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Statut
                        </label>

                        <select name="status" class="form-select">

                            <option value="active"
                                <?= $status === 'active' ? 'selected' : '' ?>>
                                Publié
                            </option>

                            <option value="inactive"
                                <?= $status === 'inactive' ? 'selected' : '' ?>>
                                Non publié
                            </option>

                        </select>

                    </div>


                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Ordre d'affichage
                        </label>

                        <input
                            type="number"
                            name="display_order"
                            class="form-control"
                            value="<?= (int)$display_order ?>"
                            min="0"
                        >

                    </div>

                </div>


                <div class="d-flex gap-2">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-save"></i>
                        Enregistrer les modifications
                    </button>

                    <a
                        href="index.php"
                        class="btn btn-secondary"
                    >
                        Annuler
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

</body>
</html>


