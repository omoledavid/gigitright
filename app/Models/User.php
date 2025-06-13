<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\ReviewType;
use App\Enums\Status;
use App\Enums\UserStatus;
use App\Http\Filters\v1\QueryFilter;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use MannikJ\Laravel\Wallet\Traits\HasWallet;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasWallet, HasRoles, HasPanelShield;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = ['id'];
    protected $appends = ['wallet', 'escrow_wallet', 'griftis'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_ban' => 'number',
        'email_verified' => 'int',
        'mobile_verified' => 'int',
        'ver_code_send_at'  => 'datetime',
        'skills' => 'array',
        'languages' => 'array',
        'is_admin' => 'boolean',

    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return $this->is_admin; // or return $this->role === 'admin';
    }

    public function scopeFilter(Builder $builder, QueryFilter $filters)
    {
        return $filters->apply($builder);
    }
    public function scopeActive($query)
    {
        return $query->where('status', UserStatus::ACTIVE)->where('ev', Status::ACTIVE)->where('sv', Status::ACTIVE);
    }

    public function wallet(): Attribute
    {
        return new Attribute(
            function () {
                $account = $this->accounts->where('name', 'main')->first();

                if (!$account) {
                    $account = $this->accounts()->create([
                        'user_id' => $this->getKey(),
                        'name' => 'main',
                    ]);
                }

                return $account->wallet;
            }
        );
    }

    public function escrowWallet(): Attribute
    {
        return new Attribute(
            function () {
                $account = $this->accounts->where('name', 'escrow')->first();

                if (!$account) {
                    $account = $this->accounts()->create([
                        'user_id' => $this->getKey(),
                        'name' => 'escrow',
                    ]);
                }

                return $account->wallet;
            }
        );
    }
    public function griftis(): Attribute
    {
        return new Attribute(
            function () {
                $account = $this->accounts->where('name', 'griftis')->first();

                if (!$account) {
                    $account = $this->accounts()->create([
                        'user_id' => $this->getKey(),
                        'name' => 'griftis',
                    ]);
                }

                return $account->wallet;
            }
        );
    }

    public function accounts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }
    public function experience(): HasMany
    {
        return $this->hasMany(Experience::class);
    }
    public function education(): HasMany
    {
        return $this->hasMany(Education::class);
    }
    public function portfolio(): HasMany
    {
        return $this->hasMany(Portfolio::class);
    }
    public function certificate(): HasMany
    {
        return $this->hasMany(Certification::class);
    }
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function communities()
    {
        return $this->belongsToMany(Community::class, 'community_members');
    }
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewee_id')->where('type', ReviewType::USER);
    }
    public function getBalAttribute()
    {
        return $this->wallet->balance ?? 0;
    }
    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplicants::class, 'user_id');
    }
    public function orders()
    {
        return $this->hasMany(Order::class, 'talent_id');
    }

}
