<?php

namespace Tests\Unit;

use App\Exceptions\InvalidCsvException;
use App\Services\Imports\CsvCustomerReader;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CsvCustomerReaderTest extends TestCase
{
    /** @var list<string> */
    private array $temporaryFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $path) {
            @unlink($path);
        }

        parent::tearDown();
    }

    #[Test]
    public function it_normalizes_headers_detects_semicolons_and_ignores_blank_rows(): void
    {
        $path = $this->csv("\xEF\xBB\xBFFirst name;Last name;Email;Phone;Birth date;Country\nĄžuolas;Žukauskas;AZUOLAS@PVZ.LT;;1987-12-10;Lietuva\n;;;;;\n");

        $rows = app(CsvCustomerReader::class)->read($path);

        $this->assertCount(1, $rows);
        $this->assertSame(2, $rows[0]['row_number']);
        $this->assertSame('Ąžuolas', $rows[0]['data']['first_name']);
        $this->assertNull($rows[0]['data']['phone_number']);
    }

    #[Test]
    public function it_rejects_a_file_with_missing_required_headers(): void
    {
        $path = $this->csv("first_name,email\nJonas,jonas@example.com\n");

        $this->expectException(InvalidCsvException::class);
        $this->expectExceptionMessage('Missing required columns');

        app(CsvCustomerReader::class)->read($path);
    }

    #[Test]
    public function it_rejects_a_header_only_file(): void
    {
        $path = $this->csv("first_name,last_name,email,birth_date,country\n");

        $this->expectException(InvalidCsvException::class);
        $this->expectExceptionMessage('does not contain any data rows');

        app(CsvCustomerReader::class)->read($path);
    }

    private function csv(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'customers-');
        $this->assertNotFalse($path);
        file_put_contents($path, $contents);
        $this->temporaryFiles[] = $path;

        return $path;
    }
}
