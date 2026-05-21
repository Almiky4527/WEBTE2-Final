<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import { Button } from '@/components/ui/button'

const props = defineProps<{
    playing: boolean
    currentIndex: number
    length: number
    speed: number
    timeAtIndex: number
}>()

const emit = defineEmits<{
    (e: 'toggle'): void
    (e: 'reset'): void
    (e: 'seek', i: number): void
    (e: 'speed', v: number): void
}>()

const { t } = useI18n()
</script>

<template>
    <div class="flex flex-wrap items-center gap-2">
        <Button size="sm" :disabled="length === 0" @click="emit('toggle')">
            {{ playing ? t('anim.pause') : t('anim.play') }}
        </Button>
        <Button size="sm" variant="outline" :disabled="length === 0" @click="emit('reset')">
            {{ t('anim.reset') }}
        </Button>
        <label class="flex items-center gap-1 text-xs text-muted-foreground">
            {{ t('anim.speed') }}
            <select
                class="rounded border border-border bg-background px-1 py-0.5 text-xs"
                :value="props.speed"
                @change="emit('speed', Number(($event.target as HTMLSelectElement).value))"
            >
                <option :value="0.25">0.25×</option>
                <option :value="0.5">0.5×</option>
                <option :value="1">1×</option>
                <option :value="2">2×</option>
                <option :value="4">4×</option>
            </select>
        </label>
        <div class="flex flex-1 items-center gap-2 min-w-[180px]">
            <input
                type="range"
                class="flex-1"
                min="0"
                :max="Math.max(0, length - 1)"
                step="1"
                :value="currentIndex"
                :disabled="length === 0"
                @input="emit('seek', Number(($event.target as HTMLInputElement).value))"
            />
            <span class="w-20 text-right font-mono text-xs tabular-nums text-muted-foreground">
                t = {{ timeAtIndex.toFixed(2) }} s
            </span>
        </div>
    </div>
</template>
