<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Lock } from 'lucide-vue-next';
import { onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useOctaveUnlock } from '@/composables/useOctaveUnlock';

interface SummaryItem {
    anim: string;
    count: number;
}

interface DetailRow {
    created_at: string | null;
    city: string;
    country: string;
}

interface LogRow {
    id: number;
    code: string;
    success: boolean;
    error: string | null;
    created_at: string | null;
}

const { t } = useI18n();
const { unlocked, refreshStatus, markLocked } = useOctaveUnlock();

const summary = ref<SummaryItem[]>([]);
const summaryError = ref('');
const loadingSummary = ref(false);

const openAnim = ref<string | null>(null);
const detailRows = ref<DetailRow[]>([]);
const detailError = ref('');
const loadingDetail = ref(false);

const logRows = ref<LogRow[]>([]);
const logsError = ref('');
const loadingLogs = ref(false);

async function loadSummary() {
    loadingSummary.value = true;
    summaryError.value = '';

    try {
        const resp = await fetch('/api/stats/summary', { headers: { Accept: 'application/json' } });

        if (!resp.ok) {
            throw new Error(`HTTP ${resp.status}`);
        }

        const data = await resp.json();
        summary.value = data.items ?? [];
    } catch (e) {
        summaryError.value = (e as Error).message;
    } finally {
        loadingSummary.value = false;
    }
}

async function toggleDetail(anim: string) {
    if (openAnim.value === anim) {
        openAnim.value = null;
        detailRows.value = [];

        return;
    }

    openAnim.value = anim;
    detailRows.value = [];
    detailError.value = '';
    loadingDetail.value = true;

    try {
        const resp = await fetch(`/api/stats/detail/${encodeURIComponent(anim)}`, {
            headers: { Accept: 'application/json' },
        });

        if (!resp.ok) {
            throw new Error(`HTTP ${resp.status}`);
        }

        const data = await resp.json();
        detailRows.value = data.rows ?? [];
    } catch (e) {
        detailError.value = (e as Error).message;
    } finally {
        loadingDetail.value = false;
    }
}

async function loadLogs() {
    if (!unlocked.value) {
        logRows.value = [];

        return;
    }

    loadingLogs.value = true;
    logsError.value = '';

    try {
        const resp = await fetch('/api/octave/logs', {
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
        });

        if (resp.status === 401) {
            markLocked();
            logRows.value = [];

            return;
        }

        if (!resp.ok) {
            throw new Error(`HTTP ${resp.status}`);
        }

        const data = await resp.json();
        logRows.value = data.rows ?? [];
    } catch (e) {
        logsError.value = (e as Error).message;
    } finally {
        loadingLogs.value = false;
    }
}

function formatDate(iso: string | null): string {
    if (!iso) {
        return '—';
    }

    const d = new Date(iso);

    return Number.isNaN(d.getTime()) ? iso : d.toLocaleString();
}

function downloadCsv() {
    if (!unlocked.value) {
        return;
    }

    window.open('/api/octave/logs.csv', '_blank');
}

function animLabel(anim: string): string {
    const key = `stats.anim.${anim}`;
    const translated = t(key);

    return translated === key ? anim : translated;
}

watch(unlocked, (now) => {
    if (now) {
        loadLogs();
    } else {
        logRows.value = [];
        logsError.value = '';
    }
});

onMounted(async () => {
    await refreshStatus();
    await loadSummary();

    if (unlocked.value) {
        await loadLogs();
    }
});
</script>

<template>
    <Head :title="t('pages.stats.title')" />
    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <header>
            <h1 class="text-2xl font-semibold">{{ t('pages.stats.title') }}</h1>
            <p class="text-muted-foreground">{{ t('pages.stats.description') }}</p>
        </header>

        <section class="flex flex-col gap-3">
            <h2 class="text-lg font-semibold">{{ t('stats.summary.title') }}</h2>
            <p v-if="loadingSummary" class="text-sm text-muted-foreground">{{ t('stats.summary.loading') }}</p>
            <p v-else-if="summaryError" class="text-sm text-destructive">{{ summaryError }}</p>
            <div v-else class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <Card
                    v-for="item in summary"
                    :key="item.anim"
                    class="cursor-pointer transition-colors hover:bg-muted/40"
                    :class="{ 'ring-2 ring-ring': openAnim === item.anim }"
                    @click="toggleDetail(item.anim)"
                >
                    <CardHeader>
                        <CardTitle class="text-base">{{ animLabel(item.anim) }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-3xl font-bold tabular-nums">{{ item.count }}</p>
                        <p class="text-xs text-muted-foreground">{{ t('stats.summary.runs') }}</p>
                    </CardContent>
                </Card>
            </div>

            <div v-if="openAnim" class="flex flex-col gap-2 rounded-md border border-border p-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold">{{ t('stats.detail.title', { name: animLabel(openAnim) }) }}</h3>
                    <Button size="sm" variant="ghost" @click="openAnim = null">{{ t('common.close') }}</Button>
                </div>
                <p v-if="loadingDetail" class="text-sm text-muted-foreground">{{ t('stats.detail.loading') }}</p>
                <p v-else-if="detailError" class="text-sm text-destructive">{{ detailError }}</p>
                <p v-else-if="detailRows.length === 0" class="text-sm text-muted-foreground">{{ t('stats.detail.empty') }}</p>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-muted-foreground">
                            <tr>
                                <th class="px-2 py-1 font-medium">{{ t('stats.detail.col.time') }}</th>
                                <th class="px-2 py-1 font-medium">{{ t('stats.detail.col.city') }}</th>
                                <th class="px-2 py-1 font-medium">{{ t('stats.detail.col.country') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, i) in detailRows" :key="i" class="border-t border-border/40">
                                <td class="px-2 py-1 font-mono text-xs">{{ formatDate(row.created_at) }}</td>
                                <td class="px-2 py-1">{{ row.city || '—' }}</td>
                                <td class="px-2 py-1">{{ row.country || '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="flex flex-col gap-3">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-lg font-semibold">{{ t('stats.logs.title') }}</h2>
                <Button :disabled="!unlocked" @click="downloadCsv">{{ t('stats.logs.download') }}</Button>
            </div>
            <p class="text-sm text-muted-foreground">{{ t('stats.logs.description') }}</p>

            <div class="relative rounded-md border border-border">
                <div :class="['overflow-x-auto', !unlocked ? 'pointer-events-none select-none opacity-30 blur-[1px]' : '']">
                    <table class="w-full text-sm">
                        <thead class="text-left text-muted-foreground">
                            <tr>
                                <th class="px-2 py-1 font-medium">{{ t('stats.logs.col.time') }}</th>
                                <th class="px-2 py-1 font-medium">{{ t('stats.logs.col.code') }}</th>
                                <th class="px-2 py-1 font-medium">{{ t('stats.logs.col.success') }}</th>
                                <th class="px-2 py-1 font-medium">{{ t('stats.logs.col.error') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!unlocked">
                                <td colspan="4" class="px-2 py-12" />
                            </tr>
                            <tr v-else-if="loadingLogs">
                                <td colspan="4" class="px-2 py-3 text-muted-foreground">{{ t('stats.logs.loading') }}</td>
                            </tr>
                            <tr v-else-if="logsError">
                                <td colspan="4" class="px-2 py-3 text-destructive">{{ logsError }}</td>
                            </tr>
                            <tr v-else-if="logRows.length === 0">
                                <td colspan="4" class="px-2 py-3 text-muted-foreground">{{ t('stats.logs.empty') }}</td>
                            </tr>
                            <template v-else>
                                <tr v-for="row in logRows" :key="row.id" class="border-t border-border/40 align-top">
                                    <td class="px-2 py-1 font-mono text-xs whitespace-nowrap">{{ formatDate(row.created_at) }}</td>
                                    <td class="px-2 py-1 font-mono text-xs whitespace-pre-wrap break-all">{{ row.code }}</td>
                                    <td class="px-2 py-1">
                                        <span :class="row.success ? 'text-emerald-500' : 'text-destructive'">
                                            {{ row.success ? '✓' : '✗' }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-1 text-xs text-muted-foreground whitespace-pre-wrap break-all">{{ row.error || '—' }}</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="!unlocked"
                    class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center gap-2 rounded-md bg-background/40 backdrop-blur-[2px]"
                >
                    <Lock class="h-10 w-10 text-muted-foreground" />
                    <p class="text-sm text-muted-foreground">{{ t('stats.logs.lockedHint') }}</p>
                </div>
            </div>
        </section>
    </div>
</template>
