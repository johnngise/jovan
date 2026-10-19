<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$created = false;
$error = null;

require_once __DIR__ . '/../config/database.php';

try {
    $name = "Administrateur ORPAIS";
    $email = "admin@orpaics.org";
    $password = "Admin@123456";
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO admin_users
        (name, email, password, status)
        VALUES
        (:name, :email, :password, 'active')
    ");

    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'password' => $hash
    ]);
    $created = true;
} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création administrateur | ORPAIS</title>
    <link href="../assets/css/admin-common.css" rel="stylesheet">
</head>
<body class="admin-auth-page">
    <main class="admin-setup-card">
        <div class="admin-auth-brand">
            <span class="admin-auth-brand-mark">O</span>
            <span>ORPAIS <small>ADMINISTRATION</small></span>
        </div>
        <h1>Initialisation de l’administration</h1>
        <p class="subtitle">Création du compte administrateur principal.</p>

        <?php if ($created): ?>
            <section class="admin-setup-result">
                <h2>Administrateur créé avec succès</h2>
                <p><strong>Email :</strong> admin@orpaics.org</p>
                <p><strong>Mot de passe :</strong> Admin@123456</p>
                <a href="login.php">Accéder à la connexion</a>
            </section>
        <?php else: ?>
            <section class="admin-setup-result error">
                <h2>Erreur lors de la création</h2>
                <pre><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></pre>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
