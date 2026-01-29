<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UsersForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pengguna')
                    ->schema([
                        Grid::make(2) // Membagi menjadi 2 kolom agar lebih rapi
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->label('Alamat Email')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                            ]),

                        TextInput::make('password')
                            ->label('Kata Sandi')
                            ->password()
                            ->revealable() // Fitur intip password (ikon mata)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->helperText(fn (string $operation): string => 
                                $operation === 'edit' 
                                ? 'Kosongkan jika tidak ingin mengubah kata sandi.' 
                                : 'Minimal 8 karakter.'
                            )
                            ->maxLength(255),
                    ]),

                Section::make('Hak Akses & Status')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('role')
                                    ->label('Role / Jabatan')
                                    ->options(function (){
                                        $user = Auth::user();
                                        if ($user && $user->role === 'admin') {
                                            return [
                                                'admin' => 'Administrator',
                                                'qc' => 'Quality Control',
                                                'staff' => 'Staff',
                                            ];
                                        }
                                        return [
                                            'superadmin' => 'Superadmin',
                                            'admin' => 'Administrator',
                                            'qc' => 'Quality Control',
                                            'staff' => 'Staff',
                                        ];
                                    })
                                    ->default('staff')
                                    ->required()
                                    ->native(false),

                                Toggle::make('is_active')
                                    ->label('Status Akun Aktif')
                                    ->default(true)
                                    ->onColor('success')
                                    ->inline(false),
                            ]),
                    ]),
            ]);
    }
}