<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\PostStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Filters\v1\PostFilter;
use App\Http\Filters\v1\UserFilter;
use App\Http\Resources\v1\PostResource;
use App\Http\Resources\v1\UserResource;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    use ApiResponses;
    public function categories()
    {
        $categories = Category::all();
        return Category::all();
    }
    public function talents(UserFilter $filter)
    {
        return $this->ok('success', UserResource::collection(User::query()->where('role', UserRole::FREELANCER)->where('status', UserStatus::ACTIVE)->filter($filter)->get()));
    }
    public function posts(PostFilter $filter)
    {
        return $this->ok('success', PostResource::collection(Post::query()->where('status', PostStatus::APPROVED)->filter($filter)->get()));
    }
    public function toggleLike($postId)
    {
        $user = auth()->user();
        $post = Post::query()->findOrFail($postId);

        // Check if the user already liked the post
        $like = PostLike::where('post_id', $postId)
            ->where('user_id', $user->id)
            ->first();

        if ($like) {
            // Unlike the post
            $like->delete();
            return $this->ok('Post unliked successfully', new PostResource($post));
        } else {
            // Like the post
            PostLike::create([
                'post_id' => $postId,
                'user_id' => $user->id,
            ]);
            return $this->ok('Post liked successfully', new PostResource($post));
        }
    }
    public function postComment(Request $request)
    {
        $user = auth()->user();
        $validatedData = $request->validate([
            'post_id' => 'required|exists:posts,id',
            'comment' => 'required|string|max:255',
        ]);
        $validatedData['user_id'] = $user->id;
        $postComment = PostComment::create($validatedData);
        return $this->ok('Post Comment created successfully', $postComment);
    }
}
