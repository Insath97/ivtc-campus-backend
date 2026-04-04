<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'registration_code',
        'pathway_id',
        'program_id',
        'program_type',
        'full_name',
        'nic',
        'dob',
        'gender',
        'phone',
        'email',
        'district',
        'city',
        'school_name',
        'occupation',
        'status',
        'remarks',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::creating(function ($registration) {
            $registration->registration_code = self::generateRegistrationCode();
        });
    }

    /**
     * Generate a unique registration code: IVTC-yymm0001
     */
    public static function generateRegistrationCode()
    {
        $prefix = 'IVTC-' . date('ym');
        
        $latestRegistration = self::where('registration_code', 'like', $prefix . '%')
            ->orderBy('registration_code', 'desc')
            ->first();

        if ($latestRegistration) {
            $lastNumber = intval(substr($latestRegistration->registration_code, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $newNumber;
    }

    /**
     * Relationship with the pathway.
     */
    public function pathway()
    {
        return $this->belongsTo(Pathway::class);
    }

    /**
     * Polymorphic relationship for the program (Course or RegistrationProgram).
     */
    public function program()
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to search registrations.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('full_name', 'like', "%{$search}%")
              ->orWhere('nic', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to order registrations by creation date.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
