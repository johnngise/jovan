import type { Metadata } from "next";
import ActivitiesExperience, { type Activity } from "./ActivitiesExperience";

export const metadata: Metadata = {
  title: "Nos activités | ORPAIS",
  description:
    "Découvrez les activités d’ORPAIS pour la paix, la cohésion sociale et l’autonomisation des communautés.",
};

export const dynamic = "force-dynamic";

type ActivitiesResponse = {
  siteBase: string;
  activities: Activity[];
};

function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === "object" && value !== null && !Array.isArray(value);
}

function isActivitiesResponse(value: unknown): value is ActivitiesResponse {
  return (
    isRecord(value) &&
    typeof value.siteBase === "string" &&
    Array.isArray(value.activities) &&
    value.activities.every(
      (activity: unknown) =>
        isRecord(activity) &&
        typeof activity.id === "number" &&
        typeof activity.title === "string" &&
        typeof activity.category === "string" &&
        typeof activity.categoryLabel === "string" &&
        typeof activity.description === "string" &&
        typeof activity.icon === "string" &&
        typeof activity.image === "string" &&
        typeof activity.detailUrl === "string",
    )
  );
}

const apiUrl =
  process.env.ORPAIS_ACTIVITIES_API_URL ??
  "http://localhost/ORPAICS/api/activities.php";
const phpSiteBase =
  process.env.ORPAIS_SITE_BASE_URL ?? "http://localhost/ORPAICS/";

export default async function ActivitiesPage() {
  let data: ActivitiesResponse;
  try {
    const response = await fetch(apiUrl, { cache: "no-store" });
    if (!response.ok) {
      throw new Error(`Activities API returned HTTP ${response.status}`);
    }
    const responseData: unknown = await response.json();
    if (!isActivitiesResponse(responseData)) {
      throw new Error("Activities API returned an invalid response.");
    }
    data = responseData;
  } catch (error) {
    console.error("Unable to load ORPAIS activities:", error);
    return (
      <main className="activities-error">
        <p className="activities-eyebrow">ORPAIS / RDC</p>
        <h1>Nos activités sont momentanément indisponibles.</h1>
        <p>
          Vérifiez que le site PHP et sa base de données sont accessibles, puis
          réessayez.
        </p>
        <a href={`${phpSiteBase}activities.php`}>
          Ouvrir la page de secours
        </a>
      </main>
    );
  }

  return (
    <ActivitiesExperience
      activities={data.activities}
      siteBase={data.siteBase}
    />
  );
}
