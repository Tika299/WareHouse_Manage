<?php

namespace App\Http\Controllers;

use App\Models\{Product, PurchaseOrder, PurchaseDetail, PurchaseReturn, Account, Supplier, CreditLog, StockLog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ImportController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with('supplier');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $searchId = preg_replace('/[^0-9]/', '', $search);

            if ($searchId !== '') {
                $query->where('id', $searchId);
            }
        }

        $query->when($request->supplier_id, function ($q) use ($request) {
            return $q->where('supplier_id', $request->supplier_id);
        });

        if ($request->filled('status')) {
            switch ($request->status) {
                case 'paid':
                    $query->whereColumn('paid_amount', '>=', 'total_final_amount');
                    break;

                case 'debt':
                    $query->whereColumn('paid_amount', '<', 'total_final_amount');
                    break;

                case 'received':
                    $query->where(function ($q) {
                        $q->whereNull('status')
                        ->orWhere('status', 'received');
                    });
                    break;

                case 'partially_returned':
                    $query->where('status', 'partially_returned');
                    break;

                case 'returned':
                    $query->where('status', 'returned');
                    break;
            }
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        $orders = $query->latest()->paginate(15);
        $orders->appends($request->all());

        $selectedSupplier = null;
        if ($request->filled('supplier_id')) {
            $selectedSupplier = \App\Models\Supplier::find($request->supplier_id);
        }

        return view('imports.index', [
            'orders' => $orders,
            'selectedSupplier' => $selectedSupplier,
            'activeGroup' => 'inventory',
            'activeName' => 'imports'
        ]);
    }

    public function create()
    {
        $accounts = Account::all();
        return view('imports.create', compact('accounts'), ['activeGroup' => 'inventory', 'activeName' => 'imports']);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'account_id' => 'required|exists:accounts,id',
            'total_product_value' => 'required|numeric|gt:0',
            'extra_cost' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|gt:0',
            'items.*.import_price' => 'required|numeric|min:0',
        ]);

        $validator->after(function ($validator) use ($request) {
            $totalProductValue = (float) $request->input('total_product_value', 0);
            $extraCost = (float) $request->input('extra_cost', 0);
            $paidAmount = (float) $request->input('paid_amount', 0);
            $totalFinalAmount = $totalProductValue + $extraCost;

            if ($paidAmount > $totalFinalAmount) {
                $validator->errors()->add('paid_amount', 'Số tiền thanh toán không được vượt quá tổng thanh toán.');
            }
        });

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();
        $totalProductValue = (float) $validated['total_product_value'];
        $extraCost = (float) ($validated['extra_cost'] ?? 0);
        $paidAmount = (float) ($validated['paid_amount'] ?? 0);

        if ($totalProductValue <= 0) {
            return back()
                ->withErrors(['total_product_value' => 'Tổng tiền hàng phải lớn hơn 0 để tránh lỗi chia cho 0.'])
                ->withInput();
        }

        return DB::transaction(function () use ($validated, $totalProductValue, $extraCost, $paidAmount) {
            $order = PurchaseOrder::create([
                'supplier_id' => $validated['supplier_id'],
                'account_id' => $validated['account_id'],
                'total_product_value' => $totalProductValue,
                'extra_cost' => $extraCost,
                'total_final_amount' => $totalProductValue + $extraCost,
                'paid_amount' => $paidAmount,
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                if ($totalProductValue <= 0) {
                    throw new \Exception('Tổng tiền hàng phải lớn hơn 0 để tính giá vốn.');
                }

                $ratio = ($item['quantity'] * $item['import_price']) / $totalProductValue;
                $allocated = ($ratio * $extraCost) / $item['quantity'];
                $finalUnitCost = $item['import_price'] + $allocated;

                PurchaseDetail::create([
                    'purchase_order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'import_price' => $item['import_price'],
                    'allocated_cost' => $allocated,
                    'final_unit_cost' => $finalUnitCost,
                ]);

                $oldValue = $product->stock_quantity * $product->cost_price;
                $newValue = $item['quantity'] * $finalUnitCost;
                $newQty = $product->stock_quantity + $item['quantity'];
                $newCostPrice = ($oldValue + $newValue) / $newQty;

                $product->update([
                    'cost_price' => $newCostPrice,
                    'stock_quantity' => $newQty,
                ]);

                StockLog::create([
                    'product_id' => $product->id,
                    'ref_type' => 'import',
                    'ref_id' => $order->id,
                    'change_qty' => $item['quantity'],
                    'final_qty' => $newQty,
                ]);
            }

            $debt = $order->total_final_amount - $order->paid_amount;
            if ($debt > 0) {
                $supplier = Supplier::findOrFail($validated['supplier_id']);
                $oldDebt = $supplier->total_debt;

                $supplier->increment('total_debt', $debt);

                CreditLog::create([
                    'target_type' => 'supplier',
                    'target_id' => $supplier->id,
                    'ref_type' => 'order',
                    'ref_id' => $order->id,
                    'change_amount' => $debt,
                    'new_balance' => $oldDebt + $debt,
                    'note' => 'Nợ từ phiếu nhập hàng #' . $order->id,
                ]);
            }

            if ($order->paid_amount > 0) {
                $account = Account::lockForUpdate()->findOrFail($validated['account_id']);

                if ($account->current_balance < $order->paid_amount) {
                    throw new \Exception('Số dư sổ quỹ không đủ để thanh toán phiếu nhập này.');
                }

                $account->decrement('current_balance', $order->paid_amount);
            }

            return redirect()
                ->route('imports.index')
                ->with('msg', 'Nhập hàng và tính lại giá vốn thành công!');
        });
    }

    public function show($id)
    {
        $order = PurchaseOrder::with(['supplier', 'account', 'details.product'])->findOrFail($id);
        $purchaseReturnCount = PurchaseReturn::where('purchase_order_id', $order->id)->count();
        return view('imports.show', [
            'order' => $order,
            'purchaseReturnCount' => $purchaseReturnCount,
            'activeGroup' => 'inventory',
            'activeName' => 'imports'
        ]);
    }

    public function payDebt(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|gt:0',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        return DB::transaction(function () use ($request, $id) {
            $import = PurchaseOrder::lockForUpdate()->findOrFail($id);
            $supplier = Supplier::lockForUpdate()->findOrFail($import->supplier_id);
            $account = Account::lockForUpdate()->findOrFail($import->account_id);

            $remainingDebt = $import->total_final_amount - $import->paid_amount;
            $amount = (float) $request->amount;

            if ($amount > $remainingDebt) {
                return back()
                    ->withErrors([
                        'amount' => 'Số tiền thanh toán không được vượt quá công nợ còn lại: ' . number_format($remainingDebt) . ' đ.'
                    ])
                    ->withInput();
            }

            if ($remainingDebt <= 0) {
                return back()
                    ->withErrors([
                        'amount' => 'Phiếu nhập này đã được thanh toán đủ, không thể thanh toán thêm.'
                    ])
                    ->withInput();
            }

            $newPaidAmount = $import->paid_amount + $amount;
            $newSupplierDebt = $supplier->total_debt - $amount;
            $newAccountBalance = $account->current_balance - $amount;

            if ($newSupplierDebt < 0) {
                throw new \Exception('Công nợ nhà cung cấp không được âm.');
            }

            if ($newAccountBalance < 0) {
                throw new \Exception('Số dư tài khoản không đủ để thanh toán.');
            }

            $voucher = \App\Models\CashVoucher::create([
                'voucher_type' => 'payment',
                'category' => 'debt_supplier',
                'account_id' => $account->id,
                'supplier_id' => $supplier->id,
                'amount' => $amount,
                'note' => 'Thanh toán bổ sung công nợ phiếu nhập #' . $import->id,
            ]);

            $import->update([
                'paid_amount' => $newPaidAmount,
            ]);

            $supplier->update([
                'total_debt' => $newSupplierDebt,
            ]);

            $account->update([
                'current_balance' => $newAccountBalance,
            ]);

            CreditLog::create([
                'target_type' => 'supplier',
                'target_id' => $supplier->id,
                'ref_type' => 'voucher',
                'ref_id' => $voucher->id,
                'change_amount' => -$amount,
                'new_balance' => $newSupplierDebt,
                'note' => 'Thanh toán bổ sung công nợ phiếu nhập #' . $import->id,
            ]);

            return redirect()
                ->route('imports.show', $import->id)
                ->with('msg', 'Đã thanh toán bổ sung công nợ thành công!');
        });
    }
}
