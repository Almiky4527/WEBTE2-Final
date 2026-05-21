import { ref } from 'vue'
import { useOctaveUnlock } from './useOctaveUnlock'

export interface AnimSeries {
    t: number[]
    y: number[][] | number[]
    x: number[][]
}

export interface AnimRunResult {
    success: boolean
    series?: AnimSeries
    error?: string
    status: number
    locked?: boolean
}

function toQuery(params: Record<string, number>): string {
    const usp = new URLSearchParams()

    for (const [k, v] of Object.entries(params)) {
        usp.set(k, String(v))
    }

    return usp.toString()
}

export function useAnimSim(endpoint: '/api/octave/ball' | '/api/octave/pendulum') {
    const { markLocked, unlocked, refreshStatus } = useOctaveUnlock()
    const loading = ref(false)
    const series = ref<AnimSeries | null>(null)
    const errorMsg = ref<string>('')

    function clearSeries() {
        series.value = null
    }

    async function run(params: Record<string, number>): Promise<AnimRunResult> {
        loading.value = true
        errorMsg.value = ''

        try {
            const resp = await fetch(`${endpoint}?${toQuery(params)}`, {
                method: 'GET',
                headers: { Accept: 'application/json' },
            })

            const data = await resp.json().catch(() => ({}))

            if (resp.status === 401) {
                markLocked()

                return { success: false, status: 401, locked: true, error: 'locked' }
            }

            if (resp.ok && data.success && !unlocked.value) {
                unlocked.value = true
            }

            if (!resp.ok || !data.success) {
                errorMsg.value = data.error ?? data.stderr ?? `HTTP ${resp.status}`

                return { success: false, status: resp.status, error: errorMsg.value }
            }

            const s: AnimSeries = { t: data.t, y: data.y, x: data.x }
            series.value = s

            return { success: true, status: 200, series: s }
        } catch (e) {
            const msg = e instanceof Error ? e.message : String(e)
            errorMsg.value = msg

            return { success: false, status: 0, error: msg }
        } finally {
            loading.value = false
        }
    }

    return { run, loading, series, errorMsg, clearSeries, refreshStatus }
}
