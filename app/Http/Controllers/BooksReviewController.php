<?php

namespace App\Http\Controllers;

use App\Book;
use App\BookReview;
use App\Http\Requests\PostBookReviewRequest;
use App\Http\Resources\BookReviewResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BooksReviewController extends Controller
{
    public function __construct()
    {

    }

    public function store(int $bookId, PostBookReviewRequest $request)
    {
        // @TODO implement
        Book::findOrFail($bookId);
        $userId = Auth::id();
        $data = $request->all();

        $bookReview = new BookReview();
        $bookReview->review = $data['review'];
        $bookReview->comment = $data['comment'];
        $bookReview->book_id = $bookId;
        $bookReview->user_id = $userId;
        $bookReview->save();
        return new BookReviewResource($bookReview);
    }

    public function destroy(int $bookId, int $reviewId, Request $request)
    {
        // @TODO implement
        Book::findOrFail($bookId)->reviews()->where('id', $reviewId)->delete();
        return response()->json([], 204);
    }
}
