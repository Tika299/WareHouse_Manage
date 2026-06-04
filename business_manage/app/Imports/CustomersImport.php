<?php

namespace App\Imports;

use App\Models\Customer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class CustomersImport implements ToCollection, WithStartRow, WithChunkReading, SkipsEmptyRows
{
    public int $created = 0;
    public int $updated = 0;
    public int $skipped = 0;

    public function startRow(): int
    {
        // Bỏ dòng tiêu đề Excel
        return 2;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            /**
             * Mapping theo thứ tự cột Excel:
             * 0  Tên khách hàng *
             * 1  Mã khách hàng
             * 2  Email
             * 3  Điện thoại
             * 7  Địa chỉ
             * 8  Tỉnh thành
             * 9  Quận huyện
             * 10 Phường xã
             * 11 Địa chỉ - SĐT
             * 13 Địa chỉ - Họ tên
             */

            $name = trim((string) ($row[0] ?? ''));

            if ($name === '') {
                $name = trim((string) ($row[13] ?? ''));
            }

            $phone = $this->cleanPhone($row[3] ?? '');

            if ($phone === '') {
                $phone = $this->cleanPhone($row[11] ?? '');
            }

            if ($name === '') {
                $this->skipped++;
                continue;
            }

            $addressParts = [
                trim((string) ($row[7] ?? '')),
                trim((string) ($row[10] ?? '')),
                trim((string) ($row[9] ?? '')),
                trim((string) ($row[8] ?? '')),
            ];

            $address = implode(', ', array_filter($addressParts));

            $data = [
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
            ];

            if ($phone !== '') {
                $customer = Customer::where('phone', $phone)->first();

                if ($customer) {
                    $customer->update($data);
                    $this->updated++;
                } else {
                    Customer::create($data + [
                        'total_debt' => 0,
                    ]);
                    $this->created++;
                }
            } else {
                Customer::create($data + [
                    'total_debt' => 0,
                ]);
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

        // Nếu bị mất số 0 đầu, tự thêm lại
        if (strlen($phone) === 9) {
            $phone = '0' . $phone;
        }

        return $phone;
    }
}