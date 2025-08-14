# v1.2.0 — Mercado (Auction House) + Trade directo + Anti-abuso económico

## Añadido
- **Mercado (Auction House)**
  - Listados con `item_id`, cantidad, `price_per_unit`, moneda (oro), impuesto configurable y expiración.
  - Acciones: **listar**, **comprar**, **cancelar**, con registros en `market_logs`.
  - UI en `/market` para crear anuncios, ver los tuyos y comprar listados activos.
  - CLI `Marketcli::cleanup` para expirar anuncios automáticamente.
- **Trade directo (doble confirmación)**
  - Ofertas entre reinos: oro + items, con estados `pending|accepted|declined|canceled|expired`.
  - UI en `/trade` para bandeja de entrada/salida y acciones.
- **Anti-abuso**
  - Config `application/config/market.php`: `tax_rate`, `min_price_floor`, límites diarios de listar/comprar, vida del anuncio.
  - Lógica de límites diarios por reino vía `market_logs` (sell/buy).
  - Registro de eventos en `market_logs` para auditoría.

## Migraciones
- **011_market_trade**: `market_listings`, `trade_offers`, `market_logs`.

## Rutas
- Mercado: `/market`, `/market/list`, `/market/buy/{id}`, `/market/cancel/{id}`.
- Trade: `/trade`, `/trade/offer`, `/trade/accept/{id}`, `/trade/decline/{id}`, `/trade/cancel/{id}`.

## Notas
- La **transferencia real** de oro/items está marcada como TODO y debe integrarse con inventarios/carteras.
- Integra un **cron** para `marketcli/cleanup` (p. ej. cada hora).
