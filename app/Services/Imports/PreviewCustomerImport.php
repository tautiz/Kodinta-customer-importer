<?php

namespace App\Services\Imports;

use App\DTOs\CustomerImportPreview;

final readonly class PreviewCustomerImport
{
    public function __construct(
        private CsvCustomerReader $reader,
        private CustomerRowValidator $validator,
    ) {}

    public function handle(string $path): CustomerImportPreview
    {
        return new CustomerImportPreview(
            $this->validator->validate($this->reader->read($path)),
        );
    }
}
