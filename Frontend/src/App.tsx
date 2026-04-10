import { useEffect, useState } from 'react'
import type { FormEvent } from 'react'
import './App.css'
import { fetchAnagrams, fetchWords, importWords, type AnagramResponse, type WordRecord } from './lib/api'
import WordButtonList from './components/WordButtonList'

const DEFAULT_WORD = 'stream'
const DEFAULT_IMPORT_URL = 'https://www.opus.ee/lemmad2013.txt'
const QUICK_WORDS = ['aabits', 'mapp', 'karp']

function getMessage(error: unknown, fallback: string): string {
  if (error instanceof Error && error.message) {
    return error.message
  }

  return fallback
}

function App() {
  const [query, setQuery] = useState(DEFAULT_WORD)
  const [searchedWord, setSearchedWord] = useState(DEFAULT_WORD)
  const [anagrams, setAnagrams] = useState<string[]>([])
  const [searchLoading, setSearchLoading] = useState(false)
  const [searchError, setSearchError] = useState('')

  const [wordFilter, setWordFilter] = useState('')
  const [words, setWords] = useState<WordRecord[]>([])
  const [wordsLoading, setWordsLoading] = useState(false)
  const [wordsError, setWordsError] = useState('')
  const [totalWords, setTotalWords] = useState(0)
  const [importUrl, setImportUrl] = useState(DEFAULT_IMPORT_URL)
  const [importLoading, setImportLoading] = useState(false)
  const [importMessage, setImportMessage] = useState('')
  const [importError, setImportError] = useState('')

  async function searchWord(word: string) {
    const trimmedWord = word.trim()

    if (!trimmedWord) {
      setSearchError('Enter a word first.')
      setAnagrams([])
      return
    }

    setSearchLoading(true)
    setSearchError('')
    setSearchedWord(trimmedWord)

    try {
      const response: AnagramResponse = await fetchAnagrams(trimmedWord)
      setAnagrams(response.anagrams)
    } catch (error: unknown) {
      setSearchError(getMessage(error, 'Could not search for anagrams.'))
      setAnagrams([])
    } finally {
      setSearchLoading(false)
    }
  }

  async function loadWords(search: string) {
    setWordsLoading(true)
    setWordsError('')

    try {
      const response = await fetchWords(search)
      setWords(response.words)
      setTotalWords(response.total)
    } catch (error: unknown) {
      setWordsError(getMessage(error, 'Could not load words from the database.'))
      setWords([])
    } finally {
      setWordsLoading(false)
    }
  }

  async function handleImport() {
    const trimmedUrl = importUrl.trim()

    if (!trimmedUrl) {
      setImportError('Enter a URL first.')
      setImportMessage('')
      return
    }

    setImportLoading(true)
    setImportError('')
    setImportMessage('')

    try {
      const response = await importWords(trimmedUrl)
      setImportMessage(`${response.message} Inserted: ${response.inserted}.`)
      await loadWords(wordFilter)
      if (query.trim()) {
        await searchWord(query)
      }
    } catch (error: unknown) {
      setImportError(getMessage(error, 'Could not import words from that URL.'))
    } finally {
      setImportLoading(false)
    }
  }

  useEffect(() => {
    void searchWord(DEFAULT_WORD)
  }, [])

  useEffect(() => {
    void loadWords(wordFilter)
  }, [wordFilter])

  function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    void searchWord(query)
  }

  function handleImportSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    void handleImport()
  }

  function handlePickWord(word: string) {
    setQuery(word)
    void searchWord(word)
  }

  return (
    <main className="app">
      <div className="container">
        <h1>Anagram finder</h1>
        <p className="description">Search for anagrams using words from the database.</p>

        <section className="section">
          <h2>Import words</h2>
          <form className="search-form" onSubmit={handleImportSubmit}>
            <input
              type="url"
              value={importUrl}
              onChange={(event) => setImportUrl(event.target.value)}
              placeholder="Enter a word list URL"
              autoComplete="off"
            />
            <button type="submit" disabled={importLoading}>
              {importLoading ? 'Importing...' : 'Import'}
            </button>
          </form>

          {importMessage ? <p className="success-text">{importMessage}</p> : null}
          {importError ? <p className="error-text">{importError}</p> : null}
        </section>

        <form className="search-form" onSubmit={handleSubmit}>
          <input
            type="text"
            value={query}
            onChange={(event) => setQuery(event.target.value)}
            placeholder="Enter a word"
            autoComplete="off"
          />
          <button type="submit" disabled={searchLoading}>
            {searchLoading ? 'Searching...' : 'Search'}
          </button>
        </form>

        <div className="simple-links">
          {QUICK_WORDS.map((word) => (
            <button key={word} type="button" className="small-button" onClick={() => handlePickWord(word)}>
              {word}
            </button>
          ))}
        </div>

        <section className="section">
          <h2>Results for "{searchedWord}"</h2>

          {searchError ? <p className="error-text">{searchError}</p> : null}
          {searchLoading ? <p className="section-text">Loading...</p> : null}
          {!searchLoading && !searchError && anagrams.length === 0 ? (
            <p className="section-text">No anagrams found.</p>
          ) : null}
          {anagrams.length > 0 ? <WordButtonList words={anagrams} onPickWord={handlePickWord} /> : null}
        </section>

        <section className="section">
          <h2>Words in database</h2>
          <p className="section-text">{totalWords} total</p>

          <input
            type="search"
            value={wordFilter}
            onChange={(event) => setWordFilter(event.target.value)}
            placeholder="Filter words"
            autoComplete="off"
          />

          {wordsError ? <p className="error-text">{wordsError}</p> : null}
          {wordsLoading ? <p className="section-text">Loading words...</p> : null}
          {!wordsLoading && !wordsError && words.length === 0 ? <p className="section-text">No words found.</p> : null}
          {words.length > 0 ? (
            <WordButtonList
              words={words.map((word) => word.word)}
              onPickWord={handlePickWord}
            />
          ) : null}
        </section>
      </div>
    </main>
  )
}

export default App
