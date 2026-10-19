<?php
session_start();
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/database.php';

function e($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }

$errors = [];
$values = [
    'type' => 'emploi', 'title' => '', 'organization' => '', 'location' => '',
    'description' => '', 'deadline' => '', 'amount' => '', 'contact' => '', 'status' => 'active',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $key => $default) {
        $values[$key] = trim($_POST[$key] ?? $default);
    }
    if (!in_array($values['type'], ['emploi', 'financement'], true)) $errors[] = 'Le type sélectionné est invalide.';
    if ($values['title'] === '') $errors[] = 'Le titre est obligatoire.';
    if ($values['description'] === '') $errors[] = 'La description est obligatoire.';
    if ($values['deadline'] !== '') {
        $date = DateTime::createFromFormat('Y-m-d', $values['deadline']);
        if (!$date || $date->format('Y-m-d') !== $values['deadline']) $errors[] = 'La date limite est invalide.';
    }
    if (!in_array($values['status'], ['active', 'inactive'], true)) $values['status'] = 'active';

    if (!$errors) {
        $stmt = $pdo->prepare("
            INSERT INTO opportunities (type, title, organization, location, description, deadline, amount, contact, status)
            VALUES (?, ?, ?, ?, ?, NULLIF(?, ''), ?, ?, ?)
        ");
        $stmt->execute([
            $values['type'], $values['title'], $values['organization'], $values['location'],
            $values['description'], $values['deadline'], $values['amount'], $values['contact'], $values['status'],
        ]);
        header('Location: index.php?success=created');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle opportunité | ORPAIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{background:#f7fbfb;color:#163b4d}.page{max-width:900px;margin:40px auto;padding:0 20px}.card{border:0;border-radius:16px;box-shadow:0 8px 24px #164e6814}.header{background:#164e68;color:#fff;border-radius:16px;padding:25px;margin-bottom:20px}.btn-primary{background:#bd6846;border-color:#bd6846}.btn-primary:hover{background:#a95739;border-color:#a95739}</style>
    <link href="../../assets/css/admin-common.css" rel="stylesheet">
</head>
<body>
<main class="page">
    <div class="header"><h1>Publier une opportunité</h1><p class="mb-0">Ajoutez une offre d’emploi ou une campagne « Faire un don ».</p></div>
    <div class="card p-4">
        <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form method="post">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Type *</label><select name="type" id="opportunity-type" class="form-select" required><option value="emploi" <?= $values['type']==='emploi'?'selected':'' ?>>Offre d’emploi</option><option value="financement" <?= $values['type']==='financement'?'selected':'' ?>>Faire un don</option></select></div>
                <div class="col-md-6"><label class="form-label" id="title-label">Titre *</label><input name="title" class="form-control" value="<?= e($values['title']) ?>" required></div>
                <div class="col-md-6 campaign-field"><label class="form-label">Organisation</label><input name="organization" class="form-control" value="<?= e($values['organization']) ?>"></div>
                <div class="col-md-6 campaign-field"><label class="form-label">Lieu</label><input name="location" class="form-control" value="<?= e($values['location']) ?>"></div>
                <div class="col-12"><label class="form-label" id="description-label">Description *</label><textarea name="description" class="form-control" rows="6" required><?= e($values['description']) ?></textarea><div class="form-text" id="description-help">Décrivez clairement l’opportunité proposée.</div></div>
                <div class="col-md-4"><label class="form-label" id="deadline-label">Date limite</label><input type="date" name="deadline" class="form-control" value="<?= e($values['deadline']) ?>"></div>
                <div class="col-md-4"><label class="form-label" id="amount-label">Montant ou rémunération</label><input name="amount" class="form-control" value="<?= e($values['amount']) ?>" placeholder="Ex. 500 USD"></div>
                <div class="col-md-4"><label class="form-label" id="contact-label">Contact</label><input name="contact" class="form-control" value="<?= e($values['contact']) ?>" placeholder="E-mail ou téléphone"></div>
                <div class="col-md-4"><label class="form-label">Statut</label><select name="status" class="form-select"><option value="active">Publié</option><option value="inactive">Brouillon</option></select></div>
            </div>
            <div class="mt-4 d-flex gap-2"><a href="index.php" class="btn btn-outline-secondary">Annuler</a><button class="btn btn-primary">Publier</button></div>
        </form>
    </div>
</main>
<script>
    (function () {
        const type = document.getElementById('opportunity-type');
        const titleLabel = document.getElementById('title-label');
        const descriptionLabel = document.getElementById('description-label');
        const descriptionHelp = document.getElementById('description-help');
        const deadlineLabel = document.getElementById('deadline-label');
        const amountLabel = document.getElementById('amount-label');
        const contactLabel = document.getElementById('contact-label');
        const campaignFields = document.querySelectorAll('.campaign-field');

        function updateDonationFields() {
            const donation = type.value === 'financement';
            campaignFields.forEach((field) => {
                field.hidden = donation;
                field.querySelector('input').disabled = donation;
            });
            titleLabel.textContent = donation ? 'Nom de la campagne *' : 'Titre *';
            descriptionLabel.textContent = donation ? 'Message de la campagne *' : 'Description *';
            descriptionHelp.textContent = donation
                ? 'Expliquez le projet soutenu et l’impact attendu des dons.'
                : 'Décrivez clairement l’opportunité proposée.';
            deadlineLabel.textContent = donation ? 'Date de fin de la campagne' : 'Date limite';
            amountLabel.textContent = donation ? 'Objectif ou montant du don' : 'Montant ou rémunération';
            contactLabel.textContent = donation ? 'Instructions pour faire un don' : 'Contact';
        }

        type.addEventListener('change', updateDonationFields);
        updateDonationFields();
    }());
</script>
</body>
</html>

