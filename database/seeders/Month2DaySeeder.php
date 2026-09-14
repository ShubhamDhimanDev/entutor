<?php

namespace Database\Seeders;

use App\Enums\SkillArea;
use App\Enums\TenseKey;
use App\Models\Day;
use App\Models\DayTask;
use App\Models\DayTaskVocabularyItem;
use App\Models\Month;
use App\Models\Tense;
use App\Models\Week;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use RuntimeException;

class Month2DaySeeder extends Seeder
{
    /**
     * Proportional minute split across the 6 daily tasks (out of 64 parts):
     * read/vocabulary/listen/speak/write/grammar ~= 8/12/10/16/10/8. Speaking
     * (the main practice block) absorbs any rounding remainder.
     *
     * @var array<string, int>
     */
    private const MINUTE_RATIOS = [
        'reading' => 8,
        'vocabulary' => 12,
        'listening' => 10,
        'speaking' => 16,
        'writing' => 10,
        'grammar' => 8,
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $month = Month::where('month_number', 2)->first();

        if (! $month) {
            throw new RuntimeException('Month 2 not found — MonthSeeder must run before Month2DaySeeder.');
        }

        $weeks = Week::where('month_id', $month->id)->orderBy('week_number')->get();

        if ($weeks->count() !== 4) {
            throw new RuntimeException('Expected exactly 4 weeks for Month 2 before seeding days.');
        }

        // day_number is globally unique across the whole program (1-180), so
        // local day 1-30 within this month must be offset onto the global
        // range that MonthSeeder already assigned to Month 2's weeks.
        $offset = ($month->month_number - 1) * 30;

        /** @var Collection<string, int> $tenseIds */
        $tenseIds = Tense::query()->pluck('id', 'key');

        foreach ($this->days() as $localDayNumber => $data) {
            $dayNumber = $offset + $localDayNumber;

            $week = $weeks->first(
                fn (Week $week): bool => $dayNumber >= $week->start_day_number && $dayNumber <= $week->end_day_number
            );

            if (! $week) {
                throw new RuntimeException("No week found covering day {$dayNumber}.");
            }

            $day = Day::create([
                'week_id' => $week->id,
                'month_id' => $month->id,
                'day_number' => $dayNumber,
                'title' => $data['title'],
            ]);

            $minutes = $data['minutes'] ?? $this->splitMinutes($data['total_minutes']);

            DayTask::create([
                'day_id' => $day->id,
                'type' => SkillArea::Reading,
                'content' => $data['read'],
                'estimated_minutes' => $minutes['reading'],
            ]);

            $vocabularyTask = DayTask::create([
                'day_id' => $day->id,
                'type' => SkillArea::Vocabulary,
                'content' => $data['vocab_intro'],
                'estimated_minutes' => $minutes['vocabulary'],
            ]);

            foreach ($data['vocab'] as $order => $item) {
                DayTaskVocabularyItem::create([
                    'day_task_id' => $vocabularyTask->id,
                    'word_bank_entry_id' => null,
                    'term' => $item[0],
                    'native_meaning' => $item[1],
                    'order' => $order + 1,
                ]);
            }

            DayTask::create([
                'day_id' => $day->id,
                'type' => SkillArea::Listening,
                'content' => $data['listen'],
                'estimated_minutes' => $minutes['listening'],
            ]);

            DayTask::create([
                'day_id' => $day->id,
                'type' => SkillArea::Speaking,
                'content' => $data['speak'],
                'estimated_minutes' => $minutes['speaking'],
            ]);

            DayTask::create([
                'day_id' => $day->id,
                'type' => SkillArea::Writing,
                'content' => $data['write'],
                'estimated_minutes' => $minutes['writing'],
            ]);

            if (! isset($data['tense'])) {
                throw new RuntimeException("Day {$dayNumber} missing 'tense' key.");
            }

            if (! isset($data['grammar'])) {
                throw new RuntimeException("Day {$dayNumber} missing 'grammar' content.");
            }

            $tenseId = $tenseIds[$data['tense']->value] ?? throw new RuntimeException(
                "Day {$dayNumber}: no Tense found for key '{$data['tense']->value}'."
            );

            DayTask::create([
                'day_id' => $day->id,
                'type' => SkillArea::Grammar,
                'tense_id' => $tenseId,
                'content' => $data['grammar'],
                'estimated_minutes' => $minutes['grammar'],
            ]);
        }

        $this->assertMonth2Integrity($month);
    }

    /**
     * Assertion: exactly 30 Day rows and 180 DayTask rows (30 x 6) exist for
     * Month 2, and every day has exactly one task per SkillArea.
     */
    private function assertMonth2Integrity(Month $month): void
    {
        $dayCount = Day::where('month_id', $month->id)->count();
        if ($dayCount !== 30) {
            throw new RuntimeException("Expected 30 Day rows for Month 2, found {$dayCount}.");
        }

        $dayIds = Day::where('month_id', $month->id)->pluck('id');
        $dayTaskCount = DayTask::whereIn('day_id', $dayIds)->count();
        if ($dayTaskCount !== 180) {
            throw new RuntimeException("Expected 180 DayTask rows for Month 2, found {$dayTaskCount}.");
        }

        $expectedTypes = collect(SkillArea::cases())->map(fn (SkillArea $type): string => $type->value)
            ->sort()->values()->all();

        Day::where('month_id', $month->id)->with('dayTasks')->get()->each(function (Day $day) use ($expectedTypes): void {
            $types = $day->dayTasks->map(fn (DayTask $task): string => $task->type->value)->sort()->values()->all();

            if ($types !== $expectedTypes) {
                throw new RuntimeException("Day {$day->day_number} does not have exactly one task per SkillArea.");
            }
        });
    }

    /**
     * @return array<string, int>
     */
    private function splitMinutes(int $total): array
    {
        $sum = array_sum(self::MINUTE_RATIOS);
        $minutes = [];
        $allocated = 0;

        foreach (self::MINUTE_RATIOS as $key => $ratio) {
            if ($key === 'speaking') {
                continue;
            }

            $minutes[$key] = max(1, (int) round($total * $ratio / $sum));
            $allocated += $minutes[$key];
        }

        $minutes['speaking'] = max(1, $total - $allocated);

        return $minutes;
    }

    /**
     * Month 2 — full day-by-day content (local days 1-30, offset to global
     * days 31-60). Vocabulary terms are drawn from Word Bank Groups 7-12
     * (Adjectives, Food & Drink, Money & Shopping, Feelings & Emotions,
     * Common Verbs 2, Connectors & Small Words), with Hindi glosses matching
     * WordBankSeeder exactly.
     *
     * Grammar focus: local days 1-14 (weeks 1-2) teach Past Simple; local
     * days 15-30 (weeks 3-4) teach Future Simple, matching this month's
     * "Past & Future" week-4 theme. Terminology and structure formulas match
     * TenseSeeder exactly. Progression within each block: introduce the
     * affirmative form, then negative/question forms, then signal words and
     * usage rules, then common-mistake correction and freer/review practice.
     *
     * @return array<int, array<string, mixed>>
     */
    private function days(): array
    {
        return [
            1 => [
                'title' => 'Describing Your Daily Routine',
                'total_minutes' => 45,
                'read' => "Read this short routine paragraph aloud: 'Every day, I wake up early. My morning "
                    ."is good, but sometimes busy. I usually have tea and a small breakfast.'",
                'listen' => 'Watch or listen to a short video of someone describing their daily routine. '
                    ."Notice how they use 'usually' and 'every day'.",
                'speak' => "Tell your practice partner about your typical day, using 'usually' and 'every "
                    ."day' at least twice each.",
                'write' => "Write 5 sentences describing your daily routine, using 'usually' or 'every day' "
                    .'in at least 2 of them.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['good', 'अच्छा (achha)'],
                    ['bad', 'बुरा (bura)'],
                    ['big', 'बड़ा (bada)'],
                    ['small', 'छोटा (chhota)'],
                    ['new', 'नया (naya)'],
                ],
                'grammar' => "Today's grammar focus is Past Simple: Subject + verb (past form / -ed), used "
                    ."for actions that are already finished. Practice with regular verbs: 'I walked to work. "
                    ."I cooked breakfast. I called my mother.' Say 3 sentences about what you did before your "
                    .'daily routine started this morning, using the -ed form.',
                'tense' => TenseKey::PastSimple,
            ],
            2 => [
                'title' => 'Old Habits, New Habits',
                'total_minutes' => 45,
                'read' => "Read these sentences aloud: 'My old routine was slow. My new routine starts "
                    ."early. It is too hot to walk at noon, so I walk in the cool morning.'",
                'listen' => "Watch a short video comparing someone's old daily routine with their new one.",
                'speak' => 'Tell your practice partner one old habit you have changed and one new habit you '
                    .'have started.',
                'write' => 'Write 4 sentences comparing your old routine with your new routine.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['old', 'पुराना (purana)'],
                    ['hot', 'गरम (garam)'],
                    ['cold', 'ठंडा (thanda)'],
                    ['easy', 'आसान (aasaan)'],
                    ['difficult', 'मुश्किल (mushkil)'],
                ],
                'grammar' => "Continue the Past Simple affirmative, now with irregular verbs that don't take "
                    ."-ed: 'go' becomes 'went', 'have' becomes 'had', and 'be' becomes 'was/were'. Say 3 "
                    ."sentences about your old routine using 'was', 'went', or 'had', such as 'My old routine "
                    ."was slow' or 'I went to bed late'.",
                'tense' => TenseKey::PastSimple,
            ],
            3 => [
                'title' => 'Fast Mornings, Slow Evenings',
                'total_minutes' => 48,
                'read' => "Read aloud: 'My mornings are fast — I get ready quickly. My evenings are slow and "
                    ."calm; I relax at a clean home.'",
                'listen' => 'Watch a short video about a busy morning and a calm evening.',
                'speak' => 'Describe the fastest part of your day and the slowest part of your day to your '
                    .'practice partner.',
                'write' => 'Write 4 sentences: 2 about a fast part of your day, and 2 about a slow part.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['fast', 'तेज़ (tez)'],
                    ['slow', 'धीमा (dheema)'],
                    ['clean', 'साफ़ (saaf)'],
                    ['dirty', 'गंदा (ganda)'],
                    ['expensive', 'महँगा (mehanga)'],
                ],
                'grammar' => "Practice 'was' and 'were', the Past Simple forms of 'be': use 'was' with "
                    ."I/he/she/it and 'were' with you/we/they. Describe yesterday morning and yesterday "
                    ."evening to yourself in 4 sentences, using 'was' or 'were' at least twice.",
                'tense' => TenseKey::PastSimple,
            ],
            4 => [
                'title' => 'Good Days and Bad Decisions',
                'total_minutes' => 45,
                'read' => "Read these sentences aloud: 'Today was a good day. I made the correct decision. "
                    ."Yesterday I made one wrong decision, but it was not too expensive.'",
                'listen' => 'Listen to a short clip about someone reflecting on their day — the good parts '
                    .'and one mistake.',
                'speak' => 'Tell your practice partner about one correct decision and one wrong decision you '
                    .'made recently.',
                'write' => 'Write 4 sentences: 2 about something good today, and 2 about a decision, correct '
                    .'or wrong.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['cheap', 'सस्ता (sasta)'],
                    ['beautiful', 'सुंदर (sundar)'],
                    ['important', 'ज़रूरी (zaroori)'],
                    ['correct', 'सही (sahi)'],
                    ['wrong', 'गलत (galat)'],
                ],
                'grammar' => "Learn the Past Simple negative: Subject + did not/didn't + verb (base form) — "
                    ."the main verb loses its -ed once 'didn't' is there. Compare: 'I made the correct "
                    ."decision' with 'I didn't make the wrong decision.' Rewrite 2 of today's Writing-task "
                    ."sentences in the negative, using 'didn't'.",
                'tense' => TenseKey::PastSimple,
            ],
            5 => [
                'title' => 'Energy Through the Day',
                'total_minutes' => 48,
                'read' => "Read aloud: 'In the morning, I feel strong. By evening, I feel weak and tired. My "
                    ."bag feels heavy after shopping, but my phone is light.'",
                'listen' => 'Watch a short video about energy levels through the day — morning, afternoon, '
                    .'and night.',
                'speak' => 'Tell your practice partner when you feel strong and when you feel weak during '
                    .'your day, and why it is different.',
                'write' => 'Write 4 sentences about your energy at different times of day.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['strong', 'मज़बूत (mazboot)'],
                    ['weak', 'कमज़ोर (kamzor)'],
                    ['heavy', 'भारी (bhaari)'],
                    ['light', 'हल्का (halka)'],
                    ['different', 'अलग (alag)'],
                ],
                'grammar' => "Keep practicing the Past Simple negative: Subject + didn't + verb (base form). "
                    ."Say 3 negative sentences about yesterday, such as 'I didn't feel strong in the evening' "
                    ."or 'I didn't sleep enough,' remembering the verb stays in its base form after 'didn't'.",
                'tense' => TenseKey::PastSimple,
            ],
            6 => [
                'title' => 'Putting Your Day in Order',
                'total_minutes' => 45,
                'read' => "Read this ordered routine aloud: 'First, I wake up. Next, I brush my teeth. Then "
                    ."I have breakfast. After that, I go to work. Finally, I come home.'",
                'listen' => 'Watch a short video of someone explaining their routine step by step, using '
                    .'sequencing words.',
                'speak' => 'Describe your full daily routine to your practice partner in order, using '
                    ."'first', 'next', 'then', 'after that', and 'finally'.",
                'write' => 'Write your daily routine as 6 sequenced sentences, using at least 4 sequencing '
                    .'words.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['first', 'पहले (pehle)'],
                    ['next', 'अगला (agla)'],
                    ['then', 'फिर (phir)'],
                    ['after that', 'उसके बाद (uske baad)'],
                    ['before that', 'उससे पहले (usse pehle)'],
                    ['finally', 'आख़िर में (aakhir mein)'],
                    ['while', 'जब (jab)'],
                ],
                'grammar' => 'Learn the Past Simple question form: Did + subject + verb (base form)? — '
                    .'remember the main verb never takes -ed in a question. Ask your practice partner 3 '
                    ."questions about their day yesterday, such as 'Did you wake up early?' or 'Did you finish "
                    ."work on time?'",
                'tense' => TenseKey::PastSimple,
            ],
            7 => [
                'title' => 'Week 1 Review — Describe Your Daily Routine',
                'total_minutes' => 58,
                'read' => "Re-read all of this week's words aloud, once, at normal speed.",
                'listen' => 'Watch the Day 1 routine video again and notice what feels easier now.',
                'speak' => 'Describe your entire daily routine to your practice partner in 8 or more '
                    ."sentences, using 'usually', 'every day', a clock time, and at least 2 sequencing words. "
                    .'Your practice partner confirms the Week 1 milestone if you manage it.',
                'write' => "Write an 8-sentence paragraph: 'My Daily Routine', using at least 5 adjectives "
                    .'and 3 sequencing words from this week.',
                'vocab_intro' => 'No new words this week — re-read all 32 Week 1 words aloud once, at normal '
                    .'speed, and notice which ones are now easy.',
                'vocab' => [],
                'grammar' => 'Week 1 grammar review — Past Simple in all three forms: affirmative (I '
                    ."walked), negative (I didn't walk), and question (Did you walk?). Pick 3 verbs from this "
                    ."week's vocabulary and make one sentence of each type for each verb.",
                'tense' => TenseKey::PastSimple,
            ],
            8 => [
                'title' => 'Asking About Prices',
                'total_minutes' => 45,
                'read' => "Read this shop dialogue aloud: 'What is the price of this shirt? It is two "
                    ."hundred rupees. Is there any discount today?'",
                'listen' => 'Watch a short clip of a customer asking about prices in a shop.',
                'speak' => 'Ask your practice partner the price of 5 objects near you, and answer as if you '
                    .'were the shopkeeper.',
                'write' => 'Write a short 4-line dialogue asking about a price and a discount.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['money', 'पैसा (paisa)'],
                    ['price', 'कीमत (keemat)'],
                    ['rupee', 'रुपया (rupaya)'],
                    ['discount', 'छूट (chhoot)'],
                ],
                'grammar' => "Past Simple often comes with a specific past-time signal word — 'yesterday', "
                    ."'last week', 'two days ago' — that tells the listener exactly when. Say 2 sentences "
                    .'about a purchase you made, each starting with a different signal word, like '
                    ."'Yesterday, I bought a shirt.'",
                'tense' => TenseKey::PastSimple,
            ],
            9 => [
                'title' => 'Bargaining at the Market',
                'total_minutes' => 48,
                'read' => "Read this bargaining dialogue aloud: 'This is too expensive. Can you reduce the "
                    ."price? I will pay in cash.'",
                'listen' => 'Watch a short video of someone bargaining politely at a market.',
                'speak' => 'Roleplay bargaining for one item with your practice partner as the shopkeeper, '
                    .'then swap roles.',
                'write' => 'Write a 4-line bargaining dialogue.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['bargain', 'मोल-भाव (mol-bhaav)'],
                    ['pay', 'भुगतान करना (bhugtaan karna)'],
                    ['cash', 'नकद (nakad)'],
                    ['shop', 'दुकान (dukaan)'],
                ],
                'grammar' => 'Use Past Simple whenever the exact time is known or implied, even without a '
                    .'time word — a finished story about the past still needs Past Simple throughout. Tell '
                    .'your practice partner about the last time you bargained for something, using at least 2 '
                    ."past-time signal words such as 'last month' or 'ago'.",
                'tense' => TenseKey::PastSimple,
            ],
            10 => [
                'title' => 'At the Market',
                'total_minutes' => 45,
                'read' => "Read aloud: 'I am going to the market. The shopkeeper is helpful. I need a big "
                    ."bag for my vegetables.'",
                'listen' => 'Watch a short video of a typical visit to a local market.',
                'speak' => 'Describe a recent market visit to your practice partner: who helped you, and '
                    .'what you carried home.',
                'write' => 'Write 4 sentences about a market or shop you know well.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['market', 'बाज़ार (bazaar)'],
                    ['customer', 'ग्राहक (grahak)'],
                    ['shopkeeper', 'दुकानदार (dukaandaar)'],
                    ['bag', 'थैला (thaila)'],
                ],
                'grammar' => "Review irregular past forms you'll need for market stories: 'buy' becomes "
                    ."'bought', 'go' becomes 'went', 'see' becomes 'saw', and 'get' becomes 'got'. Say 4 "
                    .'sentences about your last market visit using these irregular past forms.',
                'tense' => TenseKey::PastSimple,
            ],
            11 => [
                'title' => 'Choosing Size, Colour & Getting Change',
                'total_minutes' => 48,
                'read' => "Read this shop dialogue aloud: 'Do you have a bigger size? I like this colour. "
                    ."Here is your change and receipt.'",
                'listen' => 'Watch a short clip of a customer choosing size and colour in a clothes shop.',
                'speak' => 'Roleplay buying clothes with your practice partner: ask about size and colour, '
                    .'then collect your change and receipt.',
                'write' => 'Write a 4-line dialogue about choosing a size and colour.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['size', 'साइज़ (size)'],
                    ['colour', 'रंग (rang)'],
                    ['receipt', 'रसीद (raseed)'],
                    ['change (money)', 'बाकी पैसे (baaki paise)'],
                ],
                'grammar' => 'When an action is fully finished — you chose it, paid, and left the shop — '
                    ."Past Simple is the right choice, not Present Simple. Correct these aloud: 'I choose the "
                    ."blue shirt' should be 'I chose the blue shirt'; 'the shopkeeper give me change' should "
                    ."be 'the shopkeeper gave me change'.",
                'tense' => TenseKey::PastSimple,
            ],
            12 => [
                'title' => 'Shopping Online',
                'total_minutes' => 45,
                'read' => "Read aloud: 'I ordered this online. The delivery was free. What is the total "
                    ."amount?'",
                'listen' => 'Watch a short video about placing an online order and tracking delivery.',
                'speak' => 'Tell your practice partner about something you ordered online recently — the '
                    .'total cost and the delivery.',
                'write' => 'Write 4 sentences about an online order, real or imagined.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['free', 'मुफ़्त (muft)'],
                    ['total', 'कुल (kul)'],
                    ['online', 'ऑनलाइन (online)'],
                    ['delivery', 'डिलीवरी (delivery)'],
                ],
                'grammar' => 'Recap: Past Simple means a finished action, often with a time signal word like '
                    ."'yesterday' or 'last week'. Write 2 sentences about something you ordered online in the "
                    .'past, each with a different time signal word.',
                'tense' => TenseKey::PastSimple,
            ],
            13 => [
                'title' => 'Paying Smart: Card, Wallet & Budget',
                'total_minutes' => 50,
                'read' => "Read aloud: 'I paid by card. My wallet was empty, so I used my card instead. I "
                    ."try to save money and stay within my budget.'",
                'listen' => 'Watch a short video about managing a monthly budget.',
                'speak' => 'Tell your practice partner how you usually pay for things, and one way you try to '
                    .'save money.',
                'write' => 'Write 4 sentences about how you pay for things and how you save money.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['card', 'कार्ड (card)'],
                    ['wallet', 'बटुआ (batua)'],
                    ['budget', 'बजट (budget)'],
                    ['save (money)', 'बचाना (bachaana)'],
                    ['spend', 'खर्च करना (kharch karna)'],
                ],
                'grammar' => "Common mistake: learners often keep the verb in its past form after 'didn't', "
                    ."saying 'I didn't paid' instead of 'I didn't pay'. Say the correct sentence aloud 3 "
                    ."times, then make 1 more 'didn't' + base-verb sentence about your own spending.",
                'tense' => TenseKey::PastSimple,
            ],
            14 => [
                'title' => 'Week 2 Review — Shopping Roleplay Milestone',
                'total_minutes' => 58,
                'read' => "Re-read all of this week's words aloud.",
                'listen' => 'Watch the Day 9 bargaining video again and notice what feels easier now.',
                'speak' => 'Hold a full 3-minute shopping roleplay with your practice partner: ask the price, '
                    .'bargain, choose a size or colour, and pay. Your practice partner confirms the Week 2 '
                    .'milestone.',
                'write' => 'Write the shopping conversation you just had, from memory, in 8 or more lines.',
                'vocab_intro' => 'No new words today — review all Week 2 words aloud and notice which ones '
                    .'feel easier now.',
                'vocab' => [],
                'grammar' => 'Week 2 grammar review — before your shopping roleplay, silently plan 2 Past '
                    .'Simple sentences you can use afterwards to describe it, in affirmative, negative, or '
                    ."question form (for example, 'I bargained for a discount' or 'Did the shopkeeper "
                    ."agree?').",
                'tense' => TenseKey::PastSimple,
            ],
            15 => [
                'title' => 'Everyday Food & Drink Words',
                'total_minutes' => 45,
                'read' => "Read aloud: 'I drink water and tea every day. I eat rice, bread, vegetables, and "
                    ."fruit. I take sugar in my tea.'",
                'listen' => 'Watch a short video about everyday food and drink.',
                'speak' => 'Tell your practice partner what you usually eat and drink in a normal day.',
                'write' => 'Write 5 sentences about food and drink you have every day.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['water', 'पानी (paani)'],
                    ['tea', 'चाय (chai)'],
                    ['milk', 'दूध (doodh)'],
                    ['rice', 'चावल (chawal)'],
                    ['bread', 'ब्रेड (bread)'],
                    ['vegetable', 'सब्ज़ी (sabzi)'],
                    ['fruit', 'फल (phal)'],
                    ['sugar', 'चीनी (cheeni)'],
                ],
                'grammar' => 'New grammar focus: Future Simple — Subject + will + verb (base form), for '
                    ."things that will happen. Practice: 'I will drink more water. I will try a new fruit.' "
                    .'Say 3 sentences about food or drink you will have tomorrow, using \'will\'.',
                'tense' => TenseKey::FutureSimple,
            ],
            16 => [
                'title' => 'Meals of the Day',
                'total_minutes' => 48,
                'read' => "Read aloud: 'I add a little salt to my food. I like spicy food, but my breakfast "
                    ."is usually sweet. I am hungry before lunch and thirsty by dinner.'",
                'listen' => 'Watch a short video about breakfast, lunch, and dinner in different homes.',
                'speak' => 'Describe your breakfast, lunch, and dinner yesterday to your practice partner.',
                'write' => 'Write 5 sentences about your breakfast, lunch, and dinner.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['salt', 'नमक (namak)'],
                    ['spicy', 'तीखा (teekha)'],
                    ['sweet', 'मीठा (meetha)'],
                    ['breakfast', 'नाश्ता (naashta)'],
                    ['lunch', 'दोपहर का खाना (dopahar ka khaana)'],
                    ['dinner', 'रात का खाना (raat ka khaana)'],
                    ['hungry', 'भूखा (bhookha)'],
                    ['thirsty', 'प्यासा (pyaasa)'],
                ],
                'grammar' => 'Keep practicing Future Simple affirmative: Subject + will + verb (base form) — '
                    .'the verb after \'will\' never changes form, no matter the subject. Say 3 sentences '
                    ."about your meals tomorrow, such as 'I will eat breakfast at 8' or 'We will have dinner "
                    ."together'.",
                'tense' => TenseKey::FutureSimple,
            ],
            17 => [
                'title' => 'Ordering Food at a Restaurant',
                'total_minutes' => 45,
                'read' => "Read this restaurant dialogue aloud: 'This food is delicious! Can I order a small "
                    ."snack too? Please bring the bill, a plate, and a spoon.'",
                'listen' => 'Watch a short video of someone ordering food at a restaurant.',
                'speak' => 'Roleplay ordering a meal at a restaurant with your practice partner as the '
                    .'waiter, then ask for the bill.',
                'write' => 'Write a 5-line restaurant dialogue, from ordering to paying the bill.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['delicious', 'स्वादिष्ट (svaadisht)'],
                    ['snack', 'नाश्ता / स्नैक (snack)'],
                    ['restaurant', 'रेस्टोरेंट (restaurant)'],
                    ['order (food)', 'ऑर्डर करना (order karna)'],
                    ['bill', 'बिल (bill)'],
                    ['plate', 'थाली (thaali)'],
                    ['spoon', 'चम्मच (chammach)'],
                    ['taste', 'स्वाद (svaad)'],
                    ['fresh', 'ताज़ा (taaza)'],
                ],
                'grammar' => "Use Future Simple for offers and promises at a restaurant, like 'I will order "
                    ."the soup' or 'We will pay together.' Roleplay ordering with your practice partner, and "
                    ."use 'will' in at least 3 of your lines.",
                'tense' => TenseKey::FutureSimple,
            ],
            18 => [
                'title' => 'Talking About Feelings',
                'total_minutes' => 48,
                'read' => "Read aloud: 'I feel happy when I finish my practice. I feel a little nervous "
                    ."before speaking, but confident afterwards.'",
                'listen' => 'Watch a short video about people describing how they feel.',
                'speak' => 'Tell your practice partner 3 feelings you had today and why you felt that way.',
                'write' => 'Write 4 sentences about your feelings today, with reasons.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['happy', 'खुश (khush)'],
                    ['sad', 'उदास (udaas)'],
                    ['angry', 'गुस्सा (gussa)'],
                    ['tired', 'थका हुआ (thaka hua)'],
                    ['excited', 'उत्साहित (utsaahit)'],
                    ['nervous', 'घबराया हुआ (ghabraaya hua)'],
                    ['confident', 'आत्मविश्वासी (aatmvishvaasi)'],
                    ['worried', 'चिंतित (chintit)'],
                ],
                'grammar' => "Quick check: is the verb after 'will' always in its base form? Yes — never add "
                    .'-s, -ed, or -ing. Say 3 sentences predicting how you will feel tomorrow, such as \'I '
                    ."will feel confident' or 'I will feel tired'.",
                'tense' => TenseKey::FutureSimple,
            ],
            19 => [
                'title' => 'More Feelings & Small Talk',
                'total_minutes' => 45,
                'read' => "Read aloud: 'I feel comfortable talking in English now. Sometimes I feel confused "
                    ."by a new word, but I stay calm.'",
                'listen' => 'Watch a short small-talk video between two neighbours discussing their week.',
                'speak' => 'Make 2 minutes of small talk with your practice partner: ask how they feel about '
                    .'their week.',
                'write' => 'Write 4 sentences about how you have felt this week.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['comfortable', 'सहज (sahaj)'],
                    ['confused', 'उलझन में (uljhan mein)'],
                    ['proud', 'गर्वित (garvit)'],
                    ['surprised', 'हैरान (hairaan)'],
                    ['bored', 'ऊबा हुआ (ooba hua)'],
                    ['relaxed', 'आराम से (aaraam se)'],
                    ['afraid', 'डरा हुआ (dara hua)'],
                    ['calm', 'शांत (shaant)'],
                ],
                'grammar' => 'Learn the Future Simple negative: Subject + will not/won\'t + verb (base '
                    ."form). Say 3 negative sentences about next week, such as 'I won't feel nervous' or 'I "
                    ."will not be late,' keeping the verb in base form.",
                'tense' => TenseKey::FutureSimple,
            ],
            20 => [
                'title' => 'Small Talk: Sharing How You Feel',
                'total_minutes' => 48,
                'read' => "Read aloud: 'I am grateful for this program. I felt disappointed once, but I "
                    ."stayed motivated. I am curious to learn more.'",
                'listen' => 'Watch a short video of two friends catching up and sharing how they feel about '
                    .'their week.',
                'speak' => 'Have 2 minutes of small talk with your practice partner: share one thing you are '
                    .'grateful for and one thing that frustrated you this week.',
                'write' => 'Write 5 sentences sharing your feelings about this month so far.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['embarrassed', 'शर्मिंदा (sharminda)'],
                    ['grateful', 'आभारी (aabhaari)'],
                    ['hopeful', 'आशावान (aashaavaan)'],
                    ['disappointed', 'निराश (niraash)'],
                    ['curious', 'जिज्ञासु (jigyaasu)'],
                    ['satisfied', 'संतुष्ट (santusht)'],
                    ['frustrated', 'चिढ़ा हुआ (chidha hua)'],
                    ['motivated', 'प्रेरित (prerit)'],
                    ['pleased', 'प्रसन्न (prasann)'],
                ],
                'grammar' => "Practice 'won't' (will not) in small talk: tell your practice partner 2 things "
                    ."you won't do this weekend and why, such as 'I won't work on Sunday because I need to "
                    ."rest.'",
                'tense' => TenseKey::FutureSimple,
            ],
            21 => [
                'title' => 'Week 3 Review — Order Food & Make Small Talk Milestone',
                'total_minutes' => 58,
                'read' => "Re-read all of this week's words aloud.",
                'listen' => 'Watch the Day 17 restaurant video again and notice what feels easier now.',
                'speak' => 'Combine both skills: make 1 minute of small talk with your practice partner, then '
                    .'roleplay ordering a full meal at a restaurant. Your practice partner confirms the Week '
                    .'3 milestone.',
                'write' => 'Write the restaurant conversation and the small talk you just had, in 8 or more '
                    .'lines.',
                'vocab_intro' => 'No new words today — review all Week 3 words aloud.',
                'vocab' => [],
                'grammar' => 'Week 3 grammar review — add the question form: Will + subject + verb (base '
                    ."form)? Ask your practice partner 3 'Will you...?' questions about their plans for next "
                    ."week, then answer using 'will' or 'won't'.",
                'tense' => TenseKey::FutureSimple,
            ],
            22 => [
                'title' => "Yesterday's Actions: Simple Past",
                'total_minutes' => 50,
                'read' => "Read these past-tense sentences aloud: 'Yesterday, I wore my new shirt. I opened "
                    ."the shop early and closed it late. I started work at nine and stopped at six.'",
                'listen' => 'Watch a short video of someone describing what they did yesterday.',
                'speak' => 'Tell your practice partner 5 things you did yesterday, using simple past tense.',
                'write' => 'Write 5 sentences about yesterday, using simple past tense.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['wear', 'पहनना (pehnna)'],
                    ['open', 'खोलना (kholna)'],
                    ['close', 'बंद करना (band karna)'],
                    ['start', 'शुरू करना (shuru karna)'],
                    ['stop', 'रोकना (rokna)'],
                    ['wait', 'इंतज़ार करना (intezaar karna)'],
                    ['help', 'मदद करना (madad karna)'],
                ],
                'grammar' => "Notice the contrast: today's other tasks used Past Simple questions ('Did "
                    ."you...?'); now practice their Future Simple twin, 'Will you...?'. Ask your practice "
                    ."partner 3 questions about tomorrow using 'Will you...?', and answer each with 'will' or "
                    ."'won't'.",
                'tense' => TenseKey::FutureSimple,
            ],
            23 => [
                'title' => 'Wants and Plans',
                'total_minutes' => 50,
                'read' => "Read aloud: 'I want to speak English fluently. I hope to finish this program. I "
                    ."know it will take practice, and I think I can do it.'",
                'listen' => "Watch a short video about someone's hopes and plans for the future.",
                'speak' => 'Tell your practice partner 3 things you want, and 2 things you hope for this '
                    .'month.',
                'write' => 'Write 4 sentences about what you want and hope for.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['need', 'ज़रूरत होना (zaroorat hona)'],
                    ['want', 'चाहना (chaahna)'],
                    ['like', 'पसंद करना (pasand karna)'],
                    ['love', 'प्यार करना (pyaar karna)'],
                    ['hope', 'उम्मीद करना (ummeed karna)'],
                    ['think', 'सोचना (sochna)'],
                    ['know', 'जानना (jaanna)'],
                ],
                'grammar' => "Future Simple pairs naturally with signal words like 'tomorrow', 'next week', "
                    ."'soon', and phrases like 'I think' or 'probably' for predictions. Say 3 sentences about "
                    ."your hopes for next month, using 'will' plus one of these signal words each time.",
                'tense' => TenseKey::FutureSimple,
            ],
            24 => [
                'title' => 'What I Remembered and What I Forgot',
                'total_minutes' => 45,
                'read' => "Read aloud: 'I remembered to bring my notebook, but I forgot my pen. I tried "
                    ."again and finally understood the new word.'",
                'listen' => 'Watch a short video about someone remembering and forgetting everyday things.',
                'speak' => 'Tell your practice partner one thing you remembered today and one thing you '
                    .'forgot this week.',
                'write' => "Write 4 sentences using 'remember', 'forget', 'try', 'finish', 'continue', or "
                    ."'understand'.",
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['understand', 'समझना (samajhna)'],
                    ['remember', 'याद रखना (yaad rakhna)'],
                    ['forget', 'भूलना (bhoolna)'],
                    ['try', 'कोशिश करना (koshish karna)'],
                    ['finish', 'खत्म करना (khatam karna)'],
                    ['continue', 'जारी रखना (jaari rakhna)'],
                ],
                'grammar' => "Use 'will' for a decision made right at the moment of speaking, not planned "
                    ."earlier — like 'I forgot my pen, so I will borrow one now.' Make 2 sentences about a "
                    ."small decision you are making right now, using 'will'.",
                'tense' => TenseKey::FutureSimple,
            ],
            25 => [
                'title' => 'Explaining Why: Because & So',
                'total_minutes' => 48,
                'read' => "Read aloud: 'I lost my keys, so I was late. I found them because I checked my bag "
                    ."again. I need to fix this habit and improve.'",
                'listen' => "Watch a short clip of someone explaining a problem using 'because' and 'so'.",
                'speak' => 'Tell your practice partner about something you lost or fixed recently, using '
                    ."'because' or 'so' to explain why.",
                'write' => "Write 4 sentences, each using 'because' or 'so'.",
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['find', 'ढूँढना (dhoondna)'],
                    ['lose', 'खोना (khona)'],
                    ['fix', 'ठीक करना (theek karna)'],
                    ['change', 'बदलना (badalna)'],
                    ['improve', 'सुधारना (sudhaarna)'],
                    ['because', 'क्योंकि (kyonki)'],
                    ['so', 'इसलिए (isliye)'],
                ],
                'grammar' => "Combine Future Simple with 'because' or 'so' to explain a promise: 'I will fix "
                    ."this habit because it slows me down' or 'I lost my keys, so I will buy a spare set.' "
                    ."Write 2 promise sentences of your own using 'will' with 'because' or 'so'.",
                'tense' => TenseKey::FutureSimple,
            ],
            26 => [
                'title' => 'Talking About Future Possibilities',
                'total_minutes' => 48,
                'read' => "Read aloud: 'If it rains tomorrow, I will stay home. When I finish this course, I "
                    ."will feel proud. I will keep practising until I am fluent.'",
                'listen' => 'Watch a short video of someone talking about future plans and possibilities.',
                'speak' => "Tell your practice partner 3 future plans, using 'if', 'when', or 'until' in "
                    .'each one.',
                'write' => "Write 4 sentences about future possibilities, using 'if', 'when', 'until', "
                    ."'since', or 'although'.",
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['if', 'अगर (agar)'],
                    ['when', 'जब (jab)'],
                    ['until', 'जब तक (jab tak)'],
                    ['since', 'जबसे (jabse)'],
                    ['although', 'हालाँकि (haalaanki)'],
                    ['for example', 'जैसे कि (jaise ki)'],
                ],
                'grammar' => "Important rule: after 'if' or 'when', use Present Simple, not 'will' — the "
                    ."'will' goes only in the main result clause, as in 'If it rains, I will stay home' (not "
                    ."'If it will rain'). Check today's Reading sentences and notice that 'will' appears only "
                    .'once per sentence, never in the if/when part.',
                'tense' => TenseKey::FutureSimple,
            ],
            27 => [
                'title' => 'Comparing Then and Now',
                'total_minutes' => 45,
                'read' => "Read aloud: 'A month ago, I could not say much. Now I can say much more. My "
                    ."speaking is not the same as before — every day I improve a little.'",
                'listen' => "Watch a short video comparing someone's skills before and after a month of "
                    .'practice.',
                'speak' => "Compare yourself now with yourself one month ago, using 'more', 'same', "
                    ."'different', 'instead', or 'both' at least twice.",
                'write' => 'Write 5 sentences comparing your English one month ago with your English today.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['like (comparison)', 'जैसे (jaise)'],
                    ['instead', 'इसकी जगह (iski jagah)'],
                    ['either', 'या तो (ya to)'],
                    ['both', 'दोनों (dono)'],
                    ['each', 'हर एक (har ek)'],
                    ['every', 'हर (har)'],
                    ['another', 'एक और (ek aur)'],
                    ['same', 'वही (vahi)'],
                    ['extra', 'अतिरिक्त (atirikt)'],
                    ['more', 'ज़्यादा (zyada)'],
                ],
                'grammar' => "Common mistake: using 'will' for a plan you already decided before now — say "
                    ."'I am going to visit my parents next week' for an already-decided plan, not 'I will "
                    ."visit my parents next week'. Correct this aloud: 'I will meet him tomorrow at 5, I "
                    ."decided yesterday' should be 'I am going to meet him tomorrow at 5'.",
                'tense' => TenseKey::FutureSimple,
            ],
            28 => [
                'title' => 'Full Mock Conversation Rehearsal',
                'total_minutes' => 53,
                'read' => 'Read a complete sample 3-minute conversation transcript aloud — routine, '
                    .'shopping, food, and a future plan.',
                'listen' => 'Listen to the sample conversation audio twice.',
                'speak' => 'Rehearse the full conversation with your practice partner, aiming for natural '
                    .'flow, not a memorised script.',
                'write' => "Write your personal '10 weak words or phrases' list from Month 2 in your "
                    .'notebook.',
                'vocab_intro' => 'No new words today — look back through Days 1-27 and pick your personal 10 '
                    .'hardest words to review.',
                'vocab' => [],
                'grammar' => "Before you rehearse, quickly review your Future Simple checklist: 'will' + "
                    ."base verb (never 'will to'), 'won't' for negatives, and 'Will you...?' for questions. As "
                    .'you rehearse the future-plan part of the conversation, listen for at least 2 correct '
                    ."'will' sentences.",
                'tense' => TenseKey::FutureSimple,
            ],
            29 => [
                'title' => 'Record & Review',
                'total_minutes' => 48,
                'read' => "Instead of reading something new, record yourself reading yesterday's writing "
                    .'aloud.',
                'listen' => 'Play back your Day 28 recording and listen for clarity and past/future tense '
                    .'accuracy.',
                'speak' => 'Repeat the full conversation, recorded on your phone, focusing on smooth past and '
                    .'future tense forms.',
                'write' => 'Write one thing that has improved since the start of Month 2.',
                'vocab_intro' => "No new words today — review all Week 4 words once more before tomorrow's "
                    .'milestone conversation.',
                'vocab' => [],
                'grammar' => 'Listen back to your Day 28 recording and find one Future Simple sentence you '
                    ."could improve — maybe 'will' was missing, or the verb after it wasn't in base form. Say "
                    .'the corrected sentence aloud 3 times.',
                'tense' => TenseKey::FutureSimple,
            ],
            30 => [
                'title' => 'Month 2 Milestone — Everyday Fluency Conversation',
                'minutes' => [
                    'reading' => 0,
                    'vocabulary' => 3,
                    'listening' => 0,
                    'speaking' => 40,
                    'writing' => 15,
                    'grammar' => 0,
                ],
                'read' => 'No reading exercise today — this is a performance day.',
                'listen' => 'No listening exercise today — this is a performance day.',
                'speak' => 'Hold an unscripted 3-minute conversation with your practice partner that '
                    .'includes your daily routine, a shopping moment, a food preference, and one future plan. '
                    .'This is the official Month 2 milestone — score it against the Month 2 Test rubric.',
                'write' => "Write a short reflection: 'What I can now say about my daily life, shopping, "
                    ."food, and plans that I could not say a month ago.'",
                'vocab_intro' => 'Month 2 vocabulary is complete — 300 out of 500 words introduced. No new '
                    .'words today; this is a milestone day.',
                'vocab' => [],
                'grammar' => 'Closing review: in your milestone conversation, mix both tenses naturally — '
                    ."Past Simple for what already happened ('I bought vegetables yesterday') and Future "
                    ."Simple for what comes next ('I will practice speaking every week').",
                'tense' => TenseKey::FutureSimple,
            ],
        ];
    }
}
