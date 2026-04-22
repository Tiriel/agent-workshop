# Agent Workshop

This application is a Symfony-based playground for AI-powered features. It demonstrates how to integrate LLMs (Claude Haiku via Anthropic), vector stores (PostgreSQL with `pgvector`), and similarity search into a modern web application.

The project features:
- **Conversational AI Chat**: A streaming chat interface powered by Claude Haiku with tool-calling capabilities.
- **Similarity Search**: Posts are retrieved via semantic similarity using Voyage AI embeddings stored in pgvector.
- **Automatic Tagging**: Posts are automatically tagged based on content similarity to existing tags using vector embeddings.
- **AI Tools**: Custom tools (`SimilarityPostTagger`, `TagCreator`, `StatusSearch`) leverage LLMs to enrich application data.
- **Admin CRUD**: A full dynamic administration interface built with Symfony UX Live Components.

## ⚙️ Configuration

Before installing, configure your local environment variables:

1. **Create a `.env.local` file**:
    ```bash
    cp .env .env.local
    ```

2. **Set the required variables** in `.env.local`:
    - `DATABASE_URL`: PostgreSQL connection string (e.g. `postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=16&charset=utf8`)
    - `ADMIN_EMAIL`: Login email for the admin account
    - `ADMIN_PWD`: Password for the admin account
    - `ANTHROPIC_API_KEY`: Your Anthropic API key (required for AI chat and tool features)
    - `VOYAGE_API_KEY`: Your Voyage AI API key (required for vector embeddings)

## 🚀 Installation

The project uses a `Makefile` to simplify common tasks. Run `make help` at any time to list all available targets.

1. **Full installation** (builds containers, installs dependencies, creates DB and loads fixtures):
    ```bash
    make install
    ```

2. **Set up AI stores** (vector tables + indexing — requires API keys):
    ```bash
    make db-full
    ```

3. **Start / stop the app**:
    ```bash
    make start
    make stop
    ```

## 🗄️ Database Commands

| Command | Description |
|---|---|
| `make db` | Create the database and run migrations |
| `make fixtures` | Load fixtures |
| `make db-full` | `db` + `fixtures` + AI message store + vector stores + indexing |
| `make reset` | Drop the database, run `db-full`, then clear cache |

## 🤖 AI Store Commands

| Command | Description |
|---|---|
| `make store <name>` | Set up a specific AI store |
| `make store-drop <name>` | Drop a specific AI store |
| `make index <name>` | Index documents into a specific AI store |

## 🛠️ Development Commands

| Command | Description |
|---|---|
| `make composer` | Install Composer dependencies |
| `make test` | Run PHPUnit tests |
| `make cache-clear` | Clear Symfony cache and shared cache pools |
| `make symfony cmd="<cmd>"` | Run any Symfony console command |

<details>
<summary><strong>Alternative: Without Make</strong></summary>

If you don't have `make` installed, use Docker Compose commands directly:

```bash
docker compose build
docker compose up --wait
docker compose exec php composer install
docker compose exec php bin/console doctrine:database:create --if-not-exists
docker compose exec php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec php bin/console foundry:load --no-interaction
docker compose exec php bin/console ai:message-store:setup ai.message_store.cache.messages
docker compose exec php bin/console ai:store:setup ai.store.postgres.posts
docker compose exec php bin/console ai:store:setup ai.store.postgres.tags
docker compose exec php bin/console ai:store:index posts
docker compose exec php bin/console ai:store:index tags
```

</details>

## 🏥 Health Check

1. **Functional Tests**: `make test`
2. **Web Interface**: [https://localhost](https://localhost) — homepage with latest posts
3. **Admin Panel**: [https://localhost/admin](https://localhost/admin) — manage Posts, Tags, and Users
4. **Docker Logs**:
    ```bash
    docker compose logs -f php
    ```
5. **Trainer Contact**: If you can't get the application running, contact your trainer via the email provided in the SymfonyLive workshop instructions.
