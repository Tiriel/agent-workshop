# Agent Workshop

This application is a Symfony-based playground for AI-powered features. It demonstrates how to integrate LLMs (Claude 3 Haiku via Anthropic), vector stores (PostgreSQL with `pgvector`), and similarity search into a modern web application.

The project features:
- **Automatic Tagging**: Posts are automatically tagged based on their content similarity to existing posts using vector embeddings.
- **AI Tools**: Custom tools like `SimilarityPostTagger` and `TagCreator` leverage LLMs to enrich application data.
- **Admin CRUD**: A full dymanic administration interface built with Symfony UX Live Components.

## ⚙️ Configuration

Before installing, you must configure your local environment variables:

1.  **Create a `.env.local` file**:
    ```bash
    cp .env .env.local
    ```

2.  **Set sensitive variables**:
    Open `.env.local` and set at least the following variables:
    -   `DATABASE_URL`: The database connection string (e.g., `postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=16&charset=utf8`).
    -   `ADMIN_EMAIL`: The login email for the admin account.
    -   `ADMIN_PWD`: The password for the admin account.
    -   `ANTHROPIC_API_KEY`: Your Anthropic API key (required for AI features).
    -   `VOYAGE_API_KEY`: Your Voyage AI API key (required for AI features).

## 🚀 Installation

The project uses a `Makefile` to simplify common tasks. To get started:

1.  **Full Installation**:
    Make sure you have Docker and Docker Compose installed. Run the following command to build containers, install dependencies, and setup the database:
    ```bash
    make install
    ```

2.  **Running the App**:
    Start the application:
    ```bash
    make start
    ```

3.  **Stopping the App**:
    ```bash
    make stop
    ```

4.  **Help**:
    View all available commands:
    ```bash
    make help
    ```

## 🏥 Health Check

To verify that the application is running correctly:

1.  **Functional Tests**: Run `make test` to run functional tests of the application.
2.  **Web Interface**: Open [https://localhost](https://localhost) in your browser. You should see the homepage with the latest posts.
2.  **Admin Panel**: Access [https://localhost/admin](https://localhost/admin) to manage Posts, Tags, and Users.
3.  **Docker Logs**: If something isn't working, check the logs:
    ```bash
    docker compose logs -f php
    ```
4.  **Trainer Contact**: As a last resort, if you can't make the application work, contact your trainer via email 
    (provided on the SymfonyLive workshop instructions)
