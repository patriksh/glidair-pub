<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Round extends Model
{
    use HasFactory;

    protected $fillable = [
        'round',
        'score',
        'ignore',
        'participant_id'
    ];

    protected $hidden = ['participant_id', 'created_at', 'updated_at'];

    // Serija pripada natjecatelju.
    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }
}
