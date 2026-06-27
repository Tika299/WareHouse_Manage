<?php

namespace App\Http\Controllers;

use App\Models\CreditLog;
use App\Models\Product;
use App\Models\PurchaseDetail;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnDetail;
use App\Models\StockLog;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseReturn::with(['purchaseOrder', 'supplier', 'details.product'])->latest();

        if ($request->filled('purchase_order_id')) {
            $query->where('purchase_order_id', $request->purchase_order_id);
        }

        $returns = $query->paginate(15)->withQueryString();

        return view('purchase_returns.index', [
            'returns' => $returns,
            'activeGroup' => 'finance',
            'activeName' => 'purchase_returns',
        ]);
    }

    public function show($id)
    {
        $return = PurchaseReturn::with(['purchaseOrder.details.product', 'supplier', 'details.product'])
            ->findOrFail($id);

        return view('purchase_returns.show', [
            'return' => $return,
            'activeGroup' => 'finance',
            'activeName' => 'purchase_returns',
        ]);
    }

    public function create(Request $request)
    {
        $selectedOrder = null;

        if ($request->filled('purchase_order_id')) {
            $selectedOrder = PurchaseOrder::with(['supplier', 'details.product'])
                ->find($request->purchase_order_id);
        }

        return view('purchase_returns.create', [
            'selectedOrder' => $selectedOrder,
            'activeGroup' => 'finance',
            'activeName' => 'purchase_returns',
        ]);
    }

    public function searchOrdersAjax(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $page = max(1, (int) $request->get('page', 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $query = PurchaseOrder::query()->with('supplier');

        if ($search !== '') {
            $normalized = strtoupper(str_replace([' ', '#', '-'], '', $search));
            $digitsOnly = preg_replace('/\D+/', '', $search);

            $query->where(function ($q) use ($search, $normalized, $digitsOnly) {
                if ($digitsOnly !== '') {
                    $q->orWhere('id', (int) $digitsOnly);
                    $q->orWhereRaw("CONCAT('#PN', LPAD(id, 5, '0')) LIKE ?", ['%' . $search . '%']);
                    $q->orWhereRaw("CONCAT('PN', LPAD(id, 5, '0')) LIKE ?", ['%' . $normalized . '%']);
                }

                $q->orWhereHas('supplier', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                });
            });
        }

        $totalCount = (clone $query)->count();

        $orders = $query->latest()
            ->offset($offset)
            ->limit($perPage)
            ->get()
            ->map(function ($order) {
                $label = '#PN' . str_pad((string) $order->id, 5, '0', STR_PAD_LEFT);
                $supplierName = $order->supplier->name ?? 'Không rõ NCC';

                return [
                    'id' => $order->id,
                    'text' => "{$label} - {$supplierName} (" . number_format((float) $order->total_final_amount) . "đ)",
                    'supplier_name' => $supplierName,
                    'total_final_amount' => (float) $order->total_final_amount,
                ];
            })
            ->values();

        return response()->json([
            'results' => $orders,
            'pagination' => [
                'more' => ($offset + $perPage) < $totalCount,
            ],
        ]);
    }

    public function getOrderDetails($id)
    {
        $order = PurchaseOrder::with(['supplier', 'details.product'])->find($id);

        if (!$order) {
            return response()->json([]);
        }

        $purchasedQtyByProduct = PurchaseDetail::query()
            ->where('purchase_order_id', $order->id)
            ->select('product_id', DB::raw('SUM(quantity) as purchased_qty'))
            ->groupBy('product_id')
            ->pluck('purchased_qty', 'product_id')
            ->map(fn($qty) => (int) $qty)
            ->all();

        $returnedQtyByProduct = PurchaseReturnDetail::query()
            ->whereHas('purchaseReturn', function ($q) use ($order) {
                $q->where('purchase_order_id', $order->id);
            })
            ->select('product_id', DB::raw('SUM(quantity) as returned_qty'))
            ->groupBy('product_id')
            ->pluck('returned_qty', 'product_id')
            ->map(fn($qty) => (int) $qty)
            ->all();

        $details = $order->details->map(function ($detail) use ($purchasedQtyByProduct, $returnedQtyByProduct) {
            $product = $detail->product;
            $purchasedQty = (int) ($purchasedQtyByProduct[$detail->product_id] ?? 0);
            $returnedQty = (int) ($returnedQtyByProduct[$detail->product_id] ?? 0);
            $availableQty = max(0, $purchasedQty - $returnedQty);
            $unitPrice = (float) ($detail->import_price ?? $detail->final_unit_cost ?? 0);

            return [
                'product_id' => $detail->product_id,
                'product_name' => $product->name ?? 'Sản phẩm đã xóa',
                'sku' => $product->sku ?? '---',
                'purchased_qty' => $purchasedQty,
                'returned_qty' => $returnedQty,
                'available_qty' => $availableQty,
                'return_price' => $unitPrice,
                'return_value' => $unitPrice * $availableQty,
            ];
        })->values();

        return response()->json([
            'order' => [
                'id' => $order->id,
                'supplier_name' => $order->supplier->name ?? 'Không rõ NCC',
                'supplier_id' => $order->supplier_id,
                'total_final_amount' => (float) $order->total_final_amount,
            ],
            'details' => $details,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|integer|exists:purchase_orders,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:1000',
        ]);

        try {
            return DB::transaction(function () use ($request, $validated) {
                $order = PurchaseOrder::with(['supplier', 'details.product'])
                    ->lockForUpdate()
                    ->findOrFail($validated['purchase_order_id']);

                $supplier = Supplier::lockForUpdate()->findOrFail($order->supplier_id);

                $purchasedQtyByProduct = PurchaseDetail::query()
                    ->where('purchase_order_id', $order->id)
                    ->select('product_id', DB::raw('SUM(quantity) as purchased_qty'))
                    ->groupBy('product_id')
                    ->pluck('purchased_qty', 'product_id')
                    ->map(fn($qty) => (int) $qty)
                    ->all();

                $returnedQtyByProduct = PurchaseReturnDetail::query()
                    ->whereHas('purchaseReturn', function ($q) use ($order) {
                        $q->where('purchase_order_id', $order->id);
                    })
                    ->select('product_id', DB::raw('SUM(quantity) as returned_qty'))
                    ->groupBy('product_id')
                    ->pluck('returned_qty', 'product_id')
                    ->map(fn($qty) => (int) $qty)
                    ->all();

                $requestedQtyByProduct = [];
                $positiveItems = array_values(array_filter($validated['items'], function ($item) {
                    return (int) ($item['quantity'] ?? 0) > 0;
                }));

                if (empty($positiveItems)) {
                    throw new \Exception('Vui lòng nhập ít nhất một sản phẩm có số lượng trả lớn hơn 0.');
                }

                foreach ($positiveItems as $index => $item) {
                    $productId = (int) $item['product_id'];
                    $quantity = (int) $item['quantity'];

                    if (!array_key_exists($productId, $purchasedQtyByProduct)) {
                        throw new \Exception('Dòng #' . ($index + 1) . ': sản phẩm này không có trong chi tiết phiếu nhập gốc.');
                    }

                    $availableQty = (int) ($purchasedQtyByProduct[$productId] ?? 0) - (int) ($returnedQtyByProduct[$productId] ?? 0);
                    $requestedQtyByProduct[$productId] = ($requestedQtyByProduct[$productId] ?? 0) + $quantity;

                    if ($requestedQtyByProduct[$productId] > $availableQty) {
                        throw new \Exception(
                            'Dòng #' . ($index + 1) . ': số lượng trả vượt quá số lượng còn có thể trả của sản phẩm #' .
                                $productId . '. Còn được trả: ' . $availableQty . '.'
                        );
                    }
                }

                $totalReturnValue = 0;
                $return = PurchaseReturn::create([
                    'purchase_order_id' => $order->id,
                    'supplier_id' => $supplier->id,
                    'return_code' => 'PR-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                    'returned_at' => now(),
                    'total_return_value' => 0,
                    'status' => 'completed',
                    'note' => $validated['note'] ?? null,
                ]);

                foreach ($positiveItems as $index => $item) {
                    $productId = (int) $item['product_id'];
                    $quantity = (int) $item['quantity'];

                    $detail = PurchaseDetail::query()
                        ->where('purchase_order_id', $order->id)
                        ->where('product_id', $productId)
                        ->firstOrFail();

                    $returnPrice = (float) ($detail->import_price ?? $detail->final_unit_cost ?? 0);
                    $returnValue = $quantity * $returnPrice;
                    $totalReturnValue += $returnValue;

                    PurchaseReturnDetail::create([
                        'purchase_return_id' => $return->id,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'return_price' => $returnPrice,
                        'return_value' => $returnValue,
                        'reason' => $validated['note'] ?? null,
                    ]);

                    $product = Product::lockForUpdate()->findOrFail($productId);
                    if ($product->stock_quantity < $quantity) {
                        throw new \Exception('Sản phẩm #' . $productId . ' không đủ tồn kho để hoàn trả.');
                    }

                    $newStockQty = $product->stock_quantity - $quantity;
                    $product->update(['stock_quantity' => $newStockQty]);

                    StockLog::create([
                        'product_id' => $product->id,
                        'ref_type' => 'purchase_return',
                        'ref_id' => $return->id,
                        'change_qty' => -$quantity,
                        'final_qty' => $newStockQty,
                    ]);
                }

                $return->update(['total_return_value' => $totalReturnValue]);

                $currentDebt = (float) $supplier->total_debt;
                if ($totalReturnValue > $currentDebt) {
                    throw new \Exception('Giá trị hoàn trả vượt quá công nợ hiện tại của nhà cung cấp.');
                }

                $newDebt = $currentDebt - $totalReturnValue;
                $supplier->update(['total_debt' => $newDebt]);

                CreditLog::create([
                    'target_type' => 'supplier',
                    'target_id' => $supplier->id,
                    'ref_type' => 'purchase_return',
                    'ref_id' => $return->id,
                    'change_amount' => -$totalReturnValue,
                    'new_balance' => $newDebt,
                    'note' => 'Hoàn trả hàng cho phiếu nhập #' . $order->id,
                ]);

                $purchasedTotalQty = array_sum($purchasedQtyByProduct);
                $returnedBeforeQty = array_sum($returnedQtyByProduct);
                $returnedNowQty = array_sum(array_map(fn ($item) => (int) $item['quantity'], $positiveItems));
                $returnedAfterQty = $returnedBeforeQty + $returnedNowQty;

                if ($returnedAfterQty <= 0) {
                    $newOrderStatus = 'received';
                } elseif ($returnedAfterQty < $purchasedTotalQty) {
                    $newOrderStatus = 'partially_returned';
                } else {
                    $newOrderStatus = 'returned';
                }

                $order->update([
                    'status' => $newOrderStatus,
                ]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Đã tạo phiếu hoàn trả thành công.',
                        'data' => $return->load(['purchaseOrder', 'supplier', 'details.product']),
                    ], 201);
                }

                return redirect()
                    ->route('purchase-returns.index')
                    ->with('msg', 'Đã tạo phiếu hoàn trả thành công.');
            });
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Tạo phiếu hoàn trả thất bại.',
                    'error' => $e->getMessage(),
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
