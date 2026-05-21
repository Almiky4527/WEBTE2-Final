import { useSessionStorage } from '@vueuse/core'
import { computed } from 'vue'

export interface WorkspaceVar {
    name: string
    value: string
    truncated: number
}

const MAX_VALUE_TOKENS = 10

function summariseValue(raw: string): { value: string; truncated: number } {
    const tokens = raw.split(/\s+/).filter(Boolean)

    if (tokens.length <= MAX_VALUE_TOKENS) {
        return { value: tokens.join(' '), truncated: 0 }
    }

    return {
        value: tokens.slice(0, MAX_VALUE_TOKENS).join(' '),
        truncated: tokens.length - MAX_VALUE_TOKENS,
    }
}

const blob = useSessionStorage<string>('octave_workspace', '')

function parseVariables(text: string): WorkspaceVar[] {
    if (!text) {
return []
}

    const vars: WorkspaceVar[] = []
    const lines = text.split('\n')
    let current: { name: string; lines: string[] } | null = null

    for (const line of lines) {
        const match = line.match(/^#\s*name:\s*(\w+)/)

        if (match) {
            if (current) {
                const summary = summariseValue(current.lines.join(' '))
                vars.push({ name: current.name, ...summary })
            }

            current = { name: match[1], lines: [] }
            continue
        }

        if (current && !line.startsWith('#')) {
            current.lines.push(line)
        }
    }

    if (current) {
        const summary = summariseValue(current.lines.join(' '))
        vars.push({ name: current.name, ...summary })
    }

    return vars
}

export function useOctaveWorkspace() {
    return {
        blob,
        variables: computed(() => parseVariables(blob.value)),
        hasWorkspace: computed(() => blob.value.length > 0),
        set: (value: string) => {
 blob.value = value 
},
        reset: () => {
 blob.value = '' 
},
    }
}
