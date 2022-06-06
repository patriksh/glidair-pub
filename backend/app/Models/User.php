<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo',
        'gender',
        'country',
        'club_id',
        'airtribune_id'
    ];

    protected $hidden = ['airtribune_id', 'updated_at'];

    // Korisnik pripada jednom klubu.
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    // Korisnik može biti natjecatelj u više natjecanja.
    public function participants()
    {
        return $this->hasMany(Participant::class);
    }
}
