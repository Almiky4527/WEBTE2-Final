import { useSessionStorage } from '@vueuse/core'

// UI-only flag indicating the backend session is unlocked. The actual auth
// lives in an httpOnly session cookie that JS cannot read.
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

    async function clearLock(): Promise<void> {
        try {
            await fetch('/api/octave/lock', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            })
        } finally {
            unlocked.value = false
        }
    }

    async function refreshStatus(): Promise<void> {
        try {
            const resp = await fetch('/api/octave/status', {
                method: 'GET',
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            })

            if (!resp.ok) {
return
}

            const data = await resp.json().catch(() => null)

            if (data && typeof data.unlocked === 'boolean') {
                unlocked.value = data.unlocked
            }
        } catch {
            // ignore network errors; cached flag stays as-is
        }
    }

    return {
        unlocked,
        attemptUnlock,
        markLocked,
        clearLock,
        refreshStatus,
    }
}
