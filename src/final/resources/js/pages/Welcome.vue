<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import LocaleSwitcher from '@/components/LocaleSwitcher.vue';
import LockIndicator from '@/components/LockIndicator.vue';

const { t } = useI18n();

const features = [
    { titleKey: 'nav.console', descKey: 'pages.welcome.features.consoleDesc', href: '/console' },
    { titleKey: 'nav.pendulum', descKey: 'pages.welcome.features.pendulumDesc', href: '/pendulum' },
    { titleKey: 'nav.ballBeam', descKey: 'pages.welcome.features.ballBeamDesc', href: '/ball-beam' },
    { titleKey: 'nav.stats', descKey: 'pages.welcome.features.statsDesc', href: '/stats' },
    { titleKey: 'nav.apiDocs', descKey: 'pages.welcome.features.apiDocsDesc', href: '/api-docs' },
];
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="flex min-h-screen flex-col bg-background p-6 text-foreground lg:p-8">
        <header class="mx-auto mb-16 flex w-full max-w-6xl items-center justify-between">
            <div class="text-lg font-semibold text-chrome">{{ t('pages.welcome.brand') }}</div>
            <nav class="flex items-center gap-2 rounded-md border border-border/60 bg-card/60 px-2 py-1 text-sm">
                <LockIndicator />
                <LocaleSwitcher />
            </nav>
        </header>

        <main class="mx-auto w-full max-w-6xl flex-1">
            <section class="mb-16">
                <p class="mb-4 text-xs font-medium uppercase tracking-[0.25em] text-muted-foreground">
                    {{ t('pages.welcome.brand') }}
                </p>
                <h1 class="mb-5 text-5xl font-semibold tracking-tight lg:text-6xl">
                    <span class="text-chrome">{{ t('pages.welcome.heading') }}</span>
                </h1>
                <p class="max-w-2xl text-base text-muted-foreground lg:text-lg">
                    {{ t('pages.welcome.lead') }}
                </p>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="f in features"
                    :key="f.href"
                    :href="f.href"
                    class="group rounded-lg border border-border bg-card p-6 transition hover:border-accent hover:ring-1 hover:ring-accent/30"
                >
                    <h2 class="mb-2 text-base font-medium text-card-foreground">
                        {{ t(f.titleKey) }}
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        {{ t(f.descKey) }}
                    </p>
                </Link>
            </section>
        </main>

        <footer class="mx-auto mt-16 w-full max-w-6xl text-xs text-muted-foreground">
            {{ t('pages.welcome.footer') }}
        </footer>
    </div>
</template>
