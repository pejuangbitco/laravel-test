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
        $sortColumn = $request->query('sortColumn', 'id');
        $sortDirection = $request->query('sortDirection', 'ASC');        
        $title = $request->query('title');
        $authors = $request->query('authors');

        //map authorIds to int[]
        $authorIds = false;
        if($authors) {
            $authors = explode(',', $authors);
            $authorIds = [];
            foreach($authors as $author) {
                array_push($authorIds, intval($author));
            }
        }
        
        $books = Book::where('title', 'LIKE', "%{$title}%");
        
        if($authorIds) {
            $books = $books->whereHas('authors', function ($query) use ($authorIds) {
                if($authorIds) {
                    $query->whereIn('authors.id', $authorIds);                
                }
            });
        }
            
        $books = $books->withCount(['reviews','reviews as avg_review' => function ($query) {
            $query->select(DB::raw('coalesce(round(avg(review),0),0)'));
        }])->orderBy($sortColumn, $sortDirection)->paginate();

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
