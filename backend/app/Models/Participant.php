<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id',
        'user_id',
        'team_id',
        'score',
        'order'
    ];

    protected $hidden = ['competition_id', 'team_id', 'created_at', 'updated_at'];

    // Natjecatelj pripada natjecanju.
    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }

    // Natjecatelj pripada korisniku.
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Natjecatelj pripada timu.
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    // Natjecatelj ima više serija.
    public function rounds()
    {
        return $this->hasMany(Round::class);
    }
}
