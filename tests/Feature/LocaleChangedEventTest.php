<?php

namespace CraftForge\FilamentLanguageSwitcher\Tests\Feature;

use CraftForge\FilamentLanguageSwitcher\Events\LocaleChanged;
use CraftForge\FilamentLanguageSwitcher\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class LocaleChangedEventTest extends TestCase
{
    public function test_locale_changed_event_is_dispatched_when_switching_locale(): void
    {
        Event::fake([LocaleChanged::class]);

        $this->get(route('filament-language-switcher.switch', ['code' => 'fr']));

        Event::assertDispatched(LocaleChanged::class);
    }

    public function test_locale_changed_event_contains_correct_new_locale(): void
    {
        Event::fake([LocaleChanged::class]);

        $this->get(route('filament-language-switcher.switch', ['code' => 'de']));

        Event::assertDispatched(LocaleChanged::class, function (LocaleChanged $event) {
            return $event->newLocale === 'de';
        });
    }

    public function test_locale_changed_event_contains_default_old_locale_on_first_switch(): void
    {
        Event::fake([LocaleChanged::class]);

        $this->get(route('filament-language-switcher.switch', ['code' => 'fr']));

        Event::assertDispatched(LocaleChanged::class, function (LocaleChanged $event) {
            return $event->oldLocale === config('app.locale');
        });
    }

    public function test_locale_changed_event_contains_previous_locale_as_old_locale(): void
    {
        // First switch to 'fr'
        $this->withSession(['locale' => 'fr']);

        Event::fake([LocaleChanged::class]);

        $this->get(route('filament-language-switcher.switch', ['code' => 'de']));

        Event::assertDispatched(LocaleChanged::class, function (LocaleChanged $event) {
            return $event->newLocale === 'de' && $event->oldLocale === 'fr';
        });
    }

    public function test_locale_changed_event_redirects_back(): void
    {
        Event::fake([LocaleChanged::class]);

        $response = $this->get(route('filament-language-switcher.switch', ['code' => 'fr']));

        $response->assertRedirect();
    }

    public function test_locale_is_stored_in_session_after_switch(): void
    {
        Event::fake([LocaleChanged::class]);

        $this->get(route('filament-language-switcher.switch', ['code' => 'de']));

        $this->assertEquals('de', session('locale'));
    }
}
