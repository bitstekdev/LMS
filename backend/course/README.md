# 🚀 Laravel Project Setup with Docker + Repo Sync

This project is a Laravel application configured to run inside **Docker** using PHP-FPM + Nginx, and includes a helper script to push this repo into a subfolder of another repo (`bitstekdev/LMS`).

---

## 📦 Requirements

- Docker & Docker Compose
- Git
- PHP & Composer (optional for local dev, not required inside Docker)
- `git-filter-repo` installed (for repo syncing script)

On Arch-based distros:

```
sudo pacman -S docker docker-buildx docker-compose git-filter-repo
```

---

## 🐳 Docker Setup

### 1. Build & Start Containers

Run from project root:

```
docker compose up -d --build
```

This will start:

- **laravel_app** → PHP 8.3 FPM container
- **laravel_nginx** → Nginx server on port `8080`

Access your app:

```
http://localhost:8080
```

---

### 2. Common Docker Commands

Check running containers:

```
docker ps
```

Check logs:

```
docker logs laravel_app
docker logs laravel_nginx
```

Enter PHP container:

```
docker exec -it laravel_app bash
```

---

## ⚙️ Laravel Commands inside Docker

Since Laravel is inside the **app container**, all Artisan, Composer, and Node commands should be run like this:

### Run Artisan commands

```
docker exec -it laravel_app php artisan migrate
docker exec -it laravel_app php artisan db:seed
```

### Run Composer

```
docker exec -it laravel_app composer install
```

### Run Tests

```
docker exec -it laravel_app php artisan test
```

---

## 🗄️ Database Migrations & Seeders

1. Ensure `.env` is configured with correct DB connection (MySQL/Postgres in RDS for prod, or local DB for dev).
2. Run migrations:

    ```
    docker exec -it laravel_app php artisan migrate
    ```

3. Run seeders:

    ```
    docker exec -it laravel_app php artisan db:seed
    ```

If you want fresh migrations:

```
docker exec -it laravel_app php artisan migrate:fresh --seed
```

---

## 🔄 Repo Sync with `push-to-bitstekdev.sh`

This project includes a script to **push Repo B (this project) into Repo A (`bitstekdev/LMS`)** under a subfolder (`backend/course/`).

### 1. Configure the script

The script is located at:

```
push-to-bitstekdev.sh
```

Inside, it has settings:

- Repo A URL → `https://github.com/bitstekdev/LMS.git`
- Repo A Branch → `abbasmashaddy72`
- Subfolder → `backend/course`
- Source Branch → `main` (from this repo)

### 2. Make it executable

```
chmod +x push-to-bitstekdev.sh
```

### 3. Run the script

```
./push-to-bitstekdev.sh
```

This will:

- Create a temporary clone of Repo B
- Rewrite history so all files live in `backend/course/`
- Push them into Repo A branch `abbasmashaddy72`

You can then open Repo A (`bitstekdev/LMS`) and merge this branch into its `main`.

---

## 🔍 Troubleshooting

- **Permission denied on storage/logs/cache** → Fix by ensuring the container user has write access:

    ```
    docker exec -it laravel_app chmod -R 775 storage bootstrap/cache
    ```

- **Database connection errors** → Check `.env` matches your RDS/local DB and run `docker compose restart`.

- **Sync script errors** → Ensure `git-filter-repo` is installed:

    ```
    git filter-repo --help
    ```

---

## ✅ Quick Reference

Start project:

```
docker compose up -d --build
```

Run migrations:

```
docker exec -it laravel_app php artisan migrate
```

Run seeders:

```
docker exec -it laravel_app php artisan db:seed
```

Push code to Repo A:

```
./push-to-bitstekdev.sh
```
