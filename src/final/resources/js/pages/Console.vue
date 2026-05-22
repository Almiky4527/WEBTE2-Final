<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import OctaveEditor from '@/components/octave/OctaveEditor.vue'
import OutputPanel from '@/components/octave/OutputPanel.vue'
import type {ReplItem} from '@/components/octave/OutputPanel.vue';
import UnlockDialog from '@/components/octave/UnlockDialog.vue'
import WorkspacePanel from '@/components/octave/WorkspacePanel.vue'
import { Button } from '@/components/ui/button'
import { useOctaveEval } from '@/composables/useOctaveEval'
import { useOctaveUnlock } from '@/composables/useOctaveUnlock'
import { Lock } from 'lucide-vue-next'

const { unlocked } = useOctaveUnlock()

const { t } = useI18n()

const code = ref('')
const entries = ref<ReplItem[]>([])
let nextId = 1

const unlockDialogOpen = ref(false)
let unlockResolver: ((v: boolean) => void) | null = null

function promptForUnlock(): Promise<boolean> {
    unlockDialogOpen.value = true

    return new Promise((resolve) => {
        unlockResolver = resolve
    })
}

function onUnlockSuccess() {
    unlockResolver?.(true)
    unlockResolver = null
}

function onUnlockCancel() {
    unlockResolver?.(false)
    unlockResolver = null
}

const { run, loading } = useOctaveEval(promptForUnlock)

function statusFromResult(r: { success: boolean; status: number; error?: string }): ReplItem['status'] {
    if (r.success) {
return 'ok'
}

    if (r.status === 403) {
return 'forbidden'
}

    if (r.status === 400) {
return 'invalid_workspace'
}

    return 'server_error'
}

async function onRun() {
    const trimmed = code.value.trim()

    if (!trimmed || loading.value) {
return
}

    const result = await run(trimmed)

    if (result.status === 0) {
return
}

    entries.value.push({
        id: nextId++,
        code: trimmed,
        output: result.output,
        stderr: result.stderr,
        figure: result.figure,
        error: result.error,
        status: statusFromResult(result),
    })
}

function onClearHistory() {
 entries.value = [] 
}
function onClearEditor() {
 code.value = '' 
}
</script>

<template>
    <Head :title="t('pages.console.title')" />
    <div class="flex h-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <header>
            <p class="mb-1 text-xs font-medium uppercase tracking-[0.2em] text-muted-foreground">
                {{ t('pages.welcome.brand') }}
            </p>
            <h1 class="text-3xl font-semibold tracking-tight">{{ t('pages.console.title') }}</h1>
            <p class="mt-1 text-muted-foreground">{{ t('pages.console.description') }}</p>
        </header>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[3fr_2fr]">
            <div class="relative">
                <section
                    class="flex flex-col gap-3 rounded-lg border border-border bg-card p-4 shadow-sm"
                    :class="!unlocked ? 'pointer-events-none select-none opacity-30 blur-[1px]' : ''"
                    :inert="!unlocked || undefined"
                >
                    <OctaveEditor v-model="code" :disabled="loading" @run="onRun" />
                    <div class="flex flex-wrap items-center gap-2">
                        <Button :disabled="loading || !code.trim()" @click="onRun">
                            {{ loading ? t('octave.editor.running') : t('octave.editor.run') }}
                        </Button>
                        <Button variant="secondary" :disabled="!code" @click="onClearEditor">
                            {{ t('octave.editor.clear') }}
                        </Button>
                        <span class="text-xs text-muted-foreground">{{ t('octave.editor.hint') }}</span>
                    </div>
                </section>
                <div
                    v-if="!unlocked"
                    class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center gap-2 rounded-md bg-background/40 backdrop-blur-[2px]"
                >
                    <Lock class="h-10 w-10 text-muted-foreground" />
                    <p class="text-sm text-muted-foreground">{{ t('octave.lockedHint') }}</p>
                </div>
            </div>

            <aside class="rounded-lg border border-border bg-card p-4 shadow-sm">
                <WorkspacePanel />
            </aside>
        </div>

        <section class="rounded-lg border border-border bg-card p-4 shadow-sm">
            <OutputPanel :entries="entries" @clear="onClearHistory" />
        </section>

        <UnlockDialog
            v-model:open="unlockDialogOpen"
            @success="onUnlockSuccess"
            @cancel="onUnlockCancel"
        />
    </div>
</template>
