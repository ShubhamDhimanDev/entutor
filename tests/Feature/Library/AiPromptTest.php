<?php

use App\Models\AiPrompt;
use App\Models\AiPromptCategory;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('ai-prompts.index'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can browse ai prompts grouped by category', function () {
    $user = User::factory()->create();

    $categoryA = AiPromptCategory::factory()->create(['order' => 1]);
    AiPrompt::factory()->count(2)->create(['ai_prompt_category_id' => $categoryA->id]);

    $categoryB = AiPromptCategory::factory()->create(['order' => 2]);
    AiPrompt::factory()->count(3)->create(['ai_prompt_category_id' => $categoryB->id]);

    $response = $this->actingAs($user)->get(route('ai-prompts.index'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('ai-prompts/index')
            ->has('categories', 2)
            ->has('categories.0.prompts', 2)
            ->has('categories.1.prompts', 3),
        );
});
