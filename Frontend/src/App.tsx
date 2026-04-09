import { useEffect, useState } from 'react'

type ApiResponse = {
  message: string
  status: string
}

function App() {
  const [message, setMessage] = useState('Loading...')
  const [error, setError] = useState('')

  useEffect(() => {
    const fetchMessage = async () => {
      try {
        const apiUrl = import.meta.env.VITE_API_URL
        const response = await fetch(`${apiUrl}/api/message`)

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`)
        }

        const data: ApiResponse = await response.json()
        setMessage(data.message)
      } catch (err) {
        console.error(err)
        setError('Could not connect to Laravel backend')
      }
    }

    fetchMessage()
  }, [])

  return (
    <main style={{ padding: '2rem', fontFamily: 'Arial, sans-serif' }}>
      <h1>React Frontend</h1>
      {error ? <p>{error}</p> : <p>{message}</p>}
    </main>
  )
}

export default App