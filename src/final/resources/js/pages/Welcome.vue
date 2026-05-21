<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

const features = [
    { title: 'Console', description: 'Run CAS commands.', href: '/console' },
    { title: 'Pendulum', description: 'Inverted pendulum simulation.', href: '/pendulum' },
    { title: 'Ball & Beam', description: 'Ball on beam simulation.', href: '/ball-beam' },
    { title: 'Stats', description: 'Usage statistics.', href: '/stats' },
    { title: 'API Docs', description: 'OpenAPI reference.', href: '/api-docs' },
];
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="flex min-h-screen flex-col bg-[#FDFDFC] p-6 text-[#1b1b18] lg:p-8 dark:bg-[#0a0a0a] dark:text-[#EDEDEC]">
        <header class="mx-auto mb-12 flex w-full max-w-5xl items-center justify-between">
            <div class="text-lg font-semibold">WEBTE2 — CAS Simulator</div>
            <nav class="flex items-center gap-3 text-sm">
                <Link
                    href="/console"
                    class="inline-block rounded-sm border border-[#19140035] px-4 py-1.5 hover:border-[#1915014a] dark:border-[#3E3E3A] dark:hover:border-[#62605b]"
                >
                    Open app
                </Link>
            </nav>
        </header>

        <main class="mx-auto w-full max-w-5xl flex-1">
            <section class="mb-12">
                <h1 class="mb-3 text-3xl font-semibold tracking-tight lg:text-4xl">
                    Dynamic system simulator
                </h1>
                <p class="max-w-2xl text-[#706f6c] dark:text-[#A1A09A]">
                    Inverted pendulum and ball-and-beam models with a CAS-backed
                    console, usage stats and OpenAPI docs.
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
                        {{ f.title }}
                    </h2>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        {{ f.description }}
                    </p>
                </Link>
            </section>
        </main>

        <footer class="mx-auto mt-12 w-full max-w-5xl text-xs text-[#706f6c] dark:text-[#A1A09A]">
            WEBTE2 — final project
        </footer>
    </div>
</template>
