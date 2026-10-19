<?php

require_once __DIR__ . '/config/database.php';

function activities_escape($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function activities_image_url($value): string
{
    $image = trim((string) ($value ?? ''));
    if ($image === '') {
        return 'assets/img/orpais.jpeg';
    }

    if (preg_match('~^(https?:)?//~i', $image)) {
        return $image;
    }

    $localPath = parse_url($image, PHP_URL_PATH);
    $relativePath = ltrim((string) $localPath, '/\\');
    $resolvedPath = realpath(
        __DIR__ . DIRECTORY_SEPARATOR .
        str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath)
    );
    $projectRoot = realpath(__DIR__);

    if (
        $resolvedPath === false ||
        $projectRoot === false ||
        strpos($resolvedPath, $projectRoot . DIRECTORY_SEPARATOR) !== 0 ||
        !is_file($resolvedPath)
    ) {
        return 'assets/img/orpais.jpeg';
    }

    return $image;
}

$activities = [];
$activitiesLoadError = false;

try {
    $statement = $pdo->prepare("
        SELECT id, title, category, category_label, description, icon, image
        FROM activities
        WHERE status = 'active'
        ORDER BY display_order ASC, id ASC
    ");
    $statement->execute();
    $activities = $statement->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $exception) {
    error_log('Unable to load activities page: ' . $exception->getMessage());
    $activitiesLoadError = true;
}

$activityCount = count($activities);
$filters = [
    'all' => 'Toutes les activités',
    'paix' => 'Paix & cohésion',
    'social' => 'Protection sociale',
    'developpement' => 'Développement',
    'education' => 'Éducation',
    'humanitaire' => 'Humanitaire',
];

?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta
        name="description"
        content="Découvrez les activités d’ORPAIS pour la paix, la cohésion sociale et l’autonomisation des communautés."
    >
    <title>Nos activités | ORPAIS</title>
    <link rel="icon" href="assets/img/logo.jpg">
    <link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/vendor/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/activities-next.css">
</head>
<body>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<main class="activities-page">
    <header class="activities-hero fade-in-up">
        <div class="activities-hero-orb" aria-hidden="true"></div>
        <div class="activities-hero-copy">
            <p class="activities-eyebrow">
                <span aria-hidden="true"></span>
                Agir ensemble, transformer durablement
            </p>
            <h1>
                Nos actions au service
                <br>
                <em>des communautés.</em>
            </h1>
            <p class="activities-hero-description">
                Découvrez les initiatives d’ORPAIS pour promouvoir la paix,
                renforcer la cohésion sociale et accompagner l’autonomisation en
                République démocratique du Congo.
            </p>
            <a class="activities-hero-link" href="#activities">
                Explorer nos domaines <span aria-hidden="true">↓</span>
            </a>
        </div>
        <div class="activities-hero-note">
            <strong><?= str_pad((string) $activityCount, 2, '0', STR_PAD_LEFT) ?></strong>
            <span>domaines d’action</span>
        </div>
    </header>

    <section class="activities-introduction fade-in-up" style="animation-delay: 120ms;">
        <div>
            <p class="activities-section-label">Notre engagement</p>
            <h2>
                Des réponses concrètes,
                <br>
                <em>ancrées localement.</em>
            </h2>
        </div>
        <p>
            Nos actions partent des besoins exprimés par les communautés.
            Protection, dialogue, formation et solidarité se conjuguent pour
            faire émerger des solutions durables.
        </p>
    </section>

    <section class="activities-catalog fade-in-up" id="activities" style="animation-delay: 180ms;">
        <div class="activities-catalog-heading">
            <div>
                <p class="activities-section-label">Nos domaines d’intervention</p>
                <h2>Des initiatives qui créent du lien.</h2>
            </div>
            <span id="activitiesCount" aria-live="polite">
                <?= $activityCount ?> activité(s)
            </span>
        </div>

        <div class="activities-filters" aria-label="Filtrer les activités">
            <?php foreach ($filters as $filter => $label): ?>
                <button
                    type="button"
                    data-filter="<?= activities_escape($filter) ?>"
                    aria-pressed="<?= $filter === 'all' ? 'true' : 'false' ?>"
                    class="<?= $filter === 'all' ? 'selected' : '' ?>"
                >
                    <?= activities_escape($label) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <?php if ($activitiesLoadError): ?>
            <div class="activities-empty" role="alert">
                <span aria-hidden="true">!</span>
                <h3>Les activités n’ont pas pu être chargées.</h3>
                <p>Veuillez réessayer dans quelques instants.</p>
            </div>
        <?php else: ?>
            <div class="activities-grid" id="activitiesGrid">
                <?php foreach ($activities as $index => $activity): ?>
                    <article
                        class="next-activity-card activity-item"
                        data-category="<?= activities_escape($activity['category']) ?>"
                        style="animation-delay: <?= min($index, 8) * 75 ?>ms"
                    >
                        <a
                            class="next-activity-image"
                            href="activity-single.php?id=<?= (int) $activity['id'] ?>"
                            aria-label="Découvrir l’activité : <?= activities_escape($activity['title']) ?>"
                        >
                            <img
                                src="<?= activities_escape(activities_image_url($activity['image'] ?? '')) ?>"
                                alt=""
                                loading="lazy"
                            >
                            <span><?= activities_escape($activity['category_label']) ?></span>
                        </a>
                        <div class="next-activity-content">
                            <div class="next-activity-icon" aria-hidden="true">
                                <i class="<?= activities_escape(
                                    !empty($activity['icon'])
                                        ? $activity['icon']
                                        : 'bi bi-activity'
                                ) ?>"></i>
                            </div>
                            <p class="next-activity-index">
                                ACTION / <?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?>
                            </p>
                            <h3><?= activities_escape($activity['title']) ?></h3>
                            <p class="next-activity-description">
                                <?= activities_escape($activity['description']) ?>
                            </p>
                            <a
                                class="next-activity-link"
                                href="activity-single.php?id=<?= (int) $activity['id'] ?>"
                            >
                                Découvrir l’activité <span aria-hidden="true">↗</span>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="activities-empty" id="activitiesEmpty" hidden>
                <span aria-hidden="true">✳</span>
                <h3>Aucune activité dans ce domaine pour le moment.</h3>
                <button type="button" id="showAllActivities">Voir toutes les activités</button>
            </div>
        <?php endif; ?>
    </section>

    <section class="activities-bottom-cta fade-in-up" style="animation-delay: 220ms;">
        <p class="activities-section-label">Un engagement partagé</p>
        <h2>
            La paix se construit
            <br>
            <em>ensemble.</em>
        </h2>
        <p>
            Prenez part à nos initiatives ou échangez avec notre équipe sur un
            partenariat.
        </p>
        <a href="contact.php">
            Entrer en contact <span aria-hidden="true">↗</span>
        </a>
    </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const buttons = Array.from(document.querySelectorAll('.activities-filters button'));
    const items = Array.from(document.querySelectorAll('.activity-item'));
    const count = document.getElementById('activitiesCount');
    const empty = document.getElementById('activitiesEmpty');
    const grid = document.getElementById('activitiesGrid');

    const applyFilter = (filter) => {
        let visibleCount = 0;

        items.forEach((item) => {
            const visible = filter === 'all' || item.dataset.category === filter;
            item.hidden = !visible;
            if (visible) visibleCount += 1;
        });

        buttons.forEach((button) => {
            const selected = button.dataset.filter === filter;
            button.classList.toggle('selected', selected);
            button.setAttribute('aria-pressed', String(selected));
        });

        if (count) count.textContent = `${visibleCount} activité(s)`;
        if (empty) empty.hidden = visibleCount !== 0;
        if (grid) grid.hidden = visibleCount === 0;
    };

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            applyFilter(button.dataset.filter || 'all');
        });
    });

    document.getElementById('showAllActivities')?.addEventListener('click', () => {
        applyFilter('all');
    });
});
</script>
</body>
</html>
