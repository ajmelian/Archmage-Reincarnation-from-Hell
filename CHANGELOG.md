

# v1.30.0 — Backoffice de contenidos + Importador ODS/CSV

## Añadido
- **Migración 037**: `colors`, `rarities`, `units`, `spells`, `items`, `heroes`, `resistances`.
- **Config** `content.php`: tipos, colores y rarezas base.
- **Librerías**: `ContentService` (CRUD) e `Importer` (CSV nativo; ODS/XLSX con PhpSpreadsheet si disponible).
- **AdminContent** (controlador + vistas Bootstrap) con **lista/crear/editar/borrar** e **importación** por tabla.
- **Rutas**: `/admin/content`, `/admin/content/import`, etc.
