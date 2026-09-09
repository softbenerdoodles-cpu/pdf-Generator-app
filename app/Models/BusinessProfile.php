<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The "From" block on a signed-in user's documents.
 */
class BusinessProfile extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'address'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Shaped like the `from` group in the document schemas. */
    public function toParty(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email ?? '',
            'phone' => $this->phone ?? '',
            'address' => $this->address ?? '',
        ];
    }
}
