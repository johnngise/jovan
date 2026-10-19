"use client";

import { useEffect, useState } from "react";

const languages = [
  { code: "fr", label: "Français" },
  { code: "en", label: "English" },
  { code: "sw", label: "Kiswahili" },
  { code: "es", label: "Español" },
];

declare global {
  interface Window {
    googleTranslateElementInit?: () => void;
    setNextLanguage?: (language: string) => void;
    siteLanguage?: string;
  }
}

export default function LanguageSwitcher() {
  const [language, setLanguage] = useState("fr");

  useEffect(() => {
    window.googleTranslateElementInit = () => {
      const translateWindow = window as typeof window & {
        google?: {
          translate?: {
            TranslateElement?: new (
              options: Record<string, unknown>,
              elementId: string,
            ) => unknown;
          };
        };
      };
      const TranslateElement =
        translateWindow.google?.translate?.TranslateElement;

      if (TranslateElement && !document.querySelector(".goog-te-combo")) {
        new TranslateElement(
          {
            pageLanguage: "fr",
            includedLanguages: "fr,en,sw,es",
            autoDisplay: false,
          },
          "next_google_translate_element",
        );
      }
      if (window.siteLanguage) {
        window.setNextLanguage?.(window.siteLanguage);
      }
    };

    window.setNextLanguage = (nextLanguage) => {
      setLanguage(nextLanguage);
      const combo = document.querySelector<HTMLSelectElement>(".goog-te-combo");
      if (!combo) {
        window.siteLanguage = nextLanguage;
        return;
      }
      combo.value = nextLanguage;
      combo.dispatchEvent(new Event("change"));
    };

    if (!document.querySelector('script[data-next-google-translate="true"]')) {
      const script = document.createElement("script");
      script.src =
        "https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit";
      script.async = true;
      script.dataset.nextGoogleTranslate = "true";
      document.body.appendChild(script);
    } else {
      window.googleTranslateElementInit();
    }

    return () => {
      delete window.googleTranslateElementInit;
      delete window.setNextLanguage;
    };
  }, []);

  return (
    <>
      <div className="language-switcher" aria-label="Choisir la langue">
        <span aria-hidden="true">文</span>
        <label className="sr-only" htmlFor="next-language-select">
          Langue
        </label>
        <select
          id="next-language-select"
          value={language}
          onChange={(event) => window.setNextLanguage?.(event.target.value)}
        >
          {languages.map((item) => (
            <option key={item.code} value={item.code}>
              {item.label}
            </option>
          ))}
        </select>
      </div>
      <div id="next_google_translate_element" aria-hidden="true" />
    </>
  );
}
