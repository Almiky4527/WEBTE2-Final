<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    ballPos: number
    beamAngle: number
    refTarget: number
    posRange?: number
}>()

const W = 600
const H = 280

const beamHalfPx = 250
const beamThickness = 12
const ballR = 16

const pivotX = W / 2
const pivotY = H / 2 + 20

const SCALE = computed(() => {
    const span = Math.max(Math.abs(props.refTarget) * 1.2, props.posRange ?? 0, 0.1)

    return (beamHalfPx * 0.9) / span
})

const ballDistPx = computed(() => props.ballPos * SCALE.value)
const ballX = computed(() => pivotX + Math.cos(props.beamAngle) * ballDistPx.value
    + Math.sin(props.beamAngle) * (beamThickness / 2 + ballR))
const ballY = computed(() => pivotY - Math.sin(props.beamAngle) * ballDistPx.value
    - Math.cos(props.beamAngle) * (beamThickness / 2 + ballR))

const refDistPx = computed(() => props.refTarget * SCALE.value)
</script>

<template>
    <svg :viewBox="`0 0 ${W} ${H}`" class="w-full rounded-md border border-border bg-card">
        <polygon
            :points="`${pivotX - 24},${pivotY + 40} ${pivotX + 24},${pivotY + 40} ${pivotX},${pivotY + 4}`"
            fill="#475569" stroke="#94a3b8" stroke-width="1.5"
        />

        <g :transform="`rotate(${(-props.beamAngle * 180) / Math.PI}, ${pivotX}, ${pivotY})`">
            <rect
                :x="pivotX - beamHalfPx"
                :y="pivotY - beamThickness / 2"
                :width="beamHalfPx * 2"
                :height="beamThickness"
                rx="3"
                fill="#e2e8f0"
                stroke="#94a3b8"
                stroke-width="1"
            />
            <line
                :x1="pivotX + refDistPx"
                :y1="pivotY - beamThickness / 2 - 18"
                :x2="pivotX + refDistPx"
                :y2="pivotY - beamThickness / 2"
                stroke="#a78bfa"
                stroke-width="2"
                stroke-dasharray="3,3"
            />
            <text
                :x="pivotX + refDistPx + 4"
                :y="pivotY - beamThickness / 2 - 6"
                fill="#a78bfa"
                font-size="11"
                font-family="ui-monospace, monospace"
            >ref</text>
        </g>

        <circle :cx="ballX" :cy="ballY" :r="ballR" fill="#ef4444"
                stroke="#7f1d1d" stroke-width="2" />
        <circle :cx="pivotX" :cy="pivotY" r="6" fill="#facc15" stroke="#854d0e" stroke-width="1.5" />
    </svg>
</template>
