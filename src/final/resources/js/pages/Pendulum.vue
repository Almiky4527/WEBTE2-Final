<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import PendulumScene from '@/components/anim/PendulumScene.vue'
import PlaybackControls from '@/components/anim/PlaybackControls.vue'
import TimeChart from '@/components/anim/TimeChart.vue'
import UnlockDialog from '@/components/octave/UnlockDialog.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useAnimSim } from '@/composables/useAnimSim'

const { t } = useI18n()

const params = reactive({
    M: 0.5,
    m: 0.2,
    b: 0.1,
    I: 0.006,
    g: 9.81,
    l: 0.3,
    r: 0.2,
    pos0: 0,
    angle0: 0,
    t_end: 10,
    dt: 0.05,
})

const { run, loading, series, errorMsg } = useAnimSim('/api/octave/pendulum')
const unlockOpen = ref(false)
let pendingSubmit = false

const currentIndex = ref(0)
const playing = ref(false)
const speed = ref(1)
let raf = 0
let lastTs = 0

const length = computed(() => series.value?.t.length ?? 0)
const dt = computed(() => {
    const t = series.value?.t

    return t && t.length > 1 ? t[1] - t[0] : 0.05
})
const timeAtIndex = computed(() => series.value?.t[currentIndex.value] ?? 0)

const cartPositions = computed<number[]>(() =>
    (series.value?.x ?? []).map((row) => row[0])
)
const poleAngles = computed<number[]>(() =>
    (series.value?.x ?? []).map((row) => row[2])
)
const cartPosRange = computed(() => {
    const vals = cartPositions.value

    if (!vals.length) {
return 0
}

    let lo = Infinity, hi = -Infinity

    for (const v of vals) {
        if (v < lo) {
lo = v
}

        if (v > hi) {
hi = v
}
    }

    return Math.max(Math.abs(lo), Math.abs(hi))
})

const chartSeries = computed(() => {
    if (!series.value) {
return []
}

    return [
        { label: t('anim.pendulum.cartPos'), color: '#3b82f6', values: cartPositions.value },
        { label: t('anim.pendulum.angle'),   color: '#ef4444', values: poleAngles.value },
    ]
})

function loop(ts: number) {
    if (!playing.value || !series.value) {
return
}

    if (!lastTs) {
lastTs = ts
}

    const dtMs = ts - lastTs
    lastTs = ts

    const step = (dtMs / 1000) * speed.value / dt.value
    let next = currentIndex.value + step

    if (next >= length.value - 1) {
        next = length.value - 1
        playing.value = false
    }

    currentIndex.value = next
    raf = requestAnimationFrame(loop)
}

function startLoop() {
    cancelAnimationFrame(raf)
    lastTs = 0
    raf = requestAnimationFrame(loop)
}

function togglePlay() {
    if (!series.value) {
return
}

    if (currentIndex.value >= length.value - 1) {
currentIndex.value = 0
}

    playing.value = !playing.value

    if (playing.value) {
startLoop()
} else {
cancelAnimationFrame(raf)
}
}

function resetPlay() {
    playing.value = false
    cancelAnimationFrame(raf)
    currentIndex.value = 0
}

function seek(i: number) {
    currentIndex.value = i
}

function setSpeed(v: number) {
    speed.value = v
}

async function onSubmit() {
    const result = await run({ ...params })

    if (result.locked) {
        pendingSubmit = true
        unlockOpen.value = true

        return
    }

    if (result.success) {
        currentIndex.value = 0
        playing.value = true
        startLoop()
    }
}

async function onUnlockSuccess() {
    if (pendingSubmit) {
        pendingSubmit = false
        await onSubmit()
    }
}

function onUnlockCancel() {
    pendingSubmit = false
}

onBeforeUnmount(() => cancelAnimationFrame(raf))

const idx = computed(() => {
    const i = Math.round(currentIndex.value)

    return Math.min(Math.max(0, i), Math.max(0, length.value - 1))
})
</script>

<template>
    <Head :title="t('pages.pendulum.title')" />
    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <header>
            <h1 class="text-2xl font-semibold">{{ t('pages.pendulum.title') }}</h1>
            <p class="text-muted-foreground">{{ t('pages.pendulum.description') }}</p>
        </header>

        <div class="grid flex-1 grid-cols-1 gap-4 lg:grid-cols-3">
            <form class="flex flex-col gap-3 rounded-md border border-border p-3 lg:col-span-1"
                  @submit.prevent="onSubmit">
                <h2 class="text-sm font-semibold">{{ t('anim.parameters') }}</h2>
                <div class="grid grid-cols-2 gap-2">
                    <div class="flex flex-col gap-1">
                        <Label for="p-M">M (kg)</Label>
                        <Input id="p-M" v-model.number="params.M" type="number" step="0.01" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <Label for="p-m">m (kg)</Label>
                        <Input id="p-m" v-model.number="params.m" type="number" step="0.01" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <Label for="p-b">b</Label>
                        <Input id="p-b" v-model.number="params.b" type="number" step="0.01" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <Label for="p-I">I</Label>
                        <Input id="p-I" v-model.number="params.I" type="number" step="0.001" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <Label for="p-g">g (m/s²)</Label>
                        <Input id="p-g" v-model.number="params.g" type="number" step="0.01" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <Label for="p-l">l (m)</Label>
                        <Input id="p-l" v-model.number="params.l" type="number" step="0.01" />
                    </div>
                </div>

                <h2 class="mt-2 text-sm font-semibold">{{ t('anim.initial') }}</h2>
                <div class="grid grid-cols-2 gap-2">
                    <div class="flex flex-col gap-1">
                        <Label for="p-r">{{ t('anim.refInput') }} (m)</Label>
                        <Input id="p-r" v-model.number="params.r" type="number" step="0.01" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <Label for="p-pos0">pos₀ (m)</Label>
                        <Input id="p-pos0" v-model.number="params.pos0" type="number" step="0.01" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <Label for="p-a0">θ₀ (rad)</Label>
                        <Input id="p-a0" v-model.number="params.angle0" type="number" step="0.01" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <Label for="p-tend">t_end (s)</Label>
                        <Input id="p-tend" v-model.number="params.t_end" type="number" step="0.5" min="0.5" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <Label for="p-dt">dt (s)</Label>
                        <Input id="p-dt" v-model.number="params.dt" type="number" step="0.01" min="0.005" />
                    </div>
                </div>

                <Button type="submit" :disabled="loading">
                    {{ loading ? t('anim.running') : t('anim.simulate') }}
                </Button>
                <p v-if="errorMsg" class="text-sm text-destructive">{{ errorMsg }}</p>
            </form>

            <div class="flex flex-col gap-3 lg:col-span-2">
                <PendulumScene
                    :cart-pos="cartPositions[idx] ?? params.pos0"
                    :pole-angle="poleAngles[idx] ?? params.angle0"
                    :pole-length="params.l * 2"
                    :ref-target="params.r"
                    :pos-range="cartPosRange"
                />
                <PlaybackControls
                    :playing="playing"
                    :current-index="idx"
                    :length="length"
                    :speed="speed"
                    :time-at-index="timeAtIndex"
                    @toggle="togglePlay"
                    @reset="resetPlay"
                    @seek="seek"
                    @speed="setSpeed"
                />
                <div v-if="series" class="rounded-md border border-border p-2">
                    <TimeChart :t="series.t" :series="chartSeries" :current-index="idx" />
                </div>
                <p v-else class="text-sm text-muted-foreground">{{ t('anim.noData') }}</p>
            </div>
        </div>

        <UnlockDialog
            v-model:open="unlockOpen"
            @success="onUnlockSuccess"
            @cancel="onUnlockCancel"
        />
    </div>
</template>
