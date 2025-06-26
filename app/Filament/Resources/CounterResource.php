<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CounterResource\Pages;
use App\Filament\Resources\CounterResource\RelationManagers;
use App\Models\Counter;
use App\Services\QueueService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CounterResource extends Resource
{
    protected static ?string $model = Counter::class;

    protected static ?string $navigationIcon = 'heroicon-o-hashtag';

    protected static ?string $navigationGroup = 'Administrasi';

    public static function canCreate(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('service_id')
                    ->required()
                    ->relationship('service', 'name'),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Counter')
                    ->searchable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Layanan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('activeQueue.number')
                    ->label('Nomor Antrian Saat ini')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('activeQueue.status')
                    ->label('Status Antrian')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                // menambah tombol action disampaning 
                self::getCallNextQueueAction(),
                self::getServeQueueAction(),
                self::getFinishQueueAction(),
                self::getCancelQueueAction(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->poll('5s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCounters::route('/'),
        ];
    }

    private static function getCallNextQueueAction()
    {
        return Action::make('callNextQueue')
            ->button()
            // munculkan tombol jika memang lagi antrian dicounter
            ->visible(fn(Counter $counter) => $counter->hasNextQueue)
            ->action(function(Counter $counter, $livewire){
                $nextQueue = app(QueueService::class)->callNextQueue($counter->id);

                // menghindari tejadinya bentrokan ketika memanggil antrian
                if(!$nextQueue){
                    Notification::make()
                        ->title('Tidak Ada Antrian Tersedia')
                        ->danger()
                        ->send();

                    return;
                }

                // $counter->load('activeQueue');
    
                // $livewire->dispatch('queue-called', 'Nomor Antrian ' . $counter->activeQueue->number . ' Silahkan Ke ' . $counter->name);

                $nextQueue->update(['called_at' => null]);  
                Notification::make()
                        ->title('next queue called')
                        ->success()
                        ->send();
            })
            ->label('Panggil')
            ->icon('heroicon-o-speaker-wave');
    }

    private static function getServeQueueAction()
    {
        return Action::make('serveQueue')
            ->button()
            ->label('Layani')
            ->color('success')
            ->icon('heroicon-o-check-circle')
            ->action(function(Counter $counter){
                app(QueueService::class)->serveQueue($counter->activeQueue);
            })
            ->requiresConfirmation()
            ->visible(fn(Counter $counter) => $counter->is_available && $counter->activeQueue);
    }

    private static function getFinishQueueAction()
    {
        return Action::make('finisQueue')
            ->button()
            ->label('Selesai')
            ->icon('heroicon-o-check')
              ->action(function(Counter $counter){
                app(QueueService::class)->finishQueue($counter->activeQueue);
            })
            ->requiresConfirmation()
            ->visible(fn(Counter $counter) => $counter->activeQueue?->status === "serving");
    }

    private static function getCancelQueueAction()
    {
        return Action::make('cancelQueue')
            ->button()
            ->label('Batal')
            ->color('danger')
            ->icon('heroicon-o-x-circle')
            ->action(function(Counter $counter){
                app(QueueService::class)->cancelQueue($counter->activeQueue);
            })
            ->requiresConfirmation()
             ->visible(fn(Counter $counter) => $counter->is_available && $counter->activeQueue);
    }
}
