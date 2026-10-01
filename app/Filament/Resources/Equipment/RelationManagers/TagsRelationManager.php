<?php

namespace App\Filament\Resources\Equipment\RelationManagers;

use App\Filament\Resources\Equipment\Resources\AssetTags\AssetTagResource;
use App\Models\AssetTag;
use Filament\Actions\Action;
use Filament\Forms\Components\TagsInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TagsRelationManager extends RelationManager
{
    protected static string $relationship = 'tags';

    protected static ?string $relatedResource = AssetTagResource::class;

    protected static ?string $title = 'Tags';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->tags()->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('addTags')
                    ->label('Add tags')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Add tags')
                    ->modalSubmitActionLabel('Add')
                    ->authorize(fn() => Auth::user()?->can('update', $this->getOwnerRecord()) ?? false)
                    ->schema([
                        TagsInput::make('tags')
                            ->label('Tags')
                            ->helperText('Type a tag and press Enter (or paste multiple, separated by comma). Each tag must be unique.')
                            ->splitKeys(['Tab', ',', ' '])
                            ->required()
                            ->nestedRecursiveRules([
                                'string',
                                'max:255',
                                Rule::unique('asset_tags', 'tag_id'),
                            ]),
                    ])
                    ->action(function (array $data): void {
                        // Trim + drop empties + de-dupe (case-insensitive, same as DB collation)
                        $tags = collect($data['tags'])
                            ->map(fn(string $tag): string => trim($tag))
                            ->filter()
                            ->unique(fn(string $tag): string => mb_strtolower($tag))
                            ->values();

                        $ownerId = $this->getOwnerRecord()->getKey();

                        // Single bulk INSERT instead of one query per tag
                        AssetTag::query()->insert(
                            $tags->map(fn(string $tag): array => [
                                'tag_id' => $tag,
                                'asset_parent_id' => $ownerId,
                            ])->all(),
                        );

                        Notification::make()
                            ->title($tags->count() . ' tag(s) added')
                            ->success()
                            ->send();

                        $this->dispatch('equipment-tags-updated');
                    }),
            ]);
    }
}
