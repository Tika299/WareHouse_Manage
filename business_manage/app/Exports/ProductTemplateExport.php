<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductTemplateExport implements WithHeadings, ShouldAutoSize, WithStyles
{
    /**
     * Định nghĩa hàng tiêu đề cho file Excel mẫu
     */
    public function headings(): array
    {
        return [
            'ma_sku',           // Cột A
            'ten_san_pham',     // Cột B
            'mo_ta',            // Cột C
            'don_vi_tinh',      // Cột D
            'gia_von',          // Cột E
            'muc_cong_le',      // Cột F
            'muc_cong_si',      // Cột G
            'ton_kho',          // Cột H
            'ton_toi_thieu'     // Cột I
        ];
    }

    /**
     * Định dạng cho hàng tiêu đề (In đậm, màu nền) để người dùng dễ nhìn
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '28A745'] // Màu xanh lá chuẩn Success
                ]
            ],
        ];
    }
}