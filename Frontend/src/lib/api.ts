export type AnagramResponse = {
  word: string
  anagrams: string[]
  count: number
}

export type WordRecord = {
  id: number
  word: string
  signature: string
  created_at: string
  updated_at: string
}

type WordsResponse = {
  words: WordRecord[]
  total: number
}

export type ImportWordsResponse = {
  message: string
  inserted: number
}

const SAFE_API_PROTOCOLS = new Set(['http:', 'https:'])

function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === 'object' && value !== null
}

function getApiBaseUrl(): string {
  const envUrl = import.meta.env.VITE_API_URL?.trim()
  const fallbackUrl = 'http://localhost:8000'
  const selectedUrl = envUrl && envUrl.length > 0 ? envUrl : fallbackUrl

  try {
    const parsedUrl = new URL(selectedUrl)

    if (
      !SAFE_API_PROTOCOLS.has(parsedUrl.protocol) ||
      parsedUrl.username ||
      parsedUrl.password ||
      parsedUrl.search ||
      parsedUrl.hash
    ) {
      throw new Error('Unsafe API base URL.')
    }

    return parsedUrl.toString().replace(/\/+$/, '')
  } catch {
    return fallbackUrl
  }
}

function buildApiUrl(path: string): string {
  return new URL(path, `${getApiBaseUrl()}/`).toString()
}

function getErrorMessage(payload: unknown, status: number): string {
  if (status >= 500) {
    return 'The server could not process the request.'
  }

  if (isRecord(payload)) {
    if (typeof payload.message === 'string' && payload.message.length > 0) {
      return payload.message
    }

    if (isRecord(payload.errors)) {
      const firstError = Object.values(payload.errors)
        .flatMap((messages) => {
          if (!Array.isArray(messages)) {
            return []
          }

          return messages.filter((message): message is string => typeof message === 'string')
        })
        .at(0)

      if (firstError) {
        return firstError
      }
    }
  }

  return `Request failed with status ${status}.`
}

async function parseResponse<T>(response: Response): Promise<T> {
  const contentType = response.headers.get('content-type') ?? ''
  const payload = contentType.includes('application/json') ? await response.json() : null

  if (!response.ok) {
    throw new Error(getErrorMessage(payload, response.status))
  }

  return payload as T
}

async function requestJson<T>(path: string, init?: RequestInit): Promise<T> {
  const headers = {
    Accept: 'application/json',
    ...(init?.body ? { 'Content-Type': 'application/json' } : {}),
    ...init?.headers,
  }

  const response = await fetch(buildApiUrl(path), {
    ...init,
    headers,
  })

  return parseResponse<T>(response)
}

export function fetchAnagrams(word: string): Promise<AnagramResponse> {
  const params = new URLSearchParams({ word })

  return requestJson<AnagramResponse>(`/api/anagrams?${params.toString()}`)
}

export async function fetchWords(search = ''): Promise<WordsResponse> {
  const params = new URLSearchParams()

  if (search.trim()) {
    params.set('search', search.trim())
  }
  params.set('per_page', '50')

  const suffix = params.size > 0 ? `?${params.toString()}` : ''
  const response = await requestJson<{
    data: WordRecord[]
    total: number
  }>(`/api/words${suffix}`)

  return {
    words: response.data,
    total: response.total,
  }
}

export function importWords(url: string): Promise<ImportWordsResponse> {
  return requestJson<ImportWordsResponse>('/api/words/import', {
    method: 'POST',
    body: JSON.stringify({ url }),
  })
}
