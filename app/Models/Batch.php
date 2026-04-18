<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'year',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'year' => 'integer',
    ];

    /**
     * Scope a query to only include active batches.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to search batches by name or slug.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('slug', 'like', "%{$search}%")
              ->orWhere('year', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to order batches by creation date.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('year', 'desc')->orderBy('name', 'asc');
    }

    /**
     * Activate the batch.
     */
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the batch.
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }
}
