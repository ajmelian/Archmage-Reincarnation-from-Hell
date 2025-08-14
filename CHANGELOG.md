# v1.0.0 — Combate multirronda (iniciativa/velocidad, moral, formaciones) + Replay timeline JSON

## Añadido
- **Multirronda**: resolución de combate por rondas con orden de **iniciativa** según `speed`.
- **Formaciones**: filas **front/back** y targeting que prioriza la **línea frontal**.
- **Moral**: chequeo al final de cada ronda; retirada si pérdidas ≥ `retreat_threshold` (configurable).
- **Timeline de batalla**: eventos detallados por ronda/ataque, exportables como **JSON**.
- **Endpoint JSON**: `GET /battle/json/{id}` devuelve `timeline`, `losses` y `winner`.
- **Integración**: si `formulas.combat.rounds.enable = true`, `Engine` usa el nuevo motor `CombatRounds` automáticamente.

## Migraciones
- **009_battles_timeline**: añade columnas (`timeline`, `winner`, `lossesA`, `lossesB`) a `battles` si existen.

## Configuración
- `application/config/formulas.php` → `combat.rounds`:
  ```php
  'rounds' => ['enable'=>true,'max_rounds'=>6,'retreat_threshold'=>0.3]
  ```

## Notas
- Requiere que las unidades tengan `speed`, `morale` y `row` (`front`|`back`) para aprovechar todo el cálculo.
- El **log** plano se sigue generando a partir del timeline para compatibilidad con el visor actual.

## Cómo probar
1. Migrar hasta la 009:
   ```bash
   php public/index.php migrate up
   ```
2. Asegurar `formulas.combat.rounds.enable = true`.
3. Ejecutar un combate y visitar `/battle/{id}` (log) o `/battle/json/{id}` (timeline JSON).
