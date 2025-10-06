
# YAMDB

YAMDB (Yet Another Movie Database) is a Symfony-based web application that imports and manages popular movies from The Movie Database (TMDB).

## Dependencies
1. **Docker** (https://docs.ddev.com/en/stable/users/install/docker-installation)
2. **DDEV** (https://docs.ddev.com/en/stable/users/install/ddev-installation)

## API Key
Create a key on TMDB (https://www.themoviedb.org) and fill it in .env
    
    TMDB_API_KEY=<FILL_IN_YOUR_API_KEY>

## Setup

To get started, follow these steps:

1. **Run Initialization** (*Runs the composer installation, sets up database, runs migrations, and syncs data*)
    ```bash
    ddev init
    ```

2. **Navigate to the Application**
    ```bash
    ddev launch
    ```
