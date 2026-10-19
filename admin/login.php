<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once __DIR__ . '/../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {

        $error = "Veuillez remplir tous les champs.";

    } else {

        $stmt = $pdo->prepare("
            SELECT *
            FROM admin_users
            WHERE email = :email
            AND status = 'active'
            LIMIT 1
        ");

        $stmt->execute([
            'email' => $email
        ]);

        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {

            session_regenerate_id(true);

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];

            header("Location: index.php");
            exit;

        } else {

            $error = "Email ou mot de passe incorrect.";

        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Connexion | ORPAIS</title>

    <style>

    </style>
    <link href="../assets/css/admin-common.css" rel="stylesheet">
</head>

<body class="admin-auth-page">

<main class="admin-auth-card">
    <div class="admin-auth-brand">
        <span class="admin-auth-brand-mark">O</span>
        <span>ORPAIS <small>ADMINISTRATION</small></span>
    </div>

    <h1>Bienvenue dans votre espace</h1>
    <p class="subtitle">Connectez-vous pour gérer le contenu de votre organisation.</p>

    <?php if ($error): ?>
        <div class="admin-alert">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="admin-form-group">
            <label for="email">Adresse e-mail</label>
            <input id="email" type="email" name="email" autocomplete="username" required>
        </div>
        <div class="admin-form-group">
            <label for="password">Mot de passe</label>
            <input id="password" type="password" name="password" autocomplete="current-password" required>
        </div>
        <button class="admin-primary-button" type="submit">Se connecter</button>
    </form>
</main>

</body>
</html>
