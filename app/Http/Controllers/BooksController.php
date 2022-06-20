<?php

namespace App\Http\Controllers;

use App\Book;
use App\Http\Requests\PostBookRequest;
use App\Http\Resources\BookResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BooksController extends Controller
{
    public function __construct()
    {
    }

    public function index(Request $request)
    {
        // @TODO implement
        $title = $request->query('title', '');
        $sortColumn = $request->query('sortColumn', 'title');
        $sortDirection = $request->query('sortDirection', 'ASC');
        $books = Book::with(['authors'])
            ->where('title', 'LIKE', "%$title%")
            ->whereHas('authors', function ($query) use ($request) {
                $authors = $request->query('authors', '');

                $authors = explode(',', $authors);
                if(count($authors) > 1) {
                    $query->whereIn('authors.id', [2, 3]);
                }
            })
            ->withCount(['reviews','reviews as avg_review' => function ($query) {
                $query->select(DB::raw('coalesce(avg(review),0)'));
            }])
            ->orderBy($sortColumn, $sortDirection)->paginate();
        return BookResource::collection($books);
    }

    public function store(PostBookRequest $request)
    {
        // @TODO implement
        $data = $request->all();
        $authorIds = $request->input('authors');
        
        $book = new Book();
        $book->isbn = $data['isbn'];
        $book->title = $data['title'];
        $book->description = $data['description'];
        $book->published_year = $data['published_year'];

        $book->save();
        $book->authors()->attach($authorIds);
        return new BookResource($book);
    }
}
