# ChromaAi - Webová aplikácia

Moderná AI platforma s profesionálnym dizajnom.

## Obsah
- `index.php`: Úvodná stránka s vyhľadávacím poľom a moderným dizajnom.
- `chat.php`: Placeholder pre budúcu chatovaciu funkcionalitu.
- `logo.png`: Logo projektu použité v dizajne a ako favicon.
- `.github/workflows/deploy.yml`: Automatické nasadenie na server pri pushnutí do vetvy `main`.

## Použité technológie
- PHP
- Bootstrap 5 (cez CDN)
- Google Fonts (Inter)
- Docker

## Features
- **Sign Up**: Now requires Name, Email, and Password with confirmation.
- **2FA Login**: Every sign-in requires a verification code sent to your email.
- **AI Models**: Support for multiple free models via OpenRouter (Llama 3, Mistral, Gemma, Phi-3, Zephyr, Gemini).
- **Supabase Storage**: Files are uploaded directly to Supabase Storage.

## Setup
1. **Database Migration**: Run `init_db.php` in your browser or terminal to update the database schema.
2. **Environment Variables**: Update your `.env` file with:
   - SMTP credentials for email verification.
   - `OPENROUTER_API_KEY` for AI models.
   - `SUPABASE_URL`, `SUPABASE_KEY`, and `SUPABASE_STORAGE_BUCKET` for file storage.

## Docker Setup
Pre spustenie projektu cez Docker použite:
```bash
docker-compose up -d --build
```
Aplikácia bude dostupná na `http://localhost:8080`.
