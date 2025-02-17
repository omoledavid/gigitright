<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\PostResource;
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
        $user = auth()->user();
        $post = $user->posts()->get();
        $post->load('community');
        return $this->ok('success', ['posts' => PostResource::collection($post)]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required',
            'content' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'community_id' => 'required|exists:communities,id'
        ],
        [
            'community_id.exists' => 'This community does not exist'
        ]);
        $validatedData['user_id'] = $request->user()->id;
        $validatedData['status'] = PostStatus::APPROVED;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('posts-images', 'public');
            $validatedData['image'] = url('storage/' . $path);
        }
        $post = Post::create($validatedData);
        $post->load('user', 'community');
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
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
        if($post->user_id !== auth()->id()){
            return $this->error('Not authorized');
        }
        $post->delete();
        return $this->ok('post deleted successfully');
    }

}
