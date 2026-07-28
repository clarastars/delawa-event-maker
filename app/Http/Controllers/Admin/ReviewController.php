<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventReview;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $reviews = EventReview::with(['event', 'contact'])
            ->latest()
            ->paginate(50);

        return view('admin.reviews.index', [
            'reviews' => $reviews,
        ]);
    }
}
