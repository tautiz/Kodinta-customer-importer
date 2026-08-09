<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_import_id',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'birth_date',
        'country',
    ];

    protected function casts(): array
    {
        return ['birth_date' => 'date'];
    }

    public function customerImport(): BelongsTo
    {
        return $this->belongsTo(CustomerImport::class);
    }
}
