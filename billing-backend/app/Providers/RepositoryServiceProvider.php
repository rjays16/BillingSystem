<?php

namespace App\Providers;

use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\OrganizationRepositoryInterface;
use App\Repositories\Interfaces\InvoiceRepositoryInterface;
use App\Repositories\Interfaces\VendorRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\OrganizationRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\VendorRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(OrganizationRepositoryInterface::class, OrganizationRepository::class);
        $this->app->bind(InvoiceRepositoryInterface::class, InvoiceRepository::class);
        $this->app->bind(VendorRepositoryInterface::class, VendorRepository::class);
    }

    public function provides(): array
    {
        return [
            UserRepositoryInterface::class,
            OrganizationRepositoryInterface::class,
            InvoiceRepositoryInterface::class,
            VendorRepositoryInterface::class,
        ];
    }
}