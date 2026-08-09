<?php

namespace App\Services\Imports;

use App\DTOs\CustomerImportPreview;
use App\DTOs\ImportResult;
use App\Models\Customer;
use App\Models\CustomerImport;
use Illuminate\Support\Facades\DB;

final class ImportCustomers
{
    public function handle(CustomerImportPreview $preview, string $originalFilename): ImportResult
    {
        return DB::transaction(function () use ($preview, $originalFilename): ImportResult {
            $import = CustomerImport::create([
                'original_filename' => $originalFilename,
                'total_rows' => $preview->totalRows(),
                'imported_rows' => 0,
                'failed_rows' => $preview->totalRows(),
            ]);

            $now = now();
            $records = [];

            foreach ($preview->rows as $row) {
                if (! $row->isValid()) {
                    continue;
                }

                $records[] = [
                    'customer_import_id' => $import->id,
                    ...$row->data,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $importedRows = 0;

            foreach (array_chunk($records, 250) as $chunk) {
                $importedRows += Customer::query()->insertOrIgnore($chunk);
            }

            $failedRows = $preview->totalRows() - $importedRows;
            $import->update([
                'imported_rows' => $importedRows,
                'failed_rows' => $failedRows,
            ]);

            return new ImportResult($import->id, $preview->totalRows(), $importedRows, $failedRows);
        });
    }
}
