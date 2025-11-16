<?php
// app/Models/Vendor.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'farm_name',
        'farm_address',
        'farm_location',
        'farm_description',
        'farm_size',        // <-- ADD
        'experience',
    ];

    /**
     * Get the user that owns this vendor profile.
     */
    public function user()
    {
        // A vendor profile belongs to one user
        return $this->belongsTo(User::class);
    }
}
