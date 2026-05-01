<?php

namespace App\Filament\Admin\Resources\Fdrs\Pages;

use App\Filament\Admin\Resources\Fdrs\FdrResource;
use App\Services\FdrService;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageFdrs extends ManageRecords
{
    protected static string $resource = FdrResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->using(function (array $data) {
                    // 🔥 THIS IS THE KEY FIX
                    return FdrService::create($data);
                }),
        ];
    }
}