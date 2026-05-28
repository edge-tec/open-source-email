# EdgeMail - Complete Ubuntu Installation Guide

This guide will walk you through the process of installing and configuring the EdgeMail system on an Ubuntu Server using Docker.

## Prerequisites
- **Operating System:** Ubuntu 20.04 or 22.04 LTS
- **User Permissions:** `root` access or a user with `sudo` privileges.
- **Domain Name:** A registered domain name pointing to your server's IP address.

---

## Step 1: Update System & Install Docker

First, ensure your server is up to date and install Docker and Docker Compose.

```bash
# Update package list and upgrade system
sudo apt update && sudo apt upgrade -y

# Install Docker
sudo apt install docker.io -y

# Install Docker Compose
sudo apt install docker-compose -y

# Start and enable Docker service
sudo systemctl start docker
sudo systemctl enable docker
```

---

## Step 2: Download the Project

Navigate to your web directory (usually `/www/wwwroot` or `/var/www/html`) and clone the project from GitHub.

```bash
# Create directory if it doesn't exist
sudo mkdir -p /www/wwwroot
cd /www/wwwroot

# Clone the repository (Replace with your actual repo URL)
sudo git clone https://github.com/edge-tec/open-source-email.git

# Go into the project directory
cd open-source-email
```

---

## Step 3: Start the Docker Containers

EdgeMail is fully containerized. Start the containers using Docker Compose.

```bash
# Start all containers in the background
sudo docker-compose up -d
```
*This command will download the necessary images (PHP, Nginx, MariaDB, Redis) and start them.*

---

## Step 4: Install PHP Dependencies

Now, install the required PHP packages using Composer inside the application container.

```bash
sudo docker-compose exec app composer install --optimize-autoloader --no-dev
```

---

## Step 5: Configure Environment Variables

Set up the configuration file and generate the application key.

```bash
# Copy the example environment file
sudo cp .env.example .env

# Generate a unique application key
sudo docker-compose exec app php artisan key:generate
```

---

## Step 6: Setup the Database

Run the database migrations and seed the initial data.

```bash
# Run migrations (Type 'yes' if prompted)
sudo docker-compose exec app php artisan migrate --force

# Seed database with initial settings
sudo docker-compose exec app php artisan db:seed --force
```

---

## Step 7: Create the Super Admin Account

Create your admin account so you can log into the EdgeMail dashboard.

```bash
sudo docker-compose exec app php artisan make:admin
```
*Follow the on-screen prompts to enter your name, email, and a secure password.*

---

## Step 8: Finalizing & Permissions

Clear caches and ensure everything is optimized for production.

```bash
sudo docker-compose exec app php artisan optimize:clear
sudo docker-compose exec app php artisan view:clear
sudo docker-compose exec app php artisan config:cache
```

---

## Step 9: Accessing EdgeMail

Your EdgeMail installation is now complete!

1. Open your web browser.
2. Go to: **`http://your-server-ip:8080`** (or your domain name if a reverse proxy is configured).
3. Log in using the Admin credentials you created in Step 7.

---
**Troubleshooting:**
- **Permission Denied (Docker):** Always use `sudo` before your Docker commands.
- **500 Server Error:** Check your `.env` file for correct database credentials and ensure `APP_KEY` is generated.
