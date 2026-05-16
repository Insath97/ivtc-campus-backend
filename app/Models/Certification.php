<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'full_name',
        'starting_date',
        'ending_date',
        'entrol_number',
        'course_code',
        'verification_code',
        'certificate_number',
        'nic',
    ];

    protected $hidden = [
        'is_active',
        'deleted_at',
    ];

    protected $casts = [
        'starting_date' => 'date',
        'ending_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('full_name', 'like', "%{$search}%")
              ->orWhere('entrol_number', 'LIKE', "%{$search}%")
              ->orWhere('verification_code', 'LIKE', "%{$search}%")
              ->orWhere('certificate_number', 'LIKE', "%{$search}%")
              ->orWhere('nic', 'LIKE', "%{$search}%")
              ->orWhere('course_code', 'LIKE', "%{$search}%");
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }
}
