<?php

use App\Enums\TenseKey;
use App\Enums\TenseTime;
use App\Models\Tense;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $tense = Tense::factory()->create();

    $this->get(route('tenses.index'))->assertRedirect(route('login'));
    $this->get(route('tenses.show', $tense))->assertRedirect(route('login'));
});

test('authenticated users can browse tenses as a list', function () {
    $user = User::factory()->create();
    Tense::factory()->count(5)->create();

    $response = $this->actingAs($user)->get(route('tenses.index'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tenses/index')
            ->has('times', 3)
            ->has('tenses', 5),
        );
});

test('the time filter only returns tenses in that time', function () {
    $user = User::factory()->create();
    Tense::factory()->count(2)->create(['time' => TenseTime::Present]);
    Tense::factory()->count(3)->create(['time' => TenseTime::Past]);

    $response = $this->actingAs($user)->get(route('tenses.index', ['time' => TenseTime::Present->value]));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has(
                'tenses',
                2,
                fn (Assert $tense) => $tense->where('time', TenseTime::Present->value)->etc(),
            )
            ->where('filters.time', TenseTime::Present->value),
        );
});

test('an invalid time value is rejected', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('tenses.index', ['time' => 'not-a-real-time']));

    $response->assertSessionHasErrors('time');
});

test('the index list view does not include the full detail fields', function () {
    $user = User::factory()->create();
    Tense::factory()->create();

    $response = $this->actingAs($user)->get(route('tenses.index'));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('tenses.0', fn (Assert $tense) => $tense
            ->has('id')
            ->has('key')
            ->has('name')
            ->has('time')
            ->has('aspect')
            ->has('summary')
            ->missing('structure_affirmative')
            ->missing('usage_rules')
            ->missing('examples'),
        ),
    );
});

test('the show route returns the full detail for a valid key', function () {
    $user = User::factory()->create();
    $tense = Tense::factory()->create([
        'key' => TenseKey::PastPerfect,
        'name' => 'Past Perfect',
        'common_confusions' => 'Often mixed up with Past Simple.',
    ]);

    $response = $this->actingAs($user)->get(route('tenses.show', $tense));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tenses/show')
            ->where('tense.id', $tense->id)
            ->where('tense.key', TenseKey::PastPerfect->value)
            ->where('tense.name', 'Past Perfect')
            ->has('tense.structure_affirmative')
            ->has('tense.structure_negative')
            ->has('tense.structure_interrogative')
            ->has('tense.usage_rules')
            ->has('tense.signal_words')
            ->has('tense.examples')
            ->where('tense.common_confusions', 'Often mixed up with Past Simple.'),
        );
});

test('the show route 404s for an unknown key', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/tenses/not-a-real-tense');

    $response->assertNotFound();
});

test('the show route resolves by the key column, not the numeric id', function () {
    $user = User::factory()->create();
    // Create an earlier row (with an explicit, distinct key — leaving this
    // to the factory's random unique() pick risks colliding with the target
    // tense's key below and failing on the unique constraint) so the target
    // tense's numeric id and its key value can't accidentally coincide.
    Tense::factory()->create(['key' => TenseKey::PresentSimple]);
    $tense = Tense::factory()->create(['key' => TenseKey::FuturePerfectContinuous]);

    $response = $this->actingAs($user)->get('/tenses/'.TenseKey::FuturePerfectContinuous->value);

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('tense.id', $tense->id)
            ->where('tense.key', TenseKey::FuturePerfectContinuous->value),
        );

    // The numeric id alone is not a valid lookup value for this binding.
    $this->actingAs($user)->get('/tenses/'.$tense->id)->assertNotFound();
});
