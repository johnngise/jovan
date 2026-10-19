<?php

$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');

$activePage = $currentPage;

if ($currentPage === 'activity-single.php') {
    $activePage = 'activities.php';
}

$navigation = [
    'index.php' => ['label' => 'Accueil', 'href' => 'index.php'],
    'about.php' => ['label' => 'Qui sommes-nous ?', 'href' => 'about.php'],
    'activities.php' => ['label' => 'Nos activités', 'href' => 'activities.php'],
    'opportunites.php' => ['label' => 'Opportunités', 'href' => 'opportunites.php'],
    'agents-grid.php' => ['label' => 'Notre équipe', 'href' => 'agents-grid.php'],
    'contact.php' => ['label' => 'Contact', 'href' => 'contact.php'],
];
?>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="assets/img/logo.jpeg" alt="Logo ORPAIS">
            ORPAIS<span>/RDC</span>
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarDefault"
            aria-controls="navbarDefault"
            aria-expanded="false"
            aria-label="Ouvrir le menu"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarDefault">
            <ul class="navbar-nav align-items-lg-center">
                <?php foreach ($navigation as $page => $item): ?>
                    <li class="nav-item">
                        <a
                            class="nav-link<?php echo $activePage === $page ? ' active' : ''; ?>"
                            href="<?php echo htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>"
                        >
                            <?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="site-language" aria-label="Choisir la langue">
                <i class="bi bi-translate" aria-hidden="true"></i>
                <label class="visually-hidden" for="site-language-select">Langue</label>
                <select id="site-language-select" onchange="window.setSiteLanguage(this.value)">
                    <option value="fr">Français</option>
                    <option value="en">English</option>
                    <option value="sw">Kiswahili</option>
                    <option value="es">Español</option>
                </select>
            </div>
        </div>
    </div>
</nav>

<div id="google_translate_element" aria-hidden="true"></div>
<script>
    window.googleTranslateElementInit = function () {
        new google.translate.TranslateElement({
            pageLanguage: 'fr',
            includedLanguages: 'fr,en,sw,es',
            autoDisplay: false
        }, 'google_translate_element');
        if (window.siteLanguage) {
            window.setSiteLanguage(window.siteLanguage);
        }
    };

    window.setSiteLanguage = function (language) {
        window.siteLanguage = language;
        var select = document.querySelector('.goog-te-combo');
        if (!select) {
            return;
        }
        select.value = language;
        select.dispatchEvent(new Event('change'));
    };
</script>
<script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
