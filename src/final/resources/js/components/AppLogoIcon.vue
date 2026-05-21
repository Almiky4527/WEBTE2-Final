<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 40 42"
        :class="className"
        fill="none"
        stroke="currentColor"
        stroke-linecap="round"
        stroke-linejoin="round"
        v-bind="$attrs"
    >
        <!-- Inverted pendulum on a cart: rod -->
        <line x1="20" y1="28" x2="29" y2="7" stroke-width="2.5" />
        <!-- Ball at the rod tip -->
        <circle cx="29" cy="6.5" r="3.5" fill="currentColor" stroke="none" />
        <!-- Cart body -->
        <rect x="11" y="27" width="18" height="7" rx="1.2" fill="currentColor" stroke="none" />
        <!-- Cart wheels -->
        <circle cx="15" cy="36" r="2" fill="currentColor" stroke="none" />
        <circle cx="25" cy="36" r="2" fill="currentColor" stroke="none" />
        <!-- Track / beam -->
        <line x1="2" y1="39" x2="38" y2="39" stroke-width="1.6" opacity="0.7" />
    </svg>
</template>
