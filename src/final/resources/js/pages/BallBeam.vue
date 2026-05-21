<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import BallBeamScene from '@/components/anim/BallBeamScene.vue'
import PlaybackControls from '@/components/anim/PlaybackControls.vue'
import TimeChart from '@/components/anim/TimeChart.vue'
import UnlockDialog from '@/components/octave/UnlockDialog.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useAnimSim } from '@/composables/useAnimSim'

const { t } = useI18n()

const params = reactive({
    m: 0.111,
    R: 0.015,
    g: 9.8,
    J: 9.99e-6,
    r: 0.25,
    pos0: 0,
    angle0: 0,
    t_end: 5,
    dt: 0.02,
})

const { run, loading, series, errorMsg } = useAnimSim('/api/octave/ball')
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

    return t && t.length > 1 ? t[1] - t[0] : 0.02
})
const timeAtIndex = computed(() => series.value?.t[currentIndex.value] ?? 0)

const ballPositions = computed<number[]>(() =>
    (series.value?.x ?? []).map((row) => row[0])
)
const beamAngles = computed<number[]>(() =>
    (series.value?.x ?? []).map((row) => row[2])
)
const ballPosRange = computed(() => {
    const vals = ballPositions.value

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
        { label: t('anim.ball.position'), color: '#3b82f6', values: ballPositions.value },
        { label: t('anim.ball.beamAngle'), color: '#ef4444', values: beamAngles.value },
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
    <Head :title="t('pages.ballBeam.title')" />
    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <header>
            <h1 class="text-2xl font-semibold">{{ t('pages.ballBeam.title') }}</h1>
            <p class="text-muted-foreground">{{ t('pages.ballBeam.description') }}</p>
        </header>

        <div class="grid flex-1 grid-cols-1 gap-4 lg:grid-cols-3">
            <form class="flex flex-col gap-3 rounded-md border border-border p-3 lg:col-span-1"
                  @submit.prevent="onSubmit">
                <h2 class="text-sm font-semibold">{{ t('anim.parameters') }}</h2>
                <div class="grid grid-cols-2 gap-2">
                    <div class="flex flex-col gap-1">
                        <Label for="b-m">m (kg)</Label>
                        <Input id="b-m" v-model.number="params.m" type="number" step="0.001" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <Label for="b-R">R (m)</Label>
                        <Input id="b-R" v-model.number="params.R" type="number" step="0.001" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <Label for="b-g">g (m/s²)</Label>
                        <Input id="b-g" v-model.number="params.g" type="number" step="0.01" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <Label for="b-J">J</Label>
                        <Input id="b-J" v-model.number="params.J" type="number" step="0.000001" />
                    </div>
                </div>

                <h2 class="mt-2 text-sm font-semibold">{{ t('anim.initial') }}</h2>
                <div class="grid grid-cols-2 gap-2">
                    <div class="flex flex-col gap-1">
                        <Label for="b-r">{{ t('anim.refInput') }} (m)</Label>
                        <Input id="b-r" v-model.number="params.r" type="number" step="0.01" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <Label for="b-pos0">pos₀ (m)</Label>
                        <Input id="b-pos0" v-model.number="params.pos0" type="number" step="0.01" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <Label for="b-a0">α₀ (rad)</Label>
                        <Input id="b-a0" v-model.number="params.angle0" type="number" step="0.01" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <Label for="b-tend">t_end (s)</Label>
                        <Input id="b-tend" v-model.number="params.t_end" type="number" step="0.5" min="0.5" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <Label for="b-dt">dt (s)</Label>
                        <Input id="b-dt" v-model.number="params.dt" type="number" step="0.005" min="0.005" />
                    </div>
                </div>

                <Button type="submit" :disabled="loading">
                    {{ loading ? t('anim.running') : t('anim.simulate') }}
                </Button>
                <p v-if="errorMsg" class="text-sm text-destructive">{{ errorMsg }}</p>
            </form>

            <div class="flex flex-col gap-3 lg:col-span-2">
                <BallBeamScene
                    :ball-pos="ballPositions[idx] ?? params.pos0"
                    :beam-angle="beamAngles[idx] ?? params.angle0"
                    :ref-target="params.r"
                    :pos-range="ballPosRange"
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
