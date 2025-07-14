<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\NotificationType;
use App\Enums\Status;
use App\Helpers\FileRules;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\PortfolioResource;
use App\Models\Portfolio;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    protected $user;
    protected $portfolio;
    use ApiResponses;

    public function __construct()
    {
        $this->user = auth()->user();
        $this->portfolio = $this->user ? $this->user->portfolio : null;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $name = $this->user->name;
        return $this->ok("success", PortfolioResource::collection($this->portfolio));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required',
            'description' => 'required',
            'image' => FileRules::imageOnly(),
            'link' => 'required|url',
            'technologies' => 'required|array|min:1',
            'date' => 'required|date',
        ]);
        $validatedData['user_id'] = $this->user->id;
        $validatedData['status'] = Status::ACTIVE;
        if ($request->hasFile('image')) {
            try {
                $location = getFilePath('portfolio');
                $path = fileUploader($request->image, $location);
                $validatedData['image'] = $path;
            } catch (\Exception $exp) {
                return $this->error('Could not upload your image');
            }
        }
        $portfolio = Portfolio::query()->create($validatedData);
        $notifyMsg = [
            'title' => 'Portfolio Item Added',
            'message' => "Your portfolio item '{$portfolio->title}' has been added successfully",
            'url' => '',
            'id' => $portfolio->id
        ];
        createNotification($this->user->id, NotificationType::PORTFOLIO_ADDED, $notifyMsg);
        return $this->ok('Portfolio added', new PortfolioResource($portfolio));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $portfolio = Portfolio::query()->findOrFail($id);
        return $this->ok("Portfolio retrieved successfully", new PortfolioResource($portfolio));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'title' => 'required',
            'description' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'link' => 'required|url',
            'technologies' => 'required|array|min:1',
            'date' => 'required|date',
        ]);
        $portfolio = Portfolio::query()->findOrFail($id);
        if ($request->hasFile('image')) {
            try {
                $old = $portfolio->image;
                $location = getFilePath('portfolio');
                $validatedData['image'] = fileUploader($request->image, $location, null, $old);
            } catch (\Exception $exp) {
                return $this->error('Could not upload your image');
            }
        }
        $portfolio->update($validatedData);
        return $this->ok('Portfolio updated', new PortfolioResource($portfolio));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $portfolio = Portfolio::query()->find($id);
        if (!$portfolio) {
            return $this->error("Portfolio not found or deleted already", 404);
        }
        $portfolio->delete();
        return $this->ok('Portfolio deleted');
    }
}
