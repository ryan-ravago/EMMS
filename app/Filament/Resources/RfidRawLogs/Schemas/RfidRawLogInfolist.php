<?php

namespace App\Filament\Resources\RfidRawLogs\Schemas;

use App\Models\RfidRawLog;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RfidRawLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->inlineLabel()
            ->components([
                Section::make('RFID Raw Log')
                    ->schema([
                        TextEntry::make('tag_id')->label('Tag')->copyable(),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn(string $state): string => $state === 'IN' ? 'success' : 'warning'),
                        TextEntry::make('yard.name')->label('Yard')->placeholder('-'),
                        TextEntry::make('detected_at')->label('Detected')->dateTime(),
                        TextEntry::make('received_at')->label('Received')->dateTime(),
                        TextEntry::make('outcome')
                            ->badge()
                            ->formatStateUsing(fn(string $state): string => RfidRawLog::outcomeLabel($state))
                            ->color(fn(string $state): string => RfidRawLog::outcomeColor($state)),
                        TextEntry::make('attempts'),
                        TextEntry::make('processed_at')->label('Processed')->dateTime()->placeholder('-'),
                        TextEntry::make('batch_uuid')->label('Batch')->copyable(),
                        TextEntry::make('ip')->label('IP address')->placeholder('-'),
                        TextEntry::make('token_id')->label('Token ID')->placeholder('-')
                    ])
            ]);
    }
}
