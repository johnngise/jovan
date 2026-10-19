<?php

require_once __DIR__ . '/config/database.php';


/*
|--------------------------------------------------------------------------
| Fonction de sécurité
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


/*
|--------------------------------------------------------------------------
| Gestion des photos de l'équipe
|--------------------------------------------------------------------------
*/

function teamPhotoUrl($photo)
{
    $photo = trim((string) $photo);

    /*
    |--------------------------------------------------------------
    | Aucune photo
    |--------------------------------------------------------------
    */
    if ($photo === '') {
        return 'assets/img/team-default.jpg';
    }


    /*
    |--------------------------------------------------------------
    | URL externe
    |--------------------------------------------------------------
    */
    if (
        filter_var($photo, FILTER_VALIDATE_URL) ||
        str_starts_with($photo, 'http://') ||
        str_starts_with($photo, 'https://')
    ) {
        return $photo;
    }


    /*
    |--------------------------------------------------------------
    | Chemin absolu
    |--------------------------------------------------------------
    */
    if (str_starts_with($photo, '/')) {
        return $photo;
    }


    /*
    |--------------------------------------------------------------
    | Si le chemin contient déjà assets/
    |--------------------------------------------------------------
    */
    if (str_starts_with($photo, 'assets/')) {
        return $photo;
    }


    /*
    |--------------------------------------------------------------
    | Photos envoyées depuis le Dashboard
    |--------------------------------------------------------------
    */
    return 'uploads/team/' . ltrim($photo, '/');
}


/*
|--------------------------------------------------------------------------
| Gestion des photos des témoignages
|--------------------------------------------------------------------------
*/

function testimonialPhotoUrl($photo)
{
    $photo = trim((string) $photo);

    if ($photo === '') {
        return 'assets/img/testimonial-default.jpg';
    }

    if (
        filter_var($photo, FILTER_VALIDATE_URL) ||
        str_starts_with($photo, 'http://') ||
        str_starts_with($photo, 'https://')
    ) {
        return $photo;
    }

    if (str_starts_with($photo, '/')) {
        return $photo;
    }

    if (str_starts_with($photo, 'assets/')) {
        return $photo;
    }

    /*
    | Si les photos des témoignages sont enregistrées
    | dans uploads/testimonials/
    */
    return 'uploads/testimonials/' . ltrim($photo, '/');
}


/*
|--------------------------------------------------------------------------
| RÉCUPÉRATION DES TÉMOIGNAGES ACTIFS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        name,
        role,
        message,
        photo
    FROM testimonials
    WHERE status = 'active'
    ORDER BY display_order ASC, id ASC
");

$stmt->execute();

$testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| RÉCUPÉRATION DE L'ÉQUIPE ACTIVE
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        name,
        position,
        photo
    FROM team_members
    WHERE status = 'active'
    ORDER BY display_order ASC, id ASC
    LIMIT 3
");

$stmt->execute();

$teamMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="utf-8">

    <meta
        content="width=device-width, initial-scale=1.0"
        name="viewport"
    >

    <title>
        ORPAIS | Réconciliation, Paix et Cohésion Sociale
    </title>


    <meta
        name="description"
        content="ORPAIS œuvre pour la réconciliation, la paix, l’autonomisation et la cohésion sociale dans les communautés."
    >


    <meta
        name="keywords"
        content="ORPAIS, paix, cohésion sociale, réconciliation, autonomisation, Goma, RDC, développement communautaire"
    >


    <link
        href="assets/img/logo.jpg"
        rel="icon"
    >

    <link
        href="assets/img/logo.jpg"
        rel="apple-touch-icon"
    >


    <!-- Google Fonts -->

    <link
        href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,500i,600,700,800"
        rel="stylesheet"
    >


    <!-- Bootstrap 5 -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >


    <!-- Bootstrap Icons -->

    <link
        href="assets/vendor/bootstrap-icons/bootstrap-icons.css"
        rel="stylesheet"
    >


    <!-- Swiper -->

    <link
        href="assets/vendor/swiper/swiper-bundle.min.css"
        rel="stylesheet"
    >


    <!-- CSS principal -->

    <link
        href="assets/css/style.css"
        rel="stylesheet"
    >
    <link href="assets/css/tailwind.css" rel="stylesheet">


    <!-- =========================================================
         DESIGN ORPAIS
    ========================================================== -->

    <style>

        :root {

            --orpaics-dark: #164e68;

            --orpaics-blue: #4da9c2;

            --orpaics-light-blue: #dff3f5;

            --orpaics-soft-blue: #f7fbfb;

            --orpaics-text: #163b4d;

            --orpaics-white: #ffffff;

            --orpaics-orange: #d9865f;

            --orpaics-orange-dark: #bd6846;

        }


        * {
            scroll-behavior: smooth;
        }


        body {

            font-family: "Poppins", sans-serif;

            color: var(--orpaics-text);

            background: #ffffff;

        }


        a {
            text-decoration: none;
        }


        /* =====================================================
           TITRES
        ===================================================== */

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

            max-width: 700px;

            margin: auto;

            color: #66788a;

            line-height: 1.8;

        }


        .section-title .line {

            width: 65px;

            height: 4px;

            margin: 18px auto;

            border-radius: 10px;

            background: var(--orpaics-blue);

        }


        /* =====================================================
           NAVBAR
        ===================================================== */

        .navbar {

            background: rgba(255, 255, 255, 0.98);

            box-shadow:
                0 3px 18px rgba(7, 59, 102, 0.08);

            padding: 12px 0;

            transition: all 0.3s ease;

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

            padding: 12px 4px !important;

            position: relative;

        }


        .navbar-nav .nav-link::after {

            content: "";

            position: absolute;

            bottom: 3px;

            left: 0;

            width: 0;

            height: 3px;

            border-radius: 10px;

            background: var(--orpaics-blue);

            transition: width 0.3s ease;

        }


        .navbar-nav .nav-link:hover::after,

        .navbar-nav .nav-link.active::after {

            width: 100%;

        }


        .navbar-nav .nav-link:hover,

        .navbar-nav .nav-link.active {

            color: var(--orpaics-blue) !important;

        }


        .navbar-toggler {

            border: none;

            box-shadow: none !important;

        }


        /* =====================================================
           HERO
        ===================================================== */

        .hero-section {

            position: relative;

            min-height: 720px;

            overflow: hidden;

        }


        .hero-slider,

        .hero-slide {

            min-height: 720px;

        }


        .hero-slide {

            position: relative;

            display: flex;

            align-items: center;

            background-position: center;

            background-size: cover;

        }


        .hero-slide::before {

            position: absolute;

            inset: 0;

            content: "";

            background: linear-gradient(

                90deg,

                rgba(22, 78, 104, 0.88),

                rgba(77, 169, 194, 0.68),

                rgba(77, 169, 194, 0.18)

            );

        }


        .hero-slide > .container {

            position: relative;

            z-index: 1;

        }


        .hero-content {

            max-width: 760px;

            padding-top: 90px;

            color: var(--orpaics-white);

        }


        .hero-badge {

            display: inline-block;

            padding: 8px 17px;

            margin-bottom: 22px;

            border: 1px solid rgba(255,255,255,0.35);

            border-radius: 30px;

            background: rgba(255,255,255,0.12);

            font-size: 13px;

            font-weight: 600;

        }


        .hero-content h1 {

            font-size: 48px;

            line-height: 1.2;

            font-weight: 800;

            margin-bottom: 22px;

        }


        .hero-content h1 span {
            color: #fff0e8;
        }


        .hero-content p {

            max-width: 680px;

            font-size: 17px;

            line-height: 1.9;

            color: rgba(255,255,255,0.9);

            margin-bottom: 32px;

        }


        .hero-buttons {

            display: flex;

            flex-wrap: wrap;

            gap: 14px;

        }


        .hero-slider .swiper-pagination {

            bottom: 34px;

            text-align: left;

            padding-left: max(12px, calc((100% - 1140px) / 2));

        }


        .hero-slider .swiper-pagination-bullet {

            width: 28px;

            height: 4px;

            border-radius: 4px;

            background: rgba(255, 255, 255, 0.55);

            opacity: 1;

            transition: width 0.3s ease, background 0.3s ease;

        }


        .hero-slider .swiper-pagination-bullet-active {

            width: 52px;

            background: #ffffff;

        }


        .hero-slider .swiper-button-prev,

        .hero-slider .swiper-button-next {

            top: auto;

            right: 12px;

            bottom: 24px;

            left: auto;

            width: 42px;

            height: 42px;

            margin: 0;

            border: 1px solid rgba(255, 255, 255, 0.45);

            border-radius: 50%;

            color: #ffffff;

            background: rgba(7, 59, 102, 0.45);

        }


        .hero-slider .swiper-button-prev {

            right: 66px;

        }


        .hero-slider .swiper-button-prev::after,

        .hero-slider .swiper-button-next::after {

            font-size: 15px;

            font-weight: 700;

        }


        .btn-orpaics {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            padding: 13px 25px;

            border-radius: 30px;

            background: var(--orpaics-orange);

            color: #ffffff;

            font-size: 14px;

            font-weight: 600;

            border: 2px solid var(--orpaics-orange);

            transition: all 0.3s ease;

        }


        .btn-orpaics:hover {

            background: #ffffff;

            color: var(--orpaics-orange-dark);

            border-color: #ffffff;

        }


        .btn-orpaics-outline {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            padding: 13px 25px;

            border-radius: 30px;

            background: transparent;

            color: #ffffff;

            font-size: 14px;

            font-weight: 600;

            border: 2px solid rgba(255,255,255,0.7);

            transition: all 0.3s ease;

        }


        .btn-orpaics-outline:hover {

            background: var(--orpaics-orange);

            color: #ffffff;

        }


        /* =====================================================
           INTRODUCTION
        ===================================================== */

        .intro-section {

            padding: 90px 0;

            background: var(--orpaics-soft-blue);

        }


        .intro-image {

            position: relative;

            overflow: hidden;

            border-radius: 18px;

            box-shadow:
                0 15px 35px rgba(7,59,102,0.12);

        }


        .intro-image img {

            width: 100%;

            min-height: 360px;

            object-fit: cover;

        }


        .intro-card {

            position: absolute;

            bottom: 20px;

            left: 20px;

            right: 20px;

            padding: 20px;

            color: #ffffff;

            background: rgba(7,59,102,0.93);

            border-radius: 12px;

        }


        .intro-card h4 {

            font-size: 20px;

            font-weight: 700;

            margin-bottom: 6px;

        }


        .intro-card p {

            margin: 0;

            font-size: 13px;

        }


        .intro-content {
            padding-left: 25px;
        }


        .intro-content h2 {

            color: var(--orpaics-dark);

            font-size: 34px;

            line-height: 1.3;

            font-weight: 700;

            margin-bottom: 20px;

        }


        .intro-content h2 span {
            color: var(--orpaics-blue);
        }


        .intro-content p {

            color: #607487;

            font-size: 15px;

            line-height: 1.9;

            margin-bottom: 18px;

        }


        .check-list {

            list-style: none;

            padding: 0;

            margin: 25px 0;

        }


        .check-list li {

            display: flex;

            align-items: flex-start;

            gap: 10px;

            margin-bottom: 12px;

            font-size: 14px;

        }


        .check-list i {

            color: var(--orpaics-blue);

            font-size: 18px;

        }


        /* =====================================================
           DOMAINES
        ===================================================== */

        .domains-section {

            padding: 90px 0;

            background: #ffffff;

        }


        .domain-card {

            height: 100%;

            padding: 32px 25px;

            background: #ffffff;

            border: 1px solid #e2edf8;

            border-radius: 16px;

            box-shadow:
                0 8px 25px rgba(7,59,102,0.05);

            transition: all 0.3s ease;

        }


        .domain-card:hover {

            transform: translateY(-8px);

            border-color: var(--orpaics-blue);

            box-shadow:
                0 15px 35px rgba(11,94,215,0.13);

        }


        .domain-icon {

            display: flex;

            align-items: center;

            justify-content: center;

            width: 68px;

            height: 68px;

            margin-bottom: 22px;

            border-radius: 16px;

            background: var(--orpaics-light-blue);

            color: var(--orpaics-blue);

            font-size: 31px;

        }


        .domain-card h4 {

            color: var(--orpaics-dark);

            font-size: 18px;

            font-weight: 700;

            line-height: 1.5;

            margin-bottom: 13px;

        }


        .domain-card p {

            color: #6a7d8e;

            font-size: 14px;

            line-height: 1.8;

            margin: 0;

        }


        /* =====================================================
           VALEURS
        ===================================================== */

        .values-section {

            padding: 85px 0;

            background: var(--orpaics-light-blue);

        }


        .value-card {

            height: 100%;

            padding: 30px;

            background: #ffffff;

            border-radius: 16px;

            border: 1px solid #dceafa;

        }


        .value-card h4 {

            color: var(--orpaics-dark);

            font-weight: 700;

            margin: 18px 0 12px;

        }


        .value-card p {

            color: #6a7d8e;

            font-size: 14px;

            line-height: 1.8;

            margin: 0;

        }


        .value-number {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            width: 48px;

            height: 48px;

            border-radius: 50%;

            background: var(--orpaics-blue);

            color: #ffffff;

            font-weight: 700;

        }


        /* =====================================================
           ÉQUIPE
        ===================================================== */

        .section-team {

            padding: 90px 0;

            background: #f7faff;

        }


        .team-card {

            position: relative;

            height: 100%;

            overflow: hidden;

            background: #ffffff;

            border: 1px solid #e2edf8;

            border-radius: 22px;

            box-shadow:
                0 12px 35px rgba(7,59,102,0.10);

            transition: all 0.35s ease;

        }


        .team-card:hover {

            transform: translateY(-8px);

            box-shadow:
                0 20px 45px rgba(7,59,102,0.18);

        }


        .team-photo-wrapper {

            position: relative;

            width: 100%;

            height: 350px;

            overflow: hidden;

            background: #eaf2f8;

        }


        .team-photo {

            width: 100%;

            height: 100%;

            object-fit: cover;

            object-position: center;

            display: block;

            transition: transform 0.5s ease;

        }


        .team-card:hover .team-photo {

            transform: scale(1.05);

        }


        .team-photo-overlay {

            position: absolute;

            left: 20px;

            bottom: 20px;

            padding: 7px 15px;

            border-radius: 30px;

            background: rgba(7,59,102,0.90);

            color: #ffffff;

            font-size: 11px;

            font-weight: 700;

            letter-spacing: 1.5px;

        }


        .team-content {

            padding: 27px 25px 30px;

            text-align: center;

        }


        .team-name {

            margin: 0;

            color: var(--orpaics-dark);

            font-size: 21px;

            font-weight: 800;

            line-height: 1.4;

        }


        .team-line {

            width: 45px;

            height: 3px;

            margin: 14px auto;

            border-radius: 10px;

            background: var(--orpaics-blue);

        }


        .team-position {

            margin: 0;

            color: #687887;

            font-size: 14px;

            font-weight: 600;

            line-height: 1.6;

        }


        .team-empty {

            padding: 50px 30px;

            text-align: center;

            background: #ffffff;

            border-radius: 20px;

            box-shadow:
                0 10px 30px rgba(7,59,102,0.08);

        }


        .team-empty i {

            display: block;

            margin-bottom: 15px;

            color: var(--orpaics-blue);

            font-size: 50px;

        }


        .team-empty h4 {

            color: var(--orpaics-dark);

            font-weight: 800;

        }


        .team-empty p {

            margin-bottom: 0;

            color: #777;

        }


        /* =====================================================
           TÉMOIGNAGES
        ===================================================== */

        .testimonial-section {

            padding: 90px 0;

            background: var(--orpaics-soft-blue);

        }


        .testimonial-card {

            height: 100%;

            padding: 35px;

            background: #ffffff;

            border-radius: 16px;

            border: 1px solid #e0ebf7;

            box-shadow:
                0 8px 25px rgba(7,59,102,0.05);

            transition: all 0.3s ease;

        }


        .testimonial-card:hover {

            transform: translateY(-5px);

            box-shadow:
                0 15px 35px rgba(7,59,102,0.12);

        }


        .quote-icon {

            color: var(--orpaics-blue);

            font-size: 35px;

            margin-bottom: 15px;

        }


        .testimonial-message {

            color: #65788b;

            font-size: 14px;

            line-height: 1.9;

            font-style: italic;

            margin-bottom: 0;

        }


        .testimonial-author {

            display: flex;

            align-items: center;

            gap: 14px;

            margin-top: 22px;

        }


        .testimonial-author img {

            width: 58px;

            height: 58px;

            object-fit: cover;

            border-radius: 50%;

            border: 3px solid var(--orpaics-light-blue);

            background: #f1f5f9;

        }


        .testimonial-author h5 {

            color: var(--orpaics-dark);

            font-size: 15px;

            font-weight: 700;

            margin: 0 0 4px;

        }


        .testimonial-author span {

            color: #7c8e9f;

            font-size: 12px;

        }


        .testimonial-empty {

            text-align: center;

            padding: 50px 25px;

            background: #ffffff;

            border-radius: 16px;

            color: #6c7a89;

        }


        .testimonial-empty i {

            display: block;

            font-size: 45px;

            color: var(--orpaics-blue);

            margin-bottom: 15px;

        }


        /* =====================================================
           CTA
        ===================================================== */

        .cta-section {

            padding: 75px 0;

            background:

                linear-gradient(

                    100deg,

                    rgba(22,78,104,0.96),

                    rgba(77,169,194,0.88)

                ),

                url("assets/img/orpais.jpeg")

                center / cover no-repeat;

            color: #ffffff;

        }


        .cta-section h2 {

            font-size: 32px;

            font-weight: 700;

            margin-bottom: 15px;

        }


        .cta-section p {

            max-width: 720px;

            margin: 0 auto 25px;

            color: rgba(255,255,255,0.88);

            line-height: 1.8;

        }


        /* =====================================================
           FOOTER
        ===================================================== */

        .footer {

            padding: 65px 0 20px;

            background: #164e68;

            color: rgba(255,255,255,0.75);

        }


        .footer h4 {

            color: #ffffff;

            font-size: 18px;

            font-weight: 700;

            margin-bottom: 20px;

        }


        .footer p,

        .footer li,

        .footer a {

            color: rgba(255,255,255,0.72);

            font-size: 13px;

            line-height: 1.9;

        }


        .footer a:hover {

            color: #a9d2ff;

        }


        .footer ul {

            list-style: none;

            padding: 0;

            margin: 0;

        }


        .footer ul li {

            margin-bottom: 7px;

        }


        .footer-logo {

            display: flex;

            align-items: center;

            gap: 10px;

            margin-bottom: 18px;

            color: #ffffff;

            font-size: 24px;

            font-weight: 800;

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


        .social-links {

            display: flex;

            gap: 10px;

            margin-top: 20px;

        }


        .social-links a {

            display: flex;

            align-items: center;

            justify-content: center;

            width: 38px;

            height: 38px;

            border: 1px solid rgba(255,255,255,0.25);

            border-radius: 50%;

            color: #ffffff;

            transition: all 0.3s ease;

        }


        .social-links a:hover {

            background: var(--orpaics-blue);

            border-color: var(--orpaics-blue);

        }


        .footer-bottom {

            margin-top: 40px;

            padding-top: 20px;

            border-top: 1px solid rgba(255,255,255,0.15);

            text-align: center;

            font-size: 12px;

        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 991px) {

            .hero-section {
                min-height: 650px;
            }

            .hero-slider,
            .hero-slide {
                min-height: 650px;
            }


            .hero-content h1 {
                font-size: 38px;
            }


            .intro-content {

                padding-left: 0;

                margin-top: 35px;

            }


            .navbar-nav .nav-link {
                margin: 3px 0;
            }


            .team-photo-wrapper {
                height: 320px;
            }

        }


        @media (max-width: 767px) {

            .hero-section {
                min-height: 650px;
            }

            .hero-slider,
            .hero-slide {
                min-height: 650px;
            }


            .hero-content {
                padding-top: 80px;
            }


            .hero-content h1 {
                font-size: 31px;
            }


            .hero-content p {

                font-size: 14px;

                line-height: 1.8;

            }


            .section-title h2 {
                font-size: 27px;
            }


            .intro-content h2 {
                font-size: 28px;
            }


            .hero-buttons {

                flex-direction: column;

            }

            .hero-slider .swiper-pagination {
                padding-left: 12px;
            }

            .hero-slider .swiper-button-prev,
            .hero-slider .swiper-button-next {
                display: none;

                align-items: flex-start;

            }


            .team-photo-wrapper {
                height: 360px;
            }


            .cta-section h2 {
                font-size: 27px;
            }

        }


        @media (max-width: 480px) {

            .team-photo-wrapper {
                height: 330px;
            }


            .team-content {
                padding: 23px 18px 27px;
            }


            .team-name {
                font-size: 19px;
            }

        }

        .home-motion-ready .hero-slide::before {
            animation: hero-light-arrive 1.8s ease-out both;
        }

        .hero-slide::after {
            position: absolute;
            z-index: 0;
            inset: 0;
            content: "";
            pointer-events: none;
            background: radial-gradient(ellipse at 78% 48%, rgba(255,255,255,.18), transparent 36%);
        }

        .home-motion-ready .hero-slide.swiper-slide-active::after {
            animation: hero-ambient-fade 4s ease-in-out infinite alternate;
        }

        .home-motion-ready .hero-slide.swiper-slide-active .hero-content > * {
            animation: hero-content-arrive .9s ease both;
        }

        .home-motion-ready .hero-slide.swiper-slide-active .hero-content > *:nth-child(1) { animation-delay: .12s; }
        .home-motion-ready .hero-slide.swiper-slide-active .hero-content > *:nth-child(2) { animation-delay: .3s; }
        .home-motion-ready .hero-slide.swiper-slide-active .hero-content > *:nth-child(3) { animation-delay: .48s; }
        .home-motion-ready .hero-slide.swiper-slide-active .hero-content > *:nth-child(4) { animation-delay: .66s; }

        .home-motion-ready .hero-slide.swiper-slide-active .hero-badge {
            animation: hero-badge-arrive .9s .12s ease both;
        }

        .home-motion-ready .hero-content h1 span {
            color: #fff0e8;
        }

        .hero-scroll-cue {
            position: absolute;
            z-index: 2;
            bottom: 28px;
            left: 50%;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: rgba(255,255,255,.9);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .16em;
            text-decoration: none;
            text-transform: uppercase;
            transform: translateX(-50%);
        }

        .hero-scroll-cue span {
            display: inline-flex;
            width: 25px;
            height: 38px;
            align-items: flex-start;
            justify-content: center;
            border: 1px solid rgba(255,255,255,.75);
            border-radius: 20px;
            padding-top: 7px;
        }

        .hero-scroll-cue span::after {
            width: 3px;
            height: 7px;
            border-radius: 3px;
            background: #fff;
            content: "";
        }

        .home-motion-ready .hero-scroll-cue span::after {
            animation: scroll-cue-fade 1.4s ease-in-out infinite;
        }

        .home-motion-ready .btn-orpaics,
        .home-motion-ready .btn-orpaics-outline {
            transition: box-shadow .25s ease, background-color .25s ease;
        }

        .home-motion-ready .btn-orpaics:hover,
        .home-motion-ready .btn-orpaics-outline:hover {
            box-shadow: 0 12px 26px rgba(5,32,48,.24);
        }

        .home-motion-ready .home-reveal {
            opacity: 0;
            transition: opacity .9s ease;
            transition-delay: var(--reveal-delay, 0ms);
        }

        .home-motion-ready .home-reveal.is-visible {
            opacity: 1;
        }

        .home-motion-ready .domain-card.home-reveal,
        .home-motion-ready .value-card.home-reveal,
        .home-motion-ready .team-card.home-reveal,
        .home-motion-ready .testimonial-card.home-reveal {
            transition-delay: var(--reveal-delay, 0ms);
        }

        @keyframes hero-content-arrive {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes hero-badge-arrive {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes hero-light-arrive {
            from { opacity: .55; }
            to { opacity: 1; }
        }

        @keyframes hero-ambient-fade {
            from { opacity: .4; }
            to { opacity: 1; }
        }

        @keyframes scroll-cue-fade {
            0% { opacity: .25; }
            35% { opacity: 1; }
            100% { opacity: .25; }
        }

        @media (prefers-reduced-motion: reduce) {
            .home-motion-ready *,
            .home-motion-ready *::before,
            .home-motion-ready *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                scroll-behavior: auto !important;
                transition-duration: .01ms !important;
            }

            .home-motion-ready .home-reveal {
                opacity: 1;
            }
        }

    </style>

</head>


<body>


<!-- =========================================================
     NAVIGATION
========================================================= -->

<?php require_once __DIR__ . '/includes/header.php'; ?>


<main>


<!-- =========================================================
     HERO
========================================================= -->

<section class="hero-section" aria-label="Présentation d'ORPAIS">

    <div class="swiper hero-slider">

        <div class="swiper-wrapper">

            <div
                class="swiper-slide hero-slide"
                style="background-image: url('assets/img/orpais.jpeg');"
            >

                <div class="container">

                    <div class="hero-content">

                        <span class="hero-badge">
                            Organisation pour la paix et la cohésion sociale
                        </span>

                        <h1>
                            Construire ensemble un avenir de
                            <span>paix, d’unité et de dignité.</span>
                        </h1>

                        <p>
                            ORPAIS œuvre pour la réconciliation, la paix,
                            l’autonomisation des communautés et le renforcement
                            de la cohésion sociale en République démocratique du Congo.
                        </p>

                        <div class="hero-buttons">
                            <a href="about.php" class="btn-orpaics">
                                Découvrir ORPAIS
                                <i class="bi bi-arrow-right"></i>
                            </a>
                            <a href="contact.php" class="btn-orpaics-outline">
                                Nous contacter
                                <i class="bi bi-envelope"></i>
                            </a>
                        </div>

                    </div>

                </div>

            </div>

            <div
                class="swiper-slide hero-slide"
                style="background-image: url('assets/img/orpaiss.jpeg');"
            >

                <div class="container">

                    <div class="hero-content">

                        <span class="hero-badge">Des communautés plus fortes</span>
                        <h1>
                            Agir au cœur des communautés pour
                            <span>une paix durable.</span>
                        </h1>
                        <p>
                            Nous accompagnons les initiatives locales qui favorisent
                            le dialogue, l’inclusion et la solidarité.
                        </p>

                        <div class="hero-buttons">
                            <a href="activities.php" class="btn-orpaics">
                                Voir nos activités
                                <i class="bi bi-arrow-right"></i>
                            </a>
                            <a href="contact.php" class="btn-orpaics-outline">
                                Nous contacter
                                <i class="bi bi-envelope"></i>
                            </a>
                        </div>

                    </div>

                </div>

            </div>

            <div
                class="swiper-slide hero-slide"
                style="background-image: url('assets/img/orslide.jpeg');"
            >

                <div class="container">

                    <div class="hero-content">

                        <span class="hero-badge">Ensemble, maintenant</span>
                        <h1>
                            La cohésion sociale commence par
                            <span>le dialogue.</span>
                        </h1>
                        <p>
                            Rejoignez ORPAIS pour construire des passerelles
                            entre les personnes et faire grandir l’espoir.
                        </p>

                        <div class="hero-buttons">
                            <a href="about.php" class="btn-orpaics">
                                En savoir plus
                                <i class="bi bi-arrow-right"></i>
                            </a>
                            <a href="contact.php" class="btn-orpaics-outline">
                                Nous contacter
                                <i class="bi bi-envelope"></i>
                            </a>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="swiper-pagination"></div>
        <div class="swiper-button-prev" aria-label="Diapositive précédente"></div>
        <div class="swiper-button-next" aria-label="Diapositive suivante"></div>

    </div>

    <a class="hero-scroll-cue" href="#homepage-intro" aria-label="Faire défiler vers la présentation">
        Découvrir <span aria-hidden="true"></span>
    </a>

</section>


<!-- =========================================================
     PRÉSENTATION
========================================================= -->

<section class="intro-section" id="homepage-intro">

    <div class="container">


        <div class="section-title text-center">

            <h2>
                Ensemble pour la cohésion sociale
            </h2>

            <div class="line"></div>

            <p>

                Une organisation engagée aux côtés des communautés
                pour promouvoir la paix, la solidarité et le développement durable.

            </p>

        </div>


        <div class="row align-items-center g-5">


            <div class="col-lg-6">

                <div class="intro-image">

                    <img
                        src="assets/img/orpais.jpeg"
                        alt="Activité communautaire de l’organisation ORPAIS"
                    >


                    <div class="intro-card">

                        <h4>
                            ORPAIS/ASBL
                        </h4>

                        <p>
                            Réconciliation É Paix É Autonomisation É Cohésion sociale
                        </p>

                    </div>

                </div>

            </div>


            <div class="col-lg-6">

                <div class="intro-content">

                    <h2>

                        Une vision commune pour un

                        <span>
                            avenir meilleur
                        </span>

                    </h2>


                    <p>

                        L’Organisation pour la Réconciliation, la Paix,
                        l’Autonomisation et la Cohésion Sociale, ORPAIS,
                        est engagée dans la construction d’une société
                        plus pacifique, inclusive et solidaire.

                    </p>


                    <p>

                        É travers une approche communautaire, participative
                        et inclusive, nous accompagnons les populations
                        dans la recherche de solutions durables face aux
                        défis sociaux, économiques et humanitaires.

                    </p>


                    <ul class="check-list">

                        <li>

                            <i class="bi bi-check-circle-fill"></i>

                            Promouvoir la paix et la réconciliation communautaire.

                        </li>


                        <li>

                            <i class="bi bi-check-circle-fill"></i>

                            Renforcer l’autonomisation des populations.

                        </li>


                        <li>

                            <i class="bi bi-check-circle-fill"></i>

                            Défendre les droits humains et l’inclusion sociale.

                        </li>


                        <li>

                            <i class="bi bi-check-circle-fill"></i>

                            Encourager la participation citoyenne et le vivre-ensemble.

                        </li>

                    </ul>


                    <a
                        href="about.php"
                        class="btn-orpaics"
                    >

                        En savoir plus

                        <i class="bi bi-arrow-right"></i>

                    </a>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     DOMAINES
========================================================= -->

<section class="domains-section">

    <div class="container">


        <div class="section-title text-center">

            <h2>
                Nos domaines d’intervention
            </h2>

            <div class="line"></div>

            <p>

                Nos actions répondent aux besoins des communautés
                et contribuent à renforcer leur résilience et leur capacité d’action.

            </p>

        </div>


        <div class="row g-4">


            <div class="col-md-6 col-lg-4">

                <div class="domain-card">

                    <div class="domain-icon">

                        <i class="bi bi-shield-check"></i>

                    </div>


                    <h4>
                        Protection sociale et droits humains
                    </h4>


                    <p>

                        Promotion des droits humains, protection des personnes
                        vulnérables et accompagnement des communautés exposées
                        aux différentes formes de violence.

                    </p>

                </div>

            </div>


            <div class="col-md-6 col-lg-4">

                <div class="domain-card">

                    <div class="domain-icon">

                        <i class="bi bi-graph-up-arrow"></i>

                    </div>


                    <h4>
                        Autonomisation économique
                    </h4>


                    <p>

                        Soutien aux initiatives locales, développement des compétences
                        et accompagnement des femmes et des jeunes vers l’autonomie économique.

                    </p>

                </div>

            </div>


            <div class="col-md-6 col-lg-4">

                <div class="domain-card">

                    <div class="domain-icon">

                        <i class="bi bi-people"></i>

                    </div>


                    <h4>
                        Paix et cohésion sociale
                    </h4>


                    <p>

                        Sensibilisation, dialogue communautaire, médiation et initiatives
                        favorisant la réconciliation et le vivre-ensemble.

                    </p>

                </div>

            </div>


            <div class="col-md-6 col-lg-4">

                <div class="domain-card">

                    <div class="domain-icon">

                        <i class="bi bi-mortarboard"></i>

                    </div>


                    <h4>
                        Éducation et formation
                    </h4>


                    <p>

                        Renforcement des connaissances, développement des compétences
                        et organisation de formations adaptées aux besoins locaux.

                    </p>

                </div>

            </div>


            <div class="col-md-6 col-lg-4">

                <div class="domain-card">

                    <div class="domain-icon">

                        <i class="bi bi-heart-pulse"></i>

                    </div>


                    <h4>
                        Santé, bien-être et nutrition
                    </h4>


                    <p>

                        Promotion de la santé communautaire, du bien-être,
                        de la nutrition et de l’accès aux informations essentielles.

                    </p>

                </div>

            </div>


            <div class="col-md-6 col-lg-4">

                <div class="domain-card">

                    <div class="domain-icon">

                        <i class="bi bi-houses"></i>

                    </div>


                    <h4>
                        Développement communautaire et humanitaire
                    </h4>


                    <p>

                        Appui aux communautés, accompagnement des personnes
                        vulnérables et mise en œuvre d’initiatives de développement local.

                    </p>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     MISSION / VISION / VALEURS
========================================================= -->

<section class="values-section">

    <div class="container">


        <div class="section-title text-center">

            <h2>
                Notre engagement
            </h2>

            <div class="line"></div>

            <p>

                Notre action repose sur des principes qui placent
                les communautés au cœur du changement.

            </p>

        </div>


        <div class="row g-4">


            <div class="col-md-4">

                <div class="value-card">

                    <span class="value-number">
                        01
                    </span>


                    <h4>
                        Notre mission
                    </h4>


                    <p>

                        Contribuer à la construction d’une société pacifique,
                        inclusive et résiliente en renforçant les capacités
                        des communautés.

                    </p>

                </div>

            </div>


            <div class="col-md-4">

                <div class="value-card">

                    <span class="value-number">
                        02
                    </span>


                    <h4>
                        Notre vision
                    </h4>


                    <p>

                        Voir des communautés unies, autonomes et engagées
                        dans la promotion de la paix, de la dignité humaine
                        et du développement.

                    </p>

                </div>

            </div>


            <div class="col-md-4">

                <div class="value-card">

                    <span class="value-number">
                        03
                    </span>


                    <h4>
                        Nos valeurs
                    </h4>


                    <p>

                        Respect, solidarité, transparence, inclusion,
                        responsabilité, équité, dialogue et engagement communautaire.

                    </p>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     ÉQUIPE DYNAMIQUE
========================================================= -->

<section
    class="section-team"
    id="equipe"
>

    <div class="container">


        <div class="section-title text-center">

            <h2>
                Notre équipe
            </h2>

            <div class="line"></div>

            <p>

                Une équipe engagée au service de la paix,
                de la cohésion sociale et du développement communautaire.

            </p>

        </div>


        <div class="row justify-content-center g-4">


            <?php if (!empty($teamMembers)): ?>


                <?php foreach ($teamMembers as $member): ?>


                    <?php
                    $photo = teamPhotoUrl($member['photo']);
                    ?>


                    <div class="col-lg-4 col-md-6">


                        <div class="team-card">


                            <div class="team-photo-wrapper">


                                <img

                                    src="<?= e($photo); ?>"

                                    alt="<?= e($member['name']); ?>"

                                    class="team-photo"

                                    loading="lazy"

                                    onerror="this.onerror=null; this.src='assets/img/team-default.jpg';"

                                >


                                <div class="team-photo-overlay">

                                    ORPAIS

                                </div>


                            </div>


                            <div class="team-content">


                                <h3 class="team-name">

                                    <?= e($member['name']); ?>

                                </h3>


                                <div class="team-line"></div>


                                <p class="team-position">

                                    <?= e($member['position']); ?>

                                </p>


                            </div>


                        </div>


                    </div>


                <?php endforeach; ?>


            <?php else: ?>


                <div class="col-md-8">


                    <div class="team-empty">

                        <i class="bi bi-people"></i>

                        <h4>
                            Notre équipe
                        </h4>

                        <p>

                            Les membres de notre équipe seront bientôt
                            présentés ici.

                        </p>

                    </div>


                </div>


            <?php endif; ?>


        </div>


        <div class="text-center mt-5">


            <a
                href="agents-grid.php"
                class="btn-orpaics"
            >

                Découvrir toute l’équipe

                <i class="bi bi-arrow-right"></i>

            </a>


        </div>


    </div>

</section>


<!-- =========================================================
     TÉMOIGNAGES DYNAMIQUES
========================================================= -->

<section class="testimonial-section">

    <div class="container">


        <div class="section-title text-center">

            <h2>
                Ils parlent de notre engagement
            </h2>

            <div class="line"></div>

            <p>

                Des témoignages qui mettent en lumière l’importance
                de l’action communautaire.

            </p>

        </div>


        <div class="row g-4">


            <?php if (!empty($testimonials)): ?>


                <?php foreach ($testimonials as $testimonial): ?>


                    <?php
                    $testimonialPhoto =
                        testimonialPhotoUrl($testimonial['photo']);
                    ?>


                    <div class="col-lg-6">


                        <div class="testimonial-card">


                            <div class="quote-icon">

                                <i class="bi bi-quote"></i>

                            </div>


                            <p class="testimonial-message">

                                <?= nl2br(e($testimonial['message'])); ?>

                            </p>


                            <div class="testimonial-author">


                                <img

                                    src="<?= e($testimonialPhoto); ?>"

                                    alt="<?= e($testimonial['name']); ?>"

                                    loading="lazy"

                                    onerror="this.onerror=null; this.src='assets/img/testimonial-default.jpg';"

                                >


                                <div>


                                    <h5>

                                        <?= e($testimonial['name']); ?>

                                    </h5>


                                    <?php if (!empty($testimonial['role'])): ?>

                                        <span>

                                            <?= e($testimonial['role']); ?>

                                        </span>

                                    <?php endif; ?>


                                </div>


                            </div>


                        </div>


                    </div>


                <?php endforeach; ?>


            <?php else: ?>


                <div class="col-12">


                    <div class="testimonial-empty">

                        <i class="bi bi-chat-quote"></i>


                        <h4>
                            Aucun témoignage publié
                        </h4>


                        <p>

                            Les témoignages publiés depuis
                            l’administration apparaîtront automatiquement ici.

                        </p>

                    </div>


                </div>


            <?php endif; ?>


        </div>

    </div>

</section>


<!-- =========================================================
     APPEL É L'ACTION
========================================================= -->

<section class="cta-section text-center">

    <div class="container">


        <h2>
            Construisons la paix ensemble
        </h2>


        <p>

            Chaque action compte. Rejoignez notre engagement en faveur
            de la réconciliation, de l’autonomisation et de la cohésion sociale.

        </p>


        <a
            href="contact.php"
            class="btn-orpaics"
        >

            Entrer en contact

            <i class="bi bi-arrow-right"></i>

        </a>


    </div>

</section>


</main>


<!-- =========================================================
     FOOTER
========================================================= -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"
></script>

<script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

<script src="assets/vendor/php-email-form/validate.js"></script>

<script src="assets/js/main.js"></script>

<script>
(() => {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const revealElements = document.querySelectorAll(
        '.intro-section .section-title, .intro-section .intro-image, .intro-section .intro-content, ' +
        '.domains-section .section-title, .domain-card, .values-section .section-title, .value-card, ' +
        '.section-team .section-title, .team-card, .testimonial-section .section-title, ' +
        '.testimonial-card, .cta-section > .container'
    );

    if (reducedMotion) {
        document.querySelector('.hero-slider')?.swiper?.autoplay?.stop();
        return;
    }

    document.body.classList.add('home-motion-ready');
    const domainCards = Array.from(document.querySelectorAll('.domains-section .domain-card'));
    revealElements.forEach((element, index) => {
        element.classList.add('home-reveal');
        if (element.matches('.value-card, .team-card, .testimonial-card')) {
            const siblingIndex = Array.from(element.parentElement.children).indexOf(element);
            element.style.setProperty('--reveal-delay', `${(siblingIndex % 3) * 100}ms`);
        }
    });

    if (!('IntersectionObserver' in window)) {
        revealElements.forEach(element => element.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries, currentObserver) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                currentObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    revealElements.forEach(element => {
        if (!element.matches('.domain-card')) observer.observe(element);
    });

    const domainsSection = document.querySelector('.domains-section');
    if (domainsSection && domainCards.length) {
        const domainsObserver = new IntersectionObserver((entries, currentObserver) => {
            if (!entries.some(entry => entry.isIntersecting)) return;
            currentObserver.unobserve(domainsSection);
            domainCards.forEach((card, index) => {
                window.setTimeout(() => card.classList.add('is-visible'), index * 350);
            });
        }, { threshold: 0.01 });
        domainsObserver.observe(domainsSection);
    }
})();
</script>

</body>

</html>
