const LETTERS_ONLY_PATTERN = /^\p{L}+$/u
const SAFE_IMPORT_PROTOCOLS = new Set(['http:', 'https:'])

export const MAX_WORD_LENGTH = 64
export const MAX_IMPORT_URL_LENGTH = 2048

export function normalizeLettersOnlyInput(value: string): string {
  return value.trim().toLocaleLowerCase()
}

export function validateWordInput(value: string, emptyMessage: string): string | null {
  const normalizedValue = normalizeLettersOnlyInput(value)

  if (!normalizedValue) {
    return emptyMessage
  }

  if (normalizedValue.length > MAX_WORD_LENGTH) {
    return `Words must be ${MAX_WORD_LENGTH} characters or fewer.`
  }

  if (!LETTERS_ONLY_PATTERN.test(normalizedValue)) {
    return 'Only letters are allowed.'
  }

  return null
}

export function validateOptionalWordInput(value: string): string | null {
  const normalizedValue = normalizeLettersOnlyInput(value)

  if (!normalizedValue) {
    return null
  }

  return validateWordInput(normalizedValue, 'Enter a word first.')
}

export function validateImportUrlInput(value: string): string | null {
  const trimmedValue = value.trim()

  if (!trimmedValue) {
    return 'Enter a URL first.'
  }

  if (trimmedValue.length > MAX_IMPORT_URL_LENGTH) {
    return `URLs must be ${MAX_IMPORT_URL_LENGTH} characters or fewer.`
  }

  let parsedUrl: URL

  try {
    parsedUrl = new URL(trimmedValue)
  } catch {
    return 'Enter a valid HTTP or HTTPS URL.'
  }

  if (!SAFE_IMPORT_PROTOCOLS.has(parsedUrl.protocol)) {
    return 'Only HTTP and HTTPS URLs are allowed.'
  }

  if (parsedUrl.username || parsedUrl.password) {
    return 'URLs with embedded credentials are not allowed.'
  }

  if (parsedUrl.hash) {
    return 'Remove the URL fragment before importing.'
  }

  return null
}
