<?php

use App\Models\ConfidenceQuestion;
use App\Models\ConfidenceTopic;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('confidence-qa.index'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can browse confidence questions grouped by topic', function () {
    $user = User::factory()->create();

    $topicA = ConfidenceTopic::factory()->create(['order' => 1]);
    ConfidenceQuestion::factory()->count(2)->create(['confidence_topic_id' => $topicA->id]);

    $topicB = ConfidenceTopic::factory()->create(['order' => 2]);
    ConfidenceQuestion::factory()->count(4)->create(['confidence_topic_id' => $topicB->id]);

    $response = $this->actingAs($user)->get(route('confidence-qa.index'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('confidence-qa/index')
            ->has('topics', 2)
            ->has('topics.0.questions', 2)
            ->has('topics.1.questions', 4)
            ->has('topics.0.questions.0.example_answer'),
        );
});
