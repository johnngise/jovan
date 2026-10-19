"use client";

import { useMemo, useState } from "react";

export type Activity = {
  id: number;
  title: string;
  category: string;
  categoryLabel: string;
  description: string;
  icon: string;
  image: string;
  detailUrl: string;
};

const filters = [
  { id: "all", label: "Toutes les activités" },
  { id: "paix", label: "Paix & cohésion" },
  { id: "social", label: "Protection sociale" },
  { id: "developpement", label: "Développement" },
  { id: "education", label: "Éducation" },
  { id: "humanitaire", label: "Humanitaire" },
];

type Props = {
  activities: Activity[];
  siteBase: string;
};

export default function ActivitiesExperience({
  activities,
  siteBase,
}: Props) {
  const [activeFilter, setActiveFilter] = useState("all");
  const visibleActivities = useMemo(
    () =>
      activeFilter === "all"
        ? activities
        : activities.filter((activity) => activity.category === activeFilter),
    [activeFilter, activities],
  );

  return (
    <main className="activities-page">
      <nav className="activities-nav" aria-label="Navigation principale">
        <a className="activities-brand" href={siteBase}>
          <span className="activities-brand-mark">O</span>
          <span>
            <strong>ORPAIS</strong>
            <small>Paix · Unité · Dignité</small>
          </span>
        </a>
        <div className="activities-nav-links">
          <a href={siteBase}>Accueil</a>
          <a className="active" href="#activities">
            Nos activités
          </a>
          <a href={`${siteBase}about.php`}>Qui sommes-nous ?</a>
          <a href={`${siteBase}contact.php`}>Contact</a>
        </div>
        <a className="activities-nav-cta" href={`${siteBase}opportunites.php`}>
          Nous soutenir <span aria-hidden="true">↗</span>
        </a>
      </nav>

      <header className="activities-hero">
        <div className="activities-hero-orb" aria-hidden="true" />
        <div className="activities-hero-copy">
          <p className="activities-eyebrow">
            <span /> Agir ensemble, transformer durablement
          </p>
          <h1>
            Nos actions au service
            <br />
            <em>des communautés.</em>
          </h1>
          <p className="activities-hero-description">
            Découvrez les initiatives d’ORPAIS pour promouvoir la paix,
            renforcer la cohésion sociale et accompagner l’autonomisation en
            République démocratique du Congo.
          </p>
          <a className="activities-hero-link" href="#activities">
            Explorer nos domaines <span aria-hidden="true">↓</span>
          </a>
        </div>
        <div className="activities-hero-note">
          <strong>{String(activities.length).padStart(2, "0")}</strong>
          <span>domaines d’action</span>
        </div>
      </header>

      <section className="activities-introduction">
        <div>
          <p className="activities-section-label">Notre engagement</p>
          <h2>
            Des réponses concrètes,
            <br />
            <em>ancrées localement.</em>
          </h2>
        </div>
        <p>
          Nos actions partent des besoins exprimés par les communautés.
          Protection, dialogue, formation et solidarité se conjuguent pour
          faire émerger des solutions durables.
        </p>
      </section>

      <section className="activities-catalog" id="activities">
        <div className="activities-catalog-heading">
          <div>
            <p className="activities-section-label">Nos domaines d’intervention</p>
            <h2>Des initiatives qui créent du lien.</h2>
          </div>
          <span>{visibleActivities.length} activité(s)</span>
        </div>

        <div className="activities-filters" aria-label="Filtrer les activités">
          {filters.map((filter) => (
            <button
              aria-pressed={activeFilter === filter.id}
              className={activeFilter === filter.id ? "selected" : ""}
              key={filter.id}
              onClick={() => setActiveFilter(filter.id)}
              type="button"
            >
              {filter.label}
            </button>
          ))}
        </div>

        {visibleActivities.length === 0 ? (
          <div className="activities-empty">
            <span aria-hidden="true">✳</span>
            <h3>Aucune activité dans ce domaine pour le moment.</h3>
            <button onClick={() => setActiveFilter("all")} type="button">
              Voir toutes les activités
            </button>
          </div>
        ) : (
          <div className="activities-grid">
            {visibleActivities.map((activity, index) => (
              <article
                className="next-activity-card"
                key={activity.id}
                style={{ animationDelay: `${Math.min(index, 8) * 75}ms` }}
              >
                <a
                  className="next-activity-image"
                  href={activity.detailUrl}
                  tabIndex={-1}
                  aria-hidden="true"
                >
                  {/* The PHP API supplies an absolute URL for local uploads. */}
                  {/* eslint-disable-next-line @next/next/no-img-element */}
                  <img src={activity.image} alt="" loading="lazy" />
                  <span>{activity.categoryLabel}</span>
                </a>
                <div className="next-activity-content">
                  <div className="next-activity-icon" aria-hidden="true">
                    <i className={activity.icon} />
                  </div>
                  <p className="next-activity-index">
                    ACTION / {String(index + 1).padStart(2, "0")}
                  </p>
                  <h3>{activity.title}</h3>
                  <p className="next-activity-description">
                    {activity.description}
                  </p>
                  <a className="next-activity-link" href={activity.detailUrl}>
                    Découvrir l’activité <span aria-hidden="true">↗</span>
                  </a>
                </div>
              </article>
            ))}
          </div>
        )}
      </section>

      <section className="activities-bottom-cta">
        <p className="activities-section-label">Un engagement partagé</p>
        <h2>
          La paix se construit
          <br />
          <em>ensemble.</em>
        </h2>
        <p>
          Prenez part à nos initiatives ou échangez avec notre équipe sur un
          partenariat.
        </p>
        <a href={`${siteBase}contact.php`}>
          Entrer en contact <span aria-hidden="true">↗</span>
        </a>
      </section>

      <footer className="activities-footer">
        <a className="activities-brand" href={siteBase}>
          <span className="activities-brand-mark">O</span>
          <span>
            <strong>ORPAIS / RDC</strong>
            <small>Paix · Unité · Dignité</small>
          </span>
        </a>
        <span>© {new Date().getFullYear()} ORPAIS. Tous droits réservés.</span>
        <a href={`${siteBase}activities.php`}>Version PHP</a>
      </footer>
    </main>
  );
}
