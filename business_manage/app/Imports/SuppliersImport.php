<?php

namespace App\Imports;

use App\Models\Supplier;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class SuppliersImport implements ToCollection, WithStartRow, WithChunkReading, SkipsEmptyRows
{
    public int $created = 0;
    public int $updated = 0;
    public int $skipped = 0;

    public function startRow(): int
    {
        return 2; // bỏ dòng tiêu đề
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            /**
             * Mapping cột Excel NCC:
             * 0  Tên nhà cung cấp *
             * 1  Mã nhà cung cấp
             * 4  Điện thoại
             * 16 Địa chỉ 1
             * 17 Địa chỉ 2
             * 18 Tỉnh/ Thành phố
             * 19 Quận huyện
             * 20 Nợ hiện tại
             */

            $name = trim((string) ($row[0] ?? ''));

            if ($name === '') {
                $this->skipped++;
                continue;
            }

            $phone = $this->cleanPhone($row[4] ?? '');

            $addressParts = [
                trim((string) ($row[16] ?? '')),
                trim((string) ($row[19] ?? '')),
                trim((string) ($row[18] ?? '')),
            ];

            $address = implode(', ', array_filter($addressParts));

            $totalDebt = $this->cleanMoney($row[20] ?? 0);

            $data = [
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
                'total_debt' => $totalDebt,
            ];

            // Có SĐT thì ưu tiên cập nhật theo SĐT
            if ($phone !== '') {
                $supplier = Supplier::where('phone', $phone)->first();

                if ($supplier) {
                    $supplier->update($data);
                    $this->updated++;
                } else {
                    Supplier::create($data);
                    $this->created++;
                }

                continue;
            }

            // Không có SĐT thì cập nhật theo tên để tránh trùng NCC
            $supplier = Supplier::where('name', $name)->first();

            if ($supplier) {
                $supplier->update($data);
                $this->updated++;
            } else {
                Supplier::create($data);
                $this->created++;
            }
        }
    }

    private function cleanPhone($value): string
    {
        $phone = trim((string) $value);

        // Trường hợp Excel đọc thành 943664568.0
        $phone = preg_replace('/\.0$/', '', $phone);

        // Chỉ giữ số
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Nếu mất số 0 đầu
        if (strlen($phone) === 9) {
            $phone = '0' . $phone;
        }

        return $phone;
    }

    private function cleanMoney($value): int
    {
        $value = trim((string) $value);

        if ($value === '') {
            return 0;
        }

        $isNegative = str_starts_with($value, '-');

        $value = str_replace('-', '', $value);

        // Giữ lại số, dấu chấm, dấu phẩy
        $value = preg_replace('/[^0-9\.,]/', '', $value);

        // Ví dụ 0,000 thì lấy phần trước dấu phẩy = 0
        if (str_contains($value, ',')) {
            $parts = explode(',', $value);
            $value = $parts[0];
        }

        // Ví dụ 34.385.613 => 34385613
        $value = str_replace('.', '', $value);

        $amount = (int) $value;

        return $isNegative ? -$amount : $amount;
    }
}