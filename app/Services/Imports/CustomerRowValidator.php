<?php

namespace App\Services\Imports;

use App\DTOs\CustomerRowPreview;
use App\Models\Customer;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Validation\Rule;

final readonly class CustomerRowValidator
{
    public function __construct(private ValidationFactory $validation) {}

    /**
     * @param  list<array{row_number: int, data: array<string, mixed>, csv_errors: list<string>}>  $rows
     * @return list<CustomerRowPreview>
     */
    public function validate(array $rows): array
    {
        $seenEmails = [];
        $preview = [];

        foreach ($rows as $row) {
            $data = $row['data'];
            $data['email'] = strtolower((string) $data['email']);

            $validator = $this->validation->make($data, [
                'first_name' => ['bail', 'required', 'string', 'max:100'],
                'last_name' => ['bail', 'required', 'string', 'max:100'],
                'email' => ['bail', 'required', 'email:rfc', 'max:255', Rule::unique(Customer::class, 'email')],
                'phone_number' => ['nullable', 'string', 'max:50'],
                'birth_date' => ['bail', 'nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
                'country' => ['bail', 'required', 'string', 'max:100'],
            ], [
                'birth_date.before_or_equal' => 'The birth date cannot be in the future.',
                'email.unique' => 'The email has already been imported.',
            ]);

            $errors = [...$row['csv_errors'], ...$validator->errors()->all()];
            $email = (string) $data['email'];

            if ($errors === [] && isset($seenEmails[$email])) {
                $errors[] = 'The email is duplicated within this CSV file.';
            }

            if ($errors === []) {
                $seenEmails[$email] = true;
            }

            $preview[] = new CustomerRowPreview($row['row_number'], $data, $errors);
        }

        return $preview;
    }
}
