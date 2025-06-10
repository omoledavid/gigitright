<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\PostStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Filters\v1\PostFilter;
use App\Http\Filters\v1\UserFilter;
use App\Http\Resources\OrderResource;
use App\Http\Resources\v1\PostResource;
use App\Http\Resources\v1\UserResource;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\User;
use App\Services\PaymentGateways\PaystackService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeneralController extends Controller
{
    use ApiResponses;
    protected $paystackService;

    public function __construct(PaystackService $paystackService)
    {
        $this->paystackService = $paystackService;
    }
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
    public function banks(){
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('PAYSTACK_SECRET_KEY'),
            'Cache-Control' => 'no-cache',
        ])->get('https://api.paystack.co/bank');

        if ($response->failed()) {
            return $this->error($response->json('message') ?? 'something went wrong');
        } else {
            return $this->success('All banks retrieved successfully',[
                'banks_count' => count($response->json('data')),
                'banks' => $response->json('data')
            ]);
//            echo $response->body();;
        }
    }
    public function verifyAccountNumber(Request $request){
        $request->validate([
            'account_number' => 'required|max:40',
            'bank_code' => 'required|string|max:200'
        ]);

        $accountNumber = $request->input('account_number');
        $bankCode = $request->input('bank_code');

        $result = $this->paystackService->validateBankAccount($accountNumber, $bankCode);
        if ($result['error']) {
            return $this->error('Failed to validate bank account, check account number');
        }
        return $this->ok('Account number verified successfully',$result['data']);
    }
    public function rates()
    {
        return $this->ok('success',[
            'giftis_rate' => 1000
        ]);
    }
    public function testing()
    {
        $user = auth()->user();
        return OrderResource::collection($user->orders);
    }
    public function siteInfo()
    {
        return $this->ok('success', [
            'name' => env('APP_NAME'),
            'description' => env('APP_DESCRIPTION'),
            'modules' => [
                'maintenance_mode' => false,
                'login' => true,
                'registration' => true,
            ],
            'social_links' => [
                'facebook' => env('FACEBOOK_LINK'),
                'twitter' => env('TWITTER_LINK'),
                'instagram' => env('INSTAGRAM_LINK'),
                'linkedin' => env('LINKEDIN_LINK'),
                'youtube' => env('YOUTUBE_LINK'),
            ],
            'pages' => [
                'about' => env('ABOUT_PAGE'),
                'terms' => env('TERMS_PAGE'),
                'privacy' => env('PRIVACY_POLICY_PAGE'),
                'faq' => env('FAQ_PAGE'),
            ],
            'contact' => [
                'email' => env('CONTACT_EMAIL'),
                'phone' => env('PHONE_NUMBER'),
                'address' => env('ADDRESS'),
            ],
            'secions' => [
                'testimonials' => env('TESTIMONIALS_SECTION', false),
            ],
            'currency' => env('CURRENCY', 'NGN'),
            'currency_symbol' => env('CURRENCY_SYMBOL', '₦'),
            'currency_code' => env('CURRENCY_CODE', 'NGN'),
        ]);
    }
}
