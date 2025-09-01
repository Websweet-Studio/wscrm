import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'

interface AvailabilityResult {
  success: boolean
  available: boolean
  domain: string
  status: string
  loading?: boolean
  error?: string
}

export function useDomainAvailability() {
  const loading = ref(false)
  const results = reactive<Record<string, AvailabilityResult>>({})

  const checkDomainAvailability = async (domain: string): Promise<AvailabilityResult> => {
    if (results[domain]) {
      return results[domain]
    }

    // Set loading state
    results[domain] = {
      success: false,
      available: false,
      domain,
      status: 'checking',
      loading: true
    }

    try {
      const response = await fetch('/api/domains/availability', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({ domain })
      })

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }

      const data = await response.json()
      
      results[domain] = {
        ...data,
        loading: false
      }

      return results[domain]
      
    } catch (error) {
      const errorResult: AvailabilityResult = {
        success: false,
        available: false,
        domain,
        status: 'error',
        loading: false,
        error: error instanceof Error ? error.message : 'Unknown error'
      }
      
      results[domain] = errorResult
      return errorResult
    }
  }

  const checkMultipleDomains = async (domains: string[]): Promise<Record<string, AvailabilityResult>> => {
    loading.value = true
    
    try {
      const promises = domains.map(domain => checkDomainAvailability(domain))
      await Promise.all(promises)
    } finally {
      loading.value = false
    }

    return results
  }

  const getDomainStatus = (domain: string) => {
    return results[domain] || {
      success: false,
      available: false,
      domain,
      status: 'unknown',
      loading: false
    }
  }

  return {
    loading,
    results,
    checkDomainAvailability,
    checkMultipleDomains,
    getDomainStatus
  }
}