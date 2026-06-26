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
            $search = $request->input('search');
            $searchId = preg_replace('/[^0-9]/', '', $search);

            $query->where(function ($q) use ($searchId) {
                $q->where('id', $searchId);
            });
        }

        $query->when($request->supplier_id, function ($q) {
            return $q->where('supplier_id', request('supplier_id'));
        });

        if ($request->filled('status')) {
            if ($request->status == 'paid') {
                $query->whereColumn('paid_amount', '>=', 'total_final_amount');
            } elseif ($request->status == 'debt') {
                $query->whereColumn('paid_amount', '<', 'total_final_amount');
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
                $validator->errors()->add('paid_amount', 'Sá»‘ tiá»n thanh toÃ¡n khÃ´ng Ä‘Æ°á»£c vÆ°á»£t quÃ¡ tá»•ng thanh toÃ¡n.');
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
                ->withErrors(['total_product_value' => 'Tá»•ng tiá»n hÃ ng pháº£i lá»›n hÆ¡n 0 Ä‘á»ƒ trÃ¡nh lá»—i chia cho 0.'])
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
                    throw new \Exception('Tá»•ng tiá»n hÃ ng pháº£i lá»›n hÆ¡n 0 Ä‘á»ƒ tÃ­nh giÃ¡ vá»‘n.');
                }

                $ratio = ($item['quantity'] * $item['import_price']) / $totalProductValue;
                $allocated = ($ratio * $extraCost) / $item['quantity'];
                $finalUnitCost = $item['import_price'] + $allocated;

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
                    'final_qty' => $newQty
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
                    'note' => 'Ná»£ tá»« phiáº¿u nháº­p hÃ ng #' . $order->id
                ]);
            }

            if ($order->paid_amount > 0) {
                Account::findOrFail($validated['account_id'])->decrement('current_balance', $order->paid_amount);
            }

            return redirect()->route('imports.index')->with('msg', 'Nháº­p hÃ ng vÃ  tÃ­nh láº¡i giÃ¡ vá»‘n thÃ nh cÃ´ng!');
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
                        'amount' => 'Sá»‘ tiá»n thanh toÃ¡n khÃ´ng Ä‘Æ°á»£c vÆ°á»£t quÃ¡ cÃ´ng ná»£ cÃ²n láº¡i: ' . number_format($remainingDebt) . ' Ä‘.'
                    ])
                    ->withInput();
            }

            if ($remainingDebt <= 0) {
                return back()
                    ->withErrors([
                        'amount' => 'Phiáº¿u nháº­p nÃ y Ä‘Ã£ Ä‘Æ°á»£c thanh toÃ¡n Ä‘á»§, khÃ´ng thá»ƒ thanh toÃ¡n thÃªm.'
                    ])
                    ->withInput();
            }

            $newPaidAmount = $import->paid_amount + $amount;
            $newSupplierDebt = $supplier->total_debt - $amount;
            $newAccountBalance = $account->current_balance - $amount;

            if ($newSupplierDebt < 0) {
                throw new \Exception('CÃ´ng ná»£ nhÃ  cung cáº¥p khÃ´ng Ä‘Æ°á»£c Ã¢m.');
            }

            if ($newAccountBalance < 0) {
                throw new \Exception('Sá»‘ dÆ° tÃ i khoáº£n khÃ´ng Ä‘á»§ Ä‘á»ƒ thanh toÃ¡n.');
            }

            $voucher = \App\Models\CashVoucher::create([
                'voucher_type' => 'payment',
                'category' => 'debt_supplier',
                'account_id' => $account->id,
                'supplier_id' => $supplier->id,
                'amount' => $amount,
                'note' => 'Thanh toÃ¡n bá»• sung cÃ´ng ná»£ phiáº¿u nháº­p #' . $import->id,
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
                'note' => 'Thanh toÃ¡n bá»• sung cÃ´ng ná»£ phiáº¿u nháº­p #' . $import->id,
            ]);

            return redirect()
                ->route('imports.show', $import->id)
                ->with('msg', 'ÄÃ£ thanh toÃ¡n bá»• sung cÃ´ng ná»£ thÃ nh cÃ´ng!');
        });
    }
}
