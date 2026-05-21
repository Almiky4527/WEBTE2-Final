<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <Head :title="t('pages.console.title')" />
    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <h1 class="text-2xl font-semibold">{{ t('pages.console.title') }}</h1>
        <p class="text-muted-foreground">{{ t('pages.console.description') }}</p>
    </div>
</template>
