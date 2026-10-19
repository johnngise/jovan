<footer class="footer footer-premium orpais-site-footer">
    <div class="container">
        <div class="footer-kicker">
            <span class="footer-kicker-line"></span>
            <span>Ensemble pour un avenir durable</span>
        </div>

        <div class="row g-5">
            <div class="col-lg-5">
                <div class="footer-logo">
                    <img src="assets/img/logo.jpeg" alt="Logo ORPAIS">
                    ORPAIS<span>/RDC</span>
                </div>
                <p class="footer-intro">
                    Nous créons des passerelles entre les communautés, les talents
                    et les initiatives qui font avancer la paix.
                </p>
                <div class="social-links">
                    <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                </div>
                <a class="footer-contact-card" href="contact.php">
                    <span>Une question ou un projet&nbsp;?</span>
                    <strong>Échangeons ensemble <i class="bi bi-arrow-up-right"></i></strong>
                </a>
            </div>

            <div class="col-lg-3 col-md-6">
                <h4>Navigation</h4>
                <ul>
                    <li><a href="index.php"><i class="bi bi-chevron-right"></i>Accueil</a></li>
                    <li><a href="about.php"><i class="bi bi-chevron-right"></i>Qui sommes-nous&nbsp;?</a></li>
                    <li><a href="activities.php"><i class="bi bi-chevron-right"></i>Nos activités</a></li>
                    <li><a href="blog-grid.php"><i class="bi bi-chevron-right"></i>Actualités</a></li>
                    <li><a href="agents-grid.php"><i class="bi bi-chevron-right"></i>Notre équipe</a></li>
                    <li><a href="contact.php"><i class="bi bi-chevron-right"></i>Contact</a></li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-6">
                <h4>Restons en contact</h4>
                <ul class="footer-contact-list">
                    <li><i class="bi bi-envelope"></i><a href="mailto:contact@orpaics.org">contact@orpaics.org</a></li>
                    <li><i class="bi bi-geo-alt"></i><span>République démocratique du Congo</span></li>
                    <li><i class="bi bi-briefcase"></i><a href="opportunites.php">Voir les opportunités</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <span>© <?= date('Y') ?> <strong>ORPAIS/RDC</strong></span>
            <span>Paix · Unité · Dignité</span>
            <span>Tous droits réservés</span>
        </div>
    </div>
</footer>

<script>
(() => {
    if (document.querySelector('.hero-section')) return;

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduceMotion) return;

    const stylesheet = document.createElement('link');
    stylesheet.rel = 'stylesheet';
    stylesheet.href = 'assets/css/site-motion.css?v=20260927-fade2';
    stylesheet.addEventListener('load', () => {
        document.body.classList.add('orpaics-motion-ready');

        const hero = document.querySelector(
            '.page-header, .intro-single, .contact-header, .donation-header, .team-header, .opportunities-header'
        );
        const revealTargets = new Set(document.querySelectorAll(
            'main > section:not(.section-footer), main > header, main .section-title, ' +
            '.activity-card, .impact-card, .opportunity-card, .team-card, .member-card, ' +
            '.value-card, .testimonial-card, .contact-card, .contact-info-item, ' +
            '.related-card, .card-box-b, .blog-single-box, .donation-info'
        ));

        if (hero) hero.classList.add('orpaics-motion-hero');
        revealTargets.forEach((element) => element.classList.add('orpaics-motion-reveal'));

        if (!('IntersectionObserver' in window)) {
            revealTargets.forEach((element) => element.classList.add('is-visible'));
            return;
        }

        const observer = new IntersectionObserver((entries, currentObserver) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                currentObserver.unobserve(entry.target);
            });
        }, { threshold: 0.01, rootMargin: '0px 0px -24px 0px' });

        revealTargets.forEach((element) => {
            if (element.matches(
                '.activity-card, .impact-card, .opportunity-card, .team-card, .member-card, ' +
                '.value-card, .testimonial-card, .contact-card, .contact-info-item, .related-card, .card-box-b'
            )) {
                const siblings = Array.from(element.parentElement.children);
                element.style.setProperty('--orpais-motion-delay', `${(siblings.indexOf(element) % 3) * 100}ms`);
                element.classList.add('orpaics-motion-card');
            }
            observer.observe(element);
        });
    });
    stylesheet.addEventListener('error', () => {
        console.error('Les animations des pages ORPAIS n’ont pas pu charger leur feuille de style.');
    });
    document.head.appendChild(stylesheet);
})();
</script>
