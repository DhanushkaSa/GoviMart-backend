<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number', // <-- ADD THIS
        'password',
        'role',
        'location',         // <-- ADD
        'profile_photo_url',       // <-- ADD THIS
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the vendor profile associated with the user.
     */
    public function vendor()
    {
        // A user (who is a farmer) has one vendor profile
        return $this->hasOne(Vendor::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
