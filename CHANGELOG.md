

# v1.26.1 — Conexión de pérdidas NP con Counters y Damage Protection

## Añadido
- **Config** `game.php`: `protections.damage_protection_hours = 12` (por defecto).
- **Librería** `BattleResults`: `applyAndLog(battleId, attId, defId, attNpLoss, defNpLoss)` registra el daño bidireccional en `pvp_damage`, recalcula pérdidas 24h y otorga **Damage Protection** automáticamente cuando corresponde. Además devuelve si ahora hay **counter** disponible en ambos sentidos.
- **ProtectionService**: helpers `totalDamage24hReceived()` y `damageLostPercent24h()`.
- **Battle**: endpoint `POST /battle/apply_result` para consumir resultados de batalla y aplicar/registrar efectos.

## Uso rápido
```bash
curl -X POST http://localhost/index.php/battle/apply_result   -d 'battle_id=123' -d 'attacker_realm_id=10' -d 'defender_realm_id=42'   -d 'attacker_np_loss=1200' -d 'defender_np_loss=3400'
```
→ Registra la batalla, recalcula ventana 24h y activa **Damage Protection** si supera el umbral.

## Notas
- El porcentaje de pérdida en 24h se aproxima como `daño_24h / (NP_actual + daño_24h)`.
- Ajusta `damage_threshold_percent_24h` y `damage_protection_hours` en `game.php` según tu servidor.


# v1.27.0 — Pre-battle: resistencias + ítems; botín=0 en counters >2× NP

## Añadido
- **Config** `game.php`: bloque `prebattle` con `barrier_max=0.75` y lista de colores.
- **DeterministicRNG**: HMAC-SHA256 CTR para tiradas reproducibles por batalla.
- **PreBattleService::resolve()**: cadena **Barrier → Color** para hechizos, y **Barrier** para ítems (plain). Devuelve probabilidades, tiradas y si aplican.
- **BattlePolicy::lootModifier()**: si es **counter** y `NP_atacante / NP_defensor > 2.0` → **botín=0**.
- **Battle::prebattle (POST)**: endpoint que resuelve pre-batalla y devuelve `loot_modifier`.
- **CLI** `Mechanicscli::prebattle_demo`.

## Uso rápido
```bash
php public/index.php mechanicscli prebattle_demo
curl -X POST http://localhost/index.php/battle/prebattle -d '{...json...}'
```


# v1.28.0 — Fase de batalla: resistencias por unidad + targeting híbrido + resolución de daño

## Añadido
- **Config** `game.php` → `battle_phase`: eficiencias por tipo, cap de daño por stack y preferencia híbrida hacia tierra.
- **Engine**:
  - `choose_attack_type()` y `can_hit_attack_type()` para híbridos y validación de objetivo.
  - `damage_phase()` — calcula daño A→D según `pairing`, aplicando **resistencias por unidad** (`unit_resists`) y eficiencia por tipo. Incluye cap por stack.
- **Battle**:
  - `POST /battle/resolve` — recibe `attacker/defender` con `stacks`, ejecuta `pairing + damage_phase`, devuelve pérdidas NP aproximadas y aplica `BattleResults::applyAndLog()` si se proporcionan los realm_id.
- **CLI**: `mechanicscli resolve_demo`.

## Notas
- El motor considera `unit_resists` por **tipo de ataque efectivo** (melee/ranged/flying). Híbridos priorizan melee contra objetivos en tierra y usan ranged si solo quedan voladores.
- La relación daño→NP es 1:1 como aproximación. Puedes ajustar una conversión distinta si tu economía lo requiere.
- Determinista y sin IA.
