<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

class SiteSettings extends Page
{
    use HasPageShield, InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Site Settings';

    protected static string|UnitEnum|null $navigationGroup = 'Super Admin';

    protected static ?int $navigationSort = 98;

    protected string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SiteSetting::instance();

        $this->form->fill([
            'site_name' => $settings->site_name,
            'site_logo' => $settings->site_logo,
            'site_favicon' => $settings->site_favicon,
            'site_primary_color' => $settings->site_primary_color,
            'site_font_family' => $settings->site_font_family,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('General Information')
                    ->description('Basic identity and theme settings.')
                    ->icon('heroicon-o-information-circle')
                    ->columns(3)
                    ->schema([
                        TextInput::make('site_name')
                            ->label('Site Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('EMMS'),

                        Select::make('site_primary_color')
                            ->label('Primary Color')
                            ->options([
                                'red' => 'Red',
                                'orange' => 'Orange',
                                'amber' => 'Amber',
                                'yellow' => 'Yellow',
                                'lime' => 'Lime',
                                'green' => 'Green',
                                'emerald' => 'Emerald',
                                'teal' => 'Teal',
                                'cyan' => 'Cyan',
                                'sky' => 'Sky',
                                'blue' => 'Blue',
                                'indigo' => 'Indigo',
                                'violet' => 'Violet',
                                'purple' => 'Purple',
                                'fuchsia' => 'Fuchsia',
                                'pink' => 'Pink',
                                'rose' => 'Rose',
                            ])
                            ->required()
                            ->native(false)
                            ->searchable(),

                        Select::make('site_font_family')
                            ->label('Font Family')
                            ->options([
                                'Inter' => 'Inter',
                                'Arial' => 'Arial',
                                'Roboto' => 'Roboto',
                                'Open Sans' => 'Open Sans',
                                'Lato' => 'Lato',
                                'Montserrat' => 'Montserrat',
                                'Poppins' => 'Poppins',
                                'Nunito' => 'Nunito',
                                'Quicksand' => 'Quicksand',
                                'Ubuntu' => 'Ubuntu',
                                'Merriweather' => 'Merriweather',
                                'Playfair Display' => 'Playfair Display',
                                'Lora' => 'Lora',
                            ])
                            ->required()
                            ->native(false)
                            ->searchable(),
                    ]),

                Section::make('Branding Assets')
                    ->description('Logo and Favicon management.')
                    ->icon('heroicon-o-photo')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('site_logo')
                            ->label('Site Logo')
                            ->image()
                            ->disk('public')
                            ->directory('site-logo')
                            ->nullable()
                            ->maxSize(2048)
                            ->imageEditor()
                            ->panelLayout('integrated'),

                        FileUpload::make('site_favicon')
                            ->label('Site Favicon')
                            ->image()
                            ->disk('public')
                            ->directory('site-favicon')
                            ->nullable()
                            ->maxSize(1024)
                            ->imageEditor()
                            ->panelLayout('integrated'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = SiteSetting::instance();
        $settings->update($data);

        Notification::make()
            ->title('Site Settings Updated')
            ->body('Your site settings have been saved successfully.')
            ->success()
            ->send();

        $this->redirect(static::getUrl(), navigate: false);
    }
}
