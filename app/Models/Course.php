<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'code',
        'duration',
        'duration_unit',
        'level',
        'medium',
        'short_description',
        'full_description',
        'show_in_registration',
        'is_active',
        'is_new',
        'primary_image',
        'fees_structure',
    ];

    protected $casts = [
        'show_in_registration' => 'boolean',
        'is_active' => 'boolean',
        'is_new' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(CourseImage::class);
    }

    public function videos()
    {
        return $this->hasMany(CourseVideo::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'course_tags');
    }

    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
            ->orWhere('code', 'LIKE', "%{$search}%")
            ->orWhere('slug', 'LIKE', "%{$search}%");
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name', 'asc');
    }

    public function scopeForRegistration($query)
    {
        return $query->where('show_in_registration', true);
    }
}
