<?php

namespace CraftForge\FilamentLanguageSwitcher\Tests\Unit;

use CraftForge\FilamentLanguageSwitcher\FilamentLanguageSwitcherPlugin;
use CraftForge\FilamentLanguageSwitcher\Tests\TestCase;
use ReflectionMethod;
use ReflectionProperty;

class FilamentLanguageSwitcherPluginTest extends TestCase
{
    protected function callGetLocales(FilamentLanguageSwitcherPlugin $plugin): array
    {
        return (new ReflectionMethod($plugin, 'getLocales'))->invoke($plugin);
    }

    public function test_locales_accepts_array(): void
    {
        $plugin = FilamentLanguageSwitcherPlugin::make()
            ->locales([
                ['code' => 'en', 'name' => 'English', 'flag' => 'gb'],
                ['code' => 'uk', 'name' => 'Українська', 'flag' => 'ua'],
            ]);

        $locales = $this->callGetLocales($plugin);

        $this->assertCount(2, $locales);
        $this->assertEquals('en', $locales[0]['code']);
        $this->assertEquals('English', $locales[0]['name']);
        $this->assertEquals('gb', $locales[0]['flag']);
        $this->assertEquals('uk', $locales[1]['code']);
        $this->assertEquals('Українська', $locales[1]['name']);
        $this->assertEquals('ua', $locales[1]['flag']);
    }

    public function test_locales_accepts_closure(): void
    {
        $plugin = FilamentLanguageSwitcherPlugin::make()
            ->locales(fn () => [
                ['code' => 'en', 'name' => 'English', 'flag' => 'gb'],
                ['code' => 'fr', 'name' => 'Français', 'flag' => 'fr'],
            ]);

        $locales = $this->callGetLocales($plugin);

        $this->assertCount(2, $locales);
        $this->assertEquals('en', $locales[0]['code']);
        $this->assertEquals('fr', $locales[1]['code']);
    }

    public function test_closure_is_lazily_evaluated(): void
    {
        $callCount = 0;

        $plugin = FilamentLanguageSwitcherPlugin::make()
            ->locales(function () use (&$callCount) {
                $callCount++;

                return [
                    ['code' => 'en', 'name' => 'English', 'flag' => 'gb'],
                ];
            });

        $this->assertEquals(0, $callCount);

        $this->callGetLocales($plugin);
        $this->assertEquals(1, $callCount);

        $this->callGetLocales($plugin);
        $this->assertEquals(2, $callCount);
    }

    public function test_auto_enriches_name_when_missing(): void
    {
        $plugin = FilamentLanguageSwitcherPlugin::make()
            ->locales([
                ['code' => 'en', 'flag' => 'gb'],
                ['code' => 'uk', 'flag' => 'ua'],
            ]);

        $locales = $this->callGetLocales($plugin);

        $this->assertEquals('English', $locales[0]['name']);
        $this->assertEquals('Українська', $locales[1]['name']);
    }

    public function test_auto_enriches_flag_when_missing(): void
    {
        $plugin = FilamentLanguageSwitcherPlugin::make()
            ->locales([
                ['code' => 'en'],
                ['code' => 'uk'],
            ]);

        $locales = $this->callGetLocales($plugin);

        $this->assertEquals('gb', $locales[0]['flag']);
        $this->assertEquals('ua', $locales[1]['flag']);
    }

    public function test_auto_enriches_from_closure(): void
    {
        $plugin = FilamentLanguageSwitcherPlugin::make()
            ->locales(fn () => [
                ['code' => 'de'],
                ['code' => 'fr'],
            ]);

        $locales = $this->callGetLocales($plugin);

        $this->assertEquals('Deutsch', $locales[0]['name']);
        $this->assertEquals('de', $locales[0]['flag']);
        $this->assertEquals('Français', $locales[1]['name']);
        $this->assertEquals('fr', $locales[1]['flag']);
    }

    public function test_unknown_locale_gets_fallback_name_and_flag(): void
    {
        $plugin = FilamentLanguageSwitcherPlugin::make()
            ->locales([
                ['code' => 'xx'],
            ]);

        $locales = $this->callGetLocales($plugin);

        $this->assertEquals('Xx', $locales[0]['name']);
        $this->assertEquals('xx', $locales[0]['flag']);
    }

    public function test_explicit_values_are_not_overridden(): void
    {
        $plugin = FilamentLanguageSwitcherPlugin::make()
            ->locales([
                ['code' => 'en', 'name' => 'American English', 'flag' => 'us'],
            ]);

        $locales = $this->callGetLocales($plugin);

        $this->assertEquals('American English', $locales[0]['name']);
        $this->assertEquals('us', $locales[0]['flag']);
    }

    public function test_empty_array_falls_back_to_filament_locales(): void
    {
        $plugin = FilamentLanguageSwitcherPlugin::make()
            ->locales([]);

        $locales = $this->callGetLocales($plugin);

        // Falls back to getFilamentLocales() which scans vendor dir
        $this->assertIsArray($locales);
    }

    public function test_empty_closure_falls_back_to_filament_locales(): void
    {
        $plugin = FilamentLanguageSwitcherPlugin::make()
            ->locales(fn () => []);

        $locales = $this->callGetLocales($plugin);

        $this->assertIsArray($locales);
    }

    public function test_flat_string_locales_are_normalized(): void
    {
        $plugin = FilamentLanguageSwitcherPlugin::make()
            ->locales(['en', 'pl']);

        $locales = $this->callGetLocales($plugin);

        $this->assertCount(2, $locales);
        $this->assertEquals('en', $locales[0]['code']);
        $this->assertEquals('English', $locales[0]['name']);
        $this->assertEquals('gb', $locales[0]['flag']);
        $this->assertEquals('pl', $locales[1]['code']);
        $this->assertEquals('Polski', $locales[1]['name']);
        $this->assertEquals('pl', $locales[1]['flag']);
    }

    public function test_flat_string_locales_from_closure(): void
    {
        $plugin = FilamentLanguageSwitcherPlugin::make()
            ->locales(fn () => ['en', 'pl']);

        $locales = $this->callGetLocales($plugin);

        $this->assertCount(2, $locales);
        $this->assertEquals('en', $locales[0]['code']);
        $this->assertEquals('English', $locales[0]['name']);
        $this->assertEquals('pl', $locales[1]['code']);
        $this->assertEquals('Polski', $locales[1]['name']);
    }

    public function test_make_returns_fluent_instance(): void
    {
        $plugin = FilamentLanguageSwitcherPlugin::make();

        $this->assertInstanceOf(FilamentLanguageSwitcherPlugin::class, $plugin);

        $result = $plugin->locales([['code' => 'en']]);
        $this->assertSame($plugin, $result);

        $result = $plugin->showFlags(false);
        $this->assertSame($plugin, $result);
    }

    public function test_show_on_auth_pages_defaults_to_false(): void
    {
        $plugin = FilamentLanguageSwitcherPlugin::make();

        $property = new ReflectionProperty($plugin, 'showOnAuthPages');

        $this->assertFalse($property->getValue($plugin));
    }

    public function test_show_on_auth_pages_can_be_enabled(): void
    {
        $plugin = FilamentLanguageSwitcherPlugin::make()
            ->showOnAuthPages();

        $property = new ReflectionProperty($plugin, 'showOnAuthPages');

        $this->assertTrue($property->getValue($plugin));
    }

    public function test_show_on_auth_pages_can_be_disabled(): void
    {
        $plugin = FilamentLanguageSwitcherPlugin::make()
            ->showOnAuthPages()
            ->showOnAuthPages(false);

        $property = new ReflectionProperty($plugin, 'showOnAuthPages');

        $this->assertFalse($property->getValue($plugin));
    }

    public function test_show_on_auth_pages_returns_fluent_instance(): void
    {
        $plugin = FilamentLanguageSwitcherPlugin::make();

        $result = $plugin->showOnAuthPages();

        $this->assertSame($plugin, $result);
    }
}
