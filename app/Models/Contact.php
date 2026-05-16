<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
    ];

    protected $hidden = [
        'status',
        'is_replied',
        'replied_by',
        'replied_at',
        'reply_message',
    ];

    protected $casts = [
        'is_replied' => 'boolean',
        'replied_at' => 'datetime',
    ];

    /**
     * Relationship with the user who replied.
     */
    public function repliedBy()
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    /**
     * Scope a query to search contact messages.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to order messages by creation date.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
