# Database Cleanup & Reset Guide

If you need to completely reset the project, remove all databases and the database user, following these steps will clean your PostgreSQL environment for a fresh install.

## 1. Drop Databases
Run the following commands to delete all updated databases (WARNING: This deletes all data):
```bash
sudo -u postgres dropdb users --if-exists
sudo -u postgres dropdb level_1 --if-exists
sudo -u postgres dropdb level_2 --if-exists
sudo -u postgres dropdb final_lvl1 --if-exists
sudo -u postgres dropdb final_lvl2 --if-exists
```

## 2. Drop Role
Remove the `sqluser` role (cleaning up permissions first):
```bash
# Revoke permissions in the default postgres database
sudo -u postgres psql -d postgres -c "DROP OWNED BY sqluser;"
# Drop the role
sudo -u postgres psql -c "DROP ROLE IF EXISTS sqluser;"
```

## 3. (Optional) Reset Postgres Password
If you need to reset the `postgres` user password to default/blank or a specific value:
```bash
sudo -u postgres psql -c "ALTER USER postgres WITH PASSWORD 'new_password';"
```
