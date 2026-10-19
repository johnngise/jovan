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
| Pagination
|--------------------------------------------------------------------------
*/

$postsPerPage = 6;

$page = filter_input(
    INPUT_GET,
    'page',
    FILTER_VALIDATE_INT
);

$page = ($page && $page > 0) ? $page : 1;


/*
|--------------------------------------------------------------------------
| Filtre catégorie
|--------------------------------------------------------------------------
*/

$category = trim($_GET['category'] ?? '');


/*
|--------------------------------------------------------------------------
| Construction dynamique de la requête
|--------------------------------------------------------------------------
*/

$where = [
    "status = 'active'"
];

$params = [];


if ($category !== '') {

    $where[] = "category = :category";

    $params['category'] = $category;
}


$whereSql = implode(' AND ', $where);


/*
|--------------------------------------------------------------------------
| Nombre total d'articles
|--------------------------------------------------------------------------
*/

$countStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM blog_posts
    WHERE $whereSql
");

$countStmt->execute($params);

$totalPosts = (int) $countStmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Nombre total de pages
|--------------------------------------------------------------------------
*/

$totalPages = max(
    1,
    (int) ceil($totalPosts / $postsPerPage)
);


/*
|--------------------------------------------------------------------------
| Correction de la page demandée
|--------------------------------------------------------------------------
*/

if ($page > $totalPages) {

    $page = $totalPages;

}


/*
|--------------------------------------------------------------------------
| Offset
|--------------------------------------------------------------------------
*/

$offset = ($page - 1) * $postsPerPage;


/*
|--------------------------------------------------------------------------
| Récupération des articles
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
    WHERE $whereSql
    ORDER BY
        COALESCE(published_at, created_at) DESC,
        id DESC
    LIMIT :limit
    OFFSET :offset
");

$stmt->bindValue(
    ':limit',
    $postsPerPage,
    PDO::PARAM_INT
);

$stmt->bindValue(
    ':offset',
    $offset,
    PDO::PARAM_INT
);

if ($params) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}

$posts = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Récupération des catégories
|--------------------------------------------------------------------------
*/

$categoryStmt = $pdo->query("
    SELECT DISTINCT category
    FROM blog_posts
    WHERE status = 'active'
    AND category IS NOT NULL
    AND category != ''
    ORDER BY category ASC
");

$categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);


/*
|--------------------------------------------------------------------------
| Préparation du lien de pagination
|--------------------------------------------------------------------------
*/

$categoryQuery = '';

if ($category !== '') {

    $categoryQuery =
        '&category=' . urlencode($category);

}

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
    ORPAIS | Actualités
</title>

<meta
    name="description"
    content="Découvrez les actualités, activités et actions d'ORPAIS en faveur de la paix, de l'autonomisation et de la cohésion sociale."
>

<meta
    name="keywords"
    content="ORPAIS, RDC, paix, cohésion sociale, autonomisation, actualités, Goma"
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
   NEWS
========================================================= */

.news-box {

    height: 100%;

    transition: .3s ease;

}

.news-box:hover {

    transform: translateY(-6px);

}


.img-box-b {

    height: 270px;

    overflow: hidden;

}


.img-box-b img {

    width: 100%;

    height: 100%;

    object-fit: cover;

    transition: .4s ease;

}


.news-box:hover .img-box-b img {

    transform: scale(1.05);

}


.card-category-b a {

    text-decoration: none;

}


.card-title-b a {

    text-decoration: none;

}


.card-title-b h2 {

    font-size: 23px;

    line-height: 1.35;

}


.card-date {

    margin-top: 10px;

}


.date-b {

    font-size: 14px;

}


/* =========================================================
   EXCERPT
========================================================= */

.news-excerpt {

    color: rgba(255,255,255,.85);

    font-size: 14px;

    line-height: 1.6;

    margin-top: 10px;

}


/* =========================================================
   FILTRE CATEGORIES
========================================================= */

.blog-categories {

    margin-bottom: 40px;

}


.blog-categories a {

    display: inline-block;

    padding: 9px 18px;

    margin: 4px;

    border: 1px solid var(--orpaics-blue);

    border-radius: 30px;

    text-decoration: none;

    color: var(--orpaics-blue);

    font-size: 14px;

    font-weight: 500;

    transition: .3s ease;

}


.blog-categories a:hover {

    background: var(--orpaics-blue);

    color: #fff;

}


.blog-categories a.active {

    background: var(--orpaics-blue);

    color: #fff;

}


/* =========================================================
   EMPTY
========================================================= */

.empty-news {

    background: #fff;

    border-radius: 15px;

    padding: 70px 30px;

    text-align: center;

    box-shadow:
        0 10px 30px rgba(7,59,102,.07);

}


.empty-news i {

    font-size: 55px;

    color: var(--orpaics-blue);

}


.empty-news h3 {

    margin-top: 20px;

    color: var(--orpaics-dark);

}


/* =========================================================
   PAGINATION
========================================================= */

.pagination-a .page-link {

    color: var(--orpaics-dark);

}


.pagination-a .page-item.active .page-link {

    background: var(--orpaics-blue);

    border-color: var(--orpaics-blue);

    color: #fff;

}


.pagination-a .page-link:hover {

    color: #fff;

    background: var(--orpaics-blue);

    border-color: var(--orpaics-blue);

}


/* =========================================================
   FOOTER
========================================================= */

.section-footer {

    margin-top: 50px;

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

                Nos actualités

            </h1>

            <span class="color-text-a">

                Actualités et actions d’ORPAIS

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


                <li
                    class="breadcrumb-item active"
                    aria-current="page"
                >

                    Actualités

                </li>


            </ol>

        </nav>

    </div>


</div>

</div>

</section>


<!-- =========================================================
     BLOG
========================================================= -->

<section class="news-grid grid">

<div class="container">


<!-- =========================================================
     FILTRE PAR CATEGORIE
========================================================= -->

<?php if (!empty($categories)): ?>

<div class="blog-categories text-center">


    <a
        href="blog-grid.php"
        class="<?= $category === '' ? 'active' : '' ?>"
    >

        Toutes

    </a>


    <?php foreach ($categories as $cat): ?>

        <a
            href="blog-grid.php?category=<?= urlencode($cat) ?>"
            class="<?= $category === $cat ? 'active' : '' ?>"
        >

            <?= e($cat) ?>

        </a>

    <?php endforeach; ?>


</div>

<?php endif; ?>


<!-- =========================================================
     ARTICLES
========================================================= -->

<?php if (!empty($posts)): ?>


<div class="row">


<?php foreach ($posts as $post): ?>


<?php

$image = !empty($post['image'])
    ? $post['image']
    : 'assets/img/orpais.jpeg';


$date = !empty($post['published_at'])
    ? $post['published_at']
    : $post['created_at'];


$excerpt = '';

if (!empty($post['excerpt'])) {

    $excerpt = $post['excerpt'];

} elseif (!empty($post['content'])) {

    $excerpt = mb_strimwidth(
        strip_tags($post['content']),
        0,
        150,
        '...'
    );

}

?>


<div class="col-md-6 col-lg-4 mb-5">


<div class="card-box-b card-shadow news-box">


<!-- =====================================================
     IMAGE
===================================================== -->

<div class="img-box-b">


    <a
        href="blog-single.php?id=<?= (int) $post['id'] ?>"
    >

        <img
            src="<?= e($image) ?>"
            alt="<?= e($post['title']) ?>"
            class="img-b img-fluid"
            loading="lazy"
            onerror="
                this.onerror=null;
                this.src='assets/img/orpais.jpeg';
            "
        >

    </a>


</div>


<!-- =====================================================
     CONTENU
===================================================== -->

<div class="card-overlay">


<div class="card-header-b">


    <!-- CATEGORIE -->

    <?php if (!empty($post['category'])): ?>

        <div class="card-category-b">

            <a
                href="blog-grid.php?category=<?= urlencode($post['category']) ?>"
                class="category-b"
            >

                <?= e($post['category']) ?>

            </a>

        </div>

    <?php endif; ?>


    <!-- TITRE -->

    <div class="card-title-b">

        <h2 class="title-2">

            <a
                href="blog-single.php?id=<?= (int) $post['id'] ?>"
            >

                <?= e($post['title']) ?>

            </a>

        </h2>

    </div>


    <!-- DATE -->

    <div class="card-date">

        <span class="date-b">

            <i class="bi bi-calendar3"></i>

            <?= e(formatDateFr($date)) ?>

        </span>

    </div>


    <!-- EXCERPT -->

    <?php if (!empty($excerpt)): ?>

        <div class="news-excerpt">

            <?= e($excerpt) ?>

        </div>

    <?php endif; ?>


    <!-- LIEN -->

    <div class="mt-3">

        <a
            href="blog-single.php?id=<?= (int) $post['id'] ?>"
            class="btn btn-sm btn-light"
        >

            Lire l’article

            <i class="bi bi-arrow-right"></i>

        </a>

    </div>


</div>

</div>

</div>

</div>


<?php endforeach; ?>


</div>


<?php else: ?>


<!-- =========================================================
     AUCUNE ACTUALITE
========================================================= -->

<div class="row">


<div class="col-12">


<div class="empty-news">


    <i class="bi bi-newspaper"></i>


    <h3>

        <?php if ($category !== ''): ?>

            Aucun article dans cette catégorie

        <?php else: ?>

            Aucune actualité disponible

        <?php endif; ?>

    </h3>


    <p class="text-muted mb-4">

        <?php if ($category !== ''): ?>

            Aucune publication ORPAIS ne correspond
            actuellement à la catégorie
            <strong><?= e($category) ?></strong>.

        <?php else: ?>

            Les actualités et publications
            d’ORPAIS seront prochainement
            disponibles.

        <?php endif; ?>

    </p>


    <?php if ($category !== ''): ?>

        <a
            href="blog-grid.php"
            class="btn btn-primary"
        >

            Voir toutes les actualités

        </a>

    <?php endif; ?>


</div>


</div>

</div>


<?php endif; ?>


<!-- =========================================================
     PAGINATION
========================================================= -->

<?php if ($totalPages > 1): ?>


<div class="row mt-4">


<div class="col-sm-12">


<nav class="pagination-a">


<ul class="pagination justify-content-end">


<!-- PREVIOUS -->

<li
    class="page-item
    <?= $page <= 1 ? 'disabled' : '' ?>"
>


<?php if ($page > 1): ?>

    <a
        class="page-link"
        href="blog-grid.php?page=<?= $page - 1 ?><?= $categoryQuery ?>"
    >

        <span class="bi bi-chevron-left"></span>

    </a>

<?php else: ?>

    <span class="page-link">

        <span class="bi bi-chevron-left"></span>

    </span>

<?php endif; ?>


</li>


<!-- NUMEROS -->

<?php for ($i = 1; $i <= $totalPages; $i++): ?>


<li
    class="page-item
    <?= $i === $page ? 'active' : '' ?>"
>


<a
    class="page-link"
    href="blog-grid.php?page=<?= $i ?><?= $categoryQuery ?>"
>

    <?= $i ?>

</a>


</li>


<?php endfor; ?>


<!-- NEXT -->

<li
    class="page-item
    <?= $page >= $totalPages ? 'disabled' : '' ?>"
>


<?php if ($page < $totalPages): ?>

    <a
        class="page-link"
        href="blog-grid.php?page=<?= $page + 1 ?><?= $categoryQuery ?>"
    >

        <span class="bi bi-chevron-right"></span>

    </a>

<?php else: ?>

    <span class="page-link">

        <span class="bi bi-chevron-right"></span>

    </span>

<?php endif; ?>


</li>


</ul>

</nav>

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


