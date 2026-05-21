<script setup lang="ts">
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { useOctaveUnlock } from '@/composables/useOctaveUnlock'

const { t } = useI18n()
const { attemptUnlock } = useOctaveUnlock()

const props = defineProps<{ open: boolean }>()
const emit = defineEmits<{
    (e: 'success'): void
    (e: 'cancel'): void
    (e: 'update:open', value: boolean): void
}>()

const value = ref('')
const submitting = ref(false)
const errorMsg = ref('')

watch(() => props.open, (now) => {
    if (now) {
        value.value = ''
        errorMsg.value = ''
    }
})

async function onSubmit() {
    const password = value.value

    if (!password || submitting.value) {
        return
    }

    submitting.value = true
    errorMsg.value = ''

    try {
        const result = await attemptUnlock(password)

        if (result.ok) {
            emit('success')
            emit('update:open', false)
        } else {
            errorMsg.value = t('octave.unlockDialog.invalid')
            value.value = ''
        }
    } finally {
        submitting.value = false
    }
}

function onCancel() {
    emit('cancel')
    emit('update:open', false)
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ t('octave.unlockDialog.title') }}</DialogTitle>
                <DialogDescription>{{ t('octave.unlockDialog.description') }}</DialogDescription>
            </DialogHeader>
            <Input
                v-model="value"
                type="password"
                autocomplete="off"
                :placeholder="t('octave.unlockDialog.placeholder')"
                :disabled="submitting"
                @keydown.enter.prevent="onSubmit"
            />
            <p v-if="errorMsg" class="text-sm text-destructive">{{ errorMsg }}</p>
            <DialogFooter>
                <Button variant="outline" :disabled="submitting" @click="onCancel">{{ t('common.cancel') }}</Button>
                <Button :disabled="!value || submitting" @click="onSubmit">
                    {{ submitting ? t('octave.unlockDialog.checking') : t('octave.unlockDialog.submit') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
