<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    cartPos: number
    poleAngle: number
    poleLength: number
    refTarget: number
    posRange?: number
}>()

const W = 600
const H = 320

// Auto-scale so cart fills ~70% of width even for small displacements.
const SCALE = computed(() => {
    const span = Math.max(Math.abs(props.refTarget) * 1.5, props.posRange ?? 0, 0.1)

    return (W * 0.7) / (2 * span)
})

// Pole visual length is independent of position scale - keep it 100-180px regardless.
const POLE_PX = computed(() => {
    const ideal = props.poleLength * 220

    return Math.min(180, Math.max(80, ideal))
})

const cartX = computed(() => W / 2 + props.cartPos * SCALE.value)
const trackY = H - 70
const cartW = 80
const cartH = 36

const poleEndX = computed(() => cartX.value + Math.sin(props.poleAngle) * POLE_PX.value)
const poleEndY = computed(() => trackY - Math.cos(props.poleAngle) * POLE_PX.value)

const refX = computed(() => W / 2 + props.refTarget * SCALE.value)
</script>

<template>
    <svg :viewBox="`0 0 ${W} ${H}`" class="w-full rounded-md border border-border bg-card">
        <line x1="0" :y1="trackY + cartH / 2" :x2="W" :y2="trackY + cartH / 2"
              stroke="#64748b" stroke-width="2" />

        <line :x1="refX" :x2="refX" :y1="trackY - POLE_PX - 10" :y2="trackY + cartH"
              stroke="#a78bfa" stroke-dasharray="4,4" stroke-opacity="0.8" />
        <text :x="refX + 6" :y="trackY - POLE_PX - 4" fill="#a78bfa" font-size="11"
              font-family="ui-monospace, monospace">ref</text>

        <rect :x="cartX - cartW / 2" :y="trackY - cartH / 2" :width="cartW" :height="cartH"
              rx="4" fill="#3b82f6" stroke="#1e3a8a" stroke-width="2" />
        <circle :cx="cartX - cartW / 2 + 14" :cy="trackY + cartH / 2" r="7"
                fill="#0f172a" stroke="#94a3b8" stroke-width="2" />
        <circle :cx="cartX + cartW / 2 - 14" :cy="trackY + cartH / 2" r="7"
                fill="#0f172a" stroke="#94a3b8" stroke-width="2" />

        <line :x1="cartX" :y1="trackY" :x2="poleEndX" :y2="poleEndY"
              stroke="#e2e8f0" stroke-width="5" stroke-linecap="round" />
        <circle :cx="poleEndX" :cy="poleEndY" r="12" fill="#ef4444"
                stroke="#7f1d1d" stroke-width="2" />
        <circle :cx="cartX" :cy="trackY" r="5" fill="#facc15" />
    </svg>
</template>
