# v1.20.0 — Mercado & Subastas (tasas + anti‑abuso)

## Añadido
- **Tablas**:
  - `inventory` (si no existía): stock por `realm_id`/`item_id`.
  - `market_listings` (venta directa con depósito, expiración y fee).
  - `market_trades` (histórico para referencia de precios).
  - `auctions` (subastas con compra ya, incremento mínimo y soft-extend).
  - `auction_bids` (pujas).
- **MarketService**:
  - Publicar, cancelar, comprar y expirar listados.
  - **Depósito** (se devuelve al vender/cancelar; se pierde al expirar).
  - **Fee** al vendedor (por defecto 2.5%). Anti‑abuso:
    - Rate limit por ventana (publicaciones y compras, reutiliza `rate_counters`).
    - **Límites de precio**: ppu dentro de [min_factor..max_factor] × **precio de referencia** (mediana de últimas trades o `ref_prices` del config).
    - Bloqueo de **auto‑compra** (mismo reino).
- **AuctionService**:
  - Crear subasta, pujar (rate limit), cancelar (sin pujas), finalizar (automática o `buyout`), y **soft‑extend** en últimos segundos.
  - Cobro al ganador, fee al vendedor y entrega/escrow de items simétrico al mercado.
- **UI** (Bootstrap):
  - `Market` (listar, vender, mis listados, comprar).
  - `Auctions` (listado, detalle con pujas, crear, cancelar).
- **API v1**:
  - `GET /api/v1/market/listings`, `POST /api/v1/market/list`, `POST /api/v1/market/buy`.
  - `GET /api/v1/auctions/active`, `POST /api/v1/auctions/create`, `POST /api/v1/auctions/bid`.
- **CLI** `Marketcli::expire` (expira listados y finaliza subastas).

## Config
- `application/config/market.php`: fees, depósitos, límites de precio, duración, **ref_prices** opcionales y rate limits.

## Notas
- Determinista y sin IA. Depende de **Wallet** (`add`/`spend`) y `realms` para mapear usuario→reino.
- Para poblar inventario, inserta filas en `inventory` (realm_id, item_id, qty).
