<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerImport;
use App\Services\Imports\ImportCustomers;
use App\Services\Imports\PreviewCustomerImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CustomerImportServicesTest extends TestCase
{
    use RefreshDatabase;

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
    public function it_identifies_database_file_and_date_validation_failures(): void
    {
        $previousImport = CustomerImport::query()->create([
            'original_filename' => 'old.csv',
            'total_rows' => 1,
            'imported_rows' => 1,
            'failed_rows' => 0,
        ]);
        Customer::query()->create([
            'customer_import_id' => $previousImport->id,
            'first_name' => 'Jonas',
            'last_name' => 'Petrauskas',
            'email' => 'jonas.petrauskas@example.com',
            'birth_date' => '1990-01-01',
            'country' => 'Lietuva',
        ]);

        $tomorrow = now()->addDay()->toDateString();
        $preview = app(PreviewCustomerImport::class)->handle($this->csv(<<<CSV
first_name,last_name,email,phone_number,birth_date,country
Rasa,Jankauskaitė,RASA.JANKAUSKAITE@example.com,,1988-04-21,Lietuva
Jonas,Petrauskas,jonas.petrauskas@example.com,,1990-01-01,Lietuva
Mantas,Žilinskas,mantas.zilinskas@example.com,,{$tomorrow},Lietuva
Rasa,Dubaitė,rasa.jankauskaite@example.com,,1991-01-01,Lietuva
CSV));

        $this->assertSame(4, $preview->totalRows());
        $this->assertSame(1, $preview->validRows());
        $this->assertSame(3, $preview->invalidRows());
        $this->assertStringContainsString('already been imported', implode(' ', $preview->rows[1]->errors));
        $this->assertStringContainsString('future', implode(' ', $preview->rows[2]->errors));
        $this->assertStringContainsString('duplicated within', implode(' ', $preview->rows[3]->errors));
    }

    #[Test]
    public function it_imports_only_valid_rows_and_records_the_actual_summary(): void
    {
        $preview = app(PreviewCustomerImport::class)->handle($this->csv(<<<'CSV'
first_name,last_name,email,phone_number,birth_date,country
Ieva,Stankevičiūtė,IEVA.STANKEVICIUTE@example.com,,1992-08-14,Lietuva
,Trūksta,negaliojantis@example.com,,1990-01-01,Lietuva
CSV));

        $result = app(ImportCustomers::class)->handle($preview, 'customers.csv');

        $this->assertSame(2, $result->totalRows);
        $this->assertSame(1, $result->importedRows);
        $this->assertSame(1, $result->failedRows);
        $this->assertDatabaseHas('customers', ['email' => 'ieva.stankeviciute@example.com']);
        $this->assertDatabaseMissing('customers', ['email' => 'negaliojantis@example.com']);
        $this->assertDatabaseHas('customer_imports', [
            'id' => $result->importId,
            'total_rows' => 2,
            'imported_rows' => 1,
            'failed_rows' => 1,
        ]);
    }

    #[Test]
    public function it_allows_an_empty_birth_date(): void
    {
        $preview = app(PreviewCustomerImport::class)->handle($this->csv(<<<'CSV'
first_name,last_name,email,phone_number,birth_date,country
Gabija,Kazlauskaitė,gabija.kazlauskaite@example.com,,,Lietuva
CSV));

        $this->assertSame(1, $preview->validRows());
        $this->assertNull($preview->rows[0]->data['birth_date']);
    }

    private function csv(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'customers-');
        $this->assertNotFalse($path);
        file_put_contents($path, $contents."\n");
        $this->temporaryFiles[] = $path;

        return $path;
    }
}
