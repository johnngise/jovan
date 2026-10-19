"use client";

import { useEffect, useState } from "react";
import LanguageSwitcher from "./LanguageSwitcher";

const slides = [
  {
    eyebrow: "Organisation pour la paix et la cohÃ©sion sociale",
    title: "Construire ensemble un avenir de",
    accent: "paix, dâ€™unitÃ© et de dignitÃ©.",
    description:
      "ORPAIS Å“uvre pour la rÃ©conciliation, la paix, lâ€™autonomisation des communautÃ©s et le renforcement de la cohÃ©sion sociale en RÃ©publique dÃ©mocratique du Congo.",
    visual: "visual-sunrise",
  },
  {
    eyebrow: "Des communautÃ©s plus fortes",
    title: "Agir au cÅ“ur des communautÃ©s pour",
    accent: "une paix durable.",
    description:
      "Nous accompagnons les initiatives locales qui favorisent le dialogue, lâ€™inclusion et la solidaritÃ©.",
    visual: "visual-community",
  },
  {
    eyebrow: "Ensemble, maintenant",
    title: "La cohÃ©sion sociale commence par",
    accent: "le dialogue.",
    description:
      "Rejoignez ORPAIS pour construire des passerelles entre les personnes et faire grandir lâ€™espoir.",
    visual: "visual-dialogue",
  },
];

const stats = [
  ["12+", "annÃ©es dâ€™engagement"],
  ["35", "projets rÃ©alisÃ©s"],
  ["8", "provinces accompagnÃ©es"],
];

export default function Home() {
  const [activeSlide, setActiveSlide] = useState(0);
  const slide = slides[activeSlide];

  useEffect(() => {
    const timer = window.setInterval(
      () => setActiveSlide((current) => (current + 1) % slides.length),
      6000,
    );
    return () => window.clearInterval(timer);
  }, []);

  return (
    <main>
      <nav className="site-nav">
        <a className="brand" href="#">
          <span className="brand-mark">O</span>
          <span>
            <strong>ORPAIS</strong>
            <small>Paix Â· UnitÃ© Â· DignitÃ©</small>
          </span>
        </a>
        <div className="nav-links">
          <a className="active" href="#accueil">Accueil</a>
          <a href="#mission">Notre mission</a>
          <a href="#actions">Nos actions</a>
          <a href="/activities">Nos activités</a>
          <a href="#actualites">ActualitÃ©s</a>
        </div>
        <LanguageSwitcher />
        <a className="nav-cta" href="#contact">Nous contacter <span>â†—</span></a>
        <button className="menu-button" aria-label="Ouvrir le menu">â˜°</button>
      </nav>

      <section className="hero" id="accueil">
        <div className="hero-glow" />
        <div className="hero-copy">
          <span className="eyebrow"><i /> {slide.eyebrow}</span>
          <h1>{slide.title}<br /><em>{slide.accent}</em></h1>
          <p>{slide.description}</p>
          <div className="hero-actions">
            <a className="button primary" href="#mission">DÃ©couvrir ORPAIS <span>â†’</span></a>
            <a className="button ghost" href="/activities">Voir nos activités</a>
          </div>
          <div className="slider-controls">
            <button onClick={() => setActiveSlide((activeSlide + slides.length - 1) % slides.length)} aria-label="Slide prÃ©cÃ©dent">â†</button>
            <div className="dots">
              {slides.map((item, index) => (
                <button className={index === activeSlide ? "dot active" : "dot"} key={item.eyebrow} onClick={() => setActiveSlide(index)} aria-label={`Slide ${index + 1}`} />
              ))}
            </div>
            <button onClick={() => setActiveSlide((activeSlide + 1) % slides.length)} aria-label="Slide suivant">â†’</button>
            <span className="slide-count">0{activeSlide + 1} / 0{slides.length}</span>
          </div>
        </div>
        <div className={`hero-visual ${slide.visual}`} aria-hidden="true">
          <div className="visual-ring ring-one" />
          <div className="visual-ring ring-two" />
          <div className="visual-card">
            <span>Notre impact</span>
            <strong>Ensemble<br />on avance.</strong>
            <div className="mini-bars"><i /><i /><i /><i /></div>
          </div>
          <div className="visual-orb" />
        </div>
        <div className="hero-note"><span>â†—</span> Construire des ponts,<br />jamais des murs.</div>
      </section>

      <section className="stats-bar">
        {stats.map(([number, label]) => <div className="stat" key={label}><strong>{number}</strong><span>{label}</span></div>)}
        <p>Notre engagement<br /><b>au quotidien.</b></p>
      </section>

      <section className="mission section-wrap" id="mission">
        <div className="section-label">01 â€” Notre mission</div>
        <div>
          <h2>Faire de la paix<br />une <em>rÃ©alitÃ© vÃ©cue.</em></h2>
          <p className="lead">Nous croyons que chaque communautÃ© possÃ¨de les ressources nÃ©cessaires pour construire son propre avenir.</p>
          <a className="text-link" href="#actions">En savoir plus <span>â†—</span></a>
        </div>
        <div className="mission-card"><span>âœ¦</span><strong>Une vision.<br />Un mouvement.<br />Un avenir.</strong></div>
      </section>

      <section className="actions section-wrap" id="actions">
        <div className="section-label">02 â€” Nos actions</div>
        <div className="action-grid">
          <article><span>01</span><h3>Dialogue &<br /><em>rÃ©conciliation</em></h3><p>CrÃ©er des espaces sÃ»rs pour se parler, sâ€™Ã©couter et se comprendre.</p><a href="/activities">DÃ©couvrir â†’</a></article>
          <article><span>02</span><h3>Autonomisation<br /><em>des jeunes</em></h3><p>Donner aux jeunes les moyens de devenir acteurs du changement.</p><a href="/activities">DÃ©couvrir â†’</a></article>
          <article><span>03</span><h3>CohÃ©sion<br /><em>sociale</em></h3><p>Renforcer les liens qui unissent les communautÃ©s au quotidien.</p><a href="/activities">DÃ©couvrir â†’</a></article>
        </div>
      </section>

      <footer className="next-footer" id="contact">
        <div>
          <span className="brand-mark">O</span>
          <strong>ORPAIS<span>/RDC</span></strong>
          <p>Construisons ensemble un avenir de paix, d’unité et de dignité.</p>
        </div>
        <div className="next-footer-links">
          <a href="#mission">Notre mission</a>
          <a href="#actions">Nos actions</a>
          <a href="mailto:contact@orpaics.org">Nous contacter</a>
        </div>
        <a className="next-footer-mail" href="mailto:contact@orpaics.org">contact@orpaics.org <span>↗</span></a>
      </footer>
    </main>
  );
}
