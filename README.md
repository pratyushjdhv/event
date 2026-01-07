# Event Portal – Full Installation & Hosting Guide

A PHP + PostgreSQL LAN web app for running timed SQL puzzle rounds. Hosts control the active round via [`public/status.txt`](public/status.txt); participants join over LAN and submit answers that are stored in the `users` database.

## 1) Prerequisites
- PHP 8.x with `php-pgsql`
- PostgreSQL
- Git (optional)

Ubuntu/Debian example:
```bash
sudo apt update
sudo apt install php php-pgsql postgresql postgresql-contrib git
```

## 2) Get the Code
```bash
git clone <repo_url> event
cd event
```

## 3) PostgreSQL Setup

### 3.1 Start service
```bash
sudo service postgresql start
```

### 3.2 Set passwords (matches [`public/db.php`](public/db.php) defaults)
```bash
sudo -u postgres psql -c "ALTER USER postgres WITH PASSWORD 'root';"
sudo -u postgres psql -c "CREATE ROLE sqluser WITH LOGIN PASSWORD 'root';"
```

### 3.3 Create databases
```bash
sudo -u postgres createdb users
sudo -u postgres createdb level_1
sudo -u postgres createdb level_2
sudo -u postgres createdb final_lvl1
sudo -u postgres createdb final_lvl2
```

### 3.4 Import schemas/data
```bash
sudo -u postgres psql -d users      -f databases/users.sql
sudo -u postgres psql -d level_1    -f databases/level_1.sql
sudo -u postgres psql -d level_2    -f databases/level_2.sql
sudo -u postgres psql -d final_lvl1 -f databases/final_lvl1.sql
sudo -u postgres psql -d final_lvl2 -f databases/final_lvl2.sql
```

### 3.5 Grant permissions to `sqluser` (read-only levels, write to users)
```bash
# For each level DB (example: level_1)
sudo -u postgres psql -d level_1 -c "GRANT CONNECT ON DATABASE level_1 TO sqluser;"
sudo -u postgres psql -d level_1 -c "GRANT USAGE ON SCHEMA public TO sqluser;"
sudo -u postgres psql -d level_1 -c "GRANT SELECT ON ALL TABLES IN SCHEMA public TO sqluser;"
sudo -u postgres psql -d level_1 -c "ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT ON TABLES TO sqluser;"

# For users DB (needed by participants and host views)
sudo -u postgres psql -d users -c "GRANT CONNECT, TEMP ON DATABASE users TO sqluser;"
sudo -u postgres psql -d users -c "GRANT USAGE ON SCHEMA public TO sqluser;"
sudo -u postgres psql -d users -c "GRANT SELECT, INSERT, UPDATE ON ALL TABLES IN SCHEMA public TO sqluser;"
sudo -u postgres psql -d users -c "ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT, INSERT, UPDATE ON TABLES TO sqluser;"
```

### 3.6 Ensure leaderboard column
```bash
sudo -u postgres psql -d users -c "ALTER TABLE users ADD COLUMN IF NOT EXISTS time TIMESTAMP;"
```

## 4) Configure
- If you change database credentials, update [`public/db.php`](public/db.php).
- Control active round by editing [`public/status.txt`](public/status.txt) (see values below).

## 5) Run the Server (LAN)
From project root:
```bash
php -S 0.0.0.0:9000 -t public
php -S 192.168.0.100:3000 -t public

```
Clients open `http://<SERVER_IP>:9000`. If blocked, allow the port:
```bash
sudo ufw allow 9000/tcp
```

## 6) Host Controls (status.txt values)
- `1` → Level 1 (`lvl1.php`)
- `2` → Level 2 (`lvl2.php`)
- `3` → Final Level 1 (`final_lvl1.php`)
- `4` → Final Level 2 (`final_lvl2.php`)
- `5` → Leaderboard (Top 5 fastest)
- `reset` → Truncate `users` table
- `0` → Pause screen

Typical round:
1. Set `status.txt` to `1` (or next round number).
2. When showing winners, set to `5`.
3. Backup data.
4. Set to `reset` to clear.
5. Set to the next round number.

## 7) Backup & Restore
Quick backup (as postgres):
```bash
sudo -u postgres pg_dump users > backups/users_backup_$(date +%F_%H-%M-%S).sql
```
Restore into a new DB:
```bash
sudo -u postgres createdb old_round_data
sudo -u postgres psql -d old_round_data -f backups/users_backup_<timestamp>.sql
```
Optional script: create `backup.sh` as in current README, then `chmod +x backup.sh` and run before `reset`.

## 8) Useful Commands
- Start server: `php -S 0.0.0.0:9000 -t public`
- Dump DB: `sudo -u postgres pg_dump users > users.sql`
- Clear users table: `sudo -u postgres psql -d users -c "TRUNCATE TABLE users RESTART IDENTITY;"`

## 9) Troubleshooting
- Cannot connect: ensure Postgres service is running and passwords match `db.php`.
- Clients can’t reach server: confirm LAN IP and that port 9000 is allowed.
- Schema pages empty: verify level databases are imported and `sqluser` has SELECT privileges.

## 10) File Map
- App entry & router: [`public/index.php`](public/index.php)
- Per-round pages: [`public/lvl1.php`](public/lvl1.php), [`public/lvl2.php`](public/lvl2.php), [`public/final_lvl1.php`](public/final_lvl1.php), [`public/final_lvl2.php`](public/final_lvl2.php)
- SQL consoles: [`public/lvl1_sql.php`](public/lvl1_sql.php), [`public/lvl2_sql.php`](public/lvl2_sql.php), [`public/final_lvl1_sql.php`](public/final_lvl1_sql.php), [`public/final_lvl2_sql.php`](public/final_lvl2_sql.php)
- Schemas: [`public/lvl1_schema.php`](public/lvl1_schema.php), [`public/lvl2_schema.php`](public/lvl2_schema.php), [`public/final_lvl1_schema.php`](public/final_lvl1_schema.php), [`public/final_lvl2_schema.php`](public/final_lvl2_schema.php)
- Answer submission: [`public/answer.php`](public/answer.php)
- DB helper: [`public/db.php`](public/db.php)
- SQL dumps: [`databases/users.sql`](databases/users.sql), [`databases/level_1.sql`](databases/level_1.sql), [`databases/level_2.sql`](databases/level_2.sql), [`databases/final_lvl1.sql`](databases/final_lvl1.sql), [`databases/final_lvl2.sql`](databases/final_lvl2.sql), [`databases/roles_backup.sql`](databases/roles_backup.sql)

## 11) Ready to Host Checklist
1. Postgres running; DBs and roles created; SQL imported.
2. Passwords in [`public/db.php`](public/db.php) match your Postgres users.
3. Set [`public/status.txt`](public/status.txt) to the starting round (e.g., `1`).
4. Run `php -S 0.0.0.0:9000 -t public`.
5. Share `http://<SERVER_IP>:9000` with participants.