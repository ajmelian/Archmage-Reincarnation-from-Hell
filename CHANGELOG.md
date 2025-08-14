# v1.4.0 — Inventario & Cartera (oro/maná) + Integración Mercado/Alianza — *sin IA*

## Añadido
- **Cartera** (`wallets` + `wallet_logs`): oro/maná por reino; API `Wallet::balance/add/spend` con auditoría.
- **Inventario** (`inventories` + `inventory_logs`): stack por `item_id`; API `Inventory::add/remove` transaccional con auditoría.
- **UI** `/inventory`: consulta de cartera e inventario.

## Integraciones
- **Mercado (S11)**:
  - Al **listar**, se descuenta el ítem del inventario del vendedor (**escrow**).
  - Al **comprar**, se descuenta **oro** del comprador; el vendedor recibe el **neto** (precio×qty − impuesto) y el comprador recibe los **ítems**.
  - Al **cancelar/expirar**, el remanente vuelve al inventario del vendedor.
- **Banco de Alianza (S12)**:
  - `bankDeposit` gasta recursos del reino del actor.
  - `bankWithdraw` entrega recursos al reino del actor.

## Migraciones
- **013_inventories_wallets**: crea `wallets`, `inventories`, y sus logs.

## Notas
- Todo el flujo es **determinista y data-driven**, **sin IA**.
- Ajusta permisos y validaciones adicionales (p. ej. límites de retiro) según diseño.
