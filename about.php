<?php
/**
 * ORPAIS ASBL - Page Qui sommes-nous
 * Version PHP dynamique
 *
 * Le design HTML/CSS d'origine est conservé.
 */

// Connexion à la base de données déjà utilisée par le site.
// Si ton fichier de connexion porte un autre nom/emplacement,
// remplace simplement cette ligne.
$dbFile = __DIR__ . '/config/db.php';

$teamMembers = [];

if (file_exists($dbFile)) {
    require_once $dbFile;

    // La page fonctionne même si la table n'est pas encore disponible.
    // Structure attendue : team_members(id, name, position, photo, status)
    if (isset($conn) && $conn instanceof mysqli) {
        $sql = "SELECT name, position, photo
                FROM team_members
                WHERE status = 1
                ORDER BY id ASC";
        if ($result = $conn->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $teamMembers[] = $row;
            }
            $result->free();
        }
    }
}

// Fallback : conserve les trois membres affichés actuellement si la base
// ou la table n'est pas encore configurée.
if (empty($teamMembers)) {
    $teamMembers = [
        [
            'name' => 'MICHAEL JOHN NGISE',
            'position' => 'Président du Conseil d’Administration',
            'photo' => 'assets/img/jojojo.jpeg'
        ],
        [
            'name' => 'ALBERTINE KASUMBI TINA',
            'position' => 'Membre fondatrice',
            'photo' => 'assets/img/alber.jpeg'
        ],
        [
            'name' => 'BARAKA SHALLOOM BOMBANDO',
            'position' => 'Membre fondateur',
            'photo' => 'assets/img/gedeon.jpeg'
        ]
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>ORPAIS ASBL | Qui sommes-nous ?</title>

  <meta name="description"
    content="Découvrez ORPAIS ASBL, son histoire, sa mission, sa vision et son engagement pour la paix, la réconciliation, l'autonomisation et la cohésion sociale.">

  <meta name="keywords"
    content="ORPAIS, ASBL, Goma, RDC, paix, réconciliation, cohésion sociale, autonomisation">

  <link rel="icon" href="assets/img/logo.jpg">
  <link rel="apple-touch-icon" href="assets/img/logo.jpg">

  <!-- Google Font -->
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet">

  <!-- Bootstrap -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

  <!-- CSS principal -->
  <link href="assets/css/style.css" rel="stylesheet">
  <link href="assets/css/tailwind.css" rel="stylesheet">


  <style>

    /* ==========================================
       IDENTITÉ VISUELLE ORPAIS
    ========================================== */

    :root {
      --orpaics-blue: #0B63CE;
      --orpaics-dark-blue: #073B73;
      --orpaics-light-blue: #EAF3FF;
      --orpaics-soft: #F6F9FC;
      --orpaics-text: #4F5F70;
      --orpaics-dark: #102A43;
      --white: #ffffff;
    }

    body {
      font-family: 'Poppins', sans-serif;
      color: var(--orpaics-text);
      background: #fff;
    }

    a {
      text-decoration: none;
    }


    /* ==========================================
       NAVBAR
    ========================================== */

    .navbar {
      background: rgba(255,255,255,0.97);
      box-shadow: 0 4px 25px rgba(0,0,0,0.07);
      padding: 12px 0;
    }

    .navbar-brand {
      display: flex;
      align-items: center;
      font-size: 24px;
      font-weight: 800;
      color: var(--orpaics-dark-blue) !important;
    }

    .navbar-brand img {
      width: 48px;
      height: 48px;
      object-fit: contain;
      margin-right: 10px;
      border-radius: 50%;
    }

    .brand-blue {
      color: var(--orpaics-blue);
    }

    .brand-dark {
      color: var(--orpaics-dark-blue);
    }

    .nav-link {
      color: #26384A !important;
      font-weight: 500;
      margin: 0 7px;
      transition: 0.3s;
    }

    .nav-link:hover,
    .nav-link.active {
      color: var(--orpaics-blue) !important;
    }

    .nav-contact {
      background: var(--orpaics-blue);
      color: white !important;
      border-radius: 30px;
      padding: 10px 20px !important;
    }

    .nav-contact:hover {
      background: var(--orpaics-dark-blue);
    }


    /* ==========================================
       PAGE HEADER
    ========================================== */

    .page-header {
      position: relative;
      padding: 170px 0 90px;
      background:
        linear-gradient(
          90deg,
          rgba(7,59,115,0.94),
          rgba(11,99,206,0.78)
        ),
        url('assets/img/orpais.jpeg') center/cover no-repeat;
      color: white;
    }

    .page-header h1 {
      font-size: clamp(35px, 5vw, 55px);
      font-weight: 800;
      margin-bottom: 18px;
    }

    .page-header p {
      max-width: 750px;
      font-size: 17px;
      line-height: 1.8;
      color: rgba(255,255,255,0.9);
    }

    .breadcrumb-custom {
      margin-top: 25px;
    }

    .breadcrumb-custom a {
      color: white;
      opacity: .8;
    }

    .breadcrumb-custom span {
      margin: 0 8px;
      opacity: .6;
    }


    /* ==========================================
       SECTIONS
    ========================================== */

    .section {
      padding: 90px 0;
    }

    .section-light {
      background: var(--orpaics-soft);
    }

    .section-title {
      margin-bottom: 50px;
    }

    .section-label {
      display: inline-block;
      color: var(--orpaics-blue);
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 2px;
      text-transform: uppercase;
      margin-bottom: 10px;
    }

    .section-title h2 {
      color: var(--orpaics-dark);
      font-size: 38px;
      font-weight: 800;
      margin-bottom: 15px;
    }

    .section-title p {
      line-height: 1.8;
      max-width: 700px;
    }


    /* ==========================================
       PRESENTATION
    ========================================== */

    .about-image {
      position: relative;
    }

    .about-image img {
      width: 100%;
      border-radius: 20px;
      display: block;
      box-shadow: 0 20px 50px rgba(0,0,0,0.12);
    }

    .about-badge {
      position: absolute;
      bottom: -25px;
      right: 25px;
      background: var(--orpaics-blue);
      color: white;
      padding: 20px 25px;
      border-radius: 15px;
      box-shadow: 0 12px 30px rgba(11,99,206,.3);
    }

    .about-badge strong {
      display: block;
      font-size: 24px;
      font-weight: 800;
    }

    .about-badge span {
      font-size: 13px;
      opacity: .9;
    }

    .about-content {
      padding-left: 30px;
    }

    .about-content h2 {
      color: var(--orpaics-dark);
      font-size: 36px;
      font-weight: 800;
      margin-bottom: 20px;
    }

    .about-content h2 span {
      color: var(--orpaics-blue);
    }

    .about-content p {
      line-height: 1.9;
      margin-bottom: 18px;
    }


    /* ==========================================
       VALEURS
    ========================================== */

    .value-card {
      background: white;
      border-radius: 18px;
      padding: 35px 28px;
      height: 100%;
      border: 1px solid #e8eef5;
      transition: .3s;
    }

    .value-card:hover {
      transform: translateY(-7px);
      box-shadow: 0 15px 40px rgba(0,0,0,.08);
      border-color: transparent;
    }

    .value-icon {
      width: 60px;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--orpaics-light-blue);
      color: var(--orpaics-blue);
      border-radius: 15px;
      font-size: 28px;
      margin-bottom: 20px;
    }

    .value-card h3 {
      color: var(--orpaics-dark);
      font-size: 19px;
      font-weight: 700;
      margin-bottom: 12px;
    }

    .value-card p {
      font-size: 14px;
      line-height: 1.8;
      margin: 0;
    }


    /* ==========================================
       MISSION / VISION
    ========================================== */

    .mission-box {
      background: var(--orpaics-blue);
      color: white;
      border-radius: 20px;
      padding: 45px;
      height: 100%;
    }

    .mission-box.dark {
      background: var(--orpaics-dark-blue);
    }

    .mission-box i {
      font-size: 38px;
      margin-bottom: 20px;
      display: block;
    }

    .mission-box h3 {
      font-size: 26px;
      font-weight: 700;
      margin-bottom: 15px;
    }

    .mission-box p {
      line-height: 1.9;
      color: rgba(255,255,255,.88);
      margin: 0;
    }


    /* ==========================================
       DOMAINES
    ========================================== */

    .domain-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .domain-list li {
      padding: 13px 0;
      border-bottom: 1px solid #e5ebf2;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .domain-list i {
      color: var(--orpaics-blue);
      font-size: 18px;
    }


    /* ==========================================
       MEMBRES FONDATEURS
    ========================================== */

    .member-card {
      background: white;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 10px 35px rgba(0,0,0,.07);
      height: 100%;
      transition: .3s;
    }

    .member-card:hover {
      transform: translateY(-7px);
    }

    .member-card img {
      width: 100%;
      height: 330px;
      object-fit: cover;
    }

    .member-info {
      padding: 25px;
      text-align: center;
    }

    .member-info h3 {
      color: var(--orpaics-dark);
      font-size: 19px;
      font-weight: 700;
      margin-bottom: 7px;
    }

    .member-info span {
      color: var(--orpaics-blue);
      font-size: 14px;
      font-weight: 600;
    }


    /* ==========================================
       CTA
    ========================================== */

    .cta {
      background:
        linear-gradient(
          100deg,
          var(--orpaics-dark-blue),
          var(--orpaics-blue)
        );
      color: white;
      padding: 75px 0;
      text-align: center;
    }

    .cta h2 {
      font-size: 38px;
      font-weight: 800;
      margin-bottom: 15px;
    }

    .cta p {
      max-width: 700px;
      margin: 0 auto 30px;
      line-height: 1.8;
      color: rgba(255,255,255,.88);
    }

    .btn-white {
      display: inline-block;
      background: white;
      color: var(--orpaics-blue);
      padding: 13px 28px;
      border-radius: 30px;
      font-weight: 700;
    }

    .btn-white:hover {
      color: var(--orpaics-dark-blue);
    }


    /* ==========================================
       FOOTER
    ========================================== */

    footer {
      background: #061F3B;
      color: white;
      padding: 65px 0 25px;
    }

    .footer-brand {
      color: white;
      font-size: 25px;
      font-weight: 800;
      margin-bottom: 20px;
    }

    footer h4 {
      font-size: 17px;
      margin-bottom: 20px;
      font-weight: 700;
    }

    footer p,
    footer li,
    footer a {
      color: rgba(255,255,255,.7);
      font-size: 14px;
      line-height: 1.8;
    }

    footer a:hover {
      color: white;
    }

    .footer-bottom {
      border-top: 1px solid rgba(255,255,255,.1);
      margin-top: 40px;
      padding-top: 25px;
      text-align: center;
      color: rgba(255,255,255,.55);
      font-size: 13px;
    }


    /* ==========================================
       MOBILE
    ========================================== */

    @media (max-width: 991px) {

      .navbar-collapse {
        background: white;
        padding: 20px;
        margin-top: 10px;
        border-radius: 12px;
      }

      .about-content {
        padding-left: 0;
        margin-top: 50px;
      }

      .page-header {
        padding-top: 150px;
      }

    }

    @media (max-width: 576px) {

      .navbar-brand {
        font-size: 20px;
      }

      .navbar-brand img {
        width: 40px;
        height: 40px;
      }

      .section {
        padding: 65px 0;
      }

      .section-title h2 {
        font-size: 30px;
      }

      .about-content h2 {
        font-size: 30px;
      }

      .mission-box {
        padding: 30px;
      }

      .cta h2 {
        font-size: 30px;
      }

    }

  </style>
  <link href="assets/css/about-next.css" rel="stylesheet">

</head>


<body class="about-next-page">


<!-- ==================================================
     NAVBAR
================================================== -->

<?php require_once __DIR__ . '/includes/header.php'; ?>


<!-- ==================================================
     PAGE HEADER
================================================== -->

<section class="page-header fade-in-up">

  <div class="container">

    <h1>
      Qui sommes-nous ?
    </h1>

    <p>
      Découvrez ORPAIS, son engagement, sa vision et
      son action en faveur de la paix, de la réconciliation,
      de l’autonomisation et de la cohésion sociale.
    </p>

    <div class="breadcrumb-custom">

      <a href="index.php">
        Accueil
      </a>

      <span>/</span>

      <strong>
        Qui sommes-nous ?
      </strong>

    </div>

  </div>

</section>


<main>


<!-- ==================================================
     PRESENTATION
================================================== -->

<section class="section fade-in-up" style="animation-delay: 120ms;">

  <div class="container">

    <div class="row align-items-center g-5">


      <div class="col-lg-6">

        <div class="about-image">

          <img
            src="assets/img/orpais.jpeg"
            alt="ORPAIS ASBL">

          <div class="about-badge">

            <strong>Depuis 2023</strong>

            <span>
              Engagement communautaire
            </span>

          </div>

        </div>

      </div>


      <div class="col-lg-6">

        <div class="about-content">

          <span class="section-label">
            Notre organisation
          </span>

          <h2>
            Construire la paix et la cohésion sociale
            <span>ensemble.</span>
          </h2>

          <p>
            L’Organisation pour la Réconciliation, la Paix,
            l’Autonomisation et la Cohésion Sociale
            (<strong>ORPAIS ASBL</strong>) est une organisation
            engagée dans la construction d’une paix durable
            et le développement des communautés.
          </p>

          <p>
            Face aux défis liés aux conflits, à la pauvreté,
            aux déplacements, aux inégalités et à l’accès
            limité aux services sociaux de base, ORPAIS
            œuvre pour renforcer la réconciliation,
            la cohésion sociale, l’inclusion, les droits
            humains et la participation citoyenne.
          </p>

          <p>
            Notre approche place les communautés au cœur
            de l’action afin de favoriser leur autonomie
            et leur permettre de devenir elles-mêmes
            des acteurs du changement.
          </p>

        </div>

      </div>

    </div>

  </div>

</section>


<!-- ==================================================
     MISSION / VISION
================================================== -->

<section class="section section-light fade-in-up" style="animation-delay: 180ms;">

  <div class="container">

    <div class="section-title">

      <span class="section-label">
        Notre identité
      </span>

      <h2>
        Notre mission et notre vision
      </h2>

    </div>


    <div class="row g-4">


      <div class="col-lg-6">

        <div class="mission-box">

          <i class="bi bi-bullseye"></i>

          <h3>
            Notre mission
          </h3>

          <p>
            Contribuer à la construction d’une société
            pacifique, inclusive et résiliente en favorisant
            la réconciliation, la cohésion sociale,
            l’autonomisation des communautés et la protection
            des droits humains.
          </p>

        </div>

      </div>


      <div class="col-lg-6">

        <div class="mission-box dark">

          <i class="bi bi-eye"></i>

          <h3>
            Notre vision
          </h3>

          <p>
            Une société dans laquelle chaque personne,
            chaque famille et chaque communauté peut vivre
            dans la dignité, la paix, la sécurité et participer
            pleinement à son développement.
          </p>

        </div>

      </div>


    </div>

  </div>

</section>


<!-- ==================================================
     NOS VALEURS
================================================== -->

<section class="section">

  <div class="container">

    <div class="section-title text-center">

      <span class="section-label">
        Ce qui nous guide
      </span>

      <h2>
        Nos valeurs
      </h2>

      <p class="mx-auto">
        Nos interventions sont guidées par des valeurs
        qui placent la personne et la communauté au centre
        de notre action.
      </p>

    </div>


    <div class="row g-4">


      <div class="col-lg-3 col-md-6">

        <div class="value-card">

          <div class="value-icon">
            <i class="bi bi-peace"></i>
          </div>

          <h3>
            Paix
          </h3>

          <p>
            Promouvoir le dialogue, la tolérance
            et la coexistence pacifique.
          </p>

        </div>

      </div>


      <div class="col-lg-3 col-md-6">

        <div class="value-card">

          <div class="value-icon">
            <i class="bi bi-people"></i>
          </div>

          <h3>
            Inclusion
          </h3>

          <p>
            Favoriser la participation de toutes
            les composantes de la communauté.
          </p>

        </div>

      </div>


      <div class="col-lg-3 col-md-6">

        <div class="value-card">

          <div class="value-icon">
            <i class="bi bi-shield-check"></i>
          </div>

          <h3>
            Dignité
          </h3>

          <p>
            Défendre les droits humains et la dignité
            de chaque personne.
          </p>

        </div>

      </div>


      <div class="col-lg-3 col-md-6">

        <div class="value-card">

          <div class="value-icon">
            <i class="bi bi-lightbulb"></i>
          </div>

          <h3>
            Autonomisation
          </h3>

          <p>
            Donner aux communautés les moyens
            de devenir actrices de leur avenir.
          </p>

        </div>

      </div>


    </div>

  </div>

</section>


<!-- ==================================================
     DOMAINES D'ACTION
================================================== -->

<section class="section section-light">

  <div class="container">

    <div class="row align-items-center g-5">


      <div class="col-lg-6">

        <span class="section-label">
          Notre rayon d’action
        </span>

        <h2 style="
          color: var(--orpaics-dark);
          font-size: 36px;
          font-weight: 800;
          margin-bottom: 20px;
        ">
          Des actions au service des communautés
        </h2>

        <p style="line-height:1.9;">

          É travers une approche communautaire,
          participative et inclusive, ORPAIS intervient
          dans plusieurs domaines visant à répondre aux
          besoins des populations et à renforcer leur
          résilience.

        </p>

      </div>


      <div class="col-lg-6">

        <ul class="domain-list">

          <li>
            <i class="bi bi-check-circle-fill"></i>
            Paix et cohésion sociale
          </li>

          <li>
            <i class="bi bi-check-circle-fill"></i>
            Autonomisation socio-économique
          </li>

          <li>
            <i class="bi bi-check-circle-fill"></i>
            Éducation et formation
          </li>

          <li>
            <i class="bi bi-check-circle-fill"></i>
            Santé et bien-être
          </li>

          <li>
            <i class="bi bi-check-circle-fill"></i>
            Développement communautaire et humanitaire
          </li>

          <li>
            <i class="bi bi-check-circle-fill"></i>
            Plaidoyer et participation citoyenne
          </li>

          <li>
            <i class="bi bi-check-circle-fill"></i>
            Médiation et réconciliation
          </li>

          <li>
            <i class="bi bi-check-circle-fill"></i>
            Prévention des violences basées sur le genre
          </li>

          <li>
            <i class="bi bi-check-circle-fill"></i>
            Réinsertion communautaire
          </li>

        </ul>

      </div>


    </div>

  </div>

</section>


<!-- ==================================================
     MEMBRES FONDATEURS
================================================== -->

<section class="section">

  <div class="container">

    <div class="section-title text-center">

      <span class="section-label">
        Gouvernance
      </span>

      <h2>
        Membres fondateurs
      </h2>

      <p class="mx-auto">
        Des membres engagés dans la réalisation de la
        mission et de la vision d’ORPAIS.
      </p>

    </div>


    <div class="row g-4">

<?php foreach ($teamMembers as $member): ?>
      <div class="col-lg-4 col-md-6">

        <div class="member-card">

          <img
            src="<?= htmlspecialchars($member['photo'] ?? 'assets/img/logo.jpg', ENT_QUOTES, 'UTF-8') ?>"
            alt="<?= htmlspecialchars($member['name'] ?? 'Membre ORPAIS', ENT_QUOTES, 'UTF-8') ?>">

          <div class="member-info">

            <h3>
              <?= htmlspecialchars($member['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </h3>

            <span>
              <?= htmlspecialchars($member['position'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </span>

          </div>

        </div>

      </div>
<?php endforeach; ?>

    </div>

  </div>

</section>


<!-- ==================================================
     CTA
================================================== -->

<section class="cta">

  <div class="container">

    <h2>
      Agissons ensemble pour la paix.
    </h2>

    <p>
      Vous souhaitez collaborer avec ORPAIS,
      soutenir nos initiatives ou contribuer à nos
      actions communautaires ?
    </p>

    <a href="contact.php" class="btn-white">

      Nous contacter

      <i class="bi bi-arrow-right"></i>

    </a>

  </div>

</section>


</main>


<!-- ==================================================
     FOOTER
================================================== -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>


<!-- ==================================================
     JAVASCRIPT
================================================== -->

<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
<script src="assets/js/main.js"></script>

</body>

</html>


