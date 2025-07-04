<?php
namespace App\Http\Controllers\Api\v1;

use App\Enums\NotificationType;
use App\Helpers\FileRules;
use App\Http\Controllers\Controller;
use App\Http\Filters\v1\GigFilter;
use App\Http\Resources\v1\GigResource;
use App\Models\Gig;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GigController extends Controller
{
    use ApiResponses;
    /**
     * Display a listing of the gigs.
     */
    public function index(GigFilter $filter)
    {
        $gigs = Gig::query()->filter($filter)->get();
        return $this->ok('gigs', GigResource::collection($gigs));
    }

    /**
     * Store a newly created gig in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'skills' => 'nullable|array',
            'location' => 'nullable|string|max:255',
            'previous_works_companies' => 'nullable|array',
            'language' => 'nullable|string|max:255',
            'unique_selling_point' => 'nullable|string',
            'plans' => 'nullable|json',
            'image' => FileRules::imageOnly(),
        ]);
        if ($request->hasFile('image')) {
            $location = getFilePath('gigs');
            $path = fileUploader($request->image, $location);
            $image = $path;
        }

        $gig = Gig::create([
            'title' => $request->title,
            'user_id' => auth()->id(),
            'description' => $request->description,
            'skills' => $request->skills,
            'location' => $request->location,
            'image' => $image ?? null,
            'previous_works_companies' => $request->previous_works_companies,
            'language' => $request->language,
            'unique_selling_point' => $request->unique_selling_point,
            'plans' => json_encode($request->plans)
        ]);
        $notifyMsg = [
            'title' => 'New Gig Created',
            'message' => 'You have created a new gig',
            'url' => route('gigs.show', $gig->id),
        ];
        createNotification($gig->user_id, NotificationType::GIG_CREATED, $notifyMsg);

        return $this->ok('Gig created successfully', new GigResource($gig), Response::HTTP_CREATED);
    }

    /**
     * Display the specified gig.
     */
    public function show($id)
    {
        $gig = Gig::findOrFail($id);
        return $this->ok('Gig details', new GigResource($gig->load(['user', 'reviews'])));
    }

    /**
     * Update the specified gig in storage.
     */
    public function update(Request $request, $id)
    {
        $gig = Gig::findOrFail($id);

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'skills' => 'nullable|array',
            'location' => 'nullable|string|max:255',
            'previous_works_companies' => 'nullable|array',
            'language' => 'nullable|string|max:255',
            'unique_selling_point' => 'nullable|string',
            'plans' => 'nullable|array'
        ]);

        $gig->update([
            'title' => $request->title ?? $gig->title,
            'description' => $request->description ?? $gig->description,
            'skills' => $request->skills ?? $gig->skills,
            'location' => $request->location ?? $gig->location,
            'previous_works_companies' => $request->previous_works_companies ?? $gig->previous_works_companies,
            'language' => $request->language ?? $gig->language,
            'unique_selling_point' => $request->unique_selling_point ?? $gig->unique_selling_point,
            'plans' => json_encode($request->plans) ?? $gig->plans
        ]);
        $notifyMsg = [
            'title' => 'Gig Updated',
            'message' => 'Your gig has been updated successfully',
            'url' => route('gigs.show', $gig->id),
        ];
        createNotification($gig->user_id, NotificationType::GIG_UPDATED, $notifyMsg);
        return $this->ok('Gig updated successfully', new GigResource($gig));
    }

    /**
     * Remove the specified gig from storage.
     */
    public function destroy($id)
    {
        $gig = Gig::findOrFail($id);
        $gig->delete();
        $notifyMsg = [
            'title' => 'Gig Deleted',
            'message' => 'Your gig has been deleted successfully',
            'url' => null,
        ];
        createNotification(auth()->id(), NotificationType::GIG_DELETED, $notifyMsg);

        return $this->ok('Gig deleted successfully');
    }
}

