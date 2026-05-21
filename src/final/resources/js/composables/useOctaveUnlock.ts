import { useSessionStorage } from '@vueuse/core'

// UI-only flag indicating the backend session is unlocked. The actual auth
// lives in an httpOnly Laravel session cookie that JS cannot read.
// A 401 from the backend invalidates this flag.
const unlocked = useSessionStorage<boolean>('octave_unlocked', false)

export interface UnlockResult {
    ok: boolean
    status: number
}

export function useOctaveUnlock() {
    async function attemptUnlock(password: string): Promise<UnlockResult> {
        const resp = await fetch('/api/octave/unlock', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ password }),
        })

        if (resp.ok) {
            unlocked.value = true

            return { ok: true, status: resp.status }
        }

        return { ok: false, status: resp.status }
    }

    function markLocked() {
        unlocked.value = false
    }

    return {
        unlocked,
        attemptUnlock,
        markLocked,
    }
}
