<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_with_hindi_gets_hindi_text(): void
    {
        $user = User::factory()->create(['locale' => 'hi']);

        $this->actingAs($user)->get('/profile');

        $this->assertSame('hi', App::getLocale());
    }

    public function test_a_user_without_a_supported_locale_gets_english(): void
    {
        $user = User::factory()->create(['locale' => 'fr']);

        $this->actingAs($user)->get('/profile');

        $this->assertSame('en', App::getLocale());
    }

    public function test_switching_language_saves_it_on_the_user(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $this->actingAs($user)->post(route('locale.update', 'hi'))->assertRedirect();

        $this->assertSame('hi', $user->fresh()->locale);
    }

    public function test_an_unsupported_language_is_rejected(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $this->actingAs($user)->post('/locale/fr')->assertNotFound();

        $this->assertSame('en', $user->fresh()->locale);
    }
}
