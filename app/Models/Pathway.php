<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pathway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    protected $hidden = [
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active pathways.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to search pathways by name or slug.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('slug', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to order pathways by creation date.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Activate the pathway.
     */
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Relationship with registration programs.
     */
    public function registrationPrograms()
    {
        return $this->hasMany(RegistrationProgram::class);
    }

    /**
     * Deactivate the pathway.
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }
}
