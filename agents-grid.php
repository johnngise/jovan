<?php
require_once __DIR__ . '/config/database.php';
/*
|--------------------------------------------------------------------------
| Fonction de protection HTML
|--------------------------------------------------------------------------
*/
function e($value)
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}

function teamPhotoUrl($photo)
{
    $photo = trim((string) $photo);

    if ($photo === '') {
        return 'assets/img/logo.jpg';
    }

    if (filter_var($photo, FILTER_VALIDATE_URL)) {
        return $photo;
    }

    if (str_starts_with($photo, 'assets/') || str_starts_with($photo, 'uploads/')) {
        return ltrim($photo, '/');
    }

    return 'uploads/team/' . rawurlencode(basename($photo));
}

function isProvisionalTeamName($name)
{
    $name = preg_replace('/\s+/u', ' ', trim((string) $name));

    return $name === ''
        || preg_match(
            '/^(nouveau membre|membre|nom à définir|nom a definir|à compléter|a completer|test|n\/a)$/iu',
            $name
        ) === 1;
}

function teamMemberQualityScore(array $member)
{
    $score = 0;
    $position = trim((string) ($member['position'] ?? ''));
    $biography = trim((string) ($member['biography'] ?? ''));

    if (strlen($position) >= 8) {
        $score += 4;
    }

    if (strlen($biography) >= 24) {
        $score += 3;
    }

    if (!empty($member['photo'])) {
        $score += 1;
    }

    foreach (['facebook', 'instagram', 'linkedin', 'twitter'] as $socialNetwork) {
        if (!empty($member[$socialNetwork])) {
            $score++;
        }
    }

    return $score;
}
/*
|--------------------------------------------------------------------------
| Récupération des membres de l'équipe
|--------------------------------------------------------------------------
*/
$teamLoadError = false;

try {
    $sql = "
    SELECT
        id,
        name,
        position,
        biography,
        photo,
        phone,
        email,
        facebook,
        instagram,
        linkedin,
        twitter
    FROM team_members
    WHERE status = :status
    ORDER BY display_order ASC, id ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'status' => 'active'
    ]);
    $teamMembers = $stmt->fetchAll();

    $membersByName = [];
    foreach ($teamMembers as $member) {
        if (isProvisionalTeamName($member['name'] ?? '')) {
            continue;
        }

        $normalizedName = strtolower(
            preg_replace('/\s+/u', ' ', trim((string) $member['name']))
        );

        if (
            !isset($membersByName[$normalizedName])
            || teamMemberQualityScore($member)
                > teamMemberQualityScore($membersByName[$normalizedName])
        ) {
            $membersByName[$normalizedName] = $member;
        }
    }

    $teamMembers = array_values($membersByName);
} catch (PDOException $exception) {
    error_log('Unable to load ORPAIS team page: ' . $exception->getMessage());
    $teamMembers = [];
    $teamLoadError = true;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0">
<title>ORPAIS | Notre équipe</title>
<meta
    name="description"
    content="Découvrez l'équipe dirigeante et les membres d'ORPAIS.">
<link
    rel="icon"
    href="assets/img/logo.jpg">
<!-- Google Fonts -->
<link
    href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700"
    rel="stylesheet">
<!-- Bootstrap -->
<link
    href="assets/vendor/bootstrap/css/bootstrap.min.css"
    rel="stylesheet">
<!-- Bootstrap Icons -->
<link
    href="assets/vendor/bootstrap-icons/bootstrap-icons.css"
    rel="stylesheet">
<!-- Main CSS -->
<link
    href="assets/css/style.css"
    rel="stylesheet">
<link href="assets/css/tailwind.css" rel="stylesheet">
<style>
    :root {
        --orpaics-dark: #073b73;
        --orpaics-blue: #0b63ce;
        --orpaics-light: #eaf3ff;
        --orpaics-soft: #f5f9ff;
        --orpaics-text: #263b50;
    }
    body {
        font-family: "Poppins", sans-serif;
        color: var(--orpaics-text);
        background: #fff;
    }
    /* ==============================
       NAVBAR
    ============================== */
    .navbar {
        background: #fff !important;
        box-shadow: 0 2px 18px rgba(0,0,0,.08);
        padding: 12px 0;
    }
    .navbar-brand {
        font-weight: 700;
        color: var(--orpaics-dark) !important;
        display: flex;
        align-items: center;
    }
    .navbar-brand img {
        width: 48px;
        height: 48px;
        object-fit: contain;
        margin-right: 10px;
        border-radius: 6px;
    }
    .brand-blue {
        color: var(--orpaics-blue);
    }
    .navbar .nav-link {
        color: var(--orpaics-dark) !important;
        font-weight: 500;
        margin: 0 6px;
    }
    .navbar .nav-link:hover,
    .navbar .nav-link.active {
        color: var(--orpaics-blue) !important;
    }
    /* ==============================
       HEADER
    ============================== */
    .team-header {
        padding: 155px 0 70px;
        background:
            linear-gradient(
                135deg,
                var(--orpaics-dark),
                var(--orpaics-blue)
            );
        color: white;
    }
    .team-header h1 {
        font-size: 42px;
        font-weight: 700;
    }
    .team-header p {
        max-width: 750px;
        line-height: 1.8;
        color: rgba(255,255,255,.85);
    }
    .team-header .breadcrumb {
        justify-content: flex-end;
    }
    .team-header .breadcrumb-item,
    .team-header .breadcrumb-item a {
        color: rgba(255,255,255,.85);
    }
    .team-header .breadcrumb-item.active {
        color: #fff;
    }
    /* ==============================
       TEAM
    ============================== */
    .team-section {
        padding: 80px 0;
        background: var(--orpaics-soft);
    }
    .section-heading {
        text-align: center;
        margin-bottom: 50px;
    }
    .section-heading h2 {
        color: var(--orpaics-dark);
        font-size: 32px;
        font-weight: 700;
    }
    .section-heading p {
        max-width: 700px;
        margin: 10px auto 0;
        color: #6b7c8f;
    }
    /* ==============================
       MEMBER CARD
    ============================== */
    .team-card {
        background: #fff;
        border-radius: 18px;
        overflow: hidden;
        box-shadow:
            0 10px 35px rgba(7,59,102,.08);
        height: 100%;
        transition: .3s;
    }
    .team-card:hover {
        transform: translateY(-6px);
        box-shadow:
            0 18px 45px rgba(7,59,102,.14);
    }
    .team-image {
        height: 320px;
        overflow: hidden;
        background: var(--orpaics-light);
    }
    .team-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: .4s;
    }
    .team-card:hover .team-image img {
        transform: scale(1.04);
    }
    .team-content {
        padding: 25px;
    }
    .team-content h3 {
        color: var(--orpaics-dark);
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 7px;
    }
    .team-position {
        color: var(--orpaics-blue);
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 15px;
    }
    .team-biography {
        color: #6b7c8f;
        font-size: 14px;
        line-height: 1.8;
        margin-bottom: 0;
    }
    /* ==============================
       SOCIALS
    ============================== */
    .team-socials {
        display: flex;
        gap: 8px;
        margin-top: 18px;
    }
    .team-socials a {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--orpaics-light);
        color: var(--orpaics-blue);
        transition: .3s;
        text-decoration: none;
    }
    .team-socials a:hover {
        background: var(--orpaics-blue);
        color: #fff;
    }
    /* ==============================
       EMPTY
    ============================== */
    .empty-team {
        background: #fff;
        padding: 50px;
        border-radius: 15px;
        text-align: center;
        box-shadow:
            0 10px 30px rgba(7,59,102,.06);
    }
    .empty-team i {
        font-size: 45px;
        color: var(--orpaics-blue);
    }
    .empty-team h3 {
        color: var(--orpaics-dark);
        margin-top: 15px;
    }
    /* ==============================
       CTA
    ============================== */
    .team-cta {
        padding: 70px 0;
        background: #fff;
    }
    .cta-box {
        background:
            linear-gradient(
                135deg,
                var(--orpaics-dark),
                var(--orpaics-blue)
            );
        color: #fff;
        padding: 55px 30px;
        border-radius: 20px;
        text-align: center;
    }
    .cta-box h2 {
        font-weight: 700;
    }
    .cta-box p {
        max-width: 700px;
        margin: 15px auto 25px;
        color: rgba(255,255,255,.85);
    }
    .cta-button {
        display: inline-block;
        background: #fff;
        color: var(--orpaics-blue);
        padding: 12px 25px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: .3s;
    }
    .cta-button:hover {
        background: var(--orpaics-light);
        color: var(--orpaics-dark);
    }
    /* ==============================
       FOOTER
    ============================== */
    .section-footer {
        background: var(--orpaics-dark);
        padding: 60px 0 40px;
        color: #fff;
    }
    .footer-title {
        font-weight: 700;
        margin-bottom: 20px;
    }
    .footer-text {
        color: rgba(255,255,255,.7);
        line-height: 1.8;
    }
    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .footer-links li {
        margin-bottom: 10px;
    }
    .footer-links a {
        color: rgba(255,255,255,.7);
        text-decoration: none;
        transition: .3s;
    }
    .footer-links a:hover {
        color: #fff;
    }
    footer {
        background: #052d4f;
        color: rgba(255,255,255,.7);
        padding: 25px 0;
        text-align: center;
    }
    .footer-socials {
        margin-bottom: 15px;
    }
    .footer-socials a {
        color: #fff;
        margin: 0 7px;
        font-size: 18px;
        text-decoration: none;
    }
    /* ==============================
       RESPONSIVE
    ============================== */
    @media (max-width: 991px) {
        .team-header .breadcrumb {
            justify-content: flex-start;
            margin-top: 25px;
        }
    }
    @media (max-width: 768px) {
        .team-header {
            padding-top: 125px;
        }
        .team-header h1 {
            font-size: 32px;
        }
        .team-section {
            padding: 55px 0;
        }
        .team-image {
            height: 280px;
        }
    }
</style>
<link href="assets/css/agents-next.css?v=20260929-2" rel="stylesheet">
</head>
<body class="team-page">
<!-- =========================================================
     NAVBAR
========================================================= -->
<?php require_once __DIR__ . '/includes/header.php'; ?>
<!-- =========================================================
     HEADER
========================================================= -->
<header class="team-header">
<div class="container">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <p class="team-eyebrow">Les visages de notre engagement</p>
            <h1>
                Des femmes et des hommes
                <em>engagés pour la paix.</em>
            </h1>
            <p>
                Découvrez les femmes et les hommes qui
                contribuent à la réalisation de la mission
                d’ORPAIS en faveur de la paix, de
                l’autonomisation et de la cohésion sociale.
            </p>
            <div class="team-hero-actions">
                <a class="team-hero-primary" href="#team-members">
                    Rencontrer l’équipe <i class="bi bi-arrow-down-right" aria-hidden="true"></i>
                </a>
                <a class="team-hero-secondary" href="contact.php">
                    Nous contacter <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="team-hero-visual" aria-label="<?= count($teamMembers) ?> membres actifs">
                <span class="team-hero-orbit team-hero-orbit-outer" aria-hidden="true"></span>
                <span class="team-hero-orbit team-hero-orbit-inner" aria-hidden="true"></span>
                <div class="team-hero-stat">
                    <span>Une même ambition</span>
                    <strong><?= str_pad((string) count($teamMembers), 2, '0', STR_PAD_LEFT) ?></strong>
                    <small>membres actifs</small>
                </div>
                <p>Faire grandir le dialogue et la cohésion, au plus près des communautés.</p>
            </div>
        </div>
    </div>
</div>
</header>
<!-- =========================================================
     TEAM
========================================================= -->
<main>
<section class="team-section" id="team-members">
<div class="container">
    <div class="section-heading team-section-heading">
        <div>
            <span class="team-section-label">Les visages d’ORPAIS</span>
            <h2>Une équipe, une vision commune.</h2>
            <p>Des profils complémentaires qui unissent leurs compétences au service des communautés.</p>
        </div>
        <div class="team-heading-note">
            <span class="team-heading-indicator" aria-hidden="true"></span>
            <span>Notre engagement<br><strong>au quotidien</strong></span>
        </div>
    </div>
    <div class="row g-4">
        <?php if ($teamLoadError): ?>
            <div class="col-12">
                <div class="empty-team" role="alert">
                    <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                    <h3>Les membres de l’équipe n’ont pas pu être chargés.</h3>
                    <p class="mb-0">Veuillez réessayer dans quelques instants.</p>
                </div>
            </div>
        <?php elseif (!empty($teamMembers)): ?>
            <?php foreach ($teamMembers as $index => $member): ?>
                <div class="col-md-6 col-lg-4">
                    <article class="team-card">
                        <div class="team-image">
                            <?php
                            $photo = teamPhotoUrl($member['photo'] ?? '');
                            ?>
                            <span class="team-card-number">
                                ORPAIS <b>/</b> <?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?>
                            </span>
                            <img
                                src="<?= e($photo) ?>"
                                alt="<?= e($member['name']) ?>"
                                loading="lazy"
                                onerror="this.onerror=null;this.src='assets/img/logo.jpg';">
                            <span class="team-photo-caption">Équipe engagée</span>
                        </div>
                        <div class="team-content">
                            <span class="team-card-kicker">Membre de l’organisation</span>
                            <h3>
                                <?= e($member['name']) ?>
                            </h3>
                            <?php if (strlen(trim((string) ($member['position'] ?? ''))) >= 8): ?>
                                <div class="team-position">
                                    <?= e($member['position']) ?>
                                </div>
                            <?php endif; ?>
                            <?php if (strlen(trim((string) ($member['biography'] ?? ''))) >= 24): ?>
                                <p class="team-biography">
                                    <?= nl2br(
                                        e($member['biography'])
                                    ) ?>
                                </p>
                            <?php endif; ?>
                            <!-- RESEAUX SOCIAUX -->
                            <?php
                            $hasSocial =
                                !empty($member['facebook']) ||
                                !empty($member['instagram']) ||
                                !empty($member['linkedin']) ||
                                !empty($member['twitter']);
                            ?>
                            <?php if ($hasSocial): ?>
                                <div class="team-socials">
                                    <?php if (!empty($member['facebook'])): ?>
                                        <a
                                            href="<?= e($member['facebook']) ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            aria-label="Facebook">
                                            <i class="bi bi-facebook"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($member['instagram'])): ?>
                                        <a
                                            href="<?= e($member['instagram']) ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            aria-label="Instagram">
                                            <i class="bi bi-instagram"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($member['linkedin'])): ?>
                                        <a
                                            href="<?= e($member['linkedin']) ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            aria-label="LinkedIn">
                                            <i class="bi bi-linkedin"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($member['twitter'])): ?>
                                        <a
                                            href="<?= e($member['twitter']) ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            aria-label="X">
                                            <i class="bi bi-twitter-x"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Aucun membre -->
            <div class="col-12">
                <div class="empty-team">
                    <i class="bi bi-people"></i>
                    <h3>
                        Notre équipe sera bientôt présentée
                    </h3>
                    <p class="text-muted mb-0">
                        Les membres de l'équipe seront
                        prochainement ajoutés.
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
</section>
<!-- =========================================================
     CTA
========================================================= -->
<section class="team-cta">
<div class="container">
    <div class="cta-box">
        <h2>
            Découvrez notre engagement
        </h2>
        <p>
            ORPAIS œuvre pour renforcer la paix,
            la cohésion sociale et l’autonomisation
            des communautés.
        </p>
        <a
            href="activities.php"
            class="cta-button">
            Découvrir nos activités
            <i class="bi bi-arrow-right ms-2"></i>
        </a>
    </div>
</div>
</section>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
<!-- Bootstrap JS -->
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
