<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\PostStatus;
use App\Helpers\FileRules;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\PostResource;
use App\Models\Community;
use App\Models\Post;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class PostController extends Controller
{
    use ApiResponses;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::query()->where('user_id', auth()->id())->orderBy('id', 'desc')->get();
        $posts->load('community');
        return $this->ok('success', ['posts' => PostResource::collection($posts)]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate(
            [
            'title' => 'required',
            'content' => 'required',
            'image' => FileRules::imageOnly(),
            'community_id' => 'nullable|exists:communities,id'
            ],
            [
            'community_id.exists' => 'This community does not exist'
            ]
        );

        // Check if community_id is set and if user belongs to the community
        if (isset($validatedData['community_id'])) {
            $community = Community::find($validatedData['community_id']);
            if (!$community || !$community->members()->where('user_id', $request->user()->id)->exists()) {
            return $this->error('You do not belong to this community');
            }
        }

        $validatedData['user_id'] = $request->user()->id;
        $validatedData['status'] = PostStatus::APPROVED;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('posts-images', 'public');
            $validatedData['image'] = url('storage/' . $path);
        }
        $post = Post::create($validatedData);
        if (isset($validatedData['community_id'])) {
            $post->load('community', 'user');
        } else {
            $post->load('user');
        }

        return $this->ok('post created successfully', new PostResource($post));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $post = Post::query()->findOrFail($id);
        $post->load('user', 'community');
        return $this->ok('success', ['post' => new PostResource($post)]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'title' => 'required',
            'content' => 'required',
            'image' => FileRules::imageOnly(),
        ]);
        $validatedData['status'] = PostStatus::PENDING;
        $post = Post::query()->findOrFail($id);
        $post->update($validatedData);
        $post->load('user', 'community');
        return $this->ok('post updated successfully', new PostResource($post));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $post = Post::query()->findOrFail($id);
        if ($post->user_id !== auth()->id()) {
            return $this->error('Not authorized');
        }
        $post->delete();
        return $this->ok('post deleted successfully');
    }
}
