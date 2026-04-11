import { fireEvent, render, screen, waitFor } from '@testing-library/react'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import App from '../App'
import { fetchAnagrams, fetchWords, importWords } from '../lib/api'

vi.mock('../lib/api', () => ({
  fetchAnagrams: vi.fn(),
  fetchWords: vi.fn(),
  importWords: vi.fn(),
}))

const mockedFetchAnagrams = vi.mocked(fetchAnagrams)
const mockedFetchWords = vi.mocked(fetchWords)
const mockedImportWords = vi.mocked(importWords)

const streamWord = {
  id: 1,
  word: 'stream',
  signature: 'aemrst',
  created_at: '2026-04-10T12:00:00Z',
  updated_at: '2026-04-10T12:00:00Z',
}

const masterWord = {
  id: 2,
  word: 'master',
  signature: 'aemrst',
  created_at: '2026-04-10T12:00:00Z',
  updated_at: '2026-04-10T12:00:00Z',
}

const tamersWord = {
  id: 3,
  word: 'tamers',
  signature: 'aemrst',
  created_at: '2026-04-10T12:00:00Z',
  updated_at: '2026-04-10T12:00:00Z',
}

describe('App', () => {
  beforeEach(() => {
    vi.clearAllMocks()

    mockedFetchAnagrams.mockResolvedValue({
      word: 'stream',
      anagrams: ['master', 'tamers'],
      count: 2,
    })

    mockedFetchWords.mockResolvedValue({
      words: [streamWord, masterWord],
      total: 2,
    })

    mockedImportWords.mockResolvedValue({
      message: 'Wordbase imported successfully.',
      inserted: 2,
    })
  })

  it('shows the first results when the page loads', async () => {
    render(<App />)

    expect(await screen.findByText('2 total')).toBeInTheDocument()
    expect(mockedFetchAnagrams).toHaveBeenCalledWith('stream')
    expect(mockedFetchWords).toHaveBeenCalledWith('')
    expect(screen.getAllByRole('button', { name: 'master' })).toHaveLength(2)
    expect(screen.getByRole('heading', { name: 'Results for "stream"' })).toBeInTheDocument()
  })

  it('lets you search for another word', async () => {
    mockedFetchAnagrams
      .mockResolvedValueOnce({
        word: 'stream',
        anagrams: ['master', 'tamers'],
        count: 2,
      })
      .mockResolvedValueOnce({
        word: 'plates',
        anagrams: ['petals', 'staple'],
        count: 2,
      })

    render(<App />)

    await screen.findByText('2 total')

    fireEvent.change(screen.getByPlaceholderText('Enter a word'), {
      target: { value: 'plates' },
    })
    fireEvent.click(screen.getByRole('button', { name: 'Search' }))

    await waitFor(() => {
      expect(mockedFetchAnagrams).toHaveBeenLastCalledWith('plates')
    })

    expect(await screen.findByRole('button', { name: 'petals' })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: 'Results for "plates"' })).toBeInTheDocument()
  })

  it('shows the updated words after an import', async () => {
    mockedFetchWords
      .mockResolvedValueOnce({
        words: [streamWord],
        total: 1,
      })
      .mockResolvedValueOnce({
        words: [streamWord, masterWord, tamersWord],
        total: 3,
      })

    mockedFetchAnagrams
      .mockResolvedValueOnce({
        word: 'stream',
        anagrams: ['master'],
        count: 1,
      })
      .mockResolvedValueOnce({
        word: 'stream',
        anagrams: ['master', 'tamers'],
        count: 2,
      })

    render(<App />)

    await screen.findByText('1 total')

    fireEvent.click(screen.getByRole('button', { name: 'Import' }))

    expect(await screen.findByText('Wordbase imported successfully. Inserted: 2.')).toBeInTheDocument()
    expect(mockedImportWords).toHaveBeenCalledWith('https://www.opus.ee/lemmad2013.txt')
    expect(await screen.findByText('3 total')).toBeInTheDocument()
    expect(screen.getAllByRole('button', { name: 'tamers' }).length).toBeGreaterThan(0)
  })

  it('shows an error when search fails', async () => {
    mockedFetchAnagrams.mockResolvedValueOnce({
      word: 'stream',
      anagrams: ['master'],
      count: 1,
    })
    mockedFetchAnagrams.mockRejectedValueOnce(new Error('Word lookup failed.'))

    render(<App />)

    await screen.findByText('2 total')

    fireEvent.change(screen.getByPlaceholderText('Enter a word'), {
      target: { value: 'plates' },
    })
    fireEvent.click(screen.getByRole('button', { name: 'Search' }))

    expect(await screen.findByText('Word lookup failed.')).toBeInTheDocument()
  })

  it('blocks invalid search input before calling the API', async () => {
    render(<App />)

    await screen.findByText('2 total')

    fireEvent.change(screen.getByPlaceholderText('Enter a word'), {
      target: { value: 'bad-input' },
    })
    fireEvent.click(screen.getByRole('button', { name: 'Search' }))

    expect(await screen.findByText('Only letters are allowed.')).toBeInTheDocument()
    expect(mockedFetchAnagrams).toHaveBeenCalledTimes(1)
  })

  it('blocks unsafe import URLs before calling the API', async () => {
    render(<App />)

    await screen.findByText('2 total')

    fireEvent.change(screen.getByPlaceholderText('Enter a word list URL'), {
      target: { value: 'https://user:secret@example.com/words.txt' },
    })
    fireEvent.click(screen.getByRole('button', { name: 'Import' }))

    expect(await screen.findByText('URLs with embedded credentials are not allowed.')).toBeInTheDocument()
    expect(mockedImportWords).not.toHaveBeenCalled()
  })
})
