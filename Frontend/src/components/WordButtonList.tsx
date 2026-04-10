type WordButtonListProps = {
  words: string[]
  onPickWord: (word: string) => void
}

function WordButtonList({ words, onPickWord }: WordButtonListProps) {
  return (
    <ul className="simple-list">
      {words.map((word) => (
        <li key={word}>
          <button type="button" className="small-button" onClick={() => onPickWord(word)}>
            {word}
          </button>
        </li>
      ))}
    </ul>
  )
}

export default WordButtonList
