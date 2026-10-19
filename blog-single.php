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


/*
|--------------------------------------------------------------------------
| Formatage de la date en français
|--------------------------------------------------------------------------
*/

function formatDateFr($date)
{
    if (empty($date)) {
        return '';
    }

    $timestamp = strtotime($date);

    if (!$timestamp) {
        return '';
    }

    $months = [
        1  => 'janvier',
        2  => 'février',
        3  => 'mars',
        4  => 'avril',
        5  => 'mai',
        6  => 'juin',
        7  => 'juillet',
        8  => 'août',
        9  => 'septembre',
        10 => 'octobre',
        11 => 'novembre',
        12 => 'décembre'
    ];

    return date('j', $timestamp)
        . ' '
        . $months[(int) date('n', $timestamp)]
        . ' '
        . date('Y', $timestamp);
}


/*
|--------------------------------------------------------------------------
| Récupération de l'ID
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);


/*
|--------------------------------------------------------------------------
| Vérification de l'ID
|--------------------------------------------------------------------------
*/

if (!$id || $id <= 0) {

    header('Location: blog-grid.php');

    exit;

}


/*
|--------------------------------------------------------------------------
| Récupération de l'article
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        title,
        slug,
        category,
        excerpt,
        content,
        image,
        published_at,
        created_at
    FROM blog_posts
    WHERE id = ?
      AND status = 'active'
    LIMIT 1
");

$stmt->execute([$id]);

$post = $stmt->fetch();


/*
|--------------------------------------------------------------------------
| Article introuvable
|--------------------------------------------------------------------------
*/

if (!$post) {

    header('Location: blog-grid.php');

    exit;

}


/*
|--------------------------------------------------------------------------
| Image
|--------------------------------------------------------------------------
*/

$image = !empty($post['image'])
    ? $post['image']
    : 'assets/img/orpais.jpeg';


/*
|--------------------------------------------------------------------------
| Date
|--------------------------------------------------------------------------
*/

$date = !empty($post['published_at'])
    ? $post['published_at']
    : $post['created_at'];


/*
|--------------------------------------------------------------------------
| Catégorie
|--------------------------------------------------------------------------
*/

$category = !empty($post['category'])
    ? $post['category']
    : 'Actualités';


/*
|--------------------------------------------------------------------------
| Articles similaires
|--------------------------------------------------------------------------
*/

$relatedPosts = [];


if (!empty($post['category'])) {

    $relatedStmt = $pdo->prepare("
        SELECT
            id,
            title,
            slug,
            category,
            excerpt,
            content,
            image,
            published_at,
            created_at
        FROM blog_posts
        WHERE status = 'active'
          AND category = ?
          AND id != ?
        ORDER BY
            COALESCE(published_at, created_at) DESC,
            id DESC
        LIMIT 3
    ");

    $relatedStmt->execute([
        $post['category'],
        $post['id']
    ]);

    $relatedPosts = $relatedStmt->fetchAll();

}


/*
|--------------------------------------------------------------------------
| Description SEO
|--------------------------------------------------------------------------
*/

$seoDescription = !empty($post['excerpt'])
    ? $post['excerpt']
    : mb_strimwidth(
        strip_tags($post['content']),
        0,
        160,
        '...'
    );

?>

<!DOCTYPE html>

<html lang="fr">

<head>

<meta charset="utf-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>


<title>

<?= e($post['title']) ?>

| ORPAIS

</title>


<meta
    name="description"
    content="<?= e($seoDescription) ?>"
>


<meta
    name="keywords"
    content="ORPAIS, RDC, Goma, paix, cohésion sociale, autonomisation, actualités"
>


<!-- =========================================================
     FAVICON
========================================================= -->

<link
    href="assets/img/logo.jpg"
    rel="icon"
>


<!-- =========================================================
     GOOGLE FONTS
========================================================= -->

<link
    href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700"
    rel="stylesheet"
>


<!-- =========================================================
     VENDOR CSS
========================================================= -->

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


<!-- =========================================================
     MAIN CSS
========================================================= -->

<link
    href="assets/css/style.css"
    rel="stylesheet"
>
<link href="assets/css/tailwind.css" rel="stylesheet">


<style>

/* =========================================================
   ORPAIS
========================================================= */

:root {

    --orpaics-dark: #073b73;

    --orpaics-blue: #0b63ce;

    --orpaics-light: #eaf3ff;

    --orpaics-soft: #f5f9ff;

    --orpaics-text: #263b50;

}


/* =========================================================
   BODY
========================================================= */

body {

    font-family: "Poppins", sans-serif;

}


/* =========================================================
   NAVBAR
========================================================= */

.navbar-brand {

    font-weight: 700;

}


.navbar-brand .color-b {

    color: var(--orpaics-blue);

}


/* =========================================================
   ARTICLE
========================================================= */

.blog-single {

    padding: 0 0 70px 0;

}


/* =========================================================
   IMAGE PRINCIPALE
========================================================= */

.blog-single-image {

    width: 100%;

    height: 500px;

    object-fit: cover;

    border-radius: 8px;

    display: block;

}


/* =========================================================
   ARTICLE CARD
========================================================= */

.blog-single-box {

    background: #fff;

    padding: 40px;

    box-shadow:
        0 10px 35px rgba(7,59,102,.08);

    border-radius: 8px;

}


/* =========================================================
   META
========================================================= */

.blog-single-meta {

    margin-top: 25px;

    margin-bottom: 15px;

    display: flex;

    flex-wrap: wrap;

    gap: 18px;

    color: #777;

    font-size: 14px;

}


.blog-single-meta span {

    display: inline-flex;

    align-items: center;

    gap: 6px;

}


.blog-single-meta .category {

    color: var(--orpaics-blue);

    font-weight: 600;

}


/* =========================================================
   TITRE
========================================================= */

.blog-single-title {

    color: var(--orpaics-dark);

    font-size: 38px;

    line-height: 1.35;

    font-weight: 700;

    margin-bottom: 20px;

}


/* =========================================================
   EXCERPT
========================================================= */

.blog-single-excerpt {

    font-size: 17px;

    line-height: 1.8;

    color: #647383;

    padding-bottom: 25px;

    margin-bottom: 25px;

    border-bottom: 1px solid #e5e9ed;

}


/* =========================================================
   CONTENU
========================================================= */

.blog-single-content {

    color: var(--orpaics-text);

    font-size: 16px;

    line-height: 1.9;

}


.blog-single-content p {

    margin-bottom: 20px;

}


/* =========================================================
   RETOUR
========================================================= */

.blog-back {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    margin-bottom: 25px;

    text-decoration: none;

    color: var(--orpaics-blue);

    font-weight: 600;

}


.blog-back:hover {

    color: var(--orpaics-dark);

}


/* =========================================================
   SHARE
========================================================= */

.blog-share {

    margin-top: 40px;

    padding-top: 25px;

    border-top: 1px solid #e5e9ed;

}


.blog-share-title {

    font-weight: 600;

    color: var(--orpaics-dark);

    margin-right: 12px;

}


.blog-share a {

    width: 38px;

    height: 38px;

    display: inline-flex;

    justify-content: center;

    align-items: center;

    border-radius: 50%;

    background: var(--orpaics-light);

    color: var(--orpaics-blue);

    text-decoration: none;

    margin-right: 5px;

    transition: .3s ease;

}


.blog-share a:hover {

    background: var(--orpaics-blue);

    color: #fff;

}


/* =========================================================
   ARTICLES SIMILAIRES
========================================================= */

.related-news {

    margin-top: 70px;

}


.related-news-title {

    color: var(--orpaics-dark);

    font-size: 30px;

    font-weight: 700;

    margin-bottom: 35px;

}


/* =========================================================
   RELATED CARD
========================================================= */

.related-card {

    height: 100%;

    background: #fff;

    border-radius: 8px;

    overflow: hidden;

    box-shadow:
        0 10px 30px rgba(7,59,102,.08);

    transition: .3s ease;

}


.related-card:hover {

    transform: translateY(-6px);

}


.related-image {

    width: 100%;

    height: 230px;

    object-fit: cover;

}


.related-content {

    padding: 22px;

}


.related-category {

    color: var(--orpaics-blue);

    font-size: 13px;

    font-weight: 600;

    margin-bottom: 8px;

}


.related-title {

    font-size: 19px;

    line-height: 1.4;

    margin-bottom: 12px;

}


.related-title a {

    color: var(--orpaics-dark);

    text-decoration: none;

}


.related-title a:hover {

    color: var(--orpaics-blue);

}


.related-date {

    color: #888;

    font-size: 13px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 991px) {

    .blog-single-image {

        height: 400px;

    }


    .blog-single-title {

        font-size: 32px;

    }

}


@media (max-width: 767px) {

    .blog-single {

        padding-bottom: 50px;

    }


    .blog-single-box {

        padding: 25px;

    }


    .blog-single-image {

        height: 280px;

    }


    .blog-single-title {

        font-size: 27px;

    }


    .blog-single-excerpt {

        font-size: 15px;

    }


    .blog-single-content {

        font-size: 15px;

    }


    .related-news-title {

        font-size: 25px;

    }

}

</style>

</head>


<body>


<!-- =========================================================
     HEADER / NAVBAR
========================================================= -->

<?php require_once __DIR__ . '/includes/header.php'; ?>


<!-- =========================================================
     MAIN
========================================================= -->

<main id="main">


<!-- =========================================================
     INTRO
========================================================= -->

<section class="intro-single">

<div class="container">

<div class="row">


    <div class="col-md-12 col-lg-8">

        <div class="title-single-box">

            <h1 class="title-single">

                Actualité

            </h1>

            <span class="color-text-a">

                ORPAIS — Actualités et actions

            </span>

        </div>

    </div>


    <div class="col-md-12 col-lg-4">

        <nav
            aria-label="breadcrumb"
            class="breadcrumb-box d-flex justify-content-lg-end"
        >

            <ol class="breadcrumb">


                <li class="breadcrumb-item">

                    <a href="index.php">

                        Accueil

                    </a>

                </li>


                <li class="breadcrumb-item">

                    <a href="blog-grid.php">

                        Actualités

                    </a>

                </li>


                <li
                    class="breadcrumb-item active"
                    aria-current="page"
                >

                    Article

                </li>


            </ol>

        </nav>

    </div>


</div>

</div>

</section>


<!-- =========================================================
     ARTICLE
========================================================= -->

<section class="blog-single">

<div class="container">


<div class="row justify-content-center">


<div class="col-lg-10">


<!-- =====================================================
     RETOUR
===================================================== -->

<a
    href="blog-grid.php"
    class="blog-back"
>

    <i class="bi bi-arrow-left"></i>

    Retour aux actualités

</a>


<!-- =====================================================
     IMAGE
===================================================== -->

<img
    src="<?= e($image) ?>"
    alt="<?= e($post['title']) ?>"
    class="blog-single-image"
    loading="lazy"
    onerror="
        this.onerror=null;
        this.src='assets/img/orpais.jpeg';
    "
>


<!-- =====================================================
     CONTENU ARTICLE
===================================================== -->

<div class="blog-single-box">


<!-- META -->

<div class="blog-single-meta">


    <span class="category">

        <i class="bi bi-folder"></i>

        <?= e($category) ?>

    </span>


    <span>

        <i class="bi bi-calendar3"></i>

        <?= e(formatDateFr($date)) ?>

    </span>


    <span>

        <i class="bi bi-building"></i>

        ORPAIS/RDC

    </span>


</div>


<!-- TITRE -->

<h1 class="blog-single-title">

    <?= e($post['title']) ?>

</h1>


<!-- EXCERPT -->

<?php if (!empty($post['excerpt'])): ?>

<div class="blog-single-excerpt">

    <?= nl2br(e($post['excerpt'])) ?>

</div>

<?php endif; ?>


<!-- CONTENU -->

<div class="blog-single-content">

    <?= nl2br(e($post['content'])) ?>

</div>


<!-- =====================================================
     PARTAGE
===================================================== -->

<div class="blog-share">

    <span class="blog-share-title">

        Partager :

    </span>


    <a
        href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="Facebook"
    >

        <i class="bi bi-facebook"></i>

    </a>


    <a
        href="https://twitter.com/intent/tweet?url=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($post['title']) ?>"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="Twitter"
    >

        <i class="bi bi-twitter-x"></i>

    </a>


    <a
        href="https://api.whatsapp.com/send?text=<?= urlencode($post['title'] . ' - http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="WhatsApp"
    >

        <i class="bi bi-whatsapp"></i>

    </a>

</div>


</div>

</div>


</div>


<!-- =========================================================
     ARTICLES SIMILAIRES
========================================================= -->

<?php if (!empty($relatedPosts)): ?>

<div class="related-news">


<div class="row">


<div class="col-md-12">

<h2 class="related-news-title">

Articles similaires

</h2>

</div>


</div>


<div class="row">


<?php foreach ($relatedPosts as $related): ?>


<?php

$relatedImage = !empty($related['image'])
    ? $related['image']
    : 'assets/img/orpais.jpeg';


$relatedDate = !empty($related['published_at'])
    ? $related['published_at']
    : $related['created_at'];

?>


<div class="col-md-6 col-lg-4 mb-4">


<div class="related-card">


<a
    href="blog-single.php?id=<?= (int) $related['id'] ?>"
>

<img
    src="<?= e($relatedImage) ?>"
    alt="<?= e($related['title']) ?>"
    class="related-image"
    loading="lazy"
    onerror="
        this.onerror=null;
        this.src='assets/img/orpais.jpeg';
    "
>

</a>


<div class="related-content">


<?php if (!empty($related['category'])): ?>

<div class="related-category">

    <?= e($related['category']) ?>

</div>

<?php endif; ?>


<h3 class="related-title">

<a
    href="blog-single.php?id=<?= (int) $related['id'] ?>"
>

    <?= e($related['title']) ?>

</a>

</h3>


<div class="related-date">

<i class="bi bi-calendar3"></i>

<?= e(formatDateFr($relatedDate)) ?>

</div>


</div>

</div>


</div>


<?php endforeach; ?>


</div>

</div>

<?php endif; ?>


</div>

</section>


</main>


<!-- =========================================================
     FOOTER
========================================================= -->

<section class="section-footer">

<div class="container">


<div class="row">


<!-- =====================================================
     ORPAIS
===================================================== -->

<div class="col-sm-12 col-md-4">


<div class="widget-a">


<div class="w-header-a">

<h3 class="w-title-a text-brand">

ORPAIS

</h3>

</div>


<div class="w-body-a">

<p class="w-text-a color-text-a">

Organisation pour la Réconciliation,
la Paix, l’Autonomisation et la
Cohésion Sociale.

</p>

</div>


</div>

</div>


<!-- =====================================================
     DOMAINES
===================================================== -->

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

Protection sociale et droits humains

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

Santé, bien-être et nutrition

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


<!-- =====================================================
     MENU
===================================================== -->

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

<a href="about.php">

Qui sommes-nous ?

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

<a href="blog-grid.php">

Actualités

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

<a href="contact.php">

Nous contacter

</a>

</li>


</ul>


</div>

</div>

</div>


</div>

</div>

</section>


<!-- =========================================================
     FOOTER COPYRIGHT
========================================================= -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>


<!-- =========================================================
     PRELOADER
========================================================= -->

<div id="preloader"></div>


<!-- =========================================================
     BACK TO TOP
========================================================= -->

<div id="scroll-top">

<i class="bi bi-arrow-up-short"></i>

</div>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

<script src="assets/vendor/php-email-form/validate.js"></script>

<script src="assets/js/main.js"></script>


</body>

</html>




