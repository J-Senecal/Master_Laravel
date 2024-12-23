<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;


class ReviewController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('throttle:reviews')->only(['store']);
    // }

    public function create(Book $book)
    {
        return view('books.reviews.create', ['book' => $book]);
    }

    public function store(Request $request, Book $book)
    {
        $data = $request->validate([ //validate the data before adding it to db and create a model
            'review' => 'required|min:15',
            'rating' => 'required|min:1|max:5|integer'
        ]);

        $book->reviews()->create($data); //creates a new book model instance and stores in db

        return redirect()->route('books.show', $book);
    }
}