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

class Month3DaySeeder extends Seeder
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
        $month = Month::where('month_number', 3)->first();

        if (! $month) {
            throw new RuntimeException('Month 3 not found — MonthSeeder must run before Month3DaySeeder.');
        }

        $weeks = Week::where('month_id', $month->id)->orderBy('week_number')->get();

        if ($weeks->count() !== 4) {
            throw new RuntimeException('Expected exactly 4 weeks for Month 3 before seeding days.');
        }

        // day_number is globally unique across the whole program (1-180), so
        // local day 1-30 within this month must be offset onto the global
        // range that MonthSeeder already assigned to Month 3's weeks.
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

        $this->assertMonth3Integrity($month);
    }

    /**
     * Assertion: exactly 30 Day rows and 180 DayTask rows (30 x 6) exist for
     * Month 3, and every day has exactly one task per SkillArea.
     */
    private function assertMonth3Integrity(Month $month): void
    {
        $dayCount = Day::where('month_id', $month->id)->count();
        if ($dayCount !== 30) {
            throw new RuntimeException("Expected 30 Day rows for Month 3, found {$dayCount}.");
        }

        $dayIds = Day::where('month_id', $month->id)->pluck('id');
        $dayTaskCount = DayTask::whereIn('day_id', $dayIds)->count();
        if ($dayTaskCount !== 180) {
            throw new RuntimeException("Expected 180 DayTask rows for Month 3, found {$dayTaskCount}.");
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
     * Month 3 — full day-by-day content (local days 1-30, offset to global
     * days 61-90). Vocabulary terms are drawn from Word Bank Groups 13-17
     * (Health & Body, Technology & Phone, Weather & Seasons, Making Plans &
     * Invitations, Travel & Commute), with Hindi glosses matching
     * WordBankSeeder exactly.
     *
     * @return array<int, array<string, mixed>>
     */
    private function days(): array
    {
        return [
            1 => [
                'title' => "Talking About Today's Weather",
                'total_minutes' => 45,
                'read' => "Read this small-talk exchange aloud: 'The weather is nice today.' 'Yes, it is very "
                    ."hot, though.' 'I hope it doesn't rain later.'",
                'listen' => 'Watch a short video of two people making small talk about the weather.',
                'speak' => "Tell your practice partner about today's weather — hot, cold, rain, or sun — and "
                    .'ask them the same question.',
                'write' => "Write 4 sentences about today's weather.",
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['weather', 'मौसम (mausam)'],
                    ['hot (weather)', 'गरम (garam)'],
                    ['cold (weather)', 'ठंडा (thanda)'],
                    ['rain', 'बारिश (baarish)'],
                    ['sun', 'धूप (dhoop)'],
                ],
                'grammar' => 'New tense: Past Continuous. Structure: subject + was/were + verb-ing, for an '
                    .'action in progress at a specific past moment. Drill: say 2 sentences about what the '
                    ."weather was doing at a specific time yesterday, e.g. 'At 6 a.m., it was raining.'",
                'tense' => TenseKey::PastContinuous,
            ],
            2 => [
                'title' => 'Seasons of the Year',
                'total_minutes' => 45,
                'read' => "Read aloud: 'I like summer more than winter. The monsoon brings a lot of rain and "
                    ."wind. Clouds cover the sky before the rain starts.'",
                'listen' => 'Watch a short video about the four seasons.',
                'speak' => 'Tell your practice partner your favourite season and why.',
                'write' => 'Write 4 sentences about seasons.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['cloud', 'बादल (baadal)'],
                    ['wind', 'हवा (hawa)'],
                    ['summer', 'गर्मी (garmi)'],
                    ['winter', 'सर्दी (sardi)'],
                    ['monsoon', 'मानसून (monsoon)'],
                ],
                'grammar' => 'Keep practising Past Continuous affirmative sentences (was/were + verb-ing) '
                    .'with plural subjects. Drill: describe what was happening last monsoon season — say '
                    ."'The wind was blowing' or 'The clouds were covering the sky.'",
                'tense' => TenseKey::PastContinuous,
            ],
            3 => [
                'title' => 'Extreme Weather Small Talk',
                'total_minutes' => 48,
                'read' => "Read aloud: 'It is very humid today. Check the temperature before you go out. Take "
                    ."an umbrella — there might be a storm.'",
                'listen' => 'Watch a short video about a storm or a heavy-rain warning.',
                'speak' => 'Tell your practice partner about the most extreme weather — a flood, a storm, or '
                    .'extreme heat — you remember.',
                'write' => 'Write 4 sentences about extreme weather you have experienced.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['humid', 'उमस भरा (umas bhara)'],
                    ['temperature', 'तापमान (taapmaan)'],
                    ['umbrella', 'छाता (chhata)'],
                    ['flood', 'बाढ़ (baadh)'],
                    ['storm', 'तूफ़ान (toofaan)'],
                ],
                'grammar' => "Past Continuous can set the scene for a story: use 'while' to link two things "
                    .'happening at the same past moment. Drill: write 1 sentence with '
                    ."'while' about extreme weather, e.g. 'While the storm was approaching, we were closing "
                    ."the windows.'",
                'tense' => TenseKey::PastContinuous,
            ],
            4 => [
                'title' => 'Describing the Sky and Ground',
                'total_minutes' => 45,
                'read' => "Read aloud: 'It is a sunny day today, but yesterday was cloudy. This season is "
                    ."very dry; last month everything was wet.'",
                'listen' => 'Watch a short video comparing a sunny day and a rainy day.',
                'speak' => 'Describe the sky and the ground right now to your practice partner.',
                'write' => "Write 4 sentences describing today's sky and ground.",
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['sunny', 'धूप वाला (dhoop waala)'],
                    ['cloudy', 'बादल छाए हुए (baadal chhaaye hue)'],
                    ['season', 'मौसम (mausam)'],
                    ['dry', 'सूखा (sookha)'],
                    ['wet', 'गीला (geela)'],
                ],
                'grammar' => 'New form: Past Continuous negative = was/were + not + verb-ing. Drill: say 2 '
                    ."negative sentences about yesterday's sky, e.g. 'It wasn't raining at noon' or 'The sun "
                    ."wasn't shining in the morning.'",
                'tense' => TenseKey::PastContinuous,
            ],
            5 => [
                'title' => 'Weather Forecasts & Small Talk with Neighbours',
                'total_minutes' => 48,
                'read' => "Read aloud: 'The forecast says fog this morning and a pleasant afternoon. What is "
                    ."the climate like in your city? Today it is twenty-five degrees.'",
                'listen' => 'Watch a short video of a weather forecast report.',
                'speak' => 'Roleplay greeting a neighbour and making small talk about the weather forecast '
                    .'for tomorrow.',
                'write' => "Write 4 sentences about tomorrow's weather forecast.",
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['fog', 'कोहरा (kohra)'],
                    ['climate', 'जलवायु (jalvaayu)'],
                    ['forecast (weather)', 'मौसम का पूर्वानुमान (mausam ka poorvaanumaan)'],
                    ['degree (temperature)', 'डिग्री (degree)'],
                    ['pleasant', 'सुहावना (suhaavna)'],
                ],
                'grammar' => 'New form: Past Continuous question = Was/Were + subject + verb-ing? Drill: ask '
                    .'your practice partner 2 questions about last night, e.g. '
                    ."'Were you watching the forecast at 9 p.m.?'",
                'tense' => TenseKey::PastContinuous,
            ],
            6 => [
                'title' => 'Making Small Talk With Someone New',
                'total_minutes' => 45,
                'read' => "Read these small-talk phrases aloud: 'Nice weather today, isn't it?' 'How's it "
                    ."going?' 'Long time no see!' 'Have a good one.' 'Take it easy.'",
                'listen' => 'Watch a short video of two strangers making small talk while waiting somewhere.',
                'speak' => 'Your practice partner plays a stranger; greet them, comment on the weather, and '
                    .'make 1 minute of small talk.',
                'write' => 'Write a short 5-line small-talk dialogue with someone new.',
                'vocab_intro' => "Learn today's phrases: say each one aloud three times, then use at least "
                    .'one in a conversation with your practice partner.',
                'vocab' => [
                    ["Nice weather, isn't it?", 'क्या मौसम अच्छा नहीं है? (kya mausam achha nahin hai?)'],
                    ["How's it going?", 'कैसा चल रहा है? (kaisa chal raha hai?)'],
                    ['Long time no see', 'बहुत दिनों बाद मिले (bahut dino baad mile)'],
                    ['Have a good one', 'दिन शुभ रहे (din shubh rahe)'],
                    ['Take it easy', 'आराम से लो (aaraam se lo)'],
                ],
                'grammar' => 'Practise all 3 Past Continuous forms together. Drill: ask your practice '
                    ."partner 'What were you doing when we met?' and answer using was/were + verb-ing.",
                'tense' => TenseKey::PastContinuous,
            ],
            7 => [
                'title' => 'Week 1 Review — Small Talk Milestone',
                'total_minutes' => 58,
                'read' => "Re-read all of this week's words aloud.",
                'listen' => 'Watch the Day 1 weather video again and notice what feels easier now.',
                'speak' => 'Greet a neighbour (your practice partner), introduce yourself as if meeting for '
                    .'the first time, and make 2 minutes of small talk about the weather. Your practice '
                    .'partner confirms the Week 1 milestone.',
                'write' => 'Write the small-talk conversation you just had, in 6-8 lines.',
                'vocab_intro' => 'No new words this week — re-read all 30 Week 1 words and phrases aloud once, '
                    .'at normal speed, and notice which ones are now easy.',
                'vocab' => [],
                'grammar' => 'Week 1 Grammar review: mix affirmative, negative, and question forms of Past '
                    .'Continuous. Drill: describe what you were doing at three different times yesterday, '
                    .'using at least one negative sentence.',
                'tense' => TenseKey::PastContinuous,
            ],
            8 => [
                'title' => 'Talking About Your Phone',
                'total_minutes' => 45,
                'read' => "Read aloud: 'My mobile phone has many apps. The wifi is slow, and the network is "
                    ."weak here.'",
                'listen' => 'Watch a short video about common phone problems.',
                'speak' => 'Tell your practice partner about your phone — one app you use daily and one '
                    .'network problem you have had.',
                'write' => 'Write 4 sentences about your phone.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['mobile phone', 'मोबाइल फ़ोन (mobile phone)'],
                    ['app', 'ऐप (app)'],
                    ['wifi', 'वाईफ़ाई (wifi)'],
                    ['network', 'नेटवर्क (network)'],
                ],
                'grammar' => 'Past Continuous often describes a phone call already in progress. Drill: '
                    ."complete 'I ___ (talk) on the phone when the call dropped' with was talking, then say "
                    .'it aloud.',
                'tense' => TenseKey::PastContinuous,
            ],
            9 => [
                'title' => 'Everyday Phone Actions',
                'total_minutes' => 48,
                'read' => "Read aloud: 'My battery is low; can I borrow your charger? I will send you a "
                    ."message, then give you a call.'",
                'listen' => 'Watch a short video of someone asking to borrow a charger.',
                'speak' => 'Roleplay borrowing a charger from your practice partner and arranging to call '
                    .'later.',
                'write' => 'Write 4 sentences using battery, charger, message, or call.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['battery', 'बैटरी (battery)'],
                    ['charger', 'चार्जर (charger)'],
                    ['message (text)', 'मैसेज (message)'],
                    ['call', 'कॉल (call)'],
                ],
                'grammar' => 'Usage rule: put the longer background action in Past Continuous and the '
                    ."shorter interrupting action in Past Simple, linked by 'when'. Drill: say 'My phone was "
                    ."charging when the power went out' and make 1 more example.",
                'tense' => TenseKey::PastContinuous,
            ],
            10 => [
                'title' => 'Calls & Social Media',
                'total_minutes' => 45,
                'read' => "Read aloud: 'You have a missed call from me. Let's do a video call tonight. I saw "
                    ."your photo on social media.'",
                'listen' => 'Watch a short video of someone setting up a video call.',
                'speak' => 'Arrange a video call time with your practice partner and mention something you '
                    .'saw on social media.',
                'write' => 'Write 4 sentences about calls or social media.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['missed call', 'मिस्ड कॉल (missed call)'],
                    ['video call', 'वीडियो कॉल (video call)'],
                    ['screenshot', 'स्क्रीनशॉट (screenshot)'],
                    ['social media', 'सोशल मीडिया (social media)'],
                ],
                'grammar' => "Usage rule: use 'while' for two actions happening at the same past moment, "
                    ."both in Past Continuous. Drill: say 'While I was calling you, you were messaging me' "
                    .'and make 1 more example about calls or social media.',
                'tense' => TenseKey::PastContinuous,
            ],
            11 => [
                'title' => 'Phone Settings & Photos',
                'total_minutes' => 48,
                'read' => "Read aloud: 'Take a nice photo here. Save this number as a contact. Go to your "
                    ."phone's settings to update the app.'",
                'listen' => 'Watch a short video about saving a contact and updating an app.',
                'speak' => 'Explain to your practice partner how you save a new contact on your phone.',
                'write' => 'Write 4 sentences about photos, contacts, or settings.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['photo', 'फ़ोटो (photo)'],
                    ['contact', 'कॉन्टैक्ट (contact)'],
                    ['settings', 'सेटिंग्स (settings)'],
                    ['update (app)', 'अपडेट करना (update karna)'],
                ],
                'grammar' => 'Usage rule: state verbs (know, want, believe, own) never take -ing, even in '
                    ."the past. Drill: correct this mistake — 'I was knowing his number' should be 'I knew "
                    ."his number.' Then say 1 more state-verb sentence in Past Simple.",
                'tense' => TenseKey::PastContinuous,
            ],
            12 => [
                'title' => 'Managing Your Phone',
                'total_minutes' => 45,
                'read' => "Read aloud: 'Install this app first. Always lock your phone. Turn down the volume "
                    ."— my storage is also full.'",
                'listen' => 'Watch a short video about installing an app and managing storage.',
                'speak' => 'Tell your practice partner about one app you installed recently and one storage '
                    .'problem you have had.',
                'write' => 'Write 4 sentences about installing, locking, or storage.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['install', 'इंस्टॉल करना (install karna)'],
                    ['lock (phone)', 'लॉक करना (lock karna)'],
                    ['volume', 'आवाज़ (aawaaz)'],
                    ['storage', 'स्टोरेज (storage)'],
                ],
                'grammar' => 'Signal words for Past Continuous: while, when, at that moment, all day '
                    ."yesterday, all morning. Drill: say 1 sentence with 'all evening yesterday', e.g. 'My "
                    ."phone was downloading updates all evening yesterday.'",
                'tense' => TenseKey::PastContinuous,
            ],
            13 => [
                'title' => 'Online & Notifications',
                'total_minutes' => 50,
                'read' => "Read aloud: 'Click on this link. Search for it online. Check the website for "
                    ."details. I got a notification about my online account.'",
                'listen' => 'Watch a short video about searching for information online.',
                'speak' => 'Tell your practice partner how you would search online for something you need, '
                    .'step by step.',
                'write' => 'Write 5 sentences about searching online or notifications.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['link', 'लिंक (link)'],
                    ['search (internet)', 'सर्च करना (search karna)'],
                    ['website', 'वेबसाइट (website)'],
                    ['notification', 'नोटिफ़िकेशन (notification)'],
                    ['online account', 'ऑनलाइन अकाउंट (online account)'],
                ],
                'grammar' => "Mistake correction: 'The notification was arrive while I searched online' is "
                    ."wrong. Correct form: 'The notification arrived while I was searching online' — the "
                    .'shorter action takes Past Simple, the longer one takes Past Continuous. Drill: fix 1 '
                    .'more mixed-up sentence of your own.',
                'tense' => TenseKey::PastContinuous,
            ],
            14 => [
                'title' => 'Week 2 Review — Answer a Phone Call Milestone',
                'total_minutes' => 58,
                'read' => "Re-read all of this week's words aloud.",
                'listen' => 'Watch the Day 9 phone-charger video again and notice what feels easier now.',
                'speak' => 'Your practice partner calls you. Answer clearly, spell your name if asked, and '
                    .'end the call politely. Your practice partner confirms the Week 2 milestone.',
                'write' => 'Write the phone call you just had, from memory, in 8 or more lines.',
                'vocab_intro' => 'No new words today — review all Week 2 words aloud and notice which ones '
                    .'feel easier now.',
                'vocab' => [],
                'grammar' => 'Week 2 Grammar review: before your phone-call milestone, practise describing '
                    ."an interruption — 'I was doing X when the phone rang.' Drill: say 2 such sentences "
                    .'using your own recent phone experiences.',
                'tense' => TenseKey::PastContinuous,
            ],
            15 => [
                'title' => 'Talking About Your Body',
                'total_minutes' => 45,
                'read' => "Read aloud: 'Exercise keeps the body fit. Wash your hands before eating. My head "
                    ."hurts a little today.'",
                'listen' => 'Watch a short video about parts of the body.',
                'speak' => 'Point to and name 5 parts of your body to your practice partner.',
                'write' => 'Write 4 sentences using body, head, hand, or leg.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['health', 'स्वास्थ्य (swasthya)'],
                    ['body', 'शरीर (shareer)'],
                    ['head', 'सिर (sir)'],
                    ['hand', 'हाथ (haath)'],
                ],
                'grammar' => 'New tense: Present Perfect. Structure: subject + have/has + past participle, '
                    .'connecting a past action to the present. Drill: say 2 sentences about your body or '
                    ."health right now, e.g. 'I have had a headache since this morning.'",
                'tense' => TenseKey::PresentPerfect,
            ],
            16 => [
                'title' => 'Describing Aches and Pains',
                'total_minutes' => 48,
                'read' => "Read aloud: 'He hurt his leg while running. She has beautiful eyes. I can't hear "
                    ."from this ear. My stomach is upset today.'",
                'listen' => 'Watch a short video of someone describing a minor injury.',
                'speak' => 'Tell your practice partner about a small ache or pain you have had recently.',
                'write' => 'Write 4 sentences about aches or pains.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['leg', 'पैर (pair)'],
                    ['eye', 'आँख (aankh)'],
                    ['ear', 'कान (kaan)'],
                    ['stomach', 'पेट (pet)'],
                ],
                'grammar' => 'Keep practising Present Perfect affirmative sentences with irregular past '
                    .'participles (hurt, had, felt). Drill: say 2 sentences about a recent ache, e.g. '
                    ."'She has hurt her leg' or 'I have had a backache all week.'",
                'tense' => TenseKey::PresentPerfect,
            ],
            17 => [
                'title' => 'Describing Symptoms',
                'total_minutes' => 45,
                'read' => "Read aloud: 'He has a mild fever. I have a cough since yesterday. She has a bad "
                    ."cold. I have pain in my back.'",
                'listen' => 'Watch a short video of a patient describing symptoms to a doctor.',
                'speak' => 'Roleplay describing 3 symptoms to your practice partner as the doctor.',
                'write' => 'Write 4 sentences describing symptoms.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['fever', 'बुख़ार (bukhaar)'],
                    ['cough', 'खांसी (khaansi)'],
                    ['cold (illness)', 'ज़ुकाम (zukaam)'],
                    ['pain', 'दर्द (dard)'],
                ],
                'grammar' => "Usage rule: use 'since' + a starting point or 'for' + a length of time with "
                    .'Present Perfect to show something that began in the past and is still true now. '
                    ."Drill: say 'I have had a cough since yesterday' and 1 more sentence using 'for'.",
                'tense' => TenseKey::PresentPerfect,
            ],
            18 => [
                'title' => 'Seeing a Doctor',
                'total_minutes' => 48,
                'read' => "Read aloud: 'Take this medicine after food. I need to see a doctor. The clinic "
                    ."opens at nine. He was taken to the hospital.'",
                'listen' => "Watch a short video of a short visit to a doctor's clinic.",
                'speak' => "Roleplay a doctor's visit — your practice partner is the doctor. Describe your "
                    .'problem and get advice.',
                'write' => "Write the doctor's visit dialogue, 5 lines.",
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['medicine', 'दवा (dawaa)'],
                    ['doctor', 'डॉक्टर (doctor)'],
                    ['clinic', 'क्लीनिक (clinic)'],
                    ['hospital', 'अस्पताल (aspataal)'],
                ],
                'grammar' => 'New form: Present Perfect negative = have/has + not + past participle, often '
                    ."used with 'yet' for something not done. Drill: say 'I haven't taken my medicine yet' "
                    .'and 1 more negative sentence about your health.',
                'tense' => TenseKey::PresentPerfect,
            ],
            19 => [
                'title' => 'Staying Healthy',
                'total_minutes' => 45,
                'read' => "Read aloud: 'I have a doctor's appointment today. You need some rest. I do "
                    ."exercise every morning. Eat healthy food daily.'",
                'listen' => 'Watch a short video about staying healthy every day.',
                'speak' => 'Tell your practice partner 3 things you do to stay healthy.',
                'write' => 'Write 4 sentences about staying healthy.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['appointment', 'अपॉइंटमेंट (appointment)'],
                    ['rest', 'आराम (aaraam)'],
                    ['exercise', 'व्यायाम (vyaayaam)'],
                    ['healthy', 'स्वस्थ (swasth)'],
                ],
                'grammar' => 'New form: Present Perfect question = Have/Has + subject + past participle? '
                    ."Drill: ask your practice partner 'Have you done your exercise today?' and 1 more "
                    .'question using this pattern.',
                'tense' => TenseKey::PresentPerfect,
            ],
            20 => [
                'title' => 'A Health Checkup',
                'total_minutes' => 48,
                'read' => "Read aloud: 'She is feeling sick today. Take one tablet twice a day. I have a "
                    ."health checkup tomorrow. He has an allergy to dust. I feel full of energy today.'",
                'listen' => 'Watch a short video of a routine health checkup.',
                'speak' => 'Roleplay a full short doctor visit: describe a symptom, listen to simple advice, '
                    .'and repeat it back to confirm you understood.',
                'write' => 'Write the checkup conversation, 6-8 lines.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['sick', 'बीमार (beemaar)'],
                    ['tablet (medicine)', 'गोली (goli)'],
                    ['checkup', 'जाँच (jaanch)'],
                    ['allergy', 'एलर्जी (allergy)'],
                    ['energy', 'ऊर्जा (oorja)'],
                ],
                'grammar' => 'Signal words for Present Perfect: already, yet, just, ever, never, since, for, '
                    ."so far, recently, lately. Drill: say 3 sentences from today's checkup roleplay, each "
                    .'using a different signal word.',
                'tense' => TenseKey::PresentPerfect,
            ],
            21 => [
                'title' => 'Week 3 Review — Describe a Symptom & Understand Advice Milestone',
                'total_minutes' => 58,
                'read' => "Re-read all of this week's words aloud.",
                'listen' => 'Watch the Day 18 doctor-visit video again and notice what feels easier now.',
                'speak' => 'Describe a symptom to your practice partner (the doctor), listen to their advice, '
                    .'and repeat the 3-step advice back correctly. Your practice partner confirms the Week 3 '
                    .'milestone.',
                'write' => 'Write the full doctor conversation, from memory, in 8 or more lines.',
                'vocab_intro' => 'No new words today — review all Week 3 words aloud.',
                'vocab' => [],
                'grammar' => 'Week 3 Grammar review: mix affirmative, negative, and question forms of '
                    .'Present Perfect before your doctor-roleplay milestone. Drill: describe your symptom '
                    .'and the doctor’s advice using at least 2 Present Perfect sentences.',
                'tense' => TenseKey::PresentPerfect,
            ],
            22 => [
                'title' => 'Making a Plan',
                'total_minutes' => 50,
                'read' => "Read aloud: 'What is your plan for the weekend? I want to invite you to a "
                    ."get-together. Are you free, or are you busy?'",
                'listen' => 'Watch a short video of friends making weekend plans.',
                'speak' => 'Tell your practice partner your plan for the weekend and ask if they are free or '
                    .'busy.',
                'write' => 'Write 5 sentences about a plan you have.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['plan (noun)', 'योजना (yojana)'],
                    ['invite', 'आमंत्रित करना (aamantrit karna)'],
                    ['invitation', 'निमंत्रण (nimantran)'],
                    ['free (available)', 'खाली (khaali)'],
                    ['busy', 'व्यस्त (vyast)'],
                    ['meet up', 'मिलना (milna)'],
                    ['join', 'शामिल होना (shaamil hona)'],
                    ['celebrate', 'जश्न मनाना (jashn manaana)'],
                ],
                'grammar' => 'Usage rule: use Present Perfect for life experiences when the exact time '
                    ."doesn't matter, often with 'ever' or 'never'. Drill: ask 'Have you ever been to that "
                    ."place?' and answer 'I have never been there — let's go this weekend!'",
                'tense' => TenseKey::PresentPerfect,
            ],
            23 => [
                'title' => 'Invitations & Opinions',
                'total_minutes' => 50,
                'read' => "Read aloud: 'Thank you for the invitation to the party. She is a wonderful host. "
                    ."In my opinion, we should meet earlier. I have a suggestion.'",
                'listen' => 'Watch a short video of someone being invited to a party and sharing an opinion '
                    .'about the plan.',
                'speak' => 'Invite your practice partner to an event, and ask their opinion about the time.',
                'write' => 'Write 4 sentences about an invitation or an opinion.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['party', 'पार्टी (party)'],
                    ['host', 'मेज़बान (mezbaan)'],
                    ['accept (invitation)', 'स्वीकार करना (sveekaar karna)'],
                    ['decline (politely)', 'मना करना (mana karna)'],
                    ['agree', 'सहमत होना (sehmat hona)'],
                    ['disagree', 'असहमत होना (asehmat hona)'],
                    ['suggestion', 'सुझाव (sujhaav)'],
                    ['opinion', 'राय (raay)'],
                ],
                'grammar' => "Usage rule: use 'just', 'already', or 'yet' with Present Perfect for recently "
                    ."completed actions. Drill: say 'I have just replied to your invitation' and 1 more "
                    .'sentence about accepting or declining a plan.',
                'tense' => TenseKey::PresentPerfect,
            ],
            24 => [
                'title' => 'Confirming, Postponing & Celebrating Plans',
                'total_minutes' => 45,
                'read' => "Read aloud: 'Let's decide the time together. Can we postpone it to Sunday? Please "
                    ."confirm by tomorrow. It's a special occasion, so let's get together and celebrate.'",
                'listen' => 'Watch a short video of two people rescheduling a plan.',
                'speak' => 'Roleplay changing a plan with your practice partner: postpone or reschedule an '
                    .'event, then confirm the new time.',
                'write' => 'Write a 5-line dialogue about changing a plan.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['decide', 'फ़ैसला करना (faisla karna)'],
                    ['postpone', 'स्थगित करना (sthagit karna)'],
                    ['reschedule', 'समय बदलना (samay badalna)'],
                    ['remind', 'याद दिलाना (yaad dilaana)'],
                    ['confirm', 'पुष्टि करना (pushti karna)'],
                    ['get together', 'इकट्ठा होना (ikattha hona)'],
                    ['occasion', 'अवसर (avsar)'],
                    ['surprise', 'सरप्राइज़ (surprise)'],
                    ['catch up', 'बातें करना (baaten karna)'],
                ],
                'grammar' => "Key rule: don't use Present Perfect with a specific finished time word like "
                    ."'yesterday' — use Past Simple instead. Drill: correct the mistake 'I have confirmed it "
                    ."yesterday' to 'I confirmed it yesterday,' then make 1 more pair of your own.",
                'tense' => TenseKey::PresentPerfect,
            ],
            25 => [
                'title' => 'Getting Around Town',
                'total_minutes' => 48,
                'read' => "Read aloud: 'I travel by bus to work. I take the train to the city. I took an "
                    ."auto to the market. Meet me at the station.'",
                'listen' => 'Watch a short video about different ways people travel to work.',
                'speak' => 'Tell your practice partner how you usually travel around your town or city.',
                'write' => 'Write 4 sentences about how you travel.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['travel', 'यात्रा (yatra)'],
                    ['bus', 'बस (bus)'],
                    ['train', 'ट्रेन (train)'],
                    ['auto/rickshaw', 'ऑटो (auto)'],
                    ['station', 'स्टेशन (station)'],
                    ['ticket', 'टिकट (ticket)'],
                    ['route', 'रास्ता (raasta)'],
                    ['traffic', 'ट्रैफ़िक (traffic)'],
                ],
                'grammar' => "Contrast practice: 'I have traveled by train many times' (experience, no time "
                    ."given) versus 'I traveled by train last week' (Past Simple, specific time). Drill: say "
                    .'1 sentence of each type about your own travel.',
                'tense' => TenseKey::PresentPerfect,
            ],
            26 => [
                'title' => 'Asking for and Giving Directions',
                'total_minutes' => 48,
                'read' => "Read aloud: 'The market is near my house, but the station is a bit far. Turn left "
                    ."at the signal, then go straight. The bank is on the right.'",
                'listen' => 'Watch a short video of someone asking for and receiving directions.',
                'speak' => 'Give your practice partner directions from your home to a nearby place, using '
                    .'left, right, straight, near, and far.',
                'write' => 'Write 5 direction sentences to a place you know well.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['distance', 'दूरी (dooree)'],
                    ['near', 'पास (paas)'],
                    ['far', 'दूर (door)'],
                    ['direction', 'दिशा (disha)'],
                    ['left', 'बाएँ (baayen)'],
                    ['right', 'दाएँ (daayen)'],
                    ['straight', 'सीधे (seedhe)'],
                    ['arrive', 'पहुँचना (pahunchna)'],
                ],
                'grammar' => "Mistake correction: 'I have went to the market yesterday' is wrong on two "
                    .'counts — the wrong participle, and a specific time word used with Present Perfect. '
                    ."Correct form: 'I went to the market yesterday.' Drill: fix 1 more sentence that mixes "
                    .'Present Perfect with a time word.',
                'tense' => TenseKey::PresentPerfect,
            ],
            27 => [
                'title' => 'Commuting & Timing',
                'total_minutes' => 45,
                'read' => "Read aloud: 'I leave home at eight and arrive at nine. There is a slight delay "
                    ."today. Please come on time. He will pick me up and drop me at the market.'",
                'listen' => "Watch a short video about someone's daily commute.",
                'speak' => 'Describe your daily commute to your practice partner, step by step, mentioning '
                    .'at least one delay you have faced.',
                'write' => 'Write 5 sentences about your commute.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['leave (depart)', 'निकलना (nikalna)'],
                    ['delay', 'देरी (deri)'],
                    ['on time', 'समय पर (samay par)'],
                    ['fare', 'किराया (kiraaya)'],
                    ['pick up', 'लेने जाना (lene jaana)'],
                    ['drop', 'छोड़ना (chhodna)'],
                    ['parking', 'पार्किंग (parking)'],
                    ['helmet', 'हेलमेट (helmet)'],
                    ['commute', 'आना-जाना (aana-jaana)'],
                ],
                'grammar' => "Usage rule: use Present Perfect with 'for' or 'since' to show a duration that "
                    ."started in the past and is still true now. Drill: say 'I have taken this bus for "
                    ."three years' and 1 more sentence using 'since'.",
                'tense' => TenseKey::PresentPerfect,
            ],
            28 => [
                'title' => 'Full Mock Conversation Rehearsal',
                'total_minutes' => 53,
                'read' => 'Read a complete sample 3-minute conversation transcript aloud — a phone call, a '
                    .'symptom, a plan, and directions.',
                'listen' => 'Listen to the sample conversation audio twice.',
                'speak' => 'Rehearse the full conversation with your practice partner, aiming for natural '
                    .'flow, not a memorised script.',
                'write' => "Write your personal '10 weak words or phrases' list from Month 3 in your "
                    .'notebook.',
                'vocab_intro' => 'No new words today — look back through Days 1-27 and pick your personal 10 '
                    .'hardest words to review.',
                'vocab' => [],
                'grammar' => "Review drill: as you read today's sample conversation aloud, mark every "
                    .'Present Perfect verb you find (have/has + past participle) and say why it is used '
                    .'instead of Past Simple.',
                'tense' => TenseKey::PresentPerfect,
            ],
            29 => [
                'title' => 'Record & Review',
                'total_minutes' => 48,
                'read' => "Instead of reading something new, record yourself reading yesterday's writing "
                    .'aloud.',
                'listen' => 'Play back your Day 28 recording and listen for clarity.',
                'speak' => 'Repeat the full conversation, recorded on your phone, focusing on polite, clear '
                    .'phrasing.',
                'write' => 'Write one thing that has improved since the start of Month 3.',
                'vocab_intro' => "No new words today — review all Week 4 words once more before tomorrow's "
                    .'milestone conversation.',
                'vocab' => [],
                'grammar' => 'Review drill: describe one change since Month 3 began using Present Perfect, '
                    ."e.g. 'I have improved my pronunciation' or 'My confidence has grown.' Avoid adding a "
                    .'specific past date to these sentences.',
                'tense' => TenseKey::PresentPerfect,
            ],
            30 => [
                'title' => 'Month 3 Milestone — Real-Life Conversation',
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
                    .'includes small talk about the weather, a short phone-style exchange, describing a '
                    .'symptom or making a plan, and giving directions. This is the official Month 3 milestone '
                    .'— score it against the Month 3 Test rubric.',
                'write' => "Write a short reflection: 'What I can now say on the phone, at the doctor, and "
                    ."while making plans that I could not say two months ago.'",
                'vocab_intro' => 'Month 3 vocabulary is complete — 425 out of 500 words introduced. No new '
                    .'words today; this is a milestone day.',
                'vocab' => [],
                'grammar' => "Closing review: mix Past Continuous ('I was learning...') and Present Perfect "
                    ."('I have learned...') as you reflect on Month 3 during your milestone conversation.",
                'tense' => TenseKey::PresentPerfect,
            ],
        ];
    }
}
