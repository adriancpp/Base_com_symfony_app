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

4. **Console**
   ```bash
   php bin/console list
   php bin/console about
   ```
