<?php

namespace Mortezamasumi\FbProfile\Tests;

use Ariaieboy\FilamentJalali\FilamentJalaliServiceProvider;
use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\Facades\Filament;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireServiceProvider;
use Mortezamasumi\FbEssentials\FbEssentialsServiceProvider;
use Mortezamasumi\FbProfile\FbProfilePlugin;
use Mortezamasumi\FbProfile\FbProfileServiceProvider;
use Orchestra\Workbench\WorkbenchServiceProvider;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    protected function defineEnvironment($app)
    {
        $app['config']->set('mail.default', 'array');

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
                ->emailChangeVerification()
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
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FilamentServiceProvider::class,
            ActionsServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            NotificationsServiceProvider::class,
            SchemasServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            LivewireServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            WorkbenchServiceProvider::class,
            FilamentJalaliServiceProvider::class,
            FbEssentialsServiceProvider::class,
            FbProfileServiceProvider::class,
        ];
    }
}
