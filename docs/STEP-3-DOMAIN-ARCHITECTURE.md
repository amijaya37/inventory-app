# Step 3 — Arsitektur Domain Laravel

Step ini menerapkan fondasi arsitektur kode Inventory Stock IT agar transaksi stok tidak tersebar di controller.

## Prinsip yang diterapkan

- Controller harus tetap tipis.
- Validasi masuk melalui `FormRequest` dan DTO.
- Use case bisnis ditempatkan di `app/Actions/Inventory`.
- Logic stok terpusat di `StockMovementService`.
- Semua perubahan stok memakai `DB::transaction()` di Action.
- Saldo cepat disimpan di `stocks`.
- Histori/audit stok disimpan di `stock_cards`.
- Stok tidak boleh minus.

## Struktur utama yang dibuat

```text
app/Actions/Inventory/
app/Domain/Inventory/DTOs/
app/Domain/Inventory/Enums/
app/Domain/Inventory/Services/
app/Domain/Inventory/Models/
app/Domain/Master/Models/
tests/Unit/Inventory/
```

## Class penting

- `StockMovementService`
- `StockAvailabilityService`
- `DocumentNumberService`
- `InventoryAuditService`
- `StoreGoodsReceiptAction` / `PostGoodsReceiptAction`
- `StoreGoodsIssueAction` / `PostGoodsIssueAction`
- `StoreGoodsReturnAction` / `PostGoodsReturnAction`
- `StoreStockMutationAction` / `PostStockMutationAction`

## Enum utama

- `TransactionStatus`
- `StockDirection`
- `StockMovementType`
- `ItemCondition`
- `ReturnFinalAction`

## Catatan migrasi

Migration inventory yang sebelumnya hanya berisi `id` dan timestamp sudah dilengkapi kolom domain utama, foreign key, index, dan unique key.

Urutan migration detail transaksi juga dirapikan supaya header dibuat sebelum detail.

## Catatan PHPStan

`phpstan.neon` diberi ignore sementara untuk noise generic Eloquent scaffold:

- `missingType.generics`
- `missingType.iterableValue`
- `property.notFound`

Ini bukan blocker runtime. Nanti saat model/relationship sudah matang di step transaksi, bisa diperketat bertahap dengan PHPDoc property/relation yang lebih eksplisit.
