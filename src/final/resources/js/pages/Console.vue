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
    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <header>
            <h1 class="text-2xl font-semibold">{{ t('pages.console.title') }}</h1>
            <p class="text-muted-foreground">{{ t('pages.console.description') }}</p>
        </header>

        <div class="grid flex-1 grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="flex flex-col gap-2 lg:col-span-2">
                <OctaveEditor v-model="code" :disabled="loading" @run="onRun" />
                <div class="flex flex-wrap items-center gap-2">
                    <Button :disabled="loading || !code.trim()" @click="onRun">
                        {{ loading ? t('octave.editor.running') : t('octave.editor.run') }}
                    </Button>
                    <Button variant="outline" :disabled="!code" @click="onClearEditor">
                        {{ t('octave.editor.clear') }}
                    </Button>
                    <span class="text-xs text-muted-foreground">{{ t('octave.editor.hint') }}</span>
                </div>
                <OutputPanel :entries="entries" @clear="onClearHistory" />
            </div>

            <aside class="flex flex-col gap-2">
                <WorkspacePanel />
            </aside>
        </div>

        <UnlockDialog
            v-model:open="unlockDialogOpen"
            @success="onUnlockSuccess"
            @cancel="onUnlockCancel"
        />
    </div>
</template>
