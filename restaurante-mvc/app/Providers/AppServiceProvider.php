<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Models\Product;
use App\Models\Category;
use App\Models\Branch;
use App\Models\User;
use App\Policies\ProductPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\BranchPolicy;
use App\Policies\InventoryPolicy;
use App\Policies\UserPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Category::class => CategoryPolicy::class,
        Branch::class => BranchPolicy::class,
        User::class => UserPolicy::class,
        'inventory' => InventoryPolicy::class,
    ];

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
        Paginator::useBootstrapFive();
    }
}
