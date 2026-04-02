<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RegistrationProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'pathway_id',
        'name',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relationship with the pathway.
     */
    public function pathway()
    {
        return $this->belongsTo(Pathway::class);
    }

    /**
     * Scope a query to only include active programs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to search programs by name or slug.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('slug', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to order programs by creation date.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Activate the program.
     */
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the program.
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }
}
