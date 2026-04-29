<?php

namespace App\Filament\Admin\Resources\Fdrs\Pages;

use App\Filament\Admin\Resources\Fdrs\FdrResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageFdrs extends ManageRecords
{
    protected static string $resource = FdrResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
