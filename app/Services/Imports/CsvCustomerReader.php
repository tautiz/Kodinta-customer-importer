<?php

namespace App\Services\Imports;

use App\Exceptions\InvalidCsvException;

final class CsvCustomerReader
{
    private const REQUIRED_HEADERS = ['first_name', 'last_name', 'email', 'birth_date', 'country'];

    private const ALL_HEADERS = ['first_name', 'last_name', 'email', 'phone_number', 'birth_date', 'country'];

    /**
     * @return list<array{row_number: int, data: array<string, mixed>, csv_errors: list<string>}>
     */
    public function read(string $path): array
    {
        if (! is_readable($path)) {
            throw new InvalidCsvException('The uploaded CSV file could not be read.');
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new InvalidCsvException('The uploaded CSV file could not be opened.');
        }

        try {
            $firstLine = fgets($handle);

            if ($firstLine === false || trim($firstLine) === '') {
                throw new InvalidCsvException('The CSV file is empty.');
            }

            $delimiter = $this->detectDelimiter($firstLine);
            $rawHeaders = str_getcsv($firstLine, $delimiter, '"', '');
            $headers = array_map(
                fn (string $header): string => $this->normalizeHeader($header),
                $rawHeaders,
            );
            $this->assertValidHeaders($headers);

            $rows = [];
            $rowNumber = 1;
            $maxRows = (int) config('imports.max_rows', 1000);

            while (($values = fgetcsv($handle, null, $delimiter, '"', '')) !== false) {
                $rowNumber++;

                if ($this->isBlankRow($values)) {
                    continue;
                }

                if (count($rows) >= $maxRows) {
                    throw new InvalidCsvException("The CSV file may contain at most {$maxRows} data rows.");
                }

                $csvErrors = [];

                if (count($values) !== count($headers)) {
                    $csvErrors[] = sprintf(
                        'Expected %d columns, but found %d.',
                        count($headers),
                        count($values),
                    );
                }

                $values = array_pad(array_slice($values, 0, count($headers)), count($headers), '');
                $mapped = array_combine($headers, $values) ?: [];
                $data = [];

                foreach (self::ALL_HEADERS as $header) {
                    $value = trim((string) ($mapped[$header] ?? ''));
                    if (($header === 'phone_number' || $header === 'birth_date') && $value === '') {
                        $value = null;
                    }

                    $data[$header] = $value;
                }

                if (! mb_check_encoding(
                    implode('', array_filter($data, fn ($value): bool => is_string($value))),
                    'UTF-8',
                )) {
                    $csvErrors[] = 'The row must use UTF-8 encoding.';
                }

                $rows[] = [
                    'row_number' => $rowNumber,
                    'data' => $data,
                    'csv_errors' => $csvErrors,
                ];
            }

            if ($rows === []) {
                throw new InvalidCsvException('The CSV file does not contain any data rows.');
            }

            return $rows;
        } finally {
            fclose($handle);
        }
    }

    private function detectDelimiter(string $headerLine): string
    {
        return substr_count($headerLine, ';') > substr_count($headerLine, ',') ? ';' : ',';
    }

    private function normalizeHeader(string $header): string
    {
        $normalized = strtolower(trim($header, "\xEF\xBB\xBF \t\n\r\0\x0B"));
        $normalized = preg_replace('/[\s-]+/', '_', $normalized) ?? $normalized;

        return match ($normalized) {
            'phone', 'telephone', 'phone_no' => 'phone_number',
            default => $normalized,
        };
    }

    /** @param list<string> $headers */
    private function assertValidHeaders(array $headers): void
    {
        $duplicates = array_diff_assoc($headers, array_unique($headers));

        if ($duplicates !== []) {
            throw new InvalidCsvException('The CSV header contains duplicate columns.');
        }

        $missing = array_diff(self::REQUIRED_HEADERS, $headers);

        if ($missing !== []) {
            throw new InvalidCsvException('Missing required columns: '.implode(', ', $missing).'.');
        }
    }

    /** @param list<string|null> $values */
    private function isBlankRow(array $values): bool
    {
        return count(array_filter($values, fn ($value): bool => trim((string) $value) !== '')) === 0;
    }
}
