<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Flashcard extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'flashcards';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'question',
        'answer'
    ];

    // Define flashcard's relationship with answers
    public function answers()
    {
        return $this->hasMany('App\Models\Answer', 'flashcard_id');
    }

    public function choices(): HasMany
    {
        return $this->hasMany(Choice::class);
    }
}
