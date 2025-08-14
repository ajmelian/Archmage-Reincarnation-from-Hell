# v0.9.0 — Paridad de fórmulas + Golden tests

## Añadido
- **Configuración de fórmulas** (`application/config/formulas.php`) con versión `legacy` y coeficientes editables para economía, combate y hechizos.
- **Formulas/**:
  - `CombatFormula`: cálculo de pérdidas por ronda con resistencias por tipo y selección de objetivo proporcional.
  - `EconomyFormula`: producción de oro/maná/investigación con diminishing returns, stacking/caps y coste de investigación escalado.
  - `SpellFormula`: potencia por nivel, coste de maná escalado y duración por tipo de hechizo.
- **Golden tests (CLI)**:
  - `Golden::run [all|economy|combat|spells]` lee CSVs en `application/tests/golden/*.csv`, ejecuta casos y valida contra resultados esperados (tolerancia configurable).
  - Salida con resumen y código de retorno != 0 si hay fallos (útil para CI).
- **Integración opcional en Engine**:
  - Si `formulas.enable = true`, el combate del juego usa `CombatFormula` para calcular pérdidas por ronda (manteniendo compatibilidad de salida).

## Notas
- Los CSV de ejemplos son **plantillas**; sustituye `expected` con tus valores de referencia del juego original.
- Ajusta coeficientes en `formulas.php` para conseguir paridad exacta sin tocar código.

## Cómo ejecutar
```bash
php public/index.php golden run all
php public/index.php golden run combat
php public/index.php golden run economy
php public/index.php golden run spells
```
