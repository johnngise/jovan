<?php

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/database.php';

$error = '';
$biography = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $biography = trim($_POST['biography'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $facebook = trim($_POST['facebook'] ?? '');
    $instagram = trim($_POST['instagram'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    $twitter = trim($_POST['twitter'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 0);

    if ($name === '' || $position === '') {

        $error = "Le nom et la fonction sont obligatoires.";

    } elseif (mb_strlen($biography, 'UTF-8') > 5000) {

        $error = "La biographie ne peut pas dépasser 5 000 caractères.";

    } else {

        $photoPath = null;

        if (
            isset($_FILES['photo']) &&
            $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE
        ) {

            $file = $_FILES['photo'];

            $allowed = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];

            $mime = mime_content_type($file['tmp_name']);

            if (!isset($allowed[$mime])) {

                $error = "Format d'image non autorisé.";

            } elseif ($file['size'] > 5 * 1024 * 1024) {

                $error = "L'image ne doit pas dépasser 5 Mo.";

            } else {

                $extension = $allowed[$mime];

                $filename =
                    bin2hex(random_bytes(16))
                    . '.'
                    . $extension;

                $uploadDir =
                    __DIR__ . '/../../uploads/team/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $destination =
                    $uploadDir . $filename;

                if (move_uploaded_file(
                    $file['tmp_name'],
                    $destination
                )) {

                    $photoPath =
                        'uploads/team/' . $filename;

                } else {

                    $error = "Impossible de télécharger la photo.";
                }
            }
        }

        if ($error === '') {

            $stmt = $pdo->prepare("
                INSERT INTO team_members
                (
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
                    display_order
                )
                VALUES
                (
                    :name,
                    :position,
                    :biography,
                    :photo,
                    :phone,
                    :email,
                    :facebook,
                    :instagram,
                    :linkedin,
                    :twitter,
                    'active',
                    :display_order
                )
            ");

            $stmt->execute([
                'name' => $name,
                'position' => $position,
                'biography' => $biography ?: null,
                'photo' => $photoPath,
                'phone' => $phone ?: null,
                'email' => $email ?: null,
                'facebook' => $facebook ?: null,
                'instagram' => $instagram ?: null,
                'linkedin' => $linkedin ?: null,
                'twitter' => $twitter ?: null,
                'display_order' => $display_order
            ]);

            header("Location: index.php");
            exit;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1">

<title>Ajouter | ORPAIS</title>

<style>

body {
    background: #f5f9ff;
    font-family: Arial, sans-serif;
}

.container {
    max-width: 800px;
    margin: 40px auto;
    padding: 30px;
    background: white;
    border-radius: 15px;
}

label {
    display: block;
    margin-top: 15px;
    font-weight: bold;
}

input,
textarea {
    width: 100%;
    padding: 12px;
    margin-top: 6px;
    border: 1px solid #d7e1ed;
    border-radius: 7px;
}

textarea {
    min-height: 130px;
}

button {
    margin-top: 25px;
    padding: 13px 25px;
    border: 0;
    border-radius: 7px;
    background: #0B63CE;
    color: white;
    cursor: pointer;
}

.error {
    background: #ffe8e8;
    color: #b42318;
    padding: 12px;
    border-radius: 7px;
}

</style>

    <link href="../../assets/css/admin-common.css" rel="stylesheet">
</head>

<body>

<div class="container">

<h1>Ajouter un membre</h1>

<?php if ($error): ?>

<div class="error">
    <?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

<label>Nom complet *</label>
<input type="text" name="name" required>

<label>Fonction *</label>
<input type="text" name="position" required>

<label>Biographie</label>
<textarea name="biography" maxlength="5000"><?= htmlspecialchars($biography, ENT_QUOTES, 'UTF-8') ?></textarea>
<small>Cette biographie sera affichée sur le profil public du membre. Maximum 5 000 caractères.</small>

<label>Photo</label>
<input
    type="file"
    name="photo"
    accept="image/jpeg,image/png,image/webp"
>

<label>Téléphone</label>
<input type="text" name="phone">

<label>Email</label>
<input type="email" name="email">

<label>Facebook</label>
<input type="url" name="facebook">

<label>Instagram</label>
<input type="url" name="instagram">

<label>LinkedIn</label>
<input type="url" name="linkedin">

<label>Twitter / X</label>
<input type="url" name="twitter">

<label>Ordre d'affichage</label>
<input
    type="number"
    name="display_order"
    value="0"
>

<button type="submit">
    Ajouter le membre
</button>

</form>

</div>

</body>
</html>

