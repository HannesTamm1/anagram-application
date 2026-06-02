# Anagram Application

This project imports a wordbase into a database and finds anagrams for a given word.

## Project structure

- [Backend](Backend) Laravel API and tests
- [Frontend](Frontend) React UI and frontend tests

## Setup

### Backend

1. Install dependencies:

```bash
cd Backend
composer install
```
There may be issues with cache, for that fix go to bootstrap directory, mkdir cache there and run the commands as followed before.

2. Create the environment file if needed:

```bash
cp .env.example .env
```

3. Generate the app key:

```bash
php artisan key:generate
```

4. Configure the database in `.env`
5. Run migrations:

```bash
php artisan migrate
```

6. Start the backend:

```bash
php artisan serve
```

### Frontend

1. Install dependencies:

```bash
cd Frontend
npm install
```

2. Start the frontend:

```bash
npm run dev
```

## API endpoints

Core endpoints:

- `POST /api/words/import`
- `GET /api/anagrams?word=aabits`

Support endpoint used by the frontend:

- `GET /api/words?search=app&per_page=50`

## Architecture note

- `WordSimilarityAlgorithm`
  The anagram matching key is behind [WordSimilarityAlgorithm.php](Backend/app/Contracts/WordSimilarityAlgorithm.php).
  The current implementation is [SortedLettersAlgorithm.php](Backend/app/Services/SortedLettersAlgorithm.php).
  If a different similarity algorithm is needed later, a new implementation can be bound in the service container.

- `WordListParser`
  Imported word list parsing is behind [WordListParser.php](Backend/app/Contracts/WordListParser.php).
  The current implementation is [PlainTextWordListParser.php](Backend/app/Services/PlainTextWordListParser.php).
  This makes it easier to swap plain-text parsing for another format later.

- `WordNormalizer`
  Shared normalization rules live in [WordNormalizer.php](Backend/app/Services/WordNormalizer.php) so they are not repeated across controllers, import code, and tests.

The bindings live in [AppServiceProvider.php](Backend/app/Providers/AppServiceProvider.php).

## DRY and SOLID

The project tries to stay simple while still following common design principles.

- DRY
  Shared word normalization is centralized in `WordNormalizer`.
  Frontend request/response handling is centralized in [api.ts](Frontend/src/lib/api.ts).
  Repeated word-button rendering is shared through [WordButtonList.tsx](Frontend/src/components/WordButtonList.tsx).

- Single Responsibility
  Controllers validate input and return responses.
  Services handle importing, normalization, parsing, and matching logic.

- Open/Closed
  The similarity algorithm and word-list parser can be replaced without rewriting the rest of the application.

## Testing

Run all tests from the repository root:

```bash
npm test
```

## Example flow

1. Import the word list from `https://www.opus.ee/lemmad2013.txt`
2. Search for a word such as `aabits`
3. The backend computes a matching key for the word
4. Matching words are read from the database and returned as anagrams

## Notes

- Backend tests run from in-memory SQLite so they do not depend on a local PostgreSQL setup, it makes things less complicated.
- The frontend uses simple mocked API tests to verify user-facing behavior without real network calls.

## Security notes

- Search and filter input is normalized to lowercase, limited in length, and restricted to letters before it reaches the API queries.
- The word import endpoint only accepts public `http` and `https` URLs. Localhost, private-network targets, URL credentials, and fragments are rejected.
- Import failures now return generic error messages so backend connection details are not exposed to the client.
- For deployed environments, keep `APP_DEBUG=false` so unexpected backend exceptions are not shown to end users.
