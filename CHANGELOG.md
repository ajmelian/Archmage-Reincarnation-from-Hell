

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
