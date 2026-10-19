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
| Récupération des activités depuis MySQL
|--------------------------------------------------------------------------
*/

$activities = [];

try {

    $stmt = $pdo->prepare("
        SELECT
            id,
            title,
            slug,
            category,
            category_label,
            description,
            icon,
            image
        FROM activities
        WHERE status = 'active'
        ORDER BY display_order ASC, id ASC
    ");

    $stmt->execute();

    $activities = $stmt->fetchAll();

} catch (PDOException $e) {

    error_log($e->getMessage());

}

?>
<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="utf-8">

    <meta
        content="width=device-width, initial-scale=1.0"
        name="viewport"
    >

    <title>ORPAIS | Nos activités — ancienne présentation</title>

    <meta
        name="description"
        content="Découvrez les activités et domaines d’intervention de l’ORPAIS en faveur de la paix, de la cohésion sociale et de l’autonomisation des communautés."
    >

    <meta
        name="keywords"
        content="ORPAIS, activités, paix, cohésion sociale, RDC, Goma, développement communautaire"
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
        href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800"
        rel="stylesheet"
    >


    <!-- =====================================================
         BOOTSTRAP
    ====================================================== -->

    <link
        href="assets/vendor/bootstrap/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- =====================================================
         BOOTSTRAP ICONS
    ====================================================== -->

    <link
        href="assets/vendor/bootstrap-icons/bootstrap-icons.css"
        rel="stylesheet"
    >


    <!-- =====================================================
         ANIMATE
    ====================================================== -->

    <link
        href="assets/vendor/animate.css/animate.min.css"
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


    <!-- =====================================================
         STYLE ORPAIS
    ====================================================== -->

    <style>

        :root {

            --orpaics-dark: #073b66;
            --orpaics-blue: #0b5ed7;
            --orpaics-light: #eaf3ff;
            --orpaics-soft: #f5f9ff;
            --orpaics-text: #263b50;
            --orpaics-white: #ffffff;

        }


        body {

            font-family: "Poppins", sans-serif;
            color: var(--orpaics-text);
            background: #fff;

        }


        a {

            text-decoration: none;

        }


        /* =========================
           NAVBAR
        ========================= */

        .navbar {

            background: rgba(255,255,255,0.98);
            box-shadow: 0 3px 18px rgba(7,59,102,0.08);
            padding: 12px 0;

        }


        .navbar-brand {

            display: flex;
            align-items: center;
            gap: 10px;

            color: var(--orpaics-dark) !important;

            font-size: 23px;
            font-weight: 800;

        }


        .navbar-brand img {

            width: 48px;
            height: 48px;

            object-fit: contain;

            border-radius: 8px;

        }


        .navbar-brand span {

            color: var(--orpaics-blue);

        }


        .navbar-nav .nav-link {

            color: var(--orpaics-dark) !important;

            font-size: 14px;
            font-weight: 600;

            margin: 0 8px;

            position: relative;

        }


        .navbar-nav .nav-link::after {

            content: "";

            position: absolute;

            bottom: 0;
            left: 0;

            width: 0;
            height: 3px;

            border-radius: 10px;

            background: var(--orpaics-blue);

            transition: 0.3s;

        }


        .navbar-nav .nav-link:hover::after,
        .navbar-nav .nav-link.active::after {

            width: 100%;

        }


        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {

            color: var(--orpaics-blue) !important;

        }


        /* =========================
           PAGE HEADER
        ========================= */

        .page-header {

            margin-top: 72px;

            padding: 85px 0;

            position: relative;

            background:

                linear-gradient(
                    100deg,
                    rgba(7,59,102,0.96),
                    rgba(11,94,215,0.82)
                ),

                url("assets/img/orpais.jpeg")
                center/cover no-repeat;

            color: white;

        }


        .page-header-content {

            max-width: 800px;

        }


        .page-header h1 {

            font-size: 44px;

            font-weight: 800;

            margin-bottom: 18px;

        }


        .page-header p {

            max-width: 700px;

            font-size: 16px;

            line-height: 1.9;

            color: rgba(255,255,255,0.88);

            margin-bottom: 0;

        }


        .breadcrumb-custom {

            margin-top: 25px;

        }


        .breadcrumb-custom a {

            color: #bcdcff;

        }


        .breadcrumb-custom span {

            margin: 0 8px;

            color: rgba(255,255,255,0.5);

        }


        /* =========================
           INTRODUCTION
        ========================= */

        .activities-intro {

            padding: 80px 0 50px;

            background: var(--orpaics-soft);

        }


        .section-title {

            margin-bottom: 45px;

        }


        .section-title h2 {

            color: var(--orpaics-dark);

            font-size: 32px;

            font-weight: 700;

            margin-bottom: 15px;

        }


        .section-title p {

            max-width: 720px;

            margin: auto;

            color: #6b7e90;

            line-height: 1.8;

            font-size: 14px;

        }


        .section-line {

            width: 65px;

            height: 4px;

            margin: 18px auto;

            border-radius: 20px;

            background: var(--orpaics-blue);

        }


        /* =========================
           FILTER
        ========================= */

        .activity-filter {

            margin-bottom: 45px;

            text-align: center;

        }


        .filter-btn {

            display: inline-block;

            margin: 5px;

            padding: 9px 20px;

            border: 1px solid #d8e7f7;

            border-radius: 30px;

            background: white;

            color: var(--orpaics-dark);

            font-size: 13px;

            font-weight: 600;

            transition: all 0.3s ease;

        }


        .filter-btn:hover,
        .filter-btn.active {

            background: var(--orpaics-blue);

            border-color: var(--orpaics-blue);

            color: white;

        }


        /* =========================
           ACTIVITY CARD
        ========================= */

        .activity-card {

            height: 100%;

            overflow: hidden;

            background: white;

            border: 1px solid #e1ecf7;

            border-radius: 17px;

            box-shadow: 0 8px 28px rgba(7,59,102,0.06);

            transition: all 0.35s ease;

        }


        .activity-card:hover {

            transform: translateY(-8px);

            border-color: #b8d5f5;

            box-shadow:
                0 18px 40px rgba(11,94,215,0.14);

        }


        .activity-image {

            position: relative;

            overflow: hidden;

        }


        .activity-image img {

            width: 100%;

            height: 240px;

            object-fit: cover;

            transition: transform 0.5s ease;

        }


        .activity-card:hover .activity-image img {

            transform: scale(1.05);

        }


        .activity-category {

            position: absolute;

            top: 18px;

            left: 18px;

            padding: 7px 13px;

            border-radius: 20px;

            background: var(--orpaics-blue);

            color: white;

            font-size: 11px;

            font-weight: 600;

        }


        .activity-content {

            padding: 27px;

        }


        .activity-icon {

            display: flex;

            align-items: center;

            justify-content: center;

            width: 54px;

            height: 54px;

            margin-bottom: 17px;

            border-radius: 14px;

            background: var(--orpaics-light);

            color: var(--orpaics-blue);

            font-size: 25px;

        }


        .activity-content h3 {

            color: var(--orpaics-dark);

            font-size: 19px;

            font-weight: 700;

            line-height: 1.5;

            margin-bottom: 13px;

        }


        .activity-content p {

            color: #6b7e90;

            font-size: 13px;

            line-height: 1.85;

            margin-bottom: 22px;

        }


        .activity-link {

            display: inline-flex;

            align-items: center;

            gap: 8px;

            color: var(--orpaics-blue);

            font-size: 13px;

            font-weight: 700;

        }


        .activity-link:hover {

            color: var(--orpaics-dark);

        }


        /* =========================
           IMPACT
        ========================= */

        .impact-section {

            padding: 85px 0;

            background: var(--orpaics-light);

        }


        .impact-card {

            height: 100%;

            padding: 30px;

            background: white;

            border-radius: 16px;

            border: 1px solid #dceafa;

        }


        .impact-icon {

            font-size: 34px;

            color: var(--orpaics-blue);

            margin-bottom: 15px;

        }


        .impact-card h4 {

            color: var(--orpaics-dark);

            font-size: 18px;

            font-weight: 700;

            margin-bottom: 10px;

        }


        .impact-card p {

            color: #6b7e90;

            font-size: 13px;

            line-height: 1.8;

            margin: 0;

        }


        /* =========================
           CTA
        ========================= */

        .cta-section {

            padding: 75px 0;

            background:

                linear-gradient(
                    100deg,
                    rgba(7,59,102,0.97),
                    rgba(11,94,215,0.9)
                );

            color: white;

            text-align: center;

        }


        .cta-section h2 {

            font-size: 32px;

            font-weight: 700;

            margin-bottom: 15px;

        }


        .cta-section p {

            max-width: 700px;

            margin: 0 auto 28px;

            color: rgba(255,255,255,0.87);

            line-height: 1.8;

            font-size: 14px;

        }


        .btn-orpaics {

            display: inline-flex;

            align-items: center;

            gap: 8px;

            padding: 13px 25px;

            border-radius: 30px;

            background: white;

            color: var(--orpaics-blue);

            font-size: 14px;

            font-weight: 700;

            transition: 0.3s;

        }


        .btn-orpaics:hover {

            background: var(--orpaics-light);

            color: var(--orpaics-dark);

        }


        /* =========================
           FOOTER
        ========================= */

        .footer {

            padding: 65px 0 20px;

            background: #052d4e;

            color: rgba(255,255,255,0.7);

        }


        .footer-logo {

            display: flex;

            align-items: center;

            gap: 10px;

            color: white;

            font-size: 24px;

            font-weight: 800;

            margin-bottom: 18px;

        }


        .footer-logo img {

            width: 48px;

            height: 48px;

            object-fit: contain;

            border-radius: 8px;

        }


        .footer-logo span {

            color: #a9d2ff;

        }


        .footer h4 {

            color: white;

            font-size: 17px;

            font-weight: 700;

            margin-bottom: 20px;

        }


        .footer p,
        .footer li,
        .footer a {

            color: rgba(255,255,255,0.7);

            font-size: 13px;

            line-height: 1.9;

        }


        .footer ul {

            list-style: none;

            padding: 0;

        }


        .footer li {

            margin-bottom: 7px;

        }


        .footer a:hover {

            color: #a9d2ff;

        }


        .footer-bottom {

            margin-top: 40px;

            padding-top: 20px;

            border-top: 1px solid rgba(255,255,255,0.15);

            text-align: center;

            font-size: 12px;

        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 991px) {

            .page-header h1 {

                font-size: 36px;

            }

        }


        @media (max-width: 767px) {

            .page-header {

                padding: 70px 0;

            }

            .page-header h1 {

                font-size: 30px;

            }

            .section-title h2 {

                font-size: 27px;

            }

            .activity-image img {

                height: 220px;

            }

        }

        :root {
            --orpaics-dark: #164e68;
            --orpaics-blue: #4da9c2;
            --orpaics-light: #eaf5f6;
            --orpaics-soft: #f7fbfb;
            --orpaics-text: #163b4d;
            --orpaics-orange: #d9865f;
        }

        .page-header {
            background:
                radial-gradient(circle at 88% 18%, rgba(255,255,255,.12) 0 1px, transparent 1px 18px),
                linear-gradient(120deg, #164e68, #4da9c2);
            isolation: isolate;
            overflow: hidden;
            padding: 92px 0 82px;
        }

        .page-header::after {
            border: 1px solid rgba(255,255,255,.16);
            border-radius: 50%;
            box-shadow: 0 0 0 55px rgba(255,255,255,.035), 0 0 0 115px rgba(255,255,255,.025);
            content: "";
            height: 390px;
            position: absolute;
            right: -75px;
            top: -100px;
            width: 390px;
            z-index: -1;
        }

        .page-header-content {
            max-width: 760px;
        }

        .page-header .header-eyebrow {
            align-items: center;
            color: #f2b093;
            display: flex;
            font-size: 10px;
            font-weight: 600;
            gap: 10px;
            letter-spacing: .15em;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .page-header .header-eyebrow::before {
            background: var(--orpaics-orange);
            border-radius: 50%;
            content: "";
            height: 7px;
            width: 7px;
        }

        .page-header h1 {
            font: 400 clamp(42px, 6vw, 68px)/1.06 Georgia, serif;
            letter-spacing: -.035em;
            margin-bottom: 20px;
        }

        .page-header p {
            color: #e5f4f5;
            font-size: 14px;
            max-width: 620px;
        }

        .page-header .breadcrumb-custom {
            color: rgba(255,255,255,.72);
            font-size: 11px;
        }

        .page-header .breadcrumb-custom a {
            color: #f2b093;
        }

        .activities-intro {
            background: var(--orpaics-soft);
            padding: 82px 0 75px;
        }

        .section-title h2 {
            color: var(--orpaics-dark);
            font: 400 clamp(30px, 4vw, 44px)/1.12 Georgia, serif;
        }

        .section-line {
            background: var(--orpaics-orange);
            height: 3px;
            width: 52px;
        }

        .section-title p {
            color: #6d8994;
        }

        .activity-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
            justify-content: center;
            margin-bottom: 38px;
        }

        .filter-btn {
            margin: 0;
            padding: 10px 17px;
            border-color: #d4e6e9;
            color: #607582;
            cursor: pointer;
            font-size: 11px;
            font-weight: 500;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: var(--orpaics-dark);
            border-color: var(--orpaics-dark);
            color: #fff;
        }

        .activity-card {
            border-color: #dcecef;
            border-radius: 2px;
            box-shadow: 0 12px 28px rgba(35,94,109,.06);
        }

        .activity-card:hover {
            border-color: #afd2da;
            box-shadow: 0 18px 32px rgba(35,94,109,.13);
            transform: translateY(-5px);
        }

        .activity-image {
            background: #dff3f5;
        }

        .activity-image::after {
            background: linear-gradient(180deg, transparent 42%, rgba(12,42,54,.42));
            content: "";
            inset: 0;
            pointer-events: none;
            position: absolute;
        }

        .activity-image img {
            height: 220px;
        }

        .activity-category {
            background: #fff;
            border-radius: 0;
            bottom: 14px;
            color: var(--orpaics-dark);
            font-size: 9px;
            left: 16px;
            padding: 8px 11px;
            top: auto;
        }

        .activity-content {
            padding: 0 24px 24px;
        }

        .activity-icon {
            background: #e4f3f5;
            border: 4px solid #fff;
            border-radius: 50%;
            color: var(--orpaics-dark);
            height: 52px;
            margin-bottom: 17px;
            margin-top: -27px;
            position: relative;
            width: 52px;
            z-index: 1;
        }

        .activity-content h3 {
            color: var(--orpaics-dark);
            font: 400 23px/1.2 Georgia, serif;
        }

        .activity-content p {
            color: #6d8994;
            font-size: 11px;
            min-height: 60px;
        }

        .activity-link {
            border-top: 1px solid #e8f0f1;
            color: var(--orpaics-dark);
            display: flex;
            font-size: 10px;
            justify-content: space-between;
            margin-top: 17px;
            padding-top: 15px;
        }

        .activity-link i {
            color: var(--orpaics-orange);
        }

        .impact-section {
            background: #eaf5f6;
        }

        .impact-card {
            border: 1px solid #dcecef;
            border-radius: 2px;
            box-shadow: 0 10px 26px rgba(35,94,109,.05);
        }

        .impact-icon,
        .impact-card h4 {
            color: var(--orpaics-dark);
        }

        .cta-section {
            background: linear-gradient(120deg, #164e68, #286f88);
        }

        .cta-section h2 {
            font: 400 clamp(30px, 4vw, 44px)/1.12 Georgia, serif;
        }

        .btn-orpaics {
            background: var(--orpaics-orange);
            border-radius: 0;
            color: #fff;
            font-size: 11px;
            font-weight: 500;
        }

        .btn-orpaics:hover {
            background: #c57450;
            color: #fff;
        }

        @media (max-width: 767px) {
            .page-header {
                padding: 72px 0 65px;
            }

            .activity-filter {
                gap: 8px;
            }

            .activity-content p {
                min-height: 0;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .activity-card,
            .activity-image img {
                transition: none !important;
            }
        }

    </style>

</head>


<body>


<!-- =====================================================
     NAVIGATION
===================================================== -->

<?php require_once __DIR__ . '/includes/header.php'; ?>



<!-- =====================================================
     PAGE HEADER
===================================================== -->

<section class="page-header">

    <div class="container">

        <div class="page-header-content">

            <div class="header-eyebrow">
                Agir ensemble, transformer durablement
            </div>

            <h1>
                Nos actions au service des communautés
            </h1>


            <p>

                Découvrez les principaux domaines d’intervention
                d’ORPAIS et les initiatives mises en œuvre pour
                renforcer la paix, la cohésion sociale et
                l’autonomisation des communautés.

            </p>


            <div class="breadcrumb-custom">

                <a href="index.php">
                    Accueil
                </a>

                <span>/</span>

                <strong>
                    Nos activités
                </strong>

            </div>

        </div>

    </div>

</section>



<!-- =====================================================
     INTRODUCTION
===================================================== -->

<section class="activities-intro">

    <div class="container">


        <div class="section-title text-center">

            <h2>
                Nos domaines d’intervention
            </h2>


            <div class="section-line"></div>


            <p>

                ORPAIS développe des actions communautaires dans
                plusieurs secteurs afin de répondre aux besoins des
                populations et contribuer à un développement inclusif
                et durable.

            </p>

        </div>



        <!-- =================================================
             FILTRE
        ================================================== -->

        <div class="activity-filter">


            <button
                class="filter-btn active"
                data-filter="all"
                type="button"
            >
                Toutes
            </button>


            <button
                class="filter-btn"
                data-filter="paix"
                type="button"
            >
                Paix & cohésion
            </button>


            <button
                class="filter-btn"
                data-filter="social"
                type="button"
            >
                Social
            </button>


            <button
                class="filter-btn"
                data-filter="developpement"
                type="button"
            >
                Développement
            </button>


            <button
                class="filter-btn"
                data-filter="humanitaire"
                type="button"
            >
                Humanitaire
            </button>


        </div>



        <!-- =================================================
             ACTIVITIES
        ================================================== -->

        <div
            class="row g-4"
            id="activitiesContainer"
        >


            <?php if (!empty($activities)): ?>


                <?php foreach ($activities as $activity): ?>


                    <div
                        class="col-md-6 col-lg-4 activity-item"
                        data-category="<?= e($activity['category']) ?>"
                    >


                        <div class="activity-card">


                            <!-- IMAGE -->

                            <div class="activity-image">


                                <img
                                    src="<?= e(
                                        !empty($activity['image'])
                                            ? $activity['image']
                                            : 'assets/img/orpais.jpeg'
                                    ) ?>"
                                    alt="<?= e($activity['title']) ?>"
                                >


                                <?php if (!empty($activity['category_label'])): ?>

                                    <span class="activity-category">

                                        <?= e(
                                            $activity['category_label']
                                        ) ?>

                                    </span>

                                <?php endif; ?>


                            </div>



                            <!-- CONTENU -->

                            <div class="activity-content">


                                <!-- ICÔNE -->

                                <div class="activity-icon">

                                    <i
                                        class="<?= e(
                                            !empty($activity['icon'])
                                                ? $activity['icon']
                                                : 'bi bi-activity'
                                        ) ?>"
                                    ></i>

                                </div>



                                <!-- TITRE -->

                                <h3>

                                    <?= e(
                                        $activity['title']
                                    ) ?>

                                </h3>



                                <!-- DESCRIPTION -->

                                <p>

                                    <?= e(
                                        $activity['description']
                                    ) ?>

                                </p>



                                <!-- LIEN D’AIL -->

                                <a
                                    href="activity-single.php?id=<?= (int)$activity['id'] ?>"
                                    class="activity-link"
                                >

                                    Voir l’activité

                                    <i
                                        class="bi bi-arrow-right"
                                    ></i>

                                </a>


                            </div>


                        </div>


                    </div>


                <?php endforeach; ?>


            <?php else: ?>


                <!-- =================================================
                     AUCUNE ACTIVITÉ
                ================================================== -->

                <div class="col-12">

                    <div
                        class="text-center py-5"
                    >

                        <i
                            class="bi bi-info-circle"
                            style="
                                font-size:45px;
                                color:#0b5ed7;
                            "
                        ></i>


                        <h3 class="mt-3">

                            Aucune activité disponible

                        </h3>


                        <p>

                            Les activités seront bientôt disponibles.

                        </p>


                    </div>

                </div>


            <?php endif; ?>


        </div>

    </div>

</section>



<!-- =====================================================
     IMPACT / APPROCHE
===================================================== -->

<section class="impact-section">

    <div class="container">


        <div class="section-title text-center">

            <h2>
                Notre approche
            </h2>


            <div class="section-line"></div>


            <p>

                ORPAIS place les communautés au centre de ses
                interventions.

            </p>

        </div>



        <div class="row g-4">


            <!-- PARTICIPATION -->

            <div class="col-md-6 col-lg-3">

                <div class="impact-card">


                    <div class="impact-icon">

                        <i class="bi bi-people"></i>

                    </div>


                    <h4>
                        Participation
                    </h4>


                    <p>

                        Impliquer les communautés dans l’identification
                        des besoins et la recherche de solutions.

                    </p>


                </div>

            </div>



            <!-- DIALOGUE -->

            <div class="col-md-6 col-lg-3">

                <div class="impact-card">


                    <div class="impact-icon">

                        <i class="bi bi-chat-heart"></i>

                    </div>


                    <h4>
                        Dialogue
                    </h4>


                    <p>

                        Favoriser le dialogue, l’écoute et la compréhension
                        entre les différents acteurs communautaires.

                    </p>


                </div>

            </div>



            <!-- INNOVATION -->

            <div class="col-md-6 col-lg-3">

                <div class="impact-card">


                    <div class="impact-icon">

                        <i class="bi bi-lightbulb"></i>

                    </div>


                    <h4>
                        Innovation
                    </h4>


                    <p>

                        Encourager les initiatives locales et les solutions
                        adaptées aux réalités des communautés.

                    </p>


                </div>

            </div>



            <!-- AUTONOMISATION -->

            <div class="col-md-6 col-lg-3">

                <div class="impact-card">


                    <div class="impact-icon">

                        <i class="bi bi-arrow-up-right-circle"></i>

                    </div>


                    <h4>
                        Autonomisation
                    </h4>


                    <p>

                        Renforcer les capacités des populations afin
                        qu’elles deviennent actrices de leur propre
                        développement.

                    </p>


                </div>

            </div>


        </div>

    </div>

</section>



<!-- =====================================================
     CTA
===================================================== -->

<section class="cta-section">

    <div class="container">


        <h2>

            Vous souhaitez collaborer avec ORPAIS ?

        </h2>


        <p>

            Partenaires, organisations, institutions et acteurs
            communautaires : construisons ensemble des initiatives
            porteuses de paix, de cohésion sociale et de développement.

        </p>


        <a
            href="contact.php"
            class="btn-orpaics"
        >

            Nous contacter

            <i class="bi bi-arrow-right"></i>

        </a>


    </div>

</section>



<!-- =====================================================
     FOOTER
===================================================== -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>



<!-- =====================================================
     JAVASCRIPT BOOTSTRAP
===================================================== -->

<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>



<!-- =====================================================
     FILTRE DES ACTIVITÉS
===================================================== -->

<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {


        const filterButtons =
            document.querySelectorAll(".filter-btn");


        const activityItems =
            document.querySelectorAll(".activity-item");


        filterButtons.forEach(
            function (button) {


                button.addEventListener(
                    "click",
                    function () {


                        const filter =
                            this.getAttribute(
                                "data-filter"
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Bouton actif
                        |--------------------------------------------------------------------------
                        */

                        filterButtons.forEach(
                            function (btn) {

                                btn.classList.remove(
                                    "active"
                                );

                            }
                        );


                        this.classList.add(
                            "active"
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | Filtrage
                        |--------------------------------------------------------------------------
                        */

                        activityItems.forEach(
                            function (item) {


                                const category =
                                    item.getAttribute(
                                        "data-category"
                                    );


                                if (
                                    filter === "all" ||
                                    category === filter
                                ) {

                                    item.style.display = "";

                                } else {

                                    item.style.display = "none";

                                }

                            }
                        );


                    }
                );


            }
        );


    }
);

</script>


</body>

</html>
