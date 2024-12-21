<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Task extends Model
{
    use HasFactory;

    protected $fillable = ['title' , 'description', 'long_description']; //these column names can be modified in mass assignment
    // protected $guarded = ['password']; // these are columns that can't be changed and will allow every other property fillable
    public function toggleComplete()
    {
        $this->completed = !$this->completed;
        $this->save();
    }
}
