<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Enums\TransactionSource;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Filters\v1\UserFilter;
use App\Http\Resources\v1\UserResource;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ManageUserController extends Controller
{
    use ApiResponses;
    public function index(Request $request, UserFilter $filter)
    {

        // Filter users
        $users = User::query()->where('id', '!=', auth()->id())->filter($filter)->get();

        return $this->ok('all users', UserResource::collection($users));
    }
    public function suspend(User $user)
    {
        if($user->status === UserStatus::BLOCKED){
            $user->update(['status' => UserStatus::ACTIVE]);
            return $this->ok('User unsuspended');
        }
        $user->update(['status' => UserStatus::BLOCKED]);
        return $this->ok('User suspended');
    }
    public function sendEmail(Request $request, User $user)
    {
        $request->validate([
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        return $this->ok('Mail sent successfully', new UserResource($user));
    }
    public function stats()
    {
        // Get total user statistics
        $totalUsers = User::count();
        $totalActiveUsers = User::where('status', 'active')->count();
        $totalSuspendedUsers = User::where('status', 'suspended')->count();
        return $this->ok('users stats',[
            'total_users' => $totalUsers,
            'total_active_users' => $totalActiveUsers,
            'total_suspended_users' => $totalSuspendedUsers,
        ]);
    }
    public function getRevenueChart(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'timeFrame' => 'required|string|in:day,week,month,year',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);

        // Default to 'day' if time frame is not provided
        $timeFrame = $request->input('timeFrame', 'day');
        $date = Carbon::now();

        // Initialize base query
        $query = Transaction::query()
            ->where('transaction_type', TransactionType::CREDIT)
            ->where('Transaction_source', TransactionSource::WALLET)
            ->where('status', TransactionStatus::COMPLETED);

        // Filter by user_id if provided
        if ($request->get('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Prepare query based on time frame
        switch ($timeFrame) {
            case 'day':
                $revenueData = $query->whereDate('created_at', $date->format('Y-m-d'))
                    ->selectRaw('sum(amount) as total_revenue, DATE(created_at) as date')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                break;

            case 'month':
                $revenueData = $query->whereMonth('created_at', $date->month)
                    ->selectRaw('sum(amount) as total_revenue, DAY(created_at) as day')
                    ->groupBy('day')
                    ->orderBy('day')
                    ->get();
                break;

            case 'year':
                $revenueData = $query->whereYear('created_at', $date->year)
                    ->selectRaw('sum(amount) as total_revenue, MONTH(created_at) as month')
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get();
                break;

            default:
                // Return an empty response if time frame doesn't match expected values
                return $this->error('Invalid time frame', [], 400);
        }

        // Return the result
        $message = $request->get('user_id')
            ? 'user revenue chart for user id ' . $request->user_id
            : 'user revenue chart';

        return $this->ok($message, $revenueData);
    }


}
