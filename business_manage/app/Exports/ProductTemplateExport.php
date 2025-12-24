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
            'don_vi_tinh',      // Cột C
            'gia_von',          // Cột D
            'muc_cong_le',      // Cột E
            'muc_cong_si',      // Cột F
            'ton_kho',          // Cột G
            'ton_toi_thieu'     // Cột H
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