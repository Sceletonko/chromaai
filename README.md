# ChromaAi

Moderná webová aplikácia postavená na Laravele.

## Inštalácia a spustenie

Projekt využíva Laravel Sail (Docker). Pre spustenie použite:

```bash
docker compose up -d
```

Po spustení bude aplikácia dostupná na `http://localhost`.

### Migrácie databázy

```bash
docker compose exec chromaai.test php artisan migrate
```

## Funkcie

- **Laravel 11+**
- **Docker (Sail)** s PHP 8.4 a MySQL
- **Bez Vite**: Frontend využíva Tailwind CSS cez CDN pre okamžitý profesionálny vzhľad bez nutnosti kompilácie.
- **Moderný dizajn**: Profesionálna úvodná stránka ChromaAi.
