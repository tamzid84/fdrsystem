<?php

namespace App\Filament\Admin\Resources\Funds\Pages;

use App\Filament\Admin\Resources\Funds\FundResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageFunds extends ManageRecords
{
    protected static string $resource = FundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
