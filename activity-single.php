<?php

require_once __DIR__ . '/config/database.php';

/*
|--------------------------------------------------------------------------
| Fonction de sécurité
|--------------------------------------------------------------------------
*/

function e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}


/*
|--------------------------------------------------------------------------
| Récupération de l'ID de l'activité
|--------------------------------------------------------------------------
*/

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: activities.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Récupération de l'activité depuis MySQL
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        SELECT *
        FROM activities
        WHERE id = ?
        AND status = 'active'
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $activity = $stmt->fetch();

} catch (PDOException $e) {

    error_log($e->getMessage());

    header('Location: activities.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Vérification
|--------------------------------------------------------------------------
*/

if (!$activity) {
    header('Location: activities.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Images
|--------------------------------------------------------------------------
*/

$images = [];

if (!empty($activity['image'])) {
    $images[] = $activity['image'];
}

if (!empty($activity['image_2'])) {
    $images[] = $activity['image_2'];
}

if (empty($images)) {
    $images[] = 'assets/img/orpais.jpeg';
}


/*
|--------------------------------------------------------------------------
| Liste des biens / matériels apportés
|--------------------------------------------------------------------------
*/

$supplies = [];

if (!empty($activity['supplies'])) {

    $supplies = array_filter(
        array_map(
            'trim',
            preg_split(
                '/[,;\n]+/',
                $activity['supplies']
            )
        )
    );
}


/*
|--------------------------------------------------------------------------
| Valeurs statistiques
|--------------------------------------------------------------------------
*/

$beneficiaries = (int)($activity['beneficiaries'] ?? 0);
$men           = (int)($activity['men'] ?? 0);
$women         = (int)($activity['women'] ?? 0);
$children      = (int)($activity['children'] ?? 0);

$budget = $activity['budget'] ?? null;


/*
|--------------------------------------------------------------------------
| Formatage de la date
|--------------------------------------------------------------------------
*/

$formattedDate = '';

if (!empty($activity['event_date'])) {

    $date = DateTime::createFromFormat(
        'Y-m-d',
        $activity['event_date']
    );

    if ($date) {

        $months = [
            1 => 'janvier',
            2 => 'février',
            3 => 'mars',
            4 => 'avril',
            5 => 'mai',
            6 => 'juin',
            7 => 'juillet',
            8 => 'août',
            9 => 'septembre',
            10 => 'octobre',
            11 => 'novembre',
            12 => 'décembre'
        ];

        $formattedDate =
            $date->format('d') .
            ' ' .
            $months[(int)$date->format('m')] .
            ' ' .
            $date->format('Y');
    }
}


/*
|--------------------------------------------------------------------------
| Vidéo
|--------------------------------------------------------------------------
*/

$video = $activity['video'] ?? '';

$isVideoFile = false;

if (!empty($video)) {

    $extension = strtolower(
        pathinfo($video, PATHINFO_EXTENSION)
    );

    $isVideoFile = in_array(
        $extension,
        ['mp4', 'webm', 'ogg'],
        true
    );
}


/*
|--------------------------------------------------------------------------
| Meta
|--------------------------------------------------------------------------
*/

$pageTitle = $activity['title'] . ' | ORPAIS/RDC';

$pageDescription =
    !empty($activity['description'])
        ? $activity['description']
        : 'Découvrez une activité menée par ORPAIS en faveur de la paix, de la cohésion sociale et de l’autonomisation des communautés.';

?>
<!DOCTYPE html>

<html lang="fr">

<head>

  <meta charset="utf-8">

  <meta
    content="width=device-width, initial-scale=1.0"
    name="viewport"
  >

  <title><?= e($pageTitle) ?></title>

  <meta
    name="description"
    content="<?= e($pageDescription) ?>"
  >

  <meta
    name="keywords"
    content="ORPAIS, RDC, activités, paix, cohésion sociale, développement communautaire, humanitaire"
  >


  <!-- =====================================================
       FAVICONS
  ====================================================== -->

  <link
    href="assets/img/logo.jpg"
    rel="icon"
  >

  <link
    href="assets/img/logo.jpg"
    rel="apple-touch-icon"
  >


  <!-- =====================================================
       GOOGLE FONTS
  ====================================================== -->

  <link
    href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700"
    rel="stylesheet"
  >


  <!-- =====================================================
       VENDOR CSS
  ====================================================== -->

  <link
    href="assets/vendor/animate.css/animate.min.css"
    rel="stylesheet"
  >

  <link
    href="assets/vendor/bootstrap/css/bootstrap.min.css"
    rel="stylesheet"
  >

  <link
    href="assets/vendor/bootstrap-icons/bootstrap-icons.css"
    rel="stylesheet"
  >

  <link
    href="assets/vendor/swiper/swiper-bundle.min.css"
    rel="stylesheet"
  >


  <!-- =====================================================
       MAIN CSS
  ====================================================== -->

  <link
    href="assets/css/style.css"
    rel="stylesheet"
  >
  <link href="assets/css/tailwind.css" rel="stylesheet">

</head>


<body>


<!-- =====================================================
     HEADER / NAVBAR
===================================================== -->

<?php require_once __DIR__ . '/includes/header.php'; ?>


<!-- =====================================================
     MAIN
===================================================== -->

<main id="main">


<!-- =====================================================
     INTRO SINGLE
===================================================== -->

<section class="intro-single">

  <div class="container">

    <div class="row">


      <div class="col-md-12 col-lg-8">

        <div class="title-single-box">

          <h1 class="title-single">

            <?= e($activity['title']) ?>

          </h1>


          <?php if (!empty($activity['category_label'])): ?>

            <span class="color-text-a">

              <?= e($activity['category_label']) ?>

            </span>

          <?php endif; ?>

        </div>

      </div>


      <div class="col-md-12 col-lg-4">

        

      </div>


    </div>

  </div>

</section>


<!-- =====================================================
     ACTIVITY SINGLE
===================================================== -->

<section class="property-single nav-arrow-b">

  <div class="container">


    <!-- =================================================
         GALERIE
    ================================================== -->

    <div class="row justify-content-center">

      <div class="col-lg-8">


        <div
          id="property-single-carousel"
          class="swiper"
        >

          <div class="swiper-wrapper">


            <?php foreach ($images as $image): ?>

              <div class="carousel-item-b swiper-slide">

                <img
                  src="<?= e($image) ?>"
                  alt="<?= e($activity['title']) ?>"
                >

              </div>

            <?php endforeach; ?>


          </div>

        </div>


        <div
          class="property-single-carousel-pagination carousel-pagination"
        ></div>


      </div>

    </div>


    <!-- =================================================
         INFORMATIONS PRINCIPALES
    ================================================== -->

    <div class="row">

      <div class="col-sm-12">


        <div class="row justify-content-between">


          <!-- =============================================
               STATISTIQUES
          ============================================== -->

          <div class="col-md-5 col-lg-4">


            <?php if ($budget !== null && $budget !== ''): ?>

              <div class="property-price d-flex justify-content-center foo">

                <div class="card-header-c d-flex">

                  <div class="card-box-ico">

                    <span class="bi bi-cash">$</span>

                  </div>


                  <div class="card-title-c align-self-center">

                    <h5 class="title-c">

                      <?= number_format(
                          (float)$budget,
                          0,
                          '.',
                          ','
                      ) ?>

                    </h5>

                  </div>

                </div>

              </div>

            <?php endif; ?>


            <div class="property-summary">


              <div class="row">

                <div class="col-sm-12">

                  <div class="title-box-d section-t4">

                    <h3 class="title-d">
                      Résumé
                    </h3>

                  </div>

                </div>

              </div>


              <div class="summary-list">

                <ul class="list">


                  <?php if (!empty($formattedDate)): ?>

                    <li class="d-flex justify-content-between">

                      <strong>Date :</strong>

                      <span>
                        <?= e($formattedDate) ?>
                      </span>

                    </li>

                  <?php endif; ?>


                  <?php if (!empty($activity['location'])): ?>

                    <li class="d-flex justify-content-between">

                      <strong>Lieu :</strong>

                      <span>
                        <?= e($activity['location']) ?>
                      </span>

                    </li>

                  <?php endif; ?>


                  <?php if (!empty($activity['category_label'])): ?>

                    <li class="d-flex justify-content-between">

                      <strong>Domaine :</strong>

                      <span>
                        <?= e($activity['category_label']) ?>
                      </span>

                    </li>

                  <?php endif; ?>


                  <?php if ($beneficiaries > 0): ?>

                    <li class="d-flex justify-content-between">

                      <strong>Bénéficiaires :</strong>

                      <span>

                        <?= number_format(
                            $beneficiaries,
                            0,
                            ' ',
                            ' '
                        ) ?>

                        personnes

                      </span>

                    </li>

                  <?php endif; ?>


                  <?php if ($men > 0): ?>

                    <li class="d-flex justify-content-between">

                      <strong>Hommes :</strong>

                      <span>
                        <?= number_format($men, 0, ' ', ' ') ?>
                      </span>

                    </li>

                  <?php endif; ?>


                  <?php if ($women > 0): ?>

                    <li class="d-flex justify-content-between">

                      <strong>Femmes :</strong>

                      <span>
                        <?= number_format($women, 0, ' ', ' ') ?>
                      </span>

                    </li>

                  <?php endif; ?>


                  <?php if ($children > 0): ?>

                    <li class="d-flex justify-content-between">

                      <strong>Enfants :</strong>

                      <span>
                        <?= number_format($children, 0, ' ', ' ') ?>
                      </span>

                    </li>

                  <?php endif; ?>


                </ul>

              </div>

            </div>

          </div>


          <!-- =============================================
               DESCRIPTION
          ============================================== -->

          <div class="col-md-7 col-lg-7 section-md-t3">


            <div class="row">

              <div class="col-sm-12">

                <div class="title-box-d">

                  <h3 class="title-d">
                    Description de l'activité
                  </h3>

                </div>

              </div>

            </div>


            <div class="property-description">


              <?php if (!empty($activity['detailed_description'])): ?>

                <?php

                $paragraphs = preg_split(
                    "/\r\n|\n|\r/",
                    $activity['detailed_description']
                );

                ?>

                <?php foreach ($paragraphs as $paragraph): ?>

                  <?php if (trim($paragraph) !== ''): ?>

                    <p class="description color-text-a">

                      <?= nl2br(e(trim($paragraph))) ?>

                    </p>

                  <?php endif; ?>

                <?php endforeach; ?>


              <?php elseif (!empty($activity['description'])): ?>

                <p class="description color-text-a no-margin">

                  <?= nl2br(e($activity['description'])) ?>

                </p>


              <?php else: ?>

                <p class="description color-text-a no-margin">

                  Aucune description détaillée disponible
                  pour cette activité.

                </p>

              <?php endif; ?>


            </div>


            <!-- ===========================================
                 BIENS / MATÉRIELS
            ============================================ -->

            <?php if (!empty($supplies)): ?>

              <div class="row section-t3">

                <div class="col-sm-12">

                  <div class="title-box-d">

                    <h3 class="title-d">
                      Biens et matériels apportés
                    </h3>

                  </div>

                </div>

              </div>


              <div class="amenities-list color-text-a">

                <ul class="list-a no-margin">


                  <?php foreach ($supplies as $supply): ?>

                    <li>
                      <?= e($supply) ?>
                    </li>

                  <?php endforeach; ?>


                </ul>

              </div>

            <?php endif; ?>


          </div>

        </div>

      </div>


      <!-- =================================================
           MEDIA
      ================================================== -->

      <div class="col-md-10 offset-md-1">


        <ul
          class="nav nav-pills-a nav-pills mb-3 section-t3"
          id="pills-tab"
          role="tablist"
        >


          <?php if (!empty($video)): ?>

            <li class="nav-item">

              <a
                class="nav-link active"
                id="pills-video-tab"
                data-bs-toggle="pill"
                href="#pills-video"
                role="tab"
                aria-controls="pills-video"
                aria-selected="true"
              >
                Vidéo
              </a>

            </li>

          <?php endif; ?>


          <?php if (!empty($activity['plan_image'])): ?>

            <li class="nav-item">

              <a
                class="nav-link <?= empty($video) ? 'active' : '' ?>"
                id="pills-plans-tab"
                data-bs-toggle="pill"
                href="#pills-plans"
                role="tab"
                aria-controls="pills-plans"
                aria-selected="<?= empty($video) ? 'true' : 'false' ?>"
              >
                Documents
              </a>

            </li>

          <?php endif; ?>


          <?php if (!empty($activity['map_url'])): ?>

            <li class="nav-item">

              <a
                class="nav-link <?= empty($video) && empty($activity['plan_image']) ? 'active' : '' ?>"
                id="pills-map-tab"
                data-bs-toggle="pill"
                href="#pills-map"
                role="tab"
                aria-controls="pills-map"
                aria-selected="<?= empty($video) && empty($activity['plan_image']) ? 'true' : 'false' ?>"
              >
                Localisation
              </a>

            </li>

          <?php endif; ?>


        </ul>


        <div
          class="tab-content"
          id="pills-tabContent"
        >


          <!-- =============================================
               VIDÉO
          ============================================== -->

          <?php if (!empty($video)): ?>

            <div
              class="tab-pane fade show active"
              id="pills-video"
              role="tabpanel"
              aria-labelledby="pills-video-tab"
            >


              <?php if ($isVideoFile): ?>

                <video
                  width="100%"
                  height="460"
                  controls
                  playsinline
                >

                  <source
                    src="<?= e($video) ?>"
                    type="video/<?= e($extension) ?>"
                  >

                  Votre navigateur ne supporte pas
                  la lecture vidéo.

                </video>


              <?php else: ?>

                <iframe
                  src="<?= e($video) ?>"
                  width="100%"
                  height="460"
                  frameborder="0"
                  allowfullscreen
                ></iframe>

              <?php endif; ?>


            </div>

          <?php endif; ?>


          <!-- =============================================
               DOCUMENT / PLAN
          ============================================== -->

          <?php if (!empty($activity['plan_image'])): ?>

            <div
              class="tab-pane fade <?= empty($video) ? 'show active' : '' ?>"
              id="pills-plans"
              role="tabpanel"
              aria-labelledby="pills-plans-tab"
            >

              <img
                src="<?= e($activity['plan_image']) ?>"
                alt="Document de l'activité"
                class="img-fluid"
              >

            </div>

          <?php endif; ?>


          <!-- =============================================
               CARTE
          ============================================== -->

          <?php if (!empty($activity['map_url'])): ?>

            <div
              class="tab-pane fade <?= empty($video) && empty($activity['plan_image']) ? 'show active' : '' ?>"
              id="pills-map"
              role="tabpanel"
              aria-labelledby="pills-map-tab"
            >

              <iframe
                src="<?= e($activity['map_url']) ?>"
                width="100%"
                height="460"
                frameborder="0"
                style="border:0"
                allowfullscreen
                loading="lazy"
              ></iframe>

            </div>

          <?php endif; ?>


          <?php if (
              empty($video) &&
              empty($activity['plan_image']) &&
              empty($activity['map_url'])
          ): ?>

            <div class="text-center py-5">

              <p class="color-text-a">
                Aucun contenu multimédia disponible
                pour cette activité.
              </p>

            </div>

          <?php endif; ?>


        </div>

      </div>


    </div>

  </div>

</section>


</main>


<!-- =====================================================
     FOOTER
===================================================== -->

<section class="section-footer">

  <div class="container">

    <div class="row">


      <!-- ===============================================
           ORPAIS
      ================================================ -->

      <div class="col-sm-12 col-md-4">

        <div class="widget-a">


          <div class="w-header-a">

            <h3 class="w-title-a text-brand">
              ORPAIS/RDC
            </h3>

          </div>


          <div class="w-body-a">

            <p class="w-text-a color-text-a">

              L’Organisation pour la Réconciliation,
              la Paix, l’Autonomisation et la Cohésion
              Sociale œuvre pour des communautés plus
              pacifiques, inclusives et autonomes.

            </p>

          </div>


          <div class="w-footer-a">

            <ul class="list-unstyled">


              <li class="color-a">

                <span class="color-text-a">
                  Phone :
                </span>

                Contactez-nous

              </li>


              <li class="color-a">

                <span class="color-text-a">
                  Email :
                </span>

                Contactez-nous

              </li>


            </ul>

          </div>


        </div>

      </div>


      <!-- ===============================================
           DOMAINES
      ================================================ -->

      <div class="col-sm-12 col-md-4 section-md-t3">

        <div class="widget-a">


          <div class="w-header-a">

            <h3 class="w-title-a text-brand">
              DOMAINES
            </h3>

          </div>


          <div class="w-body-a">

            <ul class="list-unstyled">


              <li class="item-list-a">

                <i class="bi bi-chevron-right"></i>

                <a href="activities.php">
                  Protection sociale
                </a>

              </li>


              <li class="item-list-a">

                <i class="bi bi-chevron-right"></i>

                <a href="activities.php">
                  Autonomisation économique
                </a>

              </li>


              <li class="item-list-a">

                <i class="bi bi-chevron-right"></i>

                <a href="activities.php">
                  Paix et cohésion sociale
                </a>

              </li>


              <li class="item-list-a">

                <i class="bi bi-chevron-right"></i>

                <a href="activities.php">
                  Éducation et formation
                </a>

              </li>


              <li class="item-list-a">

                <i class="bi bi-chevron-right"></i>

                <a href="activities.php">
                  Santé et bien-être
                </a>

              </li>


              <li class="item-list-a">

                <i class="bi bi-chevron-right"></i>

                <a href="activities.php">
                  Développement communautaire
                </a>

              </li>


            </ul>

          </div>


        </div>

      </div>


      <!-- ===============================================
           MENU
      ================================================ -->

      <div class="col-sm-12 col-md-4 section-md-t3">

        <div class="widget-a">


          <div class="w-header-a">

            <h3 class="w-title-a text-brand">
              MENU
            </h3>

          </div>


          <div class="w-body-a">

            <ul class="list-unstyled">


              <li class="item-list-a">

                <i class="bi bi-chevron-right"></i>

                <a href="index.php">
                  Accueil
                </a>

              </li>


              <li class="item-list-a">

                <i class="bi bi-chevron-right"></i>

                <a href="about.html">
                  Qui sommes-nous
                </a>

              </li>


              <li class="item-list-a">

                <i class="bi bi-chevron-right"></i>

                <a href="activities.php">
                  Nos activités
                </a>

              </li>


              <li class="item-list-a">

                <i class="bi bi-chevron-right"></i>

                <a href="agents-grid.php">
                  Notre équipe
                </a>

              </li>


              <li class="item-list-a">

                <i class="bi bi-chevron-right"></i>

                <a href="contact.html">
                  Contact
                </a>

              </li>


            </ul>

          </div>


        </div>

      </div>


    </div>

  </div>

</section>


<!-- =====================================================
     FOOTER COPYRIGHT
===================================================== -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>


<!-- =====================================================
     PRELOADER
===================================================== -->

<div id="preloader"></div>


<!-- =====================================================
     BACK TO TOP
===================================================== -->

<a
  href="#"
  class="back-to-top d-flex align-items-center justify-content-center"
>

  <i class="bi bi-arrow-up-short"></i>

</a>


<!-- =====================================================
     JAVASCRIPT
===================================================== -->

<script
  src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"
></script>


<script
  src="assets/vendor/swiper/swiper-bundle.min.js"
></script>


<script
  src="assets/vendor/php-email-form/validate.js"
></script>


<script
  src="assets/js/main.js"
></script>


<!-- =====================================================
     SWIPER ACTIVITÉ
===================================================== -->

<script>

document.addEventListener(
  "DOMContentLoaded",
  function () {

    const carousel =
      document.querySelector(
        "#property-single-carousel"
      );

    if (carousel) {

      new Swiper(
        "#property-single-carousel",
        {

          loop: true,

          autoplay: {
            delay: 5000,
            disableOnInteraction: false
          },

          navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev"
          },

          pagination: {
            el:
              ".property-single-carousel-pagination",
            clickable: true
          }

        }
      );

    }

  }
);

</script>


</body>

</html>





