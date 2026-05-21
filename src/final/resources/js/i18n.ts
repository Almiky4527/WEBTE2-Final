import { createI18n } from 'vue-i18n';

export const SUPPORTED_LOCALES = ['sk', 'en'] as const;
export type Locale = (typeof SUPPORTED_LOCALES)[number];

const STORAGE_KEY = 'locale';

function readStoredLocale(): Locale {
    if (typeof window === 'undefined') return 'sk';
    const stored = window.localStorage.getItem(STORAGE_KEY);
    return (SUPPORTED_LOCALES as readonly string[]).includes(stored ?? '') ? (stored as Locale) : 'sk';
}

const messages = {
    sk: {
        common: {
            appName: 'Aplikácia',
            language: 'Jazyk',
            slovak: 'Slovenčina',
            english: 'Angličtina',
        },
        nav: {
            platform: 'Platforma',
            console: 'Konzola',
            pendulum: 'Kyvadlo',
            ballBeam: 'Gulička a tyč',
            stats: 'Štatistiky',
            apiDocs: 'API dokumentácia',
            repository: 'Repozitár',
            documentation: 'Dokumentácia',
        },
        pages: {
            console: {
                title: 'Konzola',
                description: 'Spustite CAS príkazy a zobrazte výstup.',
            },
            pendulum: {
                title: 'Obrátené kyvadlo',
                description: 'Animácia a živý graf.',
            },
            ballBeam: {
                title: 'Gulička a tyč',
                description: 'Animácia a živý graf.',
            },
            stats: {
                title: 'Štatistiky',
                description: 'Štatistiky používania animácií.',
            },
            apiDocs: {
                title: 'API dokumentácia',
                description: 'Prehliadač OpenAPI.',
            },
            welcome: {
                brand: 'WEBTE2 — CAS Simulátor',
                openApp: 'Otvoriť aplikáciu',
                heading: 'Simulátor dynamických systémov',
                lead: 'Obrátené kyvadlo a model gulička-tyč s konzolou pohanou CAS, štatistikami používania a OpenAPI dokumentáciou.',
                footer: 'WEBTE2 — záverečný projekt',
                features: {
                    consoleDesc: 'Spustite CAS príkazy.',
                    pendulumDesc: 'Simulácia obráteného kyvadla.',
                    ballBeamDesc: 'Simulácia guličky na tyči.',
                    statsDesc: 'Štatistiky používania.',
                    apiDocsDesc: 'OpenAPI referencia.',
                },
            },
        },
    },
    en: {
        common: {
            appName: 'Application',
            language: 'Language',
            slovak: 'Slovak',
            english: 'English',
        },
        nav: {
            platform: 'Platform',
            console: 'Console',
            pendulum: 'Pendulum',
            ballBeam: 'Ball & Beam',
            stats: 'Stats',
            apiDocs: 'API Docs',
            repository: 'Repository',
            documentation: 'Documentation',
        },
        pages: {
            console: {
                title: 'Console',
                description: 'Run CAS commands and view output.',
            },
            pendulum: {
                title: 'Inverted Pendulum',
                description: 'Animation and live plot.',
            },
            ballBeam: {
                title: 'Ball & Beam',
                description: 'Animation and live plot.',
            },
            stats: {
                title: 'Stats',
                description: 'Animation usage statistics.',
            },
            apiDocs: {
                title: 'API Docs',
                description: 'OpenAPI viewer.',
            },
            welcome: {
                brand: 'WEBTE2 — CAS Simulator',
                openApp: 'Open app',
                heading: 'Dynamic system simulator',
                lead: 'Inverted pendulum and ball-and-beam models with a CAS-backed console, usage stats and OpenAPI docs.',
                footer: 'WEBTE2 — final project',
                features: {
                    consoleDesc: 'Run CAS commands.',
                    pendulumDesc: 'Inverted pendulum simulation.',
                    ballBeamDesc: 'Ball on beam simulation.',
                    statsDesc: 'Usage statistics.',
                    apiDocsDesc: 'OpenAPI reference.',
                },
            },
        },
    },
} as const;

export const i18n = createI18n({
    legacy: false,
    locale: readStoredLocale(),
    fallbackLocale: 'sk',
    messages,
});

export function setLocale(locale: Locale): void {
    i18n.global.locale.value = locale;
    if (typeof window !== 'undefined') {
        window.localStorage.setItem(STORAGE_KEY, locale);
        document.documentElement.setAttribute('lang', locale);
    }
}
