<?php

namespace App\Models;

use App\Documents\DocumentTemplate;
use App\Services\Templates\TemplateManager;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * A document a signed-in user chose to keep.
 *
 * @property string $id
 * @property int $user_id
 * @property string $template
 * @property string $title
 * @property array $data
 * @property string $path
 * @property int $size
 * @property string $checksum
 */
class Document extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'template',
        'title',
        'data',
        'path',
        'size',
        'checksum',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'size' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function templateInstance(): DocumentTemplate
    {
        return app(TemplateManager::class)->get($this->template);
    }

    /** Filename offered to the browser on download. */
    public function filename(): string
    {
        return (Str::slug($this->title) ?: $this->template).'.pdf';
    }

    public function disk(): Filesystem
    {
        return Storage::disk(config('pdf.disk'));
    }

    public function fileExists(): bool
    {
        return $this->disk()->exists($this->path);
    }

    protected static function booted(): void
    {
        static::deleted(fn (Document $document) => $document->disk()->delete($document->path));
    }
}
