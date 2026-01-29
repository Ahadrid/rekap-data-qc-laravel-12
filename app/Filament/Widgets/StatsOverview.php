<?php

namespace App\Filament\Widgets;

use App\Models\mitra;
use App\Models\RekapData;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Pengguna', User::count())
                ->description('Pengguna terdaftar di Sistem')
                ->descriptionIcon(Heroicon::Users)
                ->color('success'),
            
            Stat::make('Total Mitra', mitra::count())
                ->description('Mitra terdaftar Saat ini ')
                ->descriptionIcon(Heroicon::BuildingOffice2)
                ->color('primary'),

            Stat::make('Rata-rata Susut (%)', function (){
                 $avg = RekapData::avg('susut_persen') ?? 0;
                    return round($avg, 2) . '%';
                })
                ->description('Statistik susut keseluruhan ')
                ->descriptionIcon(Heroicon::ArrowTrendingDown)
                ->color(fn ($state) => $state <= 0 ? 'success' : 'danger')
        ];
    }
}
