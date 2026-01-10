<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PricingController extends Controller
{
    public function index()
    {
        // Lấy giá trị hệ số (Factor) trung bình hiện tại của các sản phẩm để hiển thị lên form
        // Nếu bạn vừa chạy migration xong, nó sẽ lấy giá trị mặc định (1.5, 1.1, 1.2...)
        $currentFactors = [
            'retail'     => Product::avg('factor_retail') ?? 1.5,
            'wholesale'  => Product::avg('factor_wholesale') ?? 1.1,
            'ctv'        => Product::avg('factor_ctv') ?? 1.2,
            'eco_margin' => Product::avg('factor_eco_margin') ?? 0.5,
            'eco_fee'    => Product::avg('factor_eco_fee') ?? 0.3,
        ];

        return view('products.pricing.index', compact('currentFactors'), [
            'activeGroup' => 'inventory',
            'activeName' => 'pricing'
        ]);
    }

    public function updateAll(Request $request)
    {
        // Validate các hệ số nhân nhập vào
        $request->validate([
            'factor_retail'     => 'required|numeric|min:0',
            'factor_wholesale'  => 'required|numeric|min:0',
            'factor_ctv'        => 'required|numeric|min:0',
            'factor_eco_margin' => 'required|numeric|min:0',
            'factor_eco_fee'    => 'required|numeric|min:0|max:0.99', // Phí sàn không được >= 1 (100%) tránh lỗi chia cho 0
        ]);

        /**
         * SPEC MỚI:
         * Giá lẻ = Vốn * factor_retail
         * Giá sỉ = Vốn * factor_wholesale
         * Giá CTV = Vốn * factor_ctv
         * Giá sàn = Vốn * (1 + margin_eco) / (1 - fee_eco)
         */

        // Cập nhật các hệ số nhân cho TOÀN BỘ sản phẩm trong 1 câu lệnh SQL duy nhất
        Product::query()->update([
            'factor_retail'     => $request->factor_retail,
            'factor_wholesale'  => $request->factor_wholesale,
            'factor_ctv'        => $request->factor_ctv,
            'factor_eco_margin' => $request->factor_eco_margin,
            'factor_eco_fee'    => $request->factor_eco_fee,
            'updated_at'        => now(),
        ]);

        return back()->with('msg', 'Đã cập nhật công thức tính giá (Hệ số nhân) thành công cho toàn bộ kho hàng!');
    }
}
