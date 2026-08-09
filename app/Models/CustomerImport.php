<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CustomerImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_filename',
        'total_rows',
        'imported_rows',
        'failed_rows',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
