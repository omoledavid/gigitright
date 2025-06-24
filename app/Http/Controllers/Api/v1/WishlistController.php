<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\JobResource;
use App\Http\Resources\v1\WishlistResource;
use App\Models\Wishlist;
use App\Traits\ApiResponses;
use HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WishlistController extends Controller
{
    use ApiResponses;
    public function index()
    {
        return $this->ok('Wishlist list', WishlistResource::collection(Wishlist::query()->where('user_id', auth()->id())->get()));
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'job_id' => 'required|exists:jobs,id',
        ]);

        $user = auth()->user();

        $wishlistExists = Wishlist::where('user_id', $user->id)
            ->where('job_id', $validatedData['job_id'])
            ->exists();

        if (!$wishlistExists) {
            $wishlist = Wishlist::create([
                'user_id' => $user->id,
                'job_id' => $validatedData['job_id'],
            ]);

            return $this->ok('Job added to wishlist', new WishlistResource($wishlist), Response::HTTP_CREATED);
        }

        return $this->ok('Job already in wishlist', [], Response::HTTP_OK);
    }

    public function destroy(Wishlist $wishlist)
    {
        if ($wishlist->user_id != auth()->id()) {
            return $this->error('You are not allowed to delete this wishlist', Response::HTTP_FORBIDDEN);
        }

        $wishlist->delete();
        return $this->ok('Job deleted',);
    }
}
