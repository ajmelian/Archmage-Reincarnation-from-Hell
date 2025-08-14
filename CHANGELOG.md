# v1.24.0 — Backups & Export

## Añadido
- **BD**: tabla `backups` (metadatos de dumps).
- **Config** `backup.php`: ruta (`FCPATH/backups` por defecto), formato (gzip|zip), retención (`keep_last`, `max_total_mb`).
- **BackupService**: crea dumps de DB con CI DB Utility, guarda en disco y en `backups`, calcula checksum, **prune** automático por retención.
- **ExportService**: exporta módulos a CSV o JSON con filtros comunes (`since`, `realm_id`, etc.). Módulos incluidos:
  - `realms`, `inventory`, `market_listings`, `market_trades`, `auctions`, `auction_bids`,
  - `alliances`, `alliance_members`, `audit_log`, `mod_flags`, `mod_actions`,
  - `economy_history`, `econ_params`.
- **UI Admin**:
  - `/backup`: listar/crear/descargar/eliminar backups.
  - `/export`: selector de módulo + filtros, descarga CSV/JSON.
- **CLI**: `Backupcli` (`create`, `prune`, `list`).
- **API v1**: `GET /api/v1/export?module=...&format=csv|json` (requiere scope `read`).

## Notas
- Los ficheros se guardan en `public/backups` (ajustable). Asegura permisos y, si deseas, protege con reglas del servidor o detrás de controller download.
- Determinista y sin IA.
