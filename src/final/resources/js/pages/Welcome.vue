<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

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
    <div class="flex min-h-screen flex-col bg-[#FDFDFC] p-6 text-[#1b1b18] lg:p-8 dark:bg-[#0a0a0a] dark:text-[#EDEDEC]">
        <header class="mx-auto mb-12 flex w-full max-w-5xl items-center justify-between">
            <div class="text-lg font-semibold">{{ t('pages.welcome.brand') }}</div>
            <nav class="flex items-center gap-3 text-sm">
                <Link
                    href="/console"
                    class="inline-block rounded-sm border border-[#19140035] px-4 py-1.5 hover:border-[#1915014a] dark:border-[#3E3E3A] dark:hover:border-[#62605b]"
                >
                    {{ t('pages.welcome.openApp') }}
                </Link>
            </nav>
        </header>

        <main class="mx-auto w-full max-w-5xl flex-1">
            <section class="mb-12">
                <h1 class="mb-3 text-3xl font-semibold tracking-tight lg:text-4xl">
                    {{ t('pages.welcome.heading') }}
                </h1>
                <p class="max-w-2xl text-[#706f6c] dark:text-[#A1A09A]">
                    {{ t('pages.welcome.lead') }}
                </p>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="f in features"
                    :key="f.href"
                    :href="f.href"
                    class="group rounded-lg border border-[#19140035] bg-white p-5 transition hover:border-[#1915014a] dark:border-[#3E3E3A] dark:bg-[#161615] dark:hover:border-[#62605b]"
                >
                    <h2 class="mb-2 font-medium group-hover:text-[#f53003] dark:group-hover:text-[#FF4433]">
                        {{ t(f.titleKey) }}
                    </h2>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        {{ t(f.descKey) }}
                    </p>
                </Link>
            </section>
        </main>

        <footer class="mx-auto mt-12 w-full max-w-5xl text-xs text-[#706f6c] dark:text-[#A1A09A]">
            {{ t('pages.welcome.footer') }}
        </footer>
    </div>
</template>
