<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Someone the user bills often, kept so their details need typing once.
 */
class Customer extends Model
{
    protected $fillable = ['name', 'email', 'address'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Shaped like the `to` group in the document schemas. */
    public function toParty(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email ?? '',
            'address' => $this->address ?? '',
        ];
    }
}
