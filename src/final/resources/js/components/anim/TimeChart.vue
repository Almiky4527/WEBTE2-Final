<script setup lang="ts">
import { computed } from 'vue'

interface Series {
    label: string
    color: string
    values: number[]
}

const props = defineProps<{
    t: number[]
    series: Series[]
    currentIndex: number
    height?: number
}>()

const W = 600
const PAD_L = 44
const PAD_R = 12
const PAD_T = 12
const PAD_B = 28
const H = computed(() => props.height ?? 220)

const tMin = computed(() => (props.t.length ? props.t[0] : 0))
const tMax = computed(() => (props.t.length ? props.t[props.t.length - 1] : 1))

const range = computed(() => {
    let lo = Infinity, hi = -Infinity

    for (const s of props.series) {
        for (const v of s.values) {
            if (v < lo) {
lo = v
}

            if (v > hi) {
hi = v
}
        }
    }

    if (!isFinite(lo) || !isFinite(hi)) {
return { lo: -1, hi: 1 }
}

    if (lo === hi) {
 lo -= 1; hi += 1 
}

    const pad = (hi - lo) * 0.08

    return { lo: lo - pad, hi: hi + pad }
})

function xFor(t: number): number {
    const span = tMax.value - tMin.value || 1

    return PAD_L + ((t - tMin.value) / span) * (W - PAD_L - PAD_R)
}

function yFor(v: number): number {
    const span = range.value.hi - range.value.lo || 1

    return PAD_T + (1 - (v - range.value.lo) / span) * (H.value - PAD_T - PAD_B)
}

function pathFor(values: number[]): string {
    let d = ''

    for (let i = 0; i < values.length; i++) {
        const x = xFor(props.t[i])
        const y = yFor(values[i])
        d += (i === 0 ? 'M' : 'L') + x.toFixed(1) + ',' + y.toFixed(1) + ' '
    }

    return d
}

const cursorX = computed(() => {
    const i = Math.min(Math.max(0, props.currentIndex), props.t.length - 1)

    return xFor(props.t[i] ?? 0)
})

const yTicks = computed(() => {
    const { lo, hi } = range.value
    const n = 4
    const out: { value: number; y: number }[] = []

    for (let i = 0; i <= n; i++) {
        const v = lo + ((hi - lo) * i) / n
        out.push({ value: v, y: yFor(v) })
    }

    return out
})

const xTicks = computed(() => {
    const n = 5
    const out: { value: number; x: number }[] = []

    for (let i = 0; i <= n; i++) {
        const v = tMin.value + ((tMax.value - tMin.value) * i) / n
        out.push({ value: v, x: xFor(v) })
    }

    return out
})
</script>

<template>
    <svg :viewBox="`0 0 ${W} ${H}`" class="w-full" role="img" aria-label="time chart">
        <rect :x="PAD_L" :y="PAD_T" :width="W - PAD_L - PAD_R" :height="H - PAD_T - PAD_B"
              fill="hsl(var(--muted) / 0.2)" stroke="hsl(var(--border))" />

        <g class="text-xs" fill="currentColor" opacity="0.65" font-family="ui-monospace, monospace">
            <g v-for="(tk, i) in yTicks" :key="'y' + i">
                <line :x1="PAD_L" :x2="W - PAD_R" :y1="tk.y" :y2="tk.y"
                      stroke="currentColor" stroke-opacity="0.12" />
                <text :x="PAD_L - 6" :y="tk.y + 3" text-anchor="end">{{ tk.value.toFixed(2) }}</text>
            </g>
            <g v-for="(tk, i) in xTicks" :key="'x' + i">
                <line :x1="tk.x" :x2="tk.x" :y1="H - PAD_B" :y2="H - PAD_B + 3" stroke="currentColor" />
                <text :x="tk.x" :y="H - PAD_B + 16" text-anchor="middle">{{ tk.value.toFixed(2) }}</text>
            </g>
        </g>

        <path v-for="s in series" :key="s.label" :d="pathFor(s.values)"
              fill="none" :stroke="s.color" stroke-width="1.5" />

        <line :x1="cursorX" :x2="cursorX" :y1="PAD_T" :y2="H - PAD_B"
              stroke="hsl(var(--primary))" stroke-width="1" stroke-dasharray="3,3" />

        <g class="text-xs" font-family="ui-sans-serif, system-ui">
            <g v-for="(s, i) in series" :key="'leg' + i" :transform="`translate(${PAD_L + 8 + i * 110}, ${PAD_T + 6})`">
                <rect width="10" height="10" :fill="s.color" />
                <text x="14" y="9" fill="currentColor">{{ s.label }}</text>
            </g>
        </g>
    </svg>
</template>
