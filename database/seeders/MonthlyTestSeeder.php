<?php

namespace Database\Seeders;

use App\Enums\SkillArea;
use App\Models\Month;
use App\Models\MonthlyTest;
use App\Models\MonthlyTestSection;
use Illuminate\Database\Seeder;
use RuntimeException;

class MonthlyTestSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach ($this->tests() as $monthNumber => $sections) {
            $month = Month::where('month_number', $monthNumber)->first();

            if (! $month) {
                throw new RuntimeException(
                    "Month {$monthNumber} not found — MonthSeeder must run before MonthlyTestSeeder."
                );
            }

            $monthlyTest = MonthlyTest::create([
                'month_id' => $month->id,
                'band_excellent_min' => 80,
                'band_good_min' => 60,
                'band_fair_min' => 40,
            ]);

            $totalWeight = 0;

            foreach ($sections as $order => $section) {
                $totalWeight += $section['weight'];

                MonthlyTestSection::create([
                    'monthly_test_id' => $monthlyTest->id,
                    'skill' => $section['skill'],
                    'weight' => $section['weight'],
                    'content' => $section['content'],
                    'order' => $order + 1,
                ]);
            }

            // Assertion: each test's sections must sum to exactly 100.
            if ($totalWeight !== 100) {
                throw new RuntimeException(
                    "Month {$monthNumber} monthly test sections sum to {$totalWeight}, expected 100."
                );
            }
        }
    }

    /**
     * @return array<int, array<int, array{skill: SkillArea, weight: int, content: string}>>
     */
    protected function tests(): array
    {
        return [
            1 => [
                [
                    'skill' => SkillArea::Reading,
                    'weight' => 17,
                    'content' => 'Read a short self-introduction or daily-routine paragraph aloud without '
                        .'stopping, then answer 3 comprehension questions about it. 10 points for fluent '
                        .'reading, 10 points for correct answers.',
                ],
                [
                    'skill' => SkillArea::Vocabulary,
                    'weight' => 17,
                    'content' => '10 random words from Word Bank Groups 1-6; give the native-language meaning '
                        .'or a sentence for each. 2 points per word.',
                ],
                [
                    'skill' => SkillArea::Listening,
                    'weight' => 13,
                    'content' => 'Play a 1-minute simple self-introduction clip once; answer: the person\'s '
                        .'name, where they\'re from, and one more detail you understood.',
                ],
                [
                    'skill' => SkillArea::Speaking,
                    'weight' => 21,
                    'content' => 'An unscripted 2-minute self-introduction and daily-routine conversation. '
                        .'Rated 1-5 on each of: confidence, clarity, range of vocabulary used, complete '
                        .'sentences, and willingness to keep talking without long pauses.',
                ],
                [
                    'skill' => SkillArea::Writing,
                    'weight' => 17,
                    'content' => 'Write 8 sentences on "My Typical Day." Full marks if all 8 are '
                        .'understandable — grammar is secondary to meaning.',
                ],
                [
                    'skill' => SkillArea::Grammar,
                    'weight' => 15,
                    'content' => 'Write 6 sentences: 3 using the Present Simple for a daily routine or a '
                        .'fact about yourself, and 3 using the Present Continuous for something happening '
                        .'right now. Full marks if each tense is used correctly and the two are not mixed up.',
                ],
            ],
            2 => [
                [
                    'skill' => SkillArea::Reading,
                    'weight' => 17,
                    'content' => 'A short passage about a market visit, written in the past tense, with 3 '
                        .'comprehension questions about what was bought and for how much.',
                ],
                [
                    'skill' => SkillArea::Vocabulary,
                    'weight' => 17,
                    'content' => '10 random words from Word Bank Groups 7-12 (adjectives, food, money, '
                        .'feelings, verbs, connectors).',
                ],
                [
                    'skill' => SkillArea::Listening,
                    'weight' => 13,
                    'content' => 'A normal-speed 1-2 minute daily-life audio or video clip; answer 3 simple '
                        .'questions about it.',
                ],
                [
                    'skill' => SkillArea::Speaking,
                    'weight' => 21,
                    'content' => 'Describe yesterday, today, and tomorrow (one activity, in all three '
                        .'tenses), then complete a 2-minute market or shopping roleplay.',
                ],
                [
                    'skill' => SkillArea::Writing,
                    'weight' => 17,
                    'content' => 'A 6-line diary entry: what you did yesterday, and one plan for tomorrow.',
                ],
                [
                    'skill' => SkillArea::Grammar,
                    'weight' => 15,
                    'content' => 'Match 6 time signal words (e.g. yesterday, last week, next year, soon) to '
                        .'Past Simple or Future Simple, then write one sentence for each — 3 in Past Simple, '
                        .'3 in Future Simple. 1 point per correct match, 1 point per correct sentence.',
                ],
            ],
            3 => [
                [
                    'skill' => SkillArea::Reading,
                    'weight' => 17,
                    'content' => 'A sample short friendly message; 3 questions on what is being asked and '
                        .'what action is needed.',
                ],
                [
                    'skill' => SkillArea::Vocabulary,
                    'weight' => 17,
                    'content' => '10 random words from Word Bank Groups 13-17 (health, technology, weather, '
                        .'plans, travel).',
                ],
                [
                    'skill' => SkillArea::Listening,
                    'weight' => 13,
                    'content' => 'A short recorded voicemail or instruction; write down the 3 key points — a '
                        .'real message-taking test.',
                ],
                [
                    'skill' => SkillArea::Speaking,
                    'weight' => 21,
                    'content' => 'A phone-call roleplay (your practice partner calls, you answer naturally), '
                        .'plus repeating a 3-step instruction back correctly.',
                ],
                [
                    'skill' => SkillArea::Writing,
                    'weight' => 17,
                    'content' => 'A clean 5-line message asking for help or information.',
                ],
                [
                    'skill' => SkillArea::Grammar,
                    'weight' => 15,
                    'content' => 'Fill in the blanks in 6 sentences about an interrupted activity and a '
                        .'recent event, choosing correctly between Past Continuous ("was/were + -ing") and '
                        .'Present Perfect ("have/has + past participle") for each blank.',
                ],
            ],
            4 => [
                [
                    'skill' => SkillArea::Reading,
                    'weight' => 17,
                    'content' => 'A bank SMS and a weather-update text; 3 comprehension questions.',
                ],
                [
                    'skill' => SkillArea::Vocabulary,
                    'weight' => 17,
                    'content' => '15 quickfire words from Word Bank Groups 18-20 — use each in one sentence, '
                        .'under 10 seconds per word.',
                ],
                [
                    'skill' => SkillArea::Listening,
                    'weight' => 13,
                    'content' => 'A recorded call about a bill or payment; say what was asked and how it was '
                        .'resolved.',
                ],
                [
                    'skill' => SkillArea::Speaking,
                    'weight' => 21,
                    'content' => 'The Explain-the-Process challenge (an everyday task, unscripted, 10 or more '
                        .'sentences), plus a bank or shop visit roleplay.',
                ],
                [
                    'skill' => SkillArea::Writing,
                    'weight' => 17,
                    'content' => 'A message asking about a bill or payment, plus a short note about a home '
                        .'problem.',
                ],
                [
                    'skill' => SkillArea::Grammar,
                    'weight' => 15,
                    'content' => 'Write a short 6-sentence paragraph about your week: 3 sentences using the '
                        .'Future Continuous for what you will be doing at specific times, and 3 using the '
                        .'Present Perfect Continuous for what you have been doing up to now.',
                ],
            ],
            5 => [
                [
                    'skill' => SkillArea::Reading,
                    'weight' => 17,
                    'content' => 'A sample complaint message; 3 questions — what the problem is, what is '
                        .'being requested, and the tone used.',
                ],
                [
                    'skill' => SkillArea::Vocabulary,
                    'weight' => 17,
                    'content' => 'A mixed review of 15 words from across the full Word Bank — define each in '
                        .'one sentence.',
                ],
                [
                    'skill' => SkillArea::Listening,
                    'weight' => 13,
                    'content' => 'A recorded complaint or disagreement call; say what the complaint was and '
                        .'how it was resolved.',
                ],
                [
                    'skill' => SkillArea::Speaking,
                    'weight' => 21,
                    'content' => 'A complaint roleplay, plus the Confirm-the-Details drill (reading back an '
                        .'address or phone number correctly, with zero errors).',
                ],
                [
                    'skill' => SkillArea::Writing,
                    'weight' => 17,
                    'content' => 'A polite message raising a problem, plus a short opinion paragraph with '
                        .'one reason.',
                ],
                [
                    'skill' => SkillArea::Grammar,
                    'weight' => 15,
                    'content' => 'Expand 5 short sentences by adding a second clause in the correct tense — '
                        .'an earlier past action with the Past Perfect ("...had already left") or a future '
                        .'deadline with the Future Perfect ("...will have finished by..."), then read all 5 '
                        .'aloud correctly.',
                ],
            ],
            6 => [
                [
                    'skill' => SkillArea::Reading,
                    'weight' => 17,
                    'content' => 'A short passage about someone\'s daily life and goals; explain in your own '
                        .'words what it\'s about.',
                ],
                [
                    'skill' => SkillArea::Vocabulary,
                    'weight' => 17,
                    'content' => 'A mixed review of 20 words and phrases spot-checked across the whole '
                        .'program — no new list this month.',
                ],
                [
                    'skill' => SkillArea::Listening,
                    'weight' => 13,
                    'content' => 'A recorded spontaneous question; respond without preparation time.',
                ],
                [
                    'skill' => SkillArea::Speaking,
                    'weight' => 21,
                    'content' => 'A full unscripted conversation — 8-10 questions from the Confidence Q&A '
                        .'material, scored on confidence, clarity, completeness, natural pace, and handling '
                        .'one unexpected question.',
                ],
                [
                    'skill' => SkillArea::Writing,
                    'weight' => 17,
                    'content' => 'A complete written "Tell me about yourself" answer, plus a one-paragraph '
                        .'summary of your 6-month progress. This is the capstone test — a score of 70+ means '
                        .'genuine, natural conversational confidence.',
                ],
                [
                    'skill' => SkillArea::Grammar,
                    'weight' => 15,
                    'content' => 'Write a 6-sentence reflection on your 6-month journey: 3 sentences using '
                        .'the Past Perfect Continuous for how long you had been practicing before a turning '
                        .'point, and 3 using the Future Perfect Continuous for how long you will have been '
                        .'speaking English by a future milestone.',
                ],
            ],
        ];
    }
}
