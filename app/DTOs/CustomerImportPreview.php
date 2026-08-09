<?php

namespace App\DTOs;

final readonly class CustomerImportPreview
{
    /**
     * @param  list<CustomerRowPreview>  $rows
     */
    public function __construct(public array $rows) {}

    public function totalRows(): int
    {
        return count($this->rows);
    }

    public function validRows(): int
    {
        return count(array_filter($this->rows, fn (CustomerRowPreview $row): bool => $row->isValid()));
    }

    public function invalidRows(): int
    {
        return $this->totalRows() - $this->validRows();
    }
}
