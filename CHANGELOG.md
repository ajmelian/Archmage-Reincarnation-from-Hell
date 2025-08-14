# v1.18.0 — Backups & Herramientas de despliegue

## Añadido
- **Tabla** `backup_jobs` para registrar dumps/restores/seeds.
- **Config** `backup.php`: directorio, rotación (keep), compresión, tablas para seeds.
- **CLI**:
  - `Backupcli`: `dump [tablesCsv]`, `restore <filename>`, `list` y rotación automática.
  - `Seedcli`: `export [tablesCsv]` a **CSV** e `imp <csv> <tabla>` para importar.
  - `Deploycli quick`: ejecuta migraciones y guía para warmers.
- **Ops** `/ops/backups` (solo admin) con listado de archivos y jobs.
- **Health endpoints** `/healthz` y `/readyz` (comprobación de DB).

## Notas
- Determinista y sin IA. Usa **CI DB Utility** para backups y CSV puro para seeds.
- Planifica cron para dumps diarios y verifica permisos de escritura en `backup.dir`.
