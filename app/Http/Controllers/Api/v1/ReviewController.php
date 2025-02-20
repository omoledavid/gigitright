<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\ReviewResource;
use App\Models\Review;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ApiResponses;
    public function index()
    {
        return $this->ok('success', ReviewResource::collection(Review::query()->where('reviewee_id', auth()->id())->get()));
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'reviewer_id' => 'required|exists:users,id',
            'reviewee_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string',
        ]);
        $review = Review::query()->create($validatedData);
        return $this->ok('success', new ReviewResource($review));
    }
    public function update(Request $request, Review $review)
    {
        $validatedData = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'review' => 'sometimes|string',
        ]);

        $review->update($validatedData);

        return $this->ok('success', new ReviewResource($review));
    }
    public function destroy(Review $review)
    {
        $review->delete();
        return $this->ok('success', null);
    }
}
