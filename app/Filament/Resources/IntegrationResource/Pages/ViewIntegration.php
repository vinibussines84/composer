<?php

namespace App\Filament\Resources\IntegrationResource\Pages;

use App\Filament\Resources\IntegrationResource;
use Filament\Resources\Pages\Page;

class ViewIntegration extends Page
{
    protected static string $resource = IntegrationResource::class;

    protected static string $view = 'filament.resources.integration-resource.pages.view-integration';
}
