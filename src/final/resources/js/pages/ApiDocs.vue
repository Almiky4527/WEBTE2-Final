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
    <div class="flex h-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="mb-1 text-xs font-medium uppercase tracking-[0.2em] text-muted-foreground">
                    {{ t('pages.welcome.brand') }}
                </p>
                <h1 class="text-3xl font-semibold tracking-tight">{{ t('pages.apiDocs.title') }}</h1>
                <p class="mt-1 text-muted-foreground">{{ t('pages.apiDocs.description') }}</p>
            </div>
            <div class="flex gap-2">
                <a
                    href="/api/docs.yaml"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center rounded-md border border-border px-3 py-1.5 text-sm hover:border-accent hover:bg-accent/10"
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
        <div ref="container" class="swagger-shell rounded-lg border border-border border-t-2 border-t-accent bg-card shadow-sm"></div>
    </div>
</template>

<style>
.swagger-shell { min-height: 400px; }
.swagger-shell .swagger-ui .topbar { display: none; }
.swagger-shell .swagger-ui { font-family: inherit; color: var(--foreground); background: transparent; }

/* Info header */
.swagger-shell .swagger-ui .info,
.swagger-shell .swagger-ui .info .title,
.swagger-shell .swagger-ui .info p,
.swagger-shell .swagger-ui .info li,
.swagger-shell .swagger-ui .info table,
.swagger-shell .swagger-ui .info a { color: var(--foreground); }
.swagger-shell .swagger-ui .info a { text-decoration: underline; }
.swagger-shell .swagger-ui .info .base-url { color: var(--muted-foreground); }

/* Scheme container (Servers + Authorize) */
.swagger-shell .swagger-ui .scheme-container {
    background: transparent;
    box-shadow: none;
    border-bottom: 1px solid var(--border);
    padding: 16px 20px;
}
.swagger-shell .swagger-ui .scheme-container .schemes-title,
.swagger-shell .swagger-ui .scheme-container label { color: var(--foreground); }
.swagger-shell .swagger-ui select,
.swagger-shell .swagger-ui input[type='text'],
.swagger-shell .swagger-ui input[type='password'],
.swagger-shell .swagger-ui input[type='email'],
.swagger-shell .swagger-ui textarea {
    background: var(--background);
    color: var(--foreground);
    border: 1px solid var(--border);
    box-shadow: none;
}

/* Tag sections */
.swagger-shell .swagger-ui .opblock-tag {
    color: var(--foreground);
    border-bottom: 1px solid var(--border);
}
.swagger-shell .swagger-ui .opblock-tag small,
.swagger-shell .swagger-ui .opblock-tag .markdown p { color: var(--muted-foreground); }
.swagger-shell .swagger-ui .opblock-tag:hover { background: var(--muted); }

/* Operation blocks — keep method colors, retheme card bg + text */
.swagger-shell .swagger-ui .opblock {
    background: var(--card);
    border: 1px solid var(--border);
    box-shadow: none;
}
.swagger-shell .swagger-ui .opblock .opblock-summary {
    border-bottom: 1px solid var(--border);
}
.swagger-shell .swagger-ui .opblock .opblock-summary-path,
.swagger-shell .swagger-ui .opblock .opblock-summary-path__deprecated,
.swagger-shell .swagger-ui .opblock .opblock-summary-description,
.swagger-shell .swagger-ui .opblock .opblock-summary-operation-id { color: var(--foreground); }
.swagger-shell .swagger-ui .opblock.opblock-get { background: color-mix(in srgb, var(--card) 90%, hsl(210 40% 65%) 10%); }
.swagger-shell .swagger-ui .opblock.opblock-post { background: color-mix(in srgb, var(--card) 90%, hsl(220 15% 50%) 10%); }
.swagger-shell .swagger-ui .opblock.opblock-put,
.swagger-shell .swagger-ui .opblock.opblock-patch,
.swagger-shell .swagger-ui .opblock.opblock-delete { background: var(--card); }

/* Operation body */
.swagger-shell .swagger-ui .opblock-body,
.swagger-shell .swagger-ui .opblock-section-header,
.swagger-shell .swagger-ui .opblock .opblock-section-header {
    background: var(--card);
    box-shadow: none;
}
.swagger-shell .swagger-ui .opblock-section-header h4,
.swagger-shell .swagger-ui .opblock-description-wrapper p,
.swagger-shell .swagger-ui .opblock-external-docs-wrapper p,
.swagger-shell .swagger-ui .opblock-title_normal p,
.swagger-shell .swagger-ui table thead tr th,
.swagger-shell .swagger-ui table tbody tr td,
.swagger-shell .swagger-ui .response-col_status,
.swagger-shell .swagger-ui .response-col_description__inner div.markdown,
.swagger-shell .swagger-ui .response-col_description__inner div.markdown p,
.swagger-shell .swagger-ui .parameter__name,
.swagger-shell .swagger-ui .parameter__type,
.swagger-shell .swagger-ui .parameter__in,
.swagger-shell .swagger-ui .parameter__deprecated,
.swagger-shell .swagger-ui label,
.swagger-shell .swagger-ui .tab li,
.swagger-shell .swagger-ui .responses-inner h4,
.swagger-shell .swagger-ui .responses-inner h5 { color: var(--foreground); }

.swagger-shell .swagger-ui .parameter__name.required::after { color: var(--destructive); }
.swagger-shell .swagger-ui table thead tr th { border-bottom-color: var(--border); }
.swagger-shell .swagger-ui table tbody tr td { border-color: var(--border); }

/* Code & highlight blocks */
.swagger-shell .swagger-ui .highlight-code,
.swagger-shell .swagger-ui .microlight,
.swagger-shell .swagger-ui pre,
.swagger-shell .swagger-ui code {
    background: var(--muted) !important;
    color: var(--foreground) !important;
}
.swagger-shell .swagger-ui .opblock-body pre.microlight,
.swagger-shell .swagger-ui .responses-inner pre {
    background: var(--muted) !important;
    color: var(--foreground) !important;
}

/* Buttons */
.swagger-shell .swagger-ui .btn {
    background: var(--secondary);
    color: var(--secondary-foreground);
    border: 1px solid var(--border);
    box-shadow: none;
}
.swagger-shell .swagger-ui .btn.execute {
    background: var(--primary);
    color: var(--primary-foreground);
    border-color: var(--primary);
}
.swagger-shell .swagger-ui .btn.authorize {
    background: transparent;
    color: var(--accent);
    border-color: var(--accent);
}
.swagger-shell .swagger-ui .btn.authorize svg { fill: var(--accent); }

/* Models */
.swagger-shell .swagger-ui section.models {
    background: var(--card);
    border: 1px solid var(--border);
}
.swagger-shell .swagger-ui section.models h4,
.swagger-shell .swagger-ui section.models .model-title,
.swagger-shell .swagger-ui .model,
.swagger-shell .swagger-ui .model-title,
.swagger-shell .swagger-ui .prop-name,
.swagger-shell .swagger-ui .prop-type { color: var(--foreground); }
.swagger-shell .swagger-ui section.models.is-open h4 { border-bottom-color: var(--border); }

/* Try it out + response area */
.swagger-shell .swagger-ui .try-out__btn { background: var(--secondary); color: var(--secondary-foreground); border-color: var(--border); }
.swagger-shell .swagger-ui .responses-table .response { background: transparent; }
.swagger-shell .swagger-ui .response-col_links,
.swagger-shell .swagger-ui .response-col_description { color: var(--foreground); }

/* Tabs */
.swagger-shell .swagger-ui .tab li.active,
.swagger-shell .swagger-ui .tab li button.tablinks.active { color: var(--accent); }

/* SVG icons */
.swagger-shell .swagger-ui .opblock .opblock-summary-control svg,
.swagger-shell .swagger-ui .expand-operation svg,
.swagger-shell .swagger-ui section.models .model-toggle svg { fill: var(--muted-foreground); }

/* Hide model section divider color */
.swagger-shell .swagger-ui .dialog-ux .modal-ux {
    background: var(--card);
    color: var(--foreground);
    border: 1px solid var(--border);
}
</style>
