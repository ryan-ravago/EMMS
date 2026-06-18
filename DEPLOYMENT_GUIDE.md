# Deployment Guide: Zero-Touch Ubuntu Setup

This guide explains how to use the `deploy.sh` script to set up your project on a fresh Ubuntu VM from scratch.

## Prerequisites

1.  **Fresh VM:** A clean installation of Ubuntu 22.04 or 24.04.
2.  **DNS Configuration:** Point your domain's **A Record** to the Public IP address of your VM. *This is required for the SSL (HTTPS) setup to succeed.*

## Deployment Steps

1.  **Login to your VM:**
    ```bash
    ssh your_user@your_vm_ip
    ```

2.  **Clone the Project:**
    ```bash
    git clone <your-repository-url>
    cd <project-folder-name>
    ```

3.  **Make the script executable:**
    ```bash
    chmod +x deploy.sh
    ```

4.  **Run the Installer:**
    ```bash
    sudo ./deploy.sh
    ```

5.  **Provide Information:**
    The script will ask for:
    *   **Domain Name:** e.g., `emms.example.com`
    *   **Database Name:** Your desired DB name.
    *   **Database User:** Your desired DB username.
    *   **Database Password:** A strong password for the DB user.
    *   **MariaDB Root Password:** A strong password for the database root user.

## What this script does for you:

*   **Stack Install:** PHP 8.2, Nginx, MariaDB, Node.js 20, Supervisor, and Certbot.
*   **Database:** Secures MariaDB, enables remote access (0.0.0.0), and creates your database.
*   **Nginx:** Creates a production-ready config with auto-redirection from HTTP to HTTPS.
*   **Laravel:** Installs dependencies, runs migrations, and applies production optimizations (`php artisan optimize`).
*   **Workers:** Sets up 2 Supervisor workers to handle background queues.
*   **Scheduler:** Configures the Cron job for `php artisan schedule:run`.
*   **SSL:** Automatically fetches and installs a Let's Encrypt certificate.

## Post-Installation

Once the script finishes, your site will be live at `https://yourdomain.com`.

### Useful Commands:

*   **Check Workers:** `sudo supervisorctl status`
*   **Restart Workers:** `sudo supervisorctl restart all`
*   **View Logs:** `tail -f storage/logs/laravel.log`
*   **Worker Logs:** `tail -f storage/logs/worker.log`

## Updating Your Code

Whenever you push new changes to GitHub and want to apply them to your VM:

1.  **Run the update script:**
    ```bash
    sudo ./update.sh
    ```

This script will automatically:
*   Pull the latest code (`git pull`).
*   Update Composer & NPM dependencies.
*   Run database migrations.
*   Clear and re-generate production caches (`php artisan optimize`).
*   Restart your queue workers so they use the new code.

