<script setup lang="ts">
import { useI18n } from 'vue-i18n'

defineProps<{
    code: string
    output: string
    stderr: string
    figure: string
    error?: string
    status: 'ok' | 'forbidden' | 'invalid_workspace' | 'server_error'
}>()

const { t } = useI18n()
</script>

<template>
    <div class="rounded-md border border-border bg-muted/30 p-3 font-mono text-sm">
        <pre class="mb-2 whitespace-pre-wrap text-muted-foreground">&gt; {{ code }}</pre>
        <pre v-if="output" class="whitespace-pre-wrap">{{ output }}</pre>
        <pre v-if="stderr" class="whitespace-pre-wrap text-destructive">{{ stderr }}</pre>
        <img
            v-if="figure"
            :src="`data:image/png;base64,${figure}`"
            :alt="t('octave.output.figureAlt')"
            class="mt-2 max-w-full rounded border border-border bg-white"
        />
        <p v-if="status === 'forbidden'" class="text-destructive">{{ t('octave.errors.forbidden') }}</p>
        <p v-if="status === 'invalid_workspace'" class="text-destructive">{{ t('octave.errors.invalidWorkspace') }}</p>
        <p v-if="status === 'server_error'" class="text-destructive">{{ error || t('octave.errors.server') }}</p>
    </div>
</template>
