<?php

namespace App\Models;

use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    /** @use HasFactory<ContactFactory> */
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'organization_id',
        'is_favorite',
    ];

    /**
     * @return Attribute<non-falsy-string, never>
     */
    protected function name(): Attribute
    {
        return Attribute::get(
            fn () => $this->first_name.' '.$this->last_name,
        );
    }

    protected function casts(): array
    {
        return [
            'is_favorite' => 'boolean',
        ];
    }

    /**
     * @param  Builder<Contact>  $query
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, fn (Builder $query, string $search) => $query->whereAny(
            ['first_name', 'last_name', 'email'],
            'like',
            "%{$search}%",
        ));
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return HasMany<Note, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }
}
