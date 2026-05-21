import { createI18n } from 'vue-i18n';

export const SUPPORTED_LOCALES = ['sk', 'en'] as const;
export type Locale = (typeof SUPPORTED_LOCALES)[number];

const STORAGE_KEY = 'locale';

function readStoredLocale(): Locale {
    if (typeof window === 'undefined') {
return 'sk';
}

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
            save: 'Uložiť',
            cancel: 'Zrušiť',
            close: 'Zavrieť',
        },
        stats: {
            summary: {
                title: 'Prehľad',
                loading: 'Načítava sa…',
                runs: 'spustení',
            },
            detail: {
                title: 'Detail: {name}',
                loading: 'Načítavajú sa záznamy…',
                empty: 'Žiadne záznamy.',
                col: {
                    time: 'Čas',
                    city: 'Mesto',
                    country: 'Krajina',
                },
            },
            anim: {
                ball: 'Gulička a tyč',
                pendulum: 'Kyvadlo',
            },
            logs: {
                title: 'Prehľad záznamov',
                description: 'Záznamy volaní Octave REST API.',
                download: 'Stiahnuť CSV',
                loading: 'Načítavajú sa záznamy…',
                empty: 'Žiadne záznamy.',
                lockedHint: 'Pre zobrazenie záznamov odomknite aplikáciu.',
                col: {
                    time: 'Čas',
                    code: 'Kód',
                    success: 'Úspech',
                    error: 'Chyba',
                },
            },
        },
        octave: {
            lockIndicator: {
                locked: 'Aplikácia je zamknutá — kliknite pre odomknutie',
                unlocked: 'Aplikácia je odomknutá — kliknite pre zamknutie',
            },
            unlockDialog: {
                title: 'Odomknúť konzolu',
                description: 'Zadajte heslo. Pri zhode server odomkne konzolu v rámci tejto session.',
                placeholder: 'Heslo',
                submit: 'Odomknúť',
                checking: 'Overuje sa…',
                invalid: 'Nesprávne heslo.',
            },
            editor: {
                run: 'Spustiť',
                running: 'Spúšťa sa…',
                clear: 'Vyčistiť editor',
                hint: 'Ctrl+Enter na spustenie',
            },
            output: {
                title: 'Výstup',
                clear: 'Vyčistiť históriu',
                empty: 'Ešte neboli spustené žiadne príkazy.',
                figureAlt: 'Vygenerovaný graf',
            },
            workspace: {
                title: 'Workspace',
                reset: 'Reset',
                empty: 'Workspace je prázdny.',
                more: '… (+{n} ďalších)',
            },
            errors: {
                forbidden: 'Detegovaný zakázaný príkaz.',
                invalidWorkspace: 'Workspace bol poškodený a bol resetovaný.',
                server: 'Chyba servera.',
            },
        },
        anim: {
            parameters: 'Parametre',
            initial: 'Počiatočné podmienky / čas',
            refInput: 'Referenčný vstup',
            simulate: 'Simulovať',
            running: 'Počíta sa…',
            play: 'Prehrať',
            pause: 'Pauza',
            reset: 'Reset',
            speed: 'Rýchlosť',
            noData: 'Spustite simuláciu pre zobrazenie animácie a grafu.',
            pendulum: {
                cartPos: 'Pozícia vozíka [m]',
                angle: 'Uhol kyvadla [rad]',
            },
            ball: {
                position: 'Pozícia guľôčky [m]',
                beamAngle: 'Uhol tyče [rad]',
            },
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
                description: 'Interaktívny prehliadač OpenAPI špecifikácie.',
                downloadPdf: 'Stiahnuť PDF',
                downloadYaml: 'Stiahnuť YAML',
                loading: 'Načítava sa dokumentácia…',
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
            save: 'Save',
            cancel: 'Cancel',
            close: 'Close',
        },
        stats: {
            summary: {
                title: 'Overview',
                loading: 'Loading…',
                runs: 'runs',
            },
            detail: {
                title: 'Detail: {name}',
                loading: 'Loading records…',
                empty: 'No records.',
                col: {
                    time: 'Time',
                    city: 'City',
                    country: 'Country',
                },
            },
            anim: {
                ball: 'Ball & Beam',
                pendulum: 'Pendulum',
            },
            logs: {
                title: 'Log records',
                description: 'Octave REST API call records.',
                download: 'Download CSV',
                loading: 'Loading records…',
                empty: 'No records.',
                lockedHint: 'Unlock the application to view records.',
                col: {
                    time: 'Time',
                    code: 'Code',
                    success: 'Success',
                    error: 'Error',
                },
            },
        },
        octave: {
            lockIndicator: {
                locked: 'Application is locked — click to unlock',
                unlocked: 'Application is unlocked — click to lock',
            },
            unlockDialog: {
                title: 'Unlock console',
                description: 'Enter the password. On match the server unlocks the console for this session.',
                placeholder: 'Password',
                submit: 'Unlock',
                checking: 'Checking…',
                invalid: 'Invalid password.',
            },
            editor: {
                run: 'Run',
                running: 'Running…',
                clear: 'Clear editor',
                hint: 'Ctrl+Enter to run',
            },
            output: {
                title: 'Output',
                clear: 'Clear history',
                empty: 'No commands run yet.',
                figureAlt: 'Generated plot',
            },
            workspace: {
                title: 'Workspace',
                reset: 'Reset',
                empty: 'Workspace is empty.',
                more: '… (+{n} more)',
            },
            errors: {
                forbidden: 'Forbidden command detected.',
                invalidWorkspace: 'Workspace was corrupted and has been reset.',
                server: 'Server error.',
            },
        },
        anim: {
            parameters: 'Parameters',
            initial: 'Initial conditions / time',
            refInput: 'Reference input',
            simulate: 'Simulate',
            running: 'Running…',
            play: 'Play',
            pause: 'Pause',
            reset: 'Reset',
            speed: 'Speed',
            noData: 'Run a simulation to see the animation and chart.',
            pendulum: {
                cartPos: 'Cart position [m]',
                angle: 'Pendulum angle [rad]',
            },
            ball: {
                position: 'Ball position [m]',
                beamAngle: 'Beam angle [rad]',
            },
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
                description: 'Interactive OpenAPI specification viewer.',
                downloadPdf: 'Download PDF',
                downloadYaml: 'Download YAML',
                loading: 'Loading documentation…',
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
