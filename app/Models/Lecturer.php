<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lecturer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'specialization',
        'bio',
        'image',
        'linkedin_url',
        'facebook_url',
        'twitter_url',
        'website_url',
        'join_date',
        'created_by',
    ];
    protected $hidden = [
        'is_active',
        'created_by',
        'deleted_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'join_date' => 'date',
    ];

    /**
     * Scope a query to only include active lecturers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to search lecturers by name, email, or specialization.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('specialization', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to order lecturers by name.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name', 'asc');
    }

    /**
     * Activate the lecturer.
     */
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the lecturer.
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Get the user who created the lecturer.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
