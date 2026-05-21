import { ref } from 'vue'
import { useOctaveUnlock } from './useOctaveUnlock'
import { useOctaveWorkspace } from './useOctaveWorkspace'

export interface EvalResult {
    success: boolean
    output: string
    stderr: string
    figure: string
    error?: string
    status: number
}

export type UnlockPrompt = () => Promise<boolean>

export function useOctaveEval(promptForUnlock: UnlockPrompt) {
    const { unlocked, markLocked } = useOctaveUnlock()
    const { blob: workspace, set: setWorkspace } = useOctaveWorkspace()
    const loading = ref(false)

    async function ensureUnlocked(): Promise<boolean> {
        if (unlocked.value) {
            return true
        }

        return promptForUnlock()
    }

    async function run(code: string): Promise<EvalResult> {
        loading.value = true

        try {
            for (let attempt = 0; attempt < 2; attempt++) {
                const ok = await ensureUnlocked()

                if (!ok) {
                    return { success: false, output: '', stderr: '', figure: '', error: 'Locked', status: 0 }
                }

                const body: Record<string, string> = { code }

                if (workspace.value) {
                    body.workspace = workspace.value
                }

                const resp = await fetch('/api/octave/eval', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                    body: JSON.stringify(body),
                })

                const data = await resp.json().catch(() => ({}))

                if (resp.status === 401) {
                    markLocked()

                    if (attempt === 0) {
                        continue
                    }

                    return { success: false, output: '', stderr: '', figure: '', error: 'Locked', status: 401 }
                }

                if (resp.status === 400 && data?.error === 'Invalid workspace format') {
                    setWorkspace('')

                    return { success: false, output: '', stderr: '', figure: '', error: data.error, status: 400 }
                }

                if (!resp.ok) {
                    return {
                        success: false,
                        output: '',
                        stderr: '',
                        figure: '',
                        error: data?.error || `HTTP ${resp.status}`,
                        status: resp.status,
                    }
                }

                if (typeof data.workspace === 'string') {
                    setWorkspace(data.workspace)
                }

                return {
                    success: !!data.success,
                    output: data.output ?? '',
                    stderr: data.stderr ?? '',
                    figure: data.figure ?? '',
                    status: resp.status,
                }
            }

            return { success: false, output: '', stderr: '', figure: '', error: 'Locked', status: 401 }
        } finally {
            loading.value = false
        }
    }

    return { run, loading }
}
