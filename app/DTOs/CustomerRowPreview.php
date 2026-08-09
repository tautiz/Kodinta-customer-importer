<?php

namespace App\DTOs;

final readonly class CustomerRowPreview
{
    /**
     * @param  array{first_name: string, last_name: string, email: string, phone_number: ?string, birth_date: ?string, country: string}  $data
     * @param  list<string>  $errors
     */
    public function __construct(
        public int $rowNumber,
        public array $data,
        public array $errors,
    ) {}

    public function isValid(): bool
    {
        return $this->errors === [];
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'row_number' => $this->rowNumber,
            ...$this->data,
            'errors' => $this->errors,
            'is_valid' => $this->isValid(),
        ];
    }
}
