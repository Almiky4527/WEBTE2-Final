<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import { Button } from '@/components/ui/button'
import { useOctaveWorkspace } from '@/composables/useOctaveWorkspace'

const { variables, hasWorkspace, reset } = useOctaveWorkspace()
const { t } = useI18n()
</script>

<template>
    <div class="flex flex-col gap-2">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold">{{ t('octave.workspace.title') }}</h2>
            <Button size="sm" variant="ghost" :disabled="!hasWorkspace" @click="reset">
                {{ t('octave.workspace.reset') }}
            </Button>
        </div>
        <div class="rounded-md border border-border p-2 text-sm">
            <p v-if="variables.length === 0" class="text-muted-foreground">{{ t('octave.workspace.empty') }}</p>
            <table v-else class="w-full font-mono">
                <tbody>
                    <tr v-for="v in variables" :key="v.name" class="border-b border-border/40 last:border-0">
                        <td class="py-1 pr-2 align-top font-semibold">{{ v.name }}</td>
                        <td class="py-1 break-words text-muted-foreground">
                            {{ v.value }}<span v-if="v.truncated > 0" class="italic">
                                {{ t('octave.workspace.more', { n: v.truncated }) }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
