<?php

namespace App\DTOs;

final readonly class ImportResult
{
    public function __construct(
        public int $importId,
        public int $totalRows,
        public int $importedRows,
        public int $failedRows,
    ) {}

    /** @return array<string, int> */
    public function toArray(): array
    {
        return [
            'import_id' => $this->importId,
            'total_rows' => $this->totalRows,
            'imported_rows' => $this->importedRows,
            'failed_rows' => $this->failedRows,
        ];
    }
}
