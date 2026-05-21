<script setup lang="ts">
import { defaultKeymap, history, historyKeymap } from '@codemirror/commands'
import { StreamLanguage } from '@codemirror/language'
import { octave } from '@codemirror/legacy-modes/mode/octave'
import { EditorState } from '@codemirror/state'
import { oneDark } from '@codemirror/theme-one-dark'
import { EditorView, keymap, lineNumbers, highlightActiveLine } from '@codemirror/view'
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'

const props = defineProps<{ modelValue: string; disabled?: boolean }>()
const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void
    (e: 'run'): void
}>()

const host = ref<HTMLDivElement | null>(null)
let view: EditorView | null = null

function createView() {
    if (!host.value) {
return
}

    const state = EditorState.create({
        doc: props.modelValue,
        extensions: [
            lineNumbers(),
            highlightActiveLine(),
            history(),
            keymap.of([
                {
                    key: 'Mod-Enter',
                    preventDefault: true,
                    run: () => {
 emit('run');

 return true 
},
                },
                ...defaultKeymap,
                ...historyKeymap,
            ]),
            StreamLanguage.define(octave),
            oneDark,
            EditorView.updateListener.of((u) => {
                if (u.docChanged) {
emit('update:modelValue', u.state.doc.toString())
}
            }),
            EditorView.editable.of(!props.disabled),
        ],
    })
    view = new EditorView({ state, parent: host.value })
}

onMounted(createView)
onBeforeUnmount(() => {
 view?.destroy(); view = null 
})

watch(() => props.modelValue, (next) => {
    if (view && next !== view.state.doc.toString()) {
        view.dispatch({ changes: { from: 0, to: view.state.doc.length, insert: next } })
    }
})
</script>

<template>
    <div ref="host" class="overflow-hidden rounded-md border border-border min-h-[160px]" />
</template>
