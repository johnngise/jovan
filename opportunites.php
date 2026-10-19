<?php
require_once __DIR__ . '/config/database.php';

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$typeLabels = [
    'emploi' => 'Offres d’emploi',
    'financement' => 'Faire un don',
];

$opportunities = [];
$error = '';

try {
    $stmt = $pdo->query("
        SELECT id, type, title, organization, location, description, deadline, amount, contact
        FROM opportunities
        WHERE status = 'active'
        ORDER BY deadline IS NULL, deadline ASC, created_at DESC
    ");
    $opportunities = $stmt->fetchAll();
} catch (PDOException $exception) {
    $error = 'Les opportunités ne sont pas disponibles pour le moment.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opportunités | ORPAIS</title>
    <meta name="description" content="Découvrez les offres d’emploi et les possibilités de faire un don à ORPAIS.">
    <link rel="icon" href="assets/img/logo.jpg">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/tailwind.css" rel="stylesheet">
    <style>
        .opportunities-header { background: linear-gradient(120deg, #164e68, #4da9c2); color: #fff; padding: 150px 0 75px; }
        .opportunities-header h1 { font-size: 44px; font-weight: 700; }
        .opportunities-header p { color: #e8f8f8; max-width: 650px; }
        .opportunity-card { background: #fff; border: 1px solid #dff3f5; border-radius: 16px; box-shadow: 0 10px 28px rgba(22, 78, 104, .08); height: 100%; padding: 28px; }
        .opportunity-type { color: #bd6846; font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .opportunity-card h2 { color: #164e68; font-size: 22px; margin: 14px 0 10px; }
        .opportunity-meta { color: #6d8994; font-size: 13px; margin-bottom: 16px; }
        .opportunity-meta i { color: #4da9c2; margin-right: 6px; }
        .opportunity-card p { color: #526f7b; line-height: 1.7; }
        .opportunity-amount { color: #bd6846; font-weight: 700; }
        .opportunity-contact { border-top: 1px solid #dff3f5; color: #164e68; font-size: 13px; margin-top: 20px; padding-top: 16px; }
        .donation-card { border-top: 4px solid #d9865f; }
        .donation-cta { background: #d9865f; border-radius: 10px; color: #fff; display: inline-flex; font-weight: 700; margin-top: 18px; padding: 11px 16px; }
        .donation-cta:hover { background: #bd6846; color: #fff; }
        .empty-opportunities { background: #f7fbfb; border-radius: 16px; color: #6d8994; padding: 65px 20px; text-align: center; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<header class="opportunities-header">
    <div class="container">
        <h1>Opportunités</h1>
        <p>Retrouvez les offres d’emploi et les possibilités de faire un don pour contribuer à la paix, à l’autonomisation et au développement des communautés.</p>
    </div>
</header>

<main class="py-5">
    <div class="container">
        <?php if ($error): ?>
            <div class="alert alert-warning"><?= e($error) ?></div>
        <?php elseif (empty($opportunities)): ?>
            <div class="empty-opportunities">
                <i class="bi bi-briefcase fs-1"></i>
                <h2 class="mt-3">Aucune opportunité disponible</h2>
                <p>Les nouvelles opportunités seront publiées prochainement.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($opportunities as $opportunity): ?>
                    <div class="col-lg-6">
                        <article class="opportunity-card <?= $opportunity['type'] === 'financement' ? 'donation-card' : '' ?>">
                            <div class="opportunity-type">
                                <i class="bi <?= $opportunity['type'] === 'emploi' ? 'bi-person-badge' : 'bi-heart-fill' ?> me-1"></i>
                                <?= e($typeLabels[$opportunity['type']] ?? 'Opportunité') ?>
                            </div>
                            <h2><?= e($opportunity['title']) ?></h2>
                            <div class="opportunity-meta">
                                <?php if ($opportunity['organization']): ?><i class="bi bi-building"></i><?= e($opportunity['organization']) ?><?php endif; ?>
                                <?php if ($opportunity['location']): ?><span class="ms-3"><i class="bi bi-geo-alt"></i><?= e($opportunity['location']) ?></span><?php endif; ?>
                            </div>
                            <p><?= nl2br(e($opportunity['description'])) ?></p>
                            <?php if ($opportunity['deadline'] || $opportunity['amount']): ?>
                                <div class="opportunity-meta">
                                    <?php if ($opportunity['deadline']): ?><i class="bi bi-calendar-event"></i>Échéance : <?= e(date('d/m/Y', strtotime($opportunity['deadline']))) ?><?php endif; ?>
                                    <?php if ($opportunity['amount']): ?><span class="opportunity-amount ms-3"><i class="bi bi-cash-stack"></i><?= e($opportunity['amount']) ?></span><?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($opportunity['contact']): ?><div class="opportunity-contact"><i class="bi bi-envelope me-2"></i><?= e($opportunity['contact']) ?></div><?php endif; ?>
                            <?php if ($opportunity['type'] === 'financement'): ?><a class="donation-cta" href="donation.php?campaign=<?= rawurlencode($opportunity['title']) ?>">Faire un don <i class="bi bi-arrow-up-right ms-2"></i></a><?php endif; ?>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
