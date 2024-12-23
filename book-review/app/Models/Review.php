<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['review', 'rating']; //fillable allows values assigned inside the array to be mass assigned

    public function book()
    {
        return $this->belongsTo(Book::class); //defines one to many that each review belongs to one book class. Can also pass another arguments into belongsTo(classsName::class, 'other foreign key name')
    }

    protected static function booted() //clear the cache when review is updated to it pulls new updated info from db
    {
        static::updated(fn(Review $review) => cache()->forget('book:' . $review->book_id));
        static::deleted(fn(Review $review) => cache()->forget('book:' . $review->book_id));
        static::created(fn(Review $review) => cache()->forget('book:' . $review->book_id));
    }
}