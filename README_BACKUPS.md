# Backups & Deploy — Archmage CI3

Este módulo añade **copias de seguridad**, **seeds CSV** y **helpers de despliegue**.

## CLI

### Backups
```bash
# Dump completo de la BD (comprimido .gz) y rotación automática (keep=10)
php public/index.php backupcli dump

# Dump de tablas específicas (coma-separadas)
php public/index.php backupcli dump users,realms,buildings

# Listado de backups
php public/index.php backupcli list

# Restaurar desde fichero existente (¡destruye datos!)
php public/index.php backupcli restore db_full_20250101_000000.sql.gz
```

### Seeds (CSV)
```bash
# Exportar seeds (por defecto: research_levels, buildings, realms, users)
php public/index.php seedcli export

# Exportar tablas específicas
php public/index.php seedcli export units,spells

# Importar CSV en tabla
php public/index.php seedcli imp /ruta/a/units_2025xxxx.csv units
```

### Deploy rápido
```bash
# Ejecuta migraciones y muestra hint para calentar cachés
php public/index.php deploycli quick
```

## Panel Ops
- **/ops/backups** (solo admin): lista backups y jobs.
- **/healthz** (liveness) y **/readyz** (readiness con prueba de DB).

## Configuración
- `application/config/backup.php`:
  - `dir`: directorio de backups (por defecto: `./backups` en raíz del proyecto).
  - `keep`: cuántos archivos conservar.
  - `gzip`: comprimir dumps.
  - `seed_tables`: lista por defecto para export CSV.

> Recomendación: programa cron jobs para `backupcli dump` diario y `seedcli export` semanal.
