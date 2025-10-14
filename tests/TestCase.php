<?php

namespace Mortezamasumi\FbProfile\Tests;

use Ariaieboy\FilamentJalali\FilamentJalaliServiceProvider;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Mortezamasumi\FbEssentials\FbEssentialsServiceProvider;
use Mortezamasumi\FbProfile\FbProfilePlugin;
use Mortezamasumi\FbProfile\FbProfileServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    protected function defineEnvironment($app)
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('nid')->nullable();
            $table->string('gender')->nullable();
            $table->dateTime('birth_date')->nullable();
            $table->json('profile')->nullable();
            $table->string('mobile')->nullable();
            $table->string('avatar')->nullable();
            $table->timestamps();
        });

        Filament::registerPanel(
            Panel::make()
                ->id('admin')
                ->path('/')
                ->login()
                ->default()
                ->profile()
                ->pages([
                    Dashboard::class,
                ])
                ->plugins([
                    FbProfilePlugin::make(),
                ])
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            \BladeUI\Heroicons\BladeHeroiconsServiceProvider::class,
            \BladeUI\Icons\BladeIconsServiceProvider::class,
            \Filament\FilamentServiceProvider::class,
            \Filament\Actions\ActionsServiceProvider::class,
            \Filament\Forms\FormsServiceProvider::class,
            \Filament\Infolists\InfolistsServiceProvider::class,
            \Filament\Notifications\NotificationsServiceProvider::class,
            \Filament\Schemas\SchemasServiceProvider::class,
            \Filament\Support\SupportServiceProvider::class,
            \Filament\Tables\TablesServiceProvider::class,
            \Filament\Widgets\WidgetsServiceProvider::class,
            \Livewire\LivewireServiceProvider::class,
            \RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider::class,
            \Orchestra\Workbench\WorkbenchServiceProvider::class,
            FilamentJalaliServiceProvider::class,
            FbEssentialsServiceProvider::class,
            FbProfileServiceProvider::class,
        ];
    }
}
