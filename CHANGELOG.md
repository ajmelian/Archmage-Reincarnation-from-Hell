# v1.8.0 — Talentos & Sets en runtime + Golden Tests

## Añadido
- **Compilación de bonos por reino** (`compiled_bonuses`) en 2 scopes: **economy** y **combat**.
- **Stacking & caps** configurables (`application/config/talents.php`):
  - Pila por clave (`add`, `mult`, `max`) y **límites duros** (+% y planos).
- **Equipamiento** básico (`equipment`) para activar **bonos de set** de `item_set_def`.
- **TalentTree** actualizado:
  - `heroTalents`, `aggregateBonuses`, `equipmentBonuses` y `compileRealm()` con persistencia.
  - `getCompiled(realm, scope)` para consulta rápida.
- **TickRunner** usa los bonos **compilados** de economía al producir recursos.
- **Engine (combate)** aplica bonos de combate (mult de ataque/defensa y planos por unidad).
- **Golden Tests (CLI)** `Goldencli::run`:
  - Economía: verifica producción con talentos de oro (+10% y +20%).
  - Combate: verificación determinista de puntajes con mismos bonos.

## Migraciones
- **017_runtime_bonuses**: `compiled_bonuses`, `equipment`.

## Notas
- Determinista y **sin IA**. Ajusta `caps`/`stacking` según tu diseño exacto.
- Si ya tienes tus propias fórmulas de economía/combate, solo usa `TalentTree::getCompiled()` para obtener modificadores y aplícalos en tus cálculos.
