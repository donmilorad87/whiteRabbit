# Secondary Site — Local Environment Setup

Before running `docker compose up`, your local machine needs the following configuration.  
The primary site must be running first.

## 1. /etc/hosts

Add these entries so the browser and Docker containers can resolve the site hostnames:

```
127.0.0.1 local.wiserabbit.com
127.0.0.1 sec.wiserabbit.com
```

On Linux/Mac: `sudo nano /etc/hosts`  
On Windows: edit `C:\Windows\System32\drivers\etc\hosts` as Administrator

## 2. Docker and Docker Compose

Install Docker Engine and Docker Compose (v2+):

- Linux: https://docs.docker.com/engine/install/
- Mac/Windows: Docker Desktop includes both

Verify:

```bash
docker --version
docker compose version
```

## 3. Node.js

Node.js 18+ is required to build plugin assets (Vite + SCSS):

```bash
node --version   # must be 18+
npm --version
```

## 4. Ports

These ports must be free on your machine (in addition to primary site ports):

| Port | Used by                  |
|------|--------------------------|
| 81   | Secondary site (nginx)   |
| 3307 | Secondary site (MySQL)   |

## 5. File Permissions

The `wp-content/uploads` directory is mounted from your host into the container. WordPress runs as `www-data` (uid 33) inside the container, so the host directory needs to be writable.

After starting containers for the first time, run:

```bash
docker exec secondary_site-wordpress-1 chown -R www-data:www-data /var/www/html/wp-content/uploads
```

This is handled automatically by `build.sh` on container start, but if you manually create the `wp-content/uploads` folder before the first boot, run the command above.

## 6. Start

```bash
cd secondary_site
docker compose up -d
```

Then build plugin assets:

```bash
cd wp-content/plugins/wr-slot-consumer/assets/src
npm install
npm run build
```

## 7. Connect to Primary Site

1. Go to http://sec.wiserabbit.com:81/wp-admin/ (admin / admin)
2. Open **Slot Consumer** in the sidebar
3. Set **Source Site URL**: `http://local.wiserabbit.com`
4. Set **API Key**: copy from primary site at **Slots > API Key**
5. Click **Save Settings**, then **Sync Data**

## 8. Access

| Service    | URL                                          | Credentials       |
|------------|----------------------------------------------|--------------------|
| WordPress  | http://sec.wiserabbit.com:81/wp-admin/       | admin / admin      |
| phpMyAdmin | http://sec.wiserabbit.com:81/pma/            | root / rootpassword |
