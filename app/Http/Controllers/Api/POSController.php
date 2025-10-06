<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Product;
use App\Sale;
use App\SaleItem;
use App\StockMovement;
use App\ProductWarehouseStock;
use App\Warehouse;

class POSController extends Controller
{
    public function searchProduct(Request $request)
    {
        if (! auth()->user() || ! auth()->user()->hasPermission('pos.access')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $barcode = trim($request->query('barcode', ''));
        if ($barcode === '') {
            return response()->json(['success' => false, 'message' => 'Barcode diperlukan'], 422);
        }

        $product = Product::query()
            ->where('barcode', $barcode)
            ->orWhere('sku', $barcode)
            ->first();

        if (! $product) {
            return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'barcode' => $product->barcode,
                'price' => (float) ($product->price ?? $product->getPriceByType()),
            ],
        ]);
    }

    public function calculateTotal(Request $request)
    {
        if (! auth()->user() || ! auth()->user()->hasPermission('pos.access')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->all();

        $validator = Validator::make($data, [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount_type' => 'nullable|in:amount,percentage',
            'discount_value' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $subtotal += ((int) $item['qty']) * ((float) $item['unit_price']);
        }

        $discountType = $data['discount_type'] ?? 'amount';
        $discountValue = (float) ($data['discount_value'] ?? 0);

        if ($discountType === 'percentage') {
            $discountTotal = round($subtotal * ($discountValue / 100));
        } else {
            $discountTotal = min($discountValue, $subtotal);
        }

        $finalTotal = max(0, $subtotal - $discountTotal);

        return response()->json([
            'subtotal' => (float) $subtotal,
            'discount_total' => (float) $discountTotal,
            'final_total' => (float) $finalTotal,
        ]);
    }

    public function checkout(Request $request)
    {
        if (! auth()->user() || ! auth()->user()->hasPermission('pos.access')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->all();

        $validator = Validator::make($data, [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'discount_total' => 'required|numeric|min:0',
            'final_total' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'payment_amount' => 'required|numeric|min:0',
            'change_amount' => 'required|numeric|min:0',
            'warehouse_id' => 'required|integer|exists:warehouses,id',
        ]);

        $validator->after(function ($v) use ($data) {
            $warehouse = Warehouse::find($data['warehouse_id'] ?? null);
            if (! $warehouse) {
                return;
            }
            foreach ($data['items'] as $idx => $item) {
                $product = Product::find($item['product_id']);
                if (! $product) {
                    $v->errors()->add("items.$idx.product_id", 'Produk tidak ditemukan');
                    continue;
                }
                $stockRow = ProductWarehouseStock::query()->firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouse->id,
                    ],
                    [
                        'stock_on_hand' => 0,
                        'reserved_stock' => 0,
                        'safety_stock' => 0,
                    ]
                );
                if ($stockRow->stock_on_hand < (int) $item['qty']) {
                    $v->errors()->add("items.$idx.qty", 'Stok tidak mencukupi');
                }
            }
            $finalTotal = (float) ($data['final_total'] ?? 0);
            $paymentAmount = (float) ($data['payment_amount'] ?? 0);
            if ($paymentAmount < $finalTotal) {
                $v->errors()->add('payment_amount', 'Pembayaran kurang dari total');
            }
        });

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $saleNumber = 'POS-' . date('Ymd') . '-' . str_pad(Sale::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            $sale = Sale::create([
                'sale_number' => $saleNumber,
                'cashier_id' => Auth::id(),
                'subtotal' => $data['subtotal'],
                'discount_total' => $data['discount_total'],
                'final_total' => $data['final_total'],
                'payment_method' => $data['payment_method'],
                'payment_notes' => $data['payment_notes'] ?? null,
                'status' => 'PAID',
            ]);

            $warehouse = Warehouse::find($data['warehouse_id']);

            foreach ($data['items'] as $item) {
                $product = Product::find($item['product_id']);
                if (! $product) {
                    throw new \RuntimeException('Produk tidak ditemukan saat memproses penjualan.');
                }

                // Create sale item
                $saleItem = new SaleItem([
                    'product_id' => $product->id,
                    'qty' => (int) $item['qty'],
                    'unit_price' => (float) $item['unit_price'],
                    'price_tier' => 'retail',
                ]);
                $saleItem->total_price = $saleItem->qty * $saleItem->unit_price;
                $sale->saleItems()->save($saleItem);

                $stockRow = ProductWarehouseStock::query()->firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouse->id,
                    ],
                    [
                        'stock_on_hand' => 0,
                        'reserved_stock' => 0,
                        'safety_stock' => 0,
                    ]
                );

                $stockBefore = (int) $stockRow->stock_on_hand;
                $stockAfter = $stockBefore - (int) $item['qty'];

                $movement = StockMovement::createMovement($product->id, -((int) $item['qty']), 'out', [
                    'ref_type' => 'sale',
                    'ref_id' => $sale->id,
                    'note' => 'Penjualan #' . $sale->sale_number,
                    'performed_by' => Auth::id(),
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'warehouse_id' => $warehouse->id,
                    'warehouse' => $warehouse->code ?? null,
                ]);
                $movement->update(['qty' => abs((int) $item['qty'])]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'sale_number' => $sale->sale_number,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}