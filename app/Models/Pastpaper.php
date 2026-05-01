<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pastpaper extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'batch_id',
        'has_scheme',
        'description',
        'paper_file_path',
        'scheme_file_path',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'has_scheme' => 'boolean',
    ];

    /**
     * Relationship with Batch
     */
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Relationship with Creator (User)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include active past papers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to search past papers by description or related batch (name/year).
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('description', 'like', "%{$search}%")
                     ->orWhereHas('batch', function($q) use ($search) {
                         $q->where('name', 'like', "%{$search}%")
                           ->orWhere('year', 'like', "%{$search}%");
                     });
    }

    /**
     * Scope a query to order past papers.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Activate the past paper.
     */
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the past paper.
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }
}
