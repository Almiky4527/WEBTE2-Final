<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/components/ui/button';
import { setLocale, type Locale } from '@/i18n';

const { locale, t } = useI18n();

const next = computed<Locale>(() => (locale.value === 'sk' ? 'en' : 'sk'));

const code = computed(() => (locale.value === 'sk' ? 'SVK' : 'ENG'));

const nextLabel = computed(() =>
    t('common.language') + ': ' + t(next.value === 'sk' ? 'common.slovak' : 'common.english'),
);

function toggle() {
    setLocale(next.value);
}
</script>

<template>
    <Button
        variant="ghost"
        size="sm"
        :aria-label="nextLabel"
        :title="nextLabel"
        class="gap-2"
        @click="toggle"
    >
        <!-- Slovakia: white / blue / red horizontal bands with coat of arms -->
        <svg
            v-if="locale === 'sk'"
            class="h-4 w-6 rounded-sm ring-1 ring-black/10"
            viewBox="0 0 30 20"
            aria-hidden="true"
        >
            <rect width="30" height="6.67" y="0" fill="#ffffff" />
            <rect width="30" height="6.67" y="6.67" fill="#0b4ea2" />
            <rect width="30" height="6.66" y="13.34" fill="#ee1c25" />
            <!-- Coat of arms -->
            <g transform="translate(3 3)">
                <path d="M0,0 L9,0 L9,7 Q9,13 4.5,14.5 Q0,13 0,7 Z" fill="#ffffff" />
                <path d="M0.7,0.7 L8.3,0.7 L8.3,7 Q8.3,12.3 4.5,13.6 Q0.7,12.3 0.7,7 Z" fill="#ee1c25" />
                <!-- three blue hills inside shield -->
                <path d="M0.7,11.4 Q2.2,9.6 3.6,11.4 Q4.5,9.2 5.4,11.4 Q6.8,9.6 8.3,11.4 L8.3,12.3 Q4.5,13.6 0.7,12.3 Z" fill="#0b4ea2" />
                <!-- white double cross -->
                <rect x="4.1" y="3" width="0.8" height="6.5" fill="#ffffff" />
                <rect x="3" y="4.4" width="3" height="0.8" fill="#ffffff" />
                <rect x="2.5" y="6.3" width="4" height="0.8" fill="#ffffff" />
            </g>
        </svg>
        <!-- England: St George's Cross -->
        <svg
            v-else
            class="h-4 w-6 rounded-sm ring-1 ring-black/10"
            viewBox="0 0 30 18"
            preserveAspectRatio="none"
            aria-hidden="true"
        >
            <rect width="30" height="18" fill="#ffffff" />
            <rect x="12" width="6" height="18" fill="#ce1124" />
            <rect y="6" width="30" height="6" fill="#ce1124" />
        </svg>
        <span class="text-xs font-medium tracking-wide">{{ code }}</span>
    </Button>
</template>
