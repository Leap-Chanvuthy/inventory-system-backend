<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Enums\SupplierCategoryEnum;
use App\Enums\SupplierStatusEnum;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Basic Info
                Forms\Components\Fieldset::make('General Information')->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('supplier_code')
                        ->maxLength(100)
                        ->nullable(),
    
                    Forms\Components\TextInput::make('phone_number')
                        ->required()
                        ->maxLength(50),
    
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->maxLength(255),
    
                    Forms\Components\TextInput::make('contact_person')
                        ->required()
                        ->maxLength(255),
                        Forms\Components\TextInput::make('website')
                        ->maxLength(255)
                        ->nullable(),
    
                    Forms\Components\TextInput::make('social_media')
                        ->nullable(),
    
                    Forms\Components\Select::make('supplier_category')
                        ->options(SupplierCategoryEnum::options())
                        ->required(),
    
                    Forms\Components\Select::make('supplier_status')
                        ->options(SupplierStatusEnum::options())
                        ->required(),
    
                ]),

                // Location Information
                Forms\Components\Fieldset::make('Location Information')->schema([
                    Forms\Components\TextInput::make('location')
                        ->required()
                        ->maxLength(255),
    
                    Forms\Components\TextInput::make('longitude')
                        ->maxLength(100)
                        ->nullable()
                        ->disabled(),
    
                    Forms\Components\TextInput::make('latitude')
                        ->maxLength(100)
                        ->nullable()
                        ->disabled(),
    
                    Forms\Components\TextInput::make('address')
                        ->maxLength(255)
                        ->nullable(),
    
                ]),
    
                // Business Information
                Forms\Components\Fieldset::make('Business Information')->schema([
    
                    Forms\Components\TextInput::make('business_registration_number')
                        ->maxLength(100)
                        ->nullable(),
    
                    Forms\Components\TextInput::make('vat_number')
                        ->maxLength(100)
                        ->nullable(),
    
                    Forms\Components\TextInput::make('contract_length')
                        ->maxLength(100)
                        ->nullable(),
    
                    Forms\Components\TextInput::make('discount_term')
                        ->maxLength(100)
                        ->nullable(),
    
                    Forms\Components\TextInput::make('payment_term')
                        ->maxLength(100)
                        ->nullable(),
    
                    Forms\Components\Textarea::make('note')
                        ->nullable(),
                ]),
    
                // Bank Information
                Forms\Components\Fieldset::make('Bank Information')->schema([
                    Forms\Components\TextInput::make('bank_account_number')
                        ->maxLength(50)
                        ->nullable(),
    
                    Forms\Components\TextInput::make('bank_account_name')
                        ->maxLength(50)
                        ->nullable(),
    
                    Forms\Components\TextInput::make('bank_name')
                        ->maxLength(255)
                        ->nullable(),
                ]),
    
                // Image Upload
                Forms\Components\Fieldset::make('Image Upload')->schema([
                    Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disk('public') 
                    ->directory('suppliers') 
                    ->visibility('public')
                    ->nullable(),
                ]),
    
                // Google Maps Integration
                Forms\Components\Fieldset::make('Location Picker')->schema([
                    Forms\Components\View::make('components.map')
                        ->label('Pick a Location'),
            
                    Forms\Components\Hidden::make('latitude')
                        ->nullable(),
    
                    Forms\Components\Hidden::make('longitude')
                        ->nullable(),
                ]),
            ]);
    }
    

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('location')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_person')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('supplier_status')
                    ->sortable()
                    ->searchable()
                    -> badge()
                    -> color('success'),

                Tables\Columns\TextColumn::make('business_registration_number')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('vat_number')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('bank_name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('supplier_status')
                ->options(SupplierStatusEnum::class)
                ->label('Supplier Status')
                ->placeholder('Status'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'view' => Pages\ViewSupplier::route('/{record}'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
