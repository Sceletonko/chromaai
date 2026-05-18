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

## Docker Setup
Pre spustenie projektu cez Docker použite:
```bash
docker-compose up -d --build
```
Aplikácia bude dostupná na `http://localhost:8080`.
