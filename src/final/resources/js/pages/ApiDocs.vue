<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const SWAGGER_CSS = 'https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.css';
const SWAGGER_JS = 'https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.js';

const loading = ref(true);
const container = ref<HTMLDivElement | null>(null);

declare global {
    interface Window {
        SwaggerUIBundle?: (opts: Record<string, unknown>) => unknown;
    }
}

function ensureStylesheet(href: string) {
    if (document.querySelector(`link[data-swagger="${href}"]`)) return;
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = href;
    link.dataset.swagger = href;
    document.head.appendChild(link);
}

function loadScript(src: string): Promise<void> {
    if (window.SwaggerUIBundle) return Promise.resolve();
    const existing = document.querySelector<HTMLScriptElement>(`script[data-swagger="${src}"]`);
    if (existing) {
        return new Promise((resolve, reject) => {
            existing.addEventListener('load', () => resolve());
            existing.addEventListener('error', () => reject(new Error('Failed to load Swagger UI')));
        });
    }
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = src;
        script.async = true;
        script.dataset.swagger = src;
        script.addEventListener('load', () => resolve());
        script.addEventListener('error', () => reject(new Error('Failed to load Swagger UI')));
        document.body.appendChild(script);
    });
}

onMounted(async () => {
    ensureStylesheet(SWAGGER_CSS);
    await loadScript(SWAGGER_JS);
    if (!container.value || !window.SwaggerUIBundle) return;
    window.SwaggerUIBundle({
        url: '/api/docs.yaml',
        domNode: container.value,
        deepLinking: true,
        docExpansion: 'list',
        defaultModelsExpandDepth: 0,
        tryItOutEnabled: true,
    });
    loading.value = false;
});

onBeforeUnmount(() => {
    if (!container.value) return;
    while (container.value.firstChild) container.value.removeChild(container.value.firstChild);
});
</script>

<template>
    <Head :title="t('pages.apiDocs.title')" />
    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold">{{ t('pages.apiDocs.title') }}</h1>
                <p class="text-muted-foreground">{{ t('pages.apiDocs.description') }}</p>
            </div>
            <div class="flex gap-2">
                <a
                    href="/api/docs.yaml"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center rounded-md border px-3 py-1.5 text-sm hover:bg-accent"
                >
                    {{ t('pages.apiDocs.downloadYaml') }}
                </a>
                <a
                    href="/api/docs.pdf"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center rounded-md bg-primary px-3 py-1.5 text-sm text-primary-foreground hover:opacity-90"
                >
                    {{ t('pages.apiDocs.downloadPdf') }}
                </a>
            </div>
        </div>

        <div v-if="loading" class="text-sm text-muted-foreground">{{ t('pages.apiDocs.loading') }}</div>
        <div ref="container" class="swagger-shell rounded-md border bg-white"></div>
    </div>
</template>

<style>
.swagger-shell .swagger-ui .topbar { display: none; }
.swagger-shell .swagger-ui { font-family: inherit; }
.swagger-shell { min-height: 400px; }
</style>
