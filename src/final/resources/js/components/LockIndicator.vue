<script setup lang="ts">
import { Lock, LockOpen } from 'lucide-vue-next'
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import UnlockDialog from '@/components/octave/UnlockDialog.vue'
import { Button } from '@/components/ui/button'
import { useOctaveUnlock } from '@/composables/useOctaveUnlock'

const { t } = useI18n()
const { unlocked, clearLock, refreshStatus } = useOctaveUnlock()

const dialogOpen = ref(false)

onMounted(() => {
    refreshStatus()
})

const title = computed(() =>
    unlocked.value ? t('octave.lockIndicator.unlocked') : t('octave.lockIndicator.locked'),
)

function onClick() {
    if (unlocked.value) {
        clearLock()
    } else {
        dialogOpen.value = true
    }
}
</script>

<template>
    <Button
        variant="ghost"
        size="sm"
        class="gap-2"
        :aria-label="title"
        :title="title"
        @click="onClick"
    >
        <LockOpen v-if="unlocked" class="h-4 w-4 text-emerald-500" />
        <Lock v-else class="h-4 w-4 text-muted-foreground" />
    </Button>
    <UnlockDialog v-model:open="dialogOpen" />
</template>
