<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            if ($user->isAdmin()) {
                return true;
            }

            $permissionKeys = once(fn () => Schema::hasTable('permissions') ? Permission::pluck('key')->all() : []);

            if (! in_array($ability, $permissionKeys, true)) {
                return null;
            }

            return $user->hasPermission($ability);
        });
    }
}
