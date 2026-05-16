<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'designation',
        'employee_number',
        'join_date',
        'bio',
        'dob',
        'nic_number',
        'profile_image',
        'created_by',
    ];
    protected $hidden = [
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'join_date' => 'date',
        'dob' => 'date',
    ];

    /**
     * Scope a query to only include active staff.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to search staff by name, email, or designation.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('designation', 'like', "%{$search}%")
                ->orWhere('employee_number', 'like', "%{$search}%")
                ->orWhere('nic_number', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to order staff by name.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name', 'asc');
    }

    /**
     * Get the user who created the staff member.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
