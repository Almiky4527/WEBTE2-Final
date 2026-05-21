<script setup lang="ts">
import { nextTick, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { Button } from '@/components/ui/button'
import ReplEntry from './ReplEntry.vue'

export interface ReplItem {
    id: number
    code: string
    output: string
    stderr: string
    figure: string
    error?: string
    status: 'ok' | 'forbidden' | 'invalid_workspace' | 'server_error'
}

const props = defineProps<{ entries: ReplItem[] }>()
const emit = defineEmits<{ (e: 'clear'): void }>()

const { t } = useI18n()
const scrollHost = ref<HTMLDivElement | null>(null)

watch(() => props.entries.length, async () => {
    await nextTick()

    if (scrollHost.value) {
scrollHost.value.scrollTop = scrollHost.value.scrollHeight
}
})
</script>

<template>
    <div class="flex flex-col gap-2">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold">{{ t('octave.output.title') }}</h2>
            <Button size="sm" variant="ghost" :disabled="entries.length === 0" @click="emit('clear')">
                {{ t('octave.output.clear') }}
            </Button>
        </div>
        <div ref="scrollHost" class="flex max-h-[420px] flex-col gap-2 overflow-y-auto rounded-md border border-border p-2">
            <p v-if="entries.length === 0" class="text-sm text-muted-foreground">{{ t('octave.output.empty') }}</p>
            <ReplEntry v-for="e in entries" :key="e.id" v-bind="e" />
        </div>
    </div>
</template>
