# BaseLinker helpdesk integration (Symfony 6)

## How to run the project

1. **Install dependencies**
   ```bash
   composer install
   ```

2. **Configure environment**
   - Copy `.env` to `.env.local` (or create `.env.local`) and set at least:
   - `BASE_LINKER_TOKEN` – your API token from BaseLinker (Account & other → My account → API)

3. **Run the app**
   - Web server:
     ```bash
     php -S localhost:8000 -t public
     ```
     or with [Symfony CLI](https://symfony.com/download): `symfony server:start`
   - Open `http://localhost:8000`

4. **BaseLinker commands**
   ```bash
   # Test API connection
   php bin/console base_linker:test-connection

   # List available marketplaces (order sources)
   php bin/console base_linker:sources:list

   # Fetch orders directly (--days, --source)
   php bin/console base_linker:orders:fetch
   php bin/console base_linker:orders:fetch --days=30 --source=allegro

   # Fetch orders via queue (Messenger, sync)
   php bin/console base_linker:orders:fetch-queued
   php bin/console base_linker:orders:fetch-queued --days=7 --source=allegro

   # Fetch orders from one or more marketplaces (grouped by source)
   php bin/console base_linker:orders:fetch-by-marketplaces
   php bin/console base_linker:orders:fetch-by-marketplaces --marketplaces=allegro,personal --days=14
   ```

5. **Tests**
   ```bash
   php bin/phpunit
   ```
