<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Requests\Review\UpdateReviewRequest;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    public function __construct(private ReviewService $reviews) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        return Inertia::render('admin/reviews/index', [
            'reviews' => $this->reviews->paginate($search),
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/reviews/create', [
            'defaultSortOrder' => $this->reviews->nextSortOrder(),
        ]);
    }

    public function store(StoreReviewRequest $request): RedirectResponse
    {
        $this->reviews->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Review created successfully.']);

        return redirect()->route('admin.reviews.index');
    }

    public function edit(Review $review): Response
    {
        return Inertia::render('admin/reviews/edit', [
            'review' => $review,
        ]);
    }

    public function update(UpdateReviewRequest $request, Review $review): RedirectResponse
    {
        $this->reviews->update($review, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Review updated successfully.']);

        return redirect()->route('admin.reviews.index');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $this->reviews->delete($review);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Review deleted successfully.']);

        return redirect()->back();
    }
}
