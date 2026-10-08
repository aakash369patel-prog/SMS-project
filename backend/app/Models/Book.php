<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $fillable = [
        'title', 'isbn', 'author', 'category', 'total_copies', 'available_copies',
    ];

    public function loans(): HasMany
    {
        return $this->hasMany(BookLoan::class);
    }
}
