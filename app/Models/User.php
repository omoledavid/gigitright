<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Http\Filters\v1\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use MannikJ\Laravel\Wallet\Traits\HasWallet;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasWallet;

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

    public function scopeFilter(Builder $builder, QueryFilter $filters)
    {
        return $filters->apply($builder);
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

}
