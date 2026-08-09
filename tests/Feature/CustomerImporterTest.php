<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CustomerImporterTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_previews_rows_and_imports_only_valid_rows(): void
    {
        $this->withoutMiddleware();
        Storage::fake('local');
        $file = UploadedFile::fake()->createWithContent('customers.csv', <<<'CSV'
first_name,last_name,email,phone_number,birth_date,country
Rasa,Jankauskaitė,rasa.jankauskaite@example.com,,1988-04-21,Lietuva
,Petrauskas,negaliojantis@example.com,,1990-01-01,Lietuva
CSV);

        $this->post(route('imports.preview'), ['file' => $file])
            ->assertRedirect(route('imports.index'))
            ->assertSessionHas('total_rows', 2)
            ->assertSessionHas('valid_rows', 1)
            ->assertSessionHas('invalid_rows', 1);

        $this->post(route('imports.store'))
            ->assertRedirect(route('imports.index'))
            ->assertSessionHas('import_summary.imported_rows', 1)
            ->assertSessionHas('import_summary.failed_rows', 1);

        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseHas('customers', ['email' => 'rasa.jankauskaite@example.com']);
    }

    #[Test]
    public function the_page_is_available(): void
    {
        $this->withoutVite();

        $this->get('/')->assertOk()->assertSee('Klientu importas');
    }
}
