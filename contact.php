<?php
/**
 * ORPAIS ASBL - Page Contact
 * Version PHP
 *
 * Le design original est conservé.
 */

// Coordonnées de l'organisation.
// Elles peuvent être déplacées plus tard dans une table MySQL
// ou dans un fichier de configuration central.
$contactInfo = [
    'address' => 'Goma, République Démocratique du Congo',
    'email'   => 'info@orpaics.org',
    'phone'   => '+243 995 772 320',
];

// Connexion optionnelle à la base de données.
// Si config/db.php existe et expose $conn, les coordonnées peuvent
// être récupérées depuis une table site_settings si elle existe.
$dbFile = __DIR__ . '/config/db.php';

if (file_exists($dbFile)) {
    require_once $dbFile;

    if (isset($conn) && $conn instanceof mysqli) {
        $sql = "SELECT setting_key, setting_value
                FROM site_settings
                WHERE setting_key IN ('address', 'email', 'phone')";

        if ($result = $conn->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                if (array_key_exists($row['setting_key'], $contactInfo)) {
                    $contactInfo[$row['setting_key']] = $row['setting_value'];
                }
            }
            $result->free();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>ORPAIS | Contact</title>

  <meta name="description"
    content="Contactez ORPAIS, Organisation pour la Réconciliation, la Paix, l’Autonomisation et la Cohésion Sociale.">
  <meta name="keywords"
    content="ORPAIS, contact, Goma, RDC, paix, cohésion sociale, autonomisation">

  <!-- Favicon -->
  <link rel="icon" href="assets/img/logo.jpg">
  <link rel="apple-touch-icon" href="assets/img/logo.jpg">

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

  <!-- Swiper -->
  <link
    href="assets/vendor/swiper/swiper-bundle.min.css"
    rel="stylesheet">

  <!-- Main CSS -->
  <link href="assets/css/style.css" rel="stylesheet">
  <link href="assets/css/tailwind.css" rel="stylesheet">

  <style>

    /* =========================================================
       ORPAIS - IDENTITÉ VISUELLE
    ========================================================= */

    :root {
      --orpaics-dark: #073b66;
      --orpaics-blue: #0b5ed7;
      --orpaics-light: #eaf3ff;
      --orpaics-soft: #f5f9ff;
      --orpaics-text: #263b50;
      --orpaics-muted: #6b7c8f;
      --white: #ffffff;
      --border: #dce6f0;
    }

    body {
      font-family: "Poppins", sans-serif;
      color: var(--orpaics-text);
      background: #fff;
    }

    a {
      text-decoration: none;
    }

    /* =========================================================
       NAVBAR
    ========================================================= */

    .navbar {
      background: #fff !important;
      box-shadow: 0 2px 20px rgba(7, 59, 102, 0.08);
      padding: 12px 0;
    }

    .navbar-brand {
      color: var(--orpaics-dark) !important;
      font-weight: 700;
      font-size: 21px;
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

    .navbar-brand span {
      color: var(--orpaics-dark);
    }

    .navbar-brand .brand-blue {
      color: var(--orpaics-blue);
    }

    .navbar .nav-link {
      color: var(--orpaics-dark) !important;
      font-weight: 500;
      margin-left: 8px;
      margin-right: 8px;
      transition: 0.3s;
    }

    .navbar .nav-link:hover,
    .navbar .nav-link.active {
      color: var(--orpaics-blue) !important;
    }

    .navbar-toggler {
      border: none;
      box-shadow: none !important;
    }

    /* =========================================================
       PAGE HEADER
    ========================================================= */

    .contact-header {
      padding: 160px 0 80px;
      background:
        linear-gradient(
          135deg,
          rgba(7, 59, 102, 0.98),
          rgba(11, 94, 215, 0.94)
        );
      color: #fff;
    }

    .contact-header h1 {
      font-size: 44px;
      font-weight: 700;
      margin-bottom: 20px;
    }

    .contact-header p {
      max-width: 800px;
      font-size: 16px;
      line-height: 1.9;
      color: rgba(255,255,255,0.88);
    }

    .breadcrumb {
      margin-top: 20px;
      justify-content: flex-end;
    }

    .breadcrumb-item a {
      color: #fff;
    }

    .breadcrumb-item.active {
      color: rgba(255,255,255,0.7);
    }

    /* =========================================================
       CONTACT SECTION
    ========================================================= */

    .contact-section {
      padding: 80px 0;
      background: var(--orpaics-soft);
    }

    .section-title {
      margin-bottom: 45px;
    }

    .section-title h2 {
      color: var(--orpaics-dark);
      font-size: 32px;
      font-weight: 700;
      margin-bottom: 12px;
    }

    .section-title p {
      color: var(--orpaics-muted);
      max-width: 700px;
    }

    /* =========================================================
       MAP
    ========================================================= */

    .map-container {
      background: #fff;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 10px 35px rgba(7,59,102,0.08);
      margin-bottom: 45px;
    }

    .map-container iframe {
      width: 100%;
      height: 420px;
      border: 0;
      display: block;
    }

    /* =========================================================
       CARDS
    ========================================================= */

    .contact-card {
      height: 100%;
      background: #fff;
      border-radius: 18px;
      padding: 35px;
      box-shadow: 0 10px 35px rgba(7,59,102,0.07);
    }

    .contact-card h3 {
      color: var(--orpaics-dark);
      font-size: 23px;
      font-weight: 700;
      margin-bottom: 10px;
    }

    .contact-card > p {
      color: var(--orpaics-muted);
      line-height: 1.8;
      margin-bottom: 30px;
    }

    /* =========================================================
       FORMULAIRE
    ========================================================= */

    .form-label {
      color: var(--orpaics-dark);
      font-weight: 500;
      margin-bottom: 7px;
    }

    .form-control {
      border: 1px solid var(--border);
      border-radius: 9px;
      padding: 13px 15px;
      color: var(--orpaics-text);
      box-shadow: none;
      transition: 0.25s;
    }

    .form-control:focus {
      border-color: var(--orpaics-blue);
      box-shadow: 0 0 0 3px rgba(11,94,215,0.10);
    }

    .form-control.is-invalid {
      border-color: #dc3545;
    }

    .form-control.is-valid {
      border-color: #198754;
    }

    .invalid-feedback {
      font-size: 13px;
    }

    .btn-orpaics {
      background: var(--orpaics-blue);
      color: #fff;
      border: none;
      border-radius: 9px;
      padding: 13px 28px;
      font-weight: 600;
      transition: 0.3s;
    }

    .btn-orpaics:hover {
      background: var(--orpaics-dark);
      color: #fff;
      transform: translateY(-2px);
    }

    .btn-orpaics:disabled {
      opacity: 0.7;
      cursor: not-allowed;
      transform: none;
    }

    /* =========================================================
       ALERTS
    ========================================================= */

    #formAlert {
      display: none;
      border-radius: 9px;
      padding: 14px 16px;
      margin-bottom: 20px;
    }

    #formAlert.show {
      display: block;
    }

    /* =========================================================
       INFOS CONTACT
    ========================================================= */

    .contact-info-item {
      display: flex;
      align-items: flex-start;
      gap: 17px;
      margin-bottom: 28px;
    }

    .contact-info-icon {
      width: 50px;
      height: 50px;
      min-width: 50px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--orpaics-light);
      color: var(--orpaics-blue);
      font-size: 21px;
    }

    .contact-info-item h5 {
      color: var(--orpaics-dark);
      font-weight: 600;
      margin-bottom: 5px;
    }

    .contact-info-item p {
      color: var(--orpaics-muted);
      margin: 0;
      line-height: 1.7;
    }

    .contact-info-item a {
      color: var(--orpaics-blue);
    }

    /* =========================================================
       RESEAUX SOCIAUX
    ========================================================= */

    .social-links {
      display: flex;
      gap: 10px;
      margin-top: 20px;
    }

    .social-links a {
      width: 43px;
      height: 43px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--orpaics-light);
      color: var(--orpaics-blue);
      font-size: 18px;
      transition: 0.3s;
    }

    .social-links a:hover {
      background: var(--orpaics-blue);
      color: #fff;
      transform: translateY(-3px);
    }

    /* =========================================================
       CTA
    ========================================================= */

    .contact-cta {
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
      border-radius: 20px;
      padding: 55px 35px;
      text-align: center;
      color: #fff;
    }

    .cta-box h2 {
      font-weight: 700;
      margin-bottom: 15px;
    }

    .cta-box p {
      max-width: 700px;
      margin: 0 auto 25px;
      color: rgba(255,255,255,0.85);
      line-height: 1.8;
    }

    .cta-btn {
      display: inline-block;
      background: #fff;
      color: var(--orpaics-blue);
      padding: 12px 25px;
      border-radius: 8px;
      font-weight: 600;
      transition: 0.3s;
    }

    .cta-btn:hover {
      background: var(--orpaics-light);
      color: var(--orpaics-dark);
    }

    /* =========================================================
       FOOTER
    ========================================================= */

    .section-footer {
      background: var(--orpaics-dark);
      padding: 60px 0 40px;
      color: #fff;
    }

    .footer-title {
      font-size: 20px;
      font-weight: 700;
      margin-bottom: 20px;
      color: #fff;
    }

    .footer-description {
      color: rgba(255,255,255,0.72);
      line-height: 1.8;
    }

    .footer-links {
      padding: 0;
      margin: 0;
      list-style: none;
    }

    .footer-links li {
      margin-bottom: 11px;
    }

    .footer-links a {
      color: rgba(255,255,255,0.72);
      transition: 0.3s;
    }

    .footer-links a:hover {
      color: #fff;
      padding-left: 4px;
    }

    .footer-contact {
      color: rgba(255,255,255,0.72);
      margin-bottom: 10px;
    }

    .footer-contact i {
      color: #fff;
      margin-right: 8px;
    }

    .footer-bottom {
      background: #052d4f;
      padding: 22px 0;
      text-align: center;
      color: rgba(255,255,255,0.65);
    }

    .footer-socials {
      margin-bottom: 12px;
    }

    .footer-socials a {
      color: #fff;
      font-size: 18px;
      margin: 0 7px;
    }

    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 991px) {

      .navbar .nav-link {
        margin: 4px 0;
      }

      .contact-header {
        padding-top: 135px;
      }

      .breadcrumb {
        justify-content: flex-start;
      }

    }

    @media (max-width: 767px) {

      .contact-header h1 {
        font-size: 32px;
      }

      .contact-header {
        padding: 125px 0 55px;
      }

      .contact-section {
        padding: 55px 0;
      }

      .map-container iframe {
        height: 300px;
      }

      .contact-card {
        padding: 25px;
      }

      .cta-box {
        padding: 40px 22px;
      }

    }

  </style>
  <link href="assets/css/contact-next.css?v=20260929-1" rel="stylesheet">
</head>


<body class="contact-page">

  <!-- =========================================================
       NAVBAR
  ========================================================= -->

  <?php require_once __DIR__ . '/includes/header.php'; ?>


  <!-- =========================================================
       HEADER
  ========================================================= -->

  <header class="contact-header">

    <div class="container">

      <div class="row align-items-center">

        <div class="col-lg-8">

          <p class="contact-eyebrow">Parlons de vos projets</p>
          <h1>Nous contacter</h1>

          <p>
            Vous souhaitez obtenir des informations sur ORPAIS,
            nos programmes, nos activités ou nos actions en faveur
            de la paix et de la cohésion sociale ?
            Notre équipe est à votre écoute.
          </p>

        </div>


        <div class="col-lg-4">

          <nav aria-label="breadcrumb">

            <ol class="breadcrumb">

              <li class="breadcrumb-item">
                <a href="index.php">
                  Accueil
                </a>
              </li>

              <li
                class="breadcrumb-item active"
                aria-current="page">
                Contact
              </li>

            </ol>

          </nav>

        </div>

      </div>

    </div>

  </header>


  <main>


    <!-- =======================================================
         CONTACT
    ======================================================== -->

    <section class="contact-section">

      <div class="container">

        <div class="section-title">

          <h2>Restons en contact</h2>

          <p>
            Pour toute demande d'information, proposition de
            collaboration ou question concernant ORPAIS,
            vous pouvez utiliser le formulaire ci-dessous.
          </p>

        </div>


        <!-- =====================================================
             CARTE
        ====================================================== -->

        <div class="map-container">

          <!--
            Carte générale de Goma.
            Lorsque l'adresse officielle exacte d'ORPAIS
            sera confirmée, remplacer cette URL.
          -->

          <div class="map-controls">
            <button
              type="button"
              id="locateMeButton"
              class="map-location-button">
              <i class="bi bi-crosshair" aria-hidden="true"></i>
              Utiliser ma position
            </button>
            <span id="mapLocationStatus" role="status" aria-live="polite">
                Carte centrée sur Q Les volcans, 10 Ave Mont-Goma, Goma.
            </span>
          </div>

          <iframe
            id="contactMapFrame"
              src="https://www.google.com/maps?q=Q%20Les%20volcans%2C%2010%20Ave%20Mont-Goma%2C%20Goma&z=16&output=embed"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            allowfullscreen>
          </iframe>

        </div>


        <div class="row g-4 contact-content-row">


          <!-- ===================================================
               FORMULAIRE
          ==================================================== -->

          <div class="col-lg-7">

            <div class="contact-card contact-form-card">

              <p class="contact-card-eyebrow">Écrivez-nous</p>
              <h3>Envoyez-nous un message</h3>

              <p>
                Remplissez les informations ci-dessous.
                Les champs marqués d'un astérisque sont obligatoires.
              </p>


              <!-- MESSAGE DE RETOUR -->

              <div
                id="formAlert"
                role="alert"
                aria-live="polite">
              </div>


              <form
                id="contactForm"
                action="forms/contact.php"
                method="POST"
                novalidate>


                <!-- NOM -->

                <div class="mb-3">

                  <label
                    for="name"
                    class="form-label">

                    Nom complet *

                  </label>

                  <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    placeholder="Ex. Jean Dupont"
                    minlength="2"
                    maxlength="100"
                    autocomplete="name"
                    required>

                  <div class="invalid-feedback">
                    Veuillez entrer votre nom complet.
                  </div>

                </div>


                <!-- EMAIL -->

                <div class="mb-3">

                  <label
                    for="email"
                    class="form-label">

                    Adresse e-mail *

                  </label>

                  <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    placeholder="exemple@email.com"
                    maxlength="150"
                    autocomplete="email"
                    required>

                  <div class="invalid-feedback">
                    Veuillez entrer une adresse e-mail valide.
                  </div>

                </div>


                <!-- TELEPHONE -->

                <div class="mb-3">

                  <label
                    for="phone"
                    class="form-label">

                    Téléphone
                    <span class="text-muted">(facultatif)</span>

                  </label>

                  <input
                    type="tel"
                    id="phone"
                    name="phone"
                    class="form-control"
                    placeholder="+243 ..."
                    maxlength="30"
                    autocomplete="tel">

                  <div class="invalid-feedback">
                    Veuillez entrer un numéro valide.
                  </div>

                </div>


                <!-- SUJET -->

                <div class="mb-3">

                  <label
                    for="subject"
                    class="form-label">

                    Sujet *

                  </label>

                  <input
                    type="text"
                    id="subject"
                    name="subject"
                    class="form-control"
                    placeholder="Objet de votre message"
                    minlength="3"
                    maxlength="150"
                    required>

                  <div class="invalid-feedback">
                    Veuillez préciser le sujet de votre message.
                  </div>

                </div>


                <!-- MESSAGE -->

                <div class="mb-4">

                  <label
                    for="message"
                    class="form-label">

                    Votre message *

                  </label>

                  <textarea
                    id="message"
                    name="message"
                    class="form-control"
                    rows="7"
                    minlength="10"
                    maxlength="5000"
                    placeholder="Écrivez votre message..."
                    required></textarea>

                  <div class="invalid-feedback">
                    Votre message doit contenir au moins 10 caractères.
                  </div>

                </div>


                <!-- BOUTON -->

                <button
                  type="submit"
                  id="submitButton"
                  class="btn btn-orpaics">

                  <span
                    id="buttonText">

                    <i class="bi bi-send me-2"></i>
                    Envoyer le message

                  </span>

                  <span
                    id="buttonLoading"
                    class="d-none">

                    <span
                      class="spinner-border spinner-border-sm me-2"
                      aria-hidden="true">
                    </span>

                    Envoi en cours...

                  </span>

                </button>

              </form>

            </div>

          </div>


          <!-- ===================================================
               INFORMATIONS
          ==================================================== -->

          <div class="col-lg-5">

            <div class="contact-card contact-details-card">

              <p class="contact-card-eyebrow">Nos coordonnées</p>
              <h3>Nos coordonnées</h3>

              <p>
                Retrouvez ici les différents moyens de contacter
                l'organisation.
              </p>


              <!-- ADRESSE -->

              <div class="contact-info-item">

                <div class="contact-info-icon">

                  <i class="bi bi-geo-alt"></i>

                </div>

                <div>

                  <h5>Adresse</h5>

                  <p>
                    <?= htmlspecialchars($contactInfo['address'], ENT_QUOTES, 'UTF-8') ?>
                  </p>

                </div>

              </div>


              <!-- EMAIL -->

              <div class="contact-info-item">

                <div class="contact-info-icon">

                  <i class="bi bi-envelope"></i>

                </div>

                <div>

                  <h5>Email</h5>

                  <!--
                    É remplacer par l'adresse officielle ORPAIS.
                  -->

                  <p>
                    <a href="mailto:<?= htmlspecialchars($contactInfo['email'], ENT_QUOTES, 'UTF-8') ?>">
                      <?= htmlspecialchars($contactInfo['email'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                  </p>

                </div>

              </div>


              <!-- TELEPHONE -->

              <div class="contact-info-item">

                <div class="contact-info-icon">

                  <i class="bi bi-telephone"></i>

                </div>

                <div>

                  <h5>Téléphone</h5>

                  <p>
                    <a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $contactInfo['phone']), ENT_QUOTES, 'UTF-8') ?>">
                      <?= htmlspecialchars($contactInfo['phone'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                  </p>

                </div>

              </div>


              <!-- DISPONIBILITE -->

              <div class="contact-info-item">

                <div class="contact-info-icon">

                  <i class="bi bi-clock"></i>

                </div>

                <div>

                  <h5>Disponibilité</h5>

                  <p>
                    Pour toute demande, utilisez notre formulaire
                    de contact. Notre équipe vous répondra
                    dans les meilleurs délais.
                  </p>

                </div>

              </div>


              <!-- RESEAUX SOCIAUX -->

              <hr class="my-4">

              <h5 class="fw-semibold">
                Suivez ORPAIS
              </h5>

              <p class="text-muted mb-2">
                Les liens officiels seront ajoutés ici.
              </p>

              <div class="social-links">

                <!-- Remplacer # par les vrais liens -->

                <a
                  href="#"
                  aria-label="Facebook"
                  title="Facebook">

                  <i class="bi bi-facebook"></i>

                </a>

                <a
                  href="#"
                  aria-label="Instagram"
                  title="Instagram">

                  <i class="bi bi-instagram"></i>

                </a>

                <a
                  href="#"
                  aria-label="LinkedIn"
                  title="LinkedIn">

                  <i class="bi bi-linkedin"></i>

                </a>

                <a
                  href="#"
                  aria-label="X"
                  title="X">

                  <i class="bi bi-twitter-x"></i>

                </a>

              </div>

            </div>

          </div>

        </div>

      </div>

    </section>


    <!-- =======================================================
         CTA
    ======================================================== -->

    <section class="contact-cta">

      <div class="container">

        <div class="cta-box">

          <h2>
            Construisons ensemble la paix et la cohésion sociale
          </h2>

          <p>
            Découvrez les domaines dans lesquels ORPAIS intervient
            et les actions menées auprès des communautés.
          </p>

          <a
            href="activities.php"
            class="cta-btn">

            Découvrir nos activités
            <i class="bi bi-arrow-right ms-2"></i>

          </a>

        </div>

      </div>

    </section>

  </main>


  <?php require_once __DIR__ . '/includes/footer.php'; ?>


  <!-- =========================================================
       SCRIPTS
  ========================================================= -->

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

  <script src="assets/js/main.js"></script>


  <!-- =========================================================
       VALIDATION + ENVOI DU FORMULAIRE
  ========================================================= -->

  <script>

    document.addEventListener("DOMContentLoaded", function () {

      const mapFrame = document.getElementById("contactMapFrame");
      const locateMeButton = document.getElementById("locateMeButton");
      const mapLocationStatus = document.getElementById("mapLocationStatus");

      if (mapFrame && locateMeButton && mapLocationStatus) {
        locateMeButton.addEventListener("click", function () {
          if (!navigator.geolocation) {
            mapLocationStatus.textContent =
              "La géolocalisation n'est pas disponible dans ce navigateur.";
            return;
          }

          locateMeButton.disabled = true;
          mapLocationStatus.textContent =
            "Recherche de votre position en cours…";

          navigator.geolocation.getCurrentPosition(
            function (position) {
              const latitude = position.coords.latitude;
              const longitude = position.coords.longitude;

              if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
                mapLocationStatus.textContent =
                  "Votre position n'a pas pu être déterminée.";
                locateMeButton.disabled = false;
                return;
              }

              const mapUrl = new URL("https://www.google.com/maps");
              mapUrl.searchParams.set("q", latitude + "," + longitude);
              mapUrl.searchParams.set("z", "14");
              mapUrl.searchParams.set("output", "embed");
              mapFrame.src = mapUrl.toString();
              mapLocationStatus.textContent =
                "La carte est centrée sur votre position.";
              locateMeButton.disabled = false;
            },
            function (error) {
              if (error.code === error.PERMISSION_DENIED) {
                mapLocationStatus.textContent =
                  "Accès à la position refusé. Autorisez la localisation dans votre navigateur pour réessayer.";
              } else if (error.code === error.POSITION_UNAVAILABLE) {
                mapLocationStatus.textContent =
                  "Votre position est momentanément indisponible. La carte reste centrée sur l'adresse ORPAIS.";
              } else if (error.code === error.TIMEOUT) {
                mapLocationStatus.textContent =
                  "La recherche de position a expiré. Réessayez dans un instant.";
              } else {
                mapLocationStatus.textContent =
                  "Une erreur a empêché la géolocalisation. La carte reste centrée sur l'adresse ORPAIS.";
              }

              locateMeButton.disabled = false;
            },
            {
              enableHighAccuracy: true,
              timeout: 10000,
              maximumAge: 60000
            }
          );
        });
      }

      const form = document.getElementById("contactForm");
      const alertBox = document.getElementById("formAlert");
      const submitButton = document.getElementById("submitButton");
      const buttonText = document.getElementById("buttonText");
      const buttonLoading = document.getElementById("buttonLoading");

      /*
       * Fonction d'affichage des messages
       */
      function showAlert(type, message) {

        alertBox.className = "alert alert-" + type + " show";
        const icon = document.createElement("i");
        icon.className = type === "success"
          ? "bi bi-check-circle me-2"
          : "bi bi-exclamation-circle me-2";
        icon.setAttribute("aria-hidden", "true");

        const text = document.createElement("span");
        text.textContent = message;

        alertBox.replaceChildren(icon, text);

      }


      /*
       * Nettoyage des messages
       */
      function clearAlert() {

        alertBox.className = "";
        alertBox.innerHTML = "";

      }


      /*
       * Validation d'un email
       */
      function validEmail(email) {

        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

      }


      /*
       * Validation du formulaire
       */
      form.addEventListener("submit", async function (event) {

        event.preventDefault();

        clearAlert();

        let valid = true;


        /*
         * Récupération des champs
         */

        const name = document.getElementById("name");
        const email = document.getElementById("email");
        const phone = document.getElementById("phone");
        const subject = document.getElementById("subject");
        const message = document.getElementById("message");


        /*
         * Nettoyage précédent
         */

        [
          name,
          email,
          phone,
          subject,
          message
        ].forEach(function (field) {

          field.classList.remove(
            "is-valid",
            "is-invalid"
          );

        });


        /*
         * NOM
         */

        if (name.value.trim().length < 2) {

          name.classList.add("is-invalid");
          valid = false;

        } else {

          name.classList.add("is-valid");

        }


        /*
         * EMAIL
         */

        if (!validEmail(email.value.trim())) {

          email.classList.add("is-invalid");
          valid = false;

        } else {

          email.classList.add("is-valid");

        }


        /*
         * TELEPHONE - facultatif
         */

        if (phone.value.trim() !== "") {

          const phoneRegex =
            /^[0-9+\s()\-]{8,30}$/;

          if (!phoneRegex.test(phone.value.trim())) {

            phone.classList.add("is-invalid");
            valid = false;

          } else {

            phone.classList.add("is-valid");

          }

        }


        /*
         * SUJET
         */

        if (subject.value.trim().length < 3) {

          subject.classList.add("is-invalid");
          valid = false;

        } else {

          subject.classList.add("is-valid");

        }


        /*
         * MESSAGE
         */

        if (message.value.trim().length < 10) {

          message.classList.add("is-invalid");
          valid = false;

        } else {

          message.classList.add("is-valid");

        }


        /*
         * Si formulaire invalide
         */

        if (!valid) {

          showAlert(
            "danger",
            "Veuillez corriger les champs indiqués avant d'envoyer votre message."
          );

          return;

        }


        /*
         * Etat chargement
         */

        submitButton.disabled = true;

        buttonText.classList.add("d-none");
        buttonLoading.classList.remove("d-none");


        /*
         * Préparation des données
         */

        const formData = new FormData(form);


        try {

          /*
           * Envoi vers le backend PHP
           */

          const response = await fetch(
            form.action,
            {
              method: "POST",
              body: formData,
              headers: {
                "Accept": "application/json"
              }
            }
          );


          const contentType = response.headers.get("content-type") || "";
          if (!contentType.includes("application/json")) {
            throw new Error("Réponse inattendue du serveur de contact.");
          }

          const result = await response.json();


          if (result.success) {

            showAlert(
              "success",
              (
                result.message ||
                "Votre message a été envoyé avec succès. Merci de nous avoir contactés."
              )
            );


            /*
             * Réinitialisation du formulaire
             */

            form.reset();


            [
              name,
              email,
              phone,
              subject,
              message
            ].forEach(function (field) {

              field.classList.remove(
                "is-valid",
                "is-invalid"
              );

            });


          } else {

            showAlert(
              "danger",
              (
                result.message ||
                "Une erreur est survenue lors de l'envoi du message."
              )
            );

          }


        } catch (error) {

          console.error(error);

          showAlert(
            "danger",
            "Impossible d'envoyer le message pour le moment. " +
            "Veuillez réessayer plus tard."
          );


        } finally {

          /*
           * Retour à l'état normal
           */

          submitButton.disabled = false;

          buttonText.classList.remove("d-none");
          buttonLoading.classList.add("d-none");

        }

      });

    });

  </script>

</body>

</html>
