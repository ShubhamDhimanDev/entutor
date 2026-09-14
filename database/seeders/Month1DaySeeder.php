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

class Month1DaySeeder extends Seeder
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
        $month = Month::where('month_number', 1)->first();

        if (! $month) {
            throw new RuntimeException('Month 1 not found — MonthSeeder must run before Month1DaySeeder.');
        }

        $weeks = Week::where('month_id', $month->id)->orderBy('week_number')->get();

        if ($weeks->count() !== 4) {
            throw new RuntimeException('Expected exactly 4 weeks for Month 1 before seeding days.');
        }

        /** @var Collection<string, int> $tenseIds */
        $tenseIds = Tense::query()->pluck('id', 'key');

        foreach ($this->days() as $dayNumber => $data) {
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

        $this->assertMonth1Integrity($month);
    }

    /**
     * Assertion: exactly 30 Day rows and 180 DayTask rows (30 x 6) exist for
     * Month 1, and every day has exactly one task per SkillArea.
     */
    private function assertMonth1Integrity(Month $month): void
    {
        $dayCount = Day::where('month_id', $month->id)->count();
        if ($dayCount !== 30) {
            throw new RuntimeException("Expected 30 Day rows for Month 1, found {$dayCount}.");
        }

        $dayIds = Day::where('month_id', $month->id)->pluck('id');
        $dayTaskCount = DayTask::whereIn('day_id', $dayIds)->count();
        if ($dayTaskCount !== 180) {
            throw new RuntimeException("Expected 180 DayTask rows for Month 1, found {$dayTaskCount}.");
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
     * Month 1 — full day-by-day content (Days 1-30). Reading/Listening/
     * Speaking/Writing content is adapted from the source curriculum;
     * Vocabulary terms carry Hindi glosses (Devanagari + transliteration),
     * either matching Word Bank Groups 1-6 or freshly authored where the
     * source only lists the English term.
     *
     * @return array<int, array<string, mixed>>
     */
    private function days(): array
    {
        return [
            1 => [
                'title' => 'Meet the Program: English Sounds Warm-Up',
                'total_minutes' => 45,
                'read' => 'Read the English alphabet aloud from A to Z. Then read these 10 words aloud: cat, '
                    .'dog, sun, book, pen, water, home, food, work, happy.',
                'listen' => 'Watch or listen to a 2-minute video about basic English greetings. Repeat each '
                    .'greeting aloud after the speaker.',
                'speak' => 'Say hello and goodbye to your practice partner 10 times, using a different tone '
                    ."each time. Then introduce yourself: 'My name is ___.'",
                'write' => 'Write your name and 3 greeting sentences in your notebook.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['hello', 'नमस्ते (namaste)'],
                    ['hi', 'हाय (hai)'],
                    ['good morning', 'सुप्रभात (suprabhat)'],
                    ['good afternoon', 'शुभ दोपहर (shubh dopahar)'],
                    ['good evening', 'शुभ संध्या (shubh sandhya)'],
                    ['good night', 'शुभ रात्रि (shubh raatri)'],
                    ['please', 'कृपया (kripya)'],
                    ['thank you', 'धन्यवाद (dhanyavad)'],
                    ['sorry', 'माफ़ कीजिए (maaf kijiye)'],
                    ['yes', 'हाँ (haan)'],
                    ['no', 'नहीं (nahin)'],
                ],
                'grammar' => 'Learn the Present Simple: Subject + verb (base form); add -s/-es for he/she/it. '
                    ."Say 5 sentences about your daily habits, e.g. 'I speak Hindi. My friend speaks English.'",
                'tense' => TenseKey::PresentSimple,
            ],
            2 => [
                'title' => 'Introducing Yourself',
                'total_minutes' => 45,
                'read' => "Read this 4-line introduction aloud 3 times: 'My name is ___. I am from ___. I "
                    ."live in ___. I am learning English.'",
                'listen' => 'Listen to someone introducing themselves in a video or an AI voice recording. '
                    .'Notice the pattern they use.',
                'speak' => 'Say your own introduction using the pattern above — first to your practice '
                    .'partner, then to the mirror.',
                'write' => 'Write your own 4-line introduction in your notebook.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['name', 'नाम (naam)'],
                    ['age', 'उम्र (umra)'],
                    ['city', 'शहर (shahar)'],
                    ['village', 'गाँव (gaanv)'],
                    ['family', 'परिवार (parivar)'],
                    ['live', 'रहना (rehna)'],
                    ['from', 'से (se)'],
                    ['married', 'शादीशुदा (shaadishuda)'],
                    ['house', 'घर (ghar)'],
                    ['home', 'घर (ghar)'],
                ],
                'grammar' => "Practise Present Simple with 'am' and action verbs, e.g. 'I live in Pune. My "
                    ."brother lives in Delhi.' Say 3 sentences about yourself and 3 about a family member, "
                    .'remembering the -s for he/she/it.',
                'tense' => TenseKey::PresentSimple,
            ],
            3 => [
                'title' => 'Politeness Words',
                'total_minutes' => 48,
                'read' => "Read these words aloud: please, thank you, excuse me, sorry, you're welcome, no "
                    .'problem, of course, sure, okay, alright.',
                'listen' => "Watch a short clip about using 'please' and 'thank you' in different situations.",
                'speak' => 'Roleplay asking for things politely with your practice partner, 5 times, for '
                    ."example: 'Can I have some water, please?'",
                'write' => 'Write 5 polite request sentences.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['please', 'कृपया (kripya)'],
                    ['thank you', 'धन्यवाद (dhanyavad)'],
                    ['excuse me', 'सुनिए (suniye)'],
                    ['sorry', 'माफ़ कीजिए (maaf kijiye)'],
                    ["you're welcome", 'आपका स्वागत है (aapka swagat hai)'],
                    ['kindly', 'कृपया (kripya)'],
                    ['may I', 'क्या मैं (kya main)'],
                    ['can I', 'क्या मैं ... कर सकता/सकती हूँ (kya main ... kar sakta/sakti hoon)'],
                    ['pardon', 'क्षमा करें (kshama karen)'],
                    ['no problem', 'कोई बात नहीं (koi baat nahin)'],
                ],
                'grammar' => 'Learn the spelling rule for he/she/it: verbs ending in -o, -ch, -sh, -ss, or -x '
                    .'add -es (go → goes, watch → watches), and consonant + y changes to -ies (study → '
                    .'studies). Say 5 Present Simple sentences using these verbs.',
                'tense' => TenseKey::PresentSimple,
            ],
            4 => [
                'title' => 'Numbers 1-20',
                'total_minutes' => 45,
                'read' => 'Read the numbers 1 to 20 aloud, twice.',
                'listen' => 'Listen to the numbers 1-20 being spoken aloud, and repeat each one.',
                'speak' => 'Count from 1 to 20 with your practice partner, then count objects around your '
                    .'home in English.',
                'write' => 'Write the numbers 1-20 in words.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['one', 'एक (ek)'],
                    ['two', 'दो (do)'],
                    ['three', 'तीन (teen)'],
                    ['four', 'चार (chaar)'],
                    ['five', 'पाँच (paanch)'],
                    ['six', 'छह (chhah)'],
                    ['seven', 'सात (saat)'],
                    ['eight', 'आठ (aath)'],
                    ['nine', 'नौ (nau)'],
                    ['ten', 'दस (das)'],
                    ['eleven', 'ग्यारह (gyarah)'],
                    ['twelve', 'बारह (baarah)'],
                    ['thirteen', 'तेरह (terah)'],
                    ['fourteen', 'चौदह (chaudah)'],
                    ['fifteen', 'पंद्रह (pandrah)'],
                    ['sixteen', 'सोलह (solah)'],
                    ['seventeen', 'सत्रह (satrah)'],
                    ['eighteen', 'अठारह (atharah)'],
                    ['nineteen', 'उन्नीस (unnees)'],
                    ['twenty', 'बीस (bees)'],
                    ['count', 'गिनना (ginna)'],
                    ['number', 'संख्या (sankhya)'],
                    ['how many', 'कितने (kitne)'],
                ],
                'grammar' => 'Learn the Present Simple negative: Subject + do not/does not + verb (base '
                    ."form). Say 5 negative sentences using numbers, e.g. 'I don't have twenty rupees. She "
                    ."doesn't own two phones.'",
                'tense' => TenseKey::PresentSimple,
            ],
            5 => [
                'title' => 'Numbers 21-100 & Quantity Words',
                'total_minutes' => 48,
                'read' => "Read these tens aloud: twenty, thirty, forty ... hundred. Then try: '25 rupees', "
                    ."'60 minutes'.",
                'listen' => 'Listen to prices being said aloud in a shopping video.',
                'speak' => "Practise saying prices aloud, for example: 'This costs two hundred fifty rupees.'",
                'write' => 'Write 5 numbers in words, each with a real object or price.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['twenty', 'बीस (bees)'],
                    ['thirty', 'तीस (tees)'],
                    ['forty', 'चालीस (chaalees)'],
                    ['fifty', 'पचास (pachaas)'],
                    ['hundred', 'सौ (sau)'],
                    ['some', 'कुछ (kuchh)'],
                    ['many', 'कई (kai)'],
                    ['few', 'थोड़े (thode)'],
                    ['all', 'सभी (sabhi)'],
                    ['none', 'कोई नहीं (koi nahin)'],
                ],
                'grammar' => "Practise negative Present Simple with quantity words, e.g. 'I don't have many "
                    ."books. He doesn't need much time.' Say 5 negative sentences about things you have "
                    .'little or none of.',
                'tense' => TenseKey::PresentSimple,
            ],
            6 => [
                'title' => 'Days of the Week',
                'total_minutes' => 45,
                'read' => 'Read the days of the week aloud, first in order, then out of order.',
                'listen' => 'Watch a short clip about a calendar or weekly schedule.',
                'speak' => 'Tell your practice partner what you usually do on each day of the week.',
                'write' => 'Write your weekly schedule, one line per day.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['Monday', 'सोमवार (somvaar)'],
                    ['Tuesday', 'मंगलवार (mangalvaar)'],
                    ['Wednesday', 'बुधवार (budhvaar)'],
                    ['today', 'आज (aaj)'],
                    ['tomorrow', 'कल (kal)'],
                    ['yesterday', 'कल (kal)'],
                    ['week', 'सप्ताह (saptaah)'],
                    ['weekday', 'कार्यदिवस (kaaryadivas)'],
                    ['weekend', 'सप्ताहांत (saptaahaant)'],
                    ['everyday', 'हर रोज़ (har roz)'],
                ],
                'grammar' => 'Learn the Present Simple question: Do/Does + subject + verb (base form)? Ask '
                    ."your practice partner 5 yes/no questions about their week, e.g. 'Do you work on "
                    ."Mondays? Does she cook every day?'",
                'tense' => TenseKey::PresentSimple,
            ],
            7 => [
                'title' => 'Week 1 Review — First Mini Conversation',
                'total_minutes' => 58,
                'read' => "Re-read all of this week's words aloud, once, at normal speed.",
                'listen' => 'Watch the Day 1 greetings clip again and notice what feels easier now.',
                'speak' => 'Attempt a 2-minute conversation: greet your practice partner, introduce yourself, '
                    .'and mention a number and a day. Your practice partner confirms the Week 1 milestone if '
                    .'you manage it.',
                'write' => 'Write a 5-sentence paragraph that combines a greeting, your introduction, one '
                    .'number, and one day.',
                'vocab_intro' => 'No new words this week — re-read all 60-70 Week 1 words aloud once, at '
                    .'normal speed, and notice which ones are now easy.',
                'vocab' => [],
                'grammar' => 'Review all three Present Simple forms — affirmative, negative, and question — '
                    .'in one short exchange with your practice partner: state a habit, negate a different '
                    ."one, then ask a question, e.g. 'I wake up at 6. I don't sleep late. Do you wake up "
                    ."early too?'",
                'tense' => TenseKey::PresentSimple,
            ],
            8 => [
                'title' => 'Months & Dates',
                'total_minutes' => 45,
                'read' => "Read the months of the year aloud, then say: 'Today is ___.'",
                'listen' => 'Watch a short video about dates and birthdays.',
                'speak' => "Say today's date, your birth month, and a family member's birth month.",
                'write' => 'Write 3 sentences that each include a date.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['January', 'जनवरी (janvari)'],
                    ['February', 'फ़रवरी (farvari)'],
                    ['March', 'मार्च (march)'],
                    ['date', 'तारीख़ (taareekh)'],
                    ['month', 'महीना (mahina)'],
                    ['year', 'साल (saal)'],
                    ['birthday', 'जन्मदिन (janamdin)'],
                    ['born', 'पैदा होना (paida hona)'],
                    ['calendar', 'कैलेंडर (calendar)'],
                    ['century', 'सदी (sadi)'],
                ],
                'grammar' => 'Learn the wh-question pattern: wh-word + do/does + subject + verb (base form)? '
                    ."Ask your practice partner 5 questions about dates and birthdays, e.g. 'When do you "
                    ."celebrate your birthday? What do you usually do on that day?'",
                'tense' => TenseKey::PresentSimple,
            ],
            9 => [
                'title' => 'Telling the Time',
                'total_minutes' => 48,
                'read' => "Read these time expressions aloud: o'clock, half past, quarter to, quarter past.",
                'listen' => 'Watch a short clip about telling the time on a clock.',
                'speak' => "Ask and answer 'What time is it?' with your practice partner, using a clock or "
                    .'phone.',
                'write' => 'Write your daily schedule with clock times (7 lines).',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ["o'clock", 'बजे (baje)'],
                    ['half past', 'साढ़े (saadhe)'],
                    ['quarter', 'चौथाई (chauthai)'],
                    ['morning', 'सुबह (subah)'],
                    ['afternoon', 'दोपहर (dopahar)'],
                    ['evening', 'शाम (shaam)'],
                    ['night', 'रात (raat)'],
                    ['minute', 'मिनट (minat)'],
                    ['hour', 'घंटा (ghanta)'],
                    ['noon', 'मध्याह्न (madhyaahn)'],
                ],
                'grammar' => 'Learn: Present Simple also describes fixed schedules and timetables, not just '
                    ."habits, e.g. 'The class starts at 9 o'clock. The train leaves at 6:30.' Say 3 sentences "
                    .'about fixed times in your own schedule.',
                'tense' => TenseKey::PresentSimple,
            ],
            10 => [
                'title' => 'Family Members',
                'total_minutes' => 45,
                'read' => "Read family words aloud in sentences, for example: 'This is my mother.'",
                'listen' => 'Watch a short video of someone introducing their family.',
                'speak' => 'Introduce 5 family members to your practice partner, then to the mirror.',
                'write' => 'Write 5 sentences about your family.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['mother', 'माँ (maa)'],
                    ['father', 'पिता (pita)'],
                    ['sister', 'बहन (behan)'],
                    ['brother', 'भाई (bhai)'],
                    ['son', 'बेटा (beta)'],
                    ['daughter', 'बेटी (beti)'],
                    ['grandmother', 'दादी / नानी (dadi / nani)'],
                    ['grandfather', 'दादा / नाना (dada / nana)'],
                    ['family', 'परिवार (parivar)'],
                    ['relative', 'रिश्तेदार (rishtedaar)'],
                ],
                'grammar' => 'Learn: Present Simple describes permanent situations and facts, such as a '
                    .'person\'s job, language, or family relationships, e.g. \'My father works in a bank. We '
                    ."speak Hindi at home.' Say 3 permanent facts about your family.",
                'tense' => TenseKey::PresentSimple,
            ],
            11 => [
                'title' => 'More Family & My/His/Her',
                'total_minutes' => 48,
                'read' => "Read sentences using 'my', 'his', and 'her' aloud.",
                'listen' => 'Listen to a short story about a family.',
                'speak' => "Describe a family member's household briefly, using 'his' or 'her' correctly.",
                'write' => "Write 4 sentences using 'my', 'his', or 'her' correctly.",
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['my', 'मेरा (mera)'],
                    ['his', 'उसका (uska)'],
                    ['her', 'उसकी (uski)'],
                    ['our', 'हमारा (hamara)'],
                    ['their', 'उनका (unka)'],
                    ['uncle', 'चाचा / मामा (chacha / mama)'],
                    ['aunt', 'चाची / मौसी (chachi / mausi)'],
                    ['cousin', 'चचेरा भाई/बहन (chachera bhai/behan)'],
                    ['friend', 'दोस्त (dost)'],
                    ['neighbour', 'पड़ोसी (padosi)'],
                ],
                'grammar' => 'Learn: state verbs like know, like, want, and believe describe states, not '
                    .'actions — they never take -ing, even when the meaning is right now. Say 3 sentences '
                    ."with state verbs about your family, e.g. 'I know my uncle's phone number. My cousin "
                    ."likes cricket.'",
                'tense' => TenseKey::PresentSimple,
            ],
            12 => [
                'title' => 'Describing People',
                'total_minutes' => 45,
                'read' => 'Read simple sentences describing people aloud.',
                'listen' => 'Watch a short video describing different people.',
                'speak' => "Describe 3 family members using today's adjectives.",
                'write' => 'Write 3 sentences describing people you know.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['tall', 'लंबा (lamba)'],
                    ['short', 'छोटा (chhota)'],
                    ['young', 'जवान (jawaan)'],
                    ['old', 'बूढ़ा (budha)'],
                    ['kind', 'दयालु (dayalu)'],
                    ['friendly', 'मिलनसार (milansaar)'],
                    ['happy', 'खुश (khush)'],
                    ['tired', 'थका हुआ (thaka hua)'],
                    ['busy', 'व्यस्त (vyast)'],
                    ['free', 'खाली (khaali)'],
                ],
                'grammar' => "Learn where frequency adverbs go: before the main verb but after 'be' — 'She "
                    ."always helps me' but 'She is always kind.' Describe 3 people using always, usually, or "
                    .'never.',
                'tense' => TenseKey::PresentSimple,
            ],
            13 => [
                'title' => 'Answering Personal Questions',
                'total_minutes' => 50,
                'read' => 'Read a list of 10 common personal questions aloud.',
                'listen' => 'Listen to a short question-and-answer style clip.',
                'speak' => 'Your practice partner asks all 10 questions; answer in full sentences. Then swap '
                    .'roles.',
                'write' => 'Write answers to 5 of the 10 questions.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['what', 'क्या (kya)'],
                    ['where', 'कहाँ (kahaan)'],
                    ['when', 'कब (kab)'],
                    ['who', 'कौन (kaun)'],
                    ['why', 'क्यों (kyon)'],
                    ['how', 'कैसे (kaise)'],
                    ['how many', 'कितने (kitne)'],
                    ['how much', 'कितना (kitna)'],
                    ['which', 'कौन सा (kaun sa)'],
                    ['whose', 'किसका (kiska)'],
                ],
                'grammar' => "Common mistake: after does/doesn't, the main verb stays in its base form — say "
                    ."'She doesn't want tea' not 'She doesn't wants tea', and 'Does he speak English?' not "
                    ."'Does he speaks English?' Correct 3 similar mistakes, then answer today's personal "
                    .'questions using correct Present Simple.',
                'tense' => TenseKey::PresentSimple,
            ],
            14 => [
                'title' => 'Week 2 Review — Family, Time & Numbers Together',
                'total_minutes' => 58,
                'read' => "Re-read all of this week's words aloud.",
                'listen' => 'Watch the Day 9 time clip again and notice what feels easier now.',
                'speak' => "Have a 3-minute conversation about your family, today's date and time, and one "
                    .'family member\'s age. Your practice partner confirms the Week 2 milestone.',
                'write' => "Write a 6-sentence paragraph: 'My Family and My Day'.",
                'vocab_intro' => 'No new words today — review all Week 2 words aloud and notice which ones '
                    .'feel easier now.',
                'vocab' => [],
                'grammar' => 'Review Present Simple by combining affirmative, negative, question, and a '
                    .'state verb in one unscripted mini-monologue about your family and daily routine, e.g. '
                    ."'I live in ___. I don't work on Sundays. Do you like weekends?'",
                'tense' => TenseKey::PresentSimple,
            ],
            15 => [
                'title' => 'Daily Action Verbs',
                'total_minutes' => 45,
                'read' => "Read 10 simple present-tense sentences aloud, for example: 'I eat breakfast.'",
                'listen' => 'Watch a video about a daily routine.',
                'speak' => 'Say 10 things you do every single day, one sentence each.',
                'write' => 'Write 8 daily actions as full sentences.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['eat', 'खाना (khaana)'],
                    ['drink', 'पीना (peena)'],
                    ['sleep', 'सोना (sona)'],
                    ['wake up', 'जागना (jaagna)'],
                    ['go', 'जाना (jaana)'],
                    ['come', 'आना (aana)'],
                    ['work', 'काम करना (kaam karna)'],
                    ['study', 'पढ़ना (padhna)'],
                    ['cook', 'पकाना (pakaana)'],
                    ['clean', 'साफ़ करना (saaf karna)'],
                ],
                'grammar' => 'Learn the Present Continuous: Subject + am/is/are + verb-ing, for actions '
                    .'happening right now. Say 5 sentences about what you and people around you are doing '
                    ."right now, e.g. 'I am writing in my notebook. My sister is watching TV.'",
                'tense' => TenseKey::PresentContinuous,
            ],
            16 => [
                'title' => 'Frequency Words & Morning Routine',
                'total_minutes' => 48,
                'read' => "Read sentences with 'always', 'usually', 'sometimes', and 'never' aloud.",
                'listen' => 'Watch a video about daily habits.',
                'speak' => 'Describe your morning routine step by step, in order.',
                'write' => 'Write your morning routine in 6 lines.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['always', 'हमेशा (hamesha)'],
                    ['usually', 'आमतौर पर (aam taur par)'],
                    ['sometimes', 'कभी-कभी (kabhi-kabhi)'],
                    ['rarely', 'शायद ही कभी (shayad hi kabhi)'],
                    ['never', 'कभी नहीं (kabhi nahin)'],
                    ['wash', 'धोना (dhona)'],
                    ['brush', 'ब्रश करना (brush karna)'],
                    ['bathe', 'नहाना (nahaana)'],
                    ['dress', 'कपड़े पहनना (kapde pehenna)'],
                    ['comb', 'कंघी करना (kanghi karna)'],
                ],
                'grammar' => 'Learn the -ing spelling rules: drop a silent e (write → writing), and double '
                    .'the final consonant after a single vowel in a short verb (sit → sitting). Say 5 Present '
                    .'Continuous sentences using these verbs.',
                'tense' => TenseKey::PresentContinuous,
            ],
            17 => [
                'title' => 'House & Household Actions',
                'total_minutes' => 45,
                'read' => 'Read sentences about household chores aloud.',
                'listen' => 'Watch a short video about household chores.',
                'speak' => "Describe who does which chore at home, using today's verbs.",
                'write' => 'Write 5 sentences about household work.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['sweep', 'झाड़ू लगाना (jhaadu lagaana)'],
                    ['wash clothes', 'कपड़े धोना (kapde dhona)'],
                    ['cook food', 'खाना पकाना (khaana pakaana)'],
                    ['buy vegetables', 'सब्ज़ी खरीदना (sabzi khareedna)'],
                    ['watch TV', 'टीवी देखना (TV dekhna)'],
                    ['read', 'पढ़ना (parhna)'],
                    ['write', 'लिखना (likhna)'],
                    ['call', 'कॉल करना (call karna)'],
                    ['message', 'मैसेज करना (message karna)'],
                    ['meet', 'मिलना (milna)'],
                ],
                'grammar' => 'Practise Present Continuous with household actions: say what each family '
                    ."member is doing right now, e.g. 'My mother is cooking food. My brother is sweeping the "
                    ."floor.'",
                'tense' => TenseKey::PresentContinuous,
            ],
            18 => [
                'title' => 'Errands & Everyday Tasks',
                'total_minutes' => 48,
                'read' => "Read simple errand sentences aloud, for example: 'I pay the bill. I check the "
                    ."price.'",
                'listen' => 'Watch a short clip about running errands.',
                'speak' => 'Describe a typical errand you ran this week, step by step, using today\'s verbs.',
                'write' => 'Write 5 sentences about errands you do.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['pay', 'भुगतान करना (bhugtaan karna)'],
                    ['check', 'जाँचना (jaanchna)'],
                    ['buy', 'खरीदना (khareedna)'],
                    ['return', 'वापस करना (waapas karna)'],
                    ['wait', 'इंतज़ार करना (intezaar karna)'],
                    ['collect', 'इकट्ठा करना (ikattha karna)'],
                    ['carry', 'ले जाना (le jaana)'],
                    ['deliver', 'पहुँचाना (pahunchaana)'],
                    ['order', 'ऑर्डर करना (order karna)'],
                    ['fix', 'ठीक करना (theek karna)'],
                ],
                'grammar' => 'Learn the Present Continuous negative: Subject + am/is/are + not + verb-ing. '
                    ."Say 5 negative sentences about errands you are not doing right now, e.g. 'I am not "
                    ."paying the bill today. He isn't checking his messages.'",
                'tense' => TenseKey::PresentContinuous,
            ],
            19 => [
                'title' => "Can/Can't — Talking About Ability",
                'total_minutes' => 45,
                'read' => "Read sentences with 'can' and 'can't' aloud.",
                'listen' => 'Watch a short motivational clip about learning something new.',
                'speak' => 'Say 5 things you can do and 5 things you are still learning to do.',
                'write' => "Write 5 sentences about yourself using 'can' or 'can't'.",
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['can', 'सकना (sakna)'],
                    ["can't", 'नहीं सकना (nahin sakna)'],
                    ['able to', 'सक्षम होना (saksham hona)'],
                    ['difficult', 'मुश्किल (mushkil)'],
                    ['easy', 'आसान (aasaan)'],
                    ['try', 'कोशिश करना (koshish karna)'],
                    ['practice', 'अभ्यास करना (abhyaas karna)'],
                    ['learn', 'सीखना (seekhna)'],
                    ['understand', 'समझना (samajhna)'],
                    ['improve', 'सुधारना (sudhaarna)'],
                ],
                'grammar' => 'Learn the Present Continuous question: Am/Is/Are + subject + verb-ing? Ask your '
                    ."practice partner 5 questions about what they are trying to learn right now, e.g. 'Are "
                    ."you practising speaking today? Is he improving quickly?'",
                'tense' => TenseKey::PresentContinuous,
            ],
            20 => [
                'title' => 'Requests & Offers',
                'total_minutes' => 48,
                'read' => 'Read request-and-offer dialogues aloud.',
                'listen' => 'Watch a short dialogue about making requests and offers.',
                'speak' => 'Practise making 5 requests and 5 offers with your practice partner.',
                'write' => 'Write a 4-line dialogue containing one request and one reply.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['can you', 'क्या आप ... कर सकते हैं (kya aap ... kar sakte hain)'],
                    ['could you', 'क्या आप कृपया ... करेंगे (kya aap kripya ... karenge)'],
                    ['would you like', 'क्या आप चाहेंगे (kya aap chahenge)'],
                    ['let me', 'मुझे ... करने दीजिए (mujhe ... karne dijiye)'],
                    ['here you are', 'यह लीजिए (yeh lijiye)'],
                    ['no problem', 'कोई बात नहीं (koi baat nahin)'],
                    ['sure', 'ज़रूर (zaroor)'],
                    ['of course', 'बिल्कुल (bilkul)'],
                    ['help me', 'मेरी मदद कीजिए (meri madad kijiye)'],
                    ['give me', 'मुझे दीजिए (mujhe dijiye)'],
                ],
                'grammar' => 'Combine negative and question Present Continuous in short exchanges with your '
                    ."practice partner, e.g. 'Are you waiting for me?' 'No, I'm not waiting — I'm making "
                    ."tea.' Practise 4 exchanges like this.",
                'tense' => TenseKey::PresentContinuous,
            ],
            21 => [
                'title' => 'Week 3 Review — Describe Your Whole Day',
                'total_minutes' => 58,
                'read' => "Re-read all of this week's words aloud.",
                'listen' => 'Watch a daily-routine video again, this time without pausing.',
                'speak' => 'Describe your entire day, morning to night, in 10 or more unscripted sentences. '
                    .'Your practice partner confirms the Week 3 milestone.',
                'write' => "Write 'My Day' — 8-10 sentences using at least 6 of this week's verbs.",
                'vocab_intro' => 'No new words today — review all Week 3 words aloud.',
                'vocab' => [],
                'grammar' => 'Review all three Present Continuous forms — affirmative, negative, and '
                    ."question — while describing your day so far, e.g. 'I am sitting at home. I'm not "
                    ."working today. Are you doing something similar?'",
                'tense' => TenseKey::PresentContinuous,
            ],
            22 => [
                'title' => 'Putting It All Together',
                'total_minutes' => 50,
                'read' => 'Read a model 10-sentence self-introduction and daily-routine summary aloud.',
                'listen' => 'Listen to a similar model recording.',
                'speak' => 'Attempt the same self-introduction and routine summary, unscripted, timed at 2 '
                    .'minutes.',
                'write' => 'Write your own version — 8-10 connected sentences.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['and', 'और (aur)'],
                    ['but', 'लेकिन (lekin)'],
                    ['because', 'क्योंकि (kyonki)'],
                    ['so', 'इसलिए (isliye)'],
                    ['also', 'भी (bhi)'],
                ],
                'grammar' => 'Learn: use Present Continuous for temporary situations that are true only '
                    ."around now, not forever, e.g. 'I am staying with my cousin this week' (temporary) "
                    ."versus 'I live in Delhi' (permanent, Present Simple). Say 2 sentences contrasting a "
                    .'temporary and a permanent situation.',
                'tense' => TenseKey::PresentContinuous,
            ],
            23 => [
                'title' => 'Two-Way Conversation',
                'total_minutes' => 50,
                'read' => 'Read a 2-person dialogue about daily life aloud, taking both parts.',
                'listen' => 'Listen to a natural, unscripted 2-person conversation clip.',
                'speak' => 'Have a real back-and-forth conversation: ask your practice partner about their '
                    .'day, then answer about yours.',
                'write' => 'Write the dialogue you just had, from memory.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['What about you?', 'आपके बारे में क्या? (aapke baare mein kya?)'],
                    ['How about you?', 'और आपका? (aur aapka?)'],
                    ['Same here', 'मेरा भी यही हाल है (mera bhi yehi haal hai)'],
                    ['Really?', 'सच में? (sach mein?)'],
                    ["That's nice", 'यह अच्छा है (yeh achha hai)'],
                ],
                'grammar' => 'Learn: Present Continuous can describe a situation that is changing or '
                    ."developing, e.g. 'The weather is getting colder. My English is improving.' Ask your "
                    .'practice partner one question about something that is changing in their life.',
                'tense' => TenseKey::PresentContinuous,
            ],
            24 => [
                'title' => 'Likes, Dislikes & Hobbies',
                'total_minutes' => 45,
                'read' => 'Read sentences about likes and dislikes aloud.',
                'listen' => 'Watch a short video about hobbies.',
                'speak' => 'Talk about 3 things you like and 2 things you dislike, with reasons.',
                'write' => 'Write 5 sentences about your hobbies or interests.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['like', 'पसंद करना (pasand karna)'],
                    ['love', 'प्यार करना (pyaar karna)'],
                    ['hate', 'नफ़रत करना (nafrat karna)'],
                    ['enjoy', 'आनंद लेना (aanand lena)'],
                    ['prefer', 'अधिक पसंद करना (adhik pasand karna)'],
                    ['hobby', 'शौक़ (shauk)'],
                    ['free time', 'खाली समय (khaali samay)'],
                    ['interested in', 'में रुचि रखना (mein ruchi rakhna)'],
                    ['bored', 'ऊबा हुआ (ooba hua)'],
                    ['fun', 'मज़ा (maza)'],
                ],
                'grammar' => 'Learn: state verbs like like, love, prefer, and want stay in Present Simple '
                    ."even when you mean right now — say 'I like this song' not 'I am liking this song'. Say "
                    .'3 sentences about your hobbies using state verbs correctly.',
                'tense' => TenseKey::PresentContinuous,
            ],
            25 => [
                'title' => 'Talking About Plans',
                'total_minutes' => 48,
                'read' => "Read sentences with 'going to' aloud.",
                'listen' => 'Watch a short video about plans and goals.',
                'speak' => 'Tell your practice partner 3 plans for this week and one goal for this program.',
                'write' => 'Write 4 sentences about your plans.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['going to', '... करने वाला/वाली होना (karne waala/waali hona)'],
                    ['plan', 'योजना (yojana)'],
                    ['next week', 'अगले हफ़्ते (agle hafte)'],
                    ['next month', 'अगले महीने (agle mahine)'],
                    ['soon', 'जल्द ही (jald hi)'],
                    ['later', 'बाद में (baad mein)'],
                    ['tonight', 'आज रात (aaj raat)'],
                    ['this weekend', 'इस सप्ताहांत (is saptaahaant)'],
                    ['hope to', 'उम्मीद करना (ummeed karna)'],
                    ['want to', 'चाहना (chaahna)'],
                ],
                'grammar' => 'Learn: Present Continuous also describes fixed future plans that already have '
                    ."a time and place decided, e.g. 'I am meeting my coach on Friday.' Say 3 sentences about "
                    .'a plan you have already arranged for this week.',
                'tense' => TenseKey::PresentContinuous,
            ],
            26 => [
                'title' => 'Handling Surprise Questions',
                'total_minutes' => 48,
                'read' => 'Read example unscripted answers to surprise questions.',
                'listen' => 'Listen to a natural conversation clip and notice the pauses and filler words.',
                'speak' => 'Your practice partner asks 5 random questions from any earlier day. Answer '
                    .'without preparing, using a filler word if you need a second to think.',
                'write' => 'Write 3 of your surprise-question answers neatly.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['let me think', 'मुझे सोचने दीजिए (mujhe sochne dijiye)'],
                    ['actually', 'असल में (asal mein)'],
                    ['I mean', 'मेरा मतलब है (mera matlab hai)'],
                    ['well', 'अच्छा (achha)'],
                    ['you know', 'आप जानते हैं (aap jaante hain)'],
                ],
                'grammar' => 'Review Present Continuous signal words — now, right now, at the moment, '
                    .'currently, these days, still — by answering 3 surprise questions your practice partner '
                    .'asks about what you are doing these days.',
                'tense' => TenseKey::PresentContinuous,
            ],
            27 => [
                'title' => 'Correcting Yourself Politely',
                'total_minutes' => 45,
                'read' => "Read example self-correction phrases aloud, for example: 'Sorry, I mean...', "
                    ."'Let me say that again.'",
                'listen' => 'Listen to a clip where a speaker naturally corrects themselves mid-sentence.',
                'speak' => 'Make 3 small mistakes on purpose while speaking, then practise correcting '
                    .'yourself smoothly.',
                'write' => 'Write 2 sentences, each containing a deliberate self-correction.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['I mean', 'मेरा मतलब है (mera matlab hai)'],
                    ['sorry', 'माफ़ कीजिए (maaf kijiye)'],
                    ['let me correct that', 'मुझे इसे ठीक करने दीजिए (mujhe ise theek karne dijiye)'],
                    ['what I meant was', 'मेरा मतलब था (mera matlab tha)'],
                    ['actually', 'असल में (asal mein)'],
                ],
                'grammar' => "Common mistake: don't use Present Continuous with state verbs — say 'I believe "
                    ."you' not 'I am believing you', and 'She prefers tea' not 'She is preferring tea'. "
                    .'Correct 3 similar sentences, then practise saying them naturally.',
                'tense' => TenseKey::PresentContinuous,
            ],
            28 => [
                'title' => 'Full Mock Conversation Rehearsal',
                'total_minutes' => 53,
                'read' => 'Read a complete sample 2-minute conversation transcript aloud.',
                'listen' => 'Listen to the sample conversation audio twice.',
                'speak' => 'Rehearse the full 2-minute conversation with your practice partner, aiming for '
                    .'natural flow, not a memorised script.',
                'write' => "Write your personal '10 weak words' list in your notebook.",
                'vocab_intro' => 'No new words today — look back through Days 1-27 and pick your personal 10 '
                    .'hardest words to review.',
                'vocab' => [],
                'grammar' => 'Freer practice: in your mock conversation, use Present Simple for habits and '
                    .'facts and Present Continuous for what is happening now or your current plans. Notice '
                    .'each choice as you rehearse.',
                'tense' => TenseKey::PresentContinuous,
            ],
            29 => [
                'title' => 'Record & Review',
                'total_minutes' => 48,
                'read' => "Instead of reading something new, record yourself reading yesterday's writing "
                    .'aloud.',
                'listen' => 'Play back your Day 28 recording and listen for clarity.',
                'speak' => 'Repeat the full conversation, recorded on your phone, with confidence and eye '
                    .'contact in the mirror.',
                'write' => 'Write one thing that has improved since Day 1.',
                'vocab_intro' => 'No new words today — review all Week 4 words once more before tomorrow\'s '
                    .'milestone conversation.',
                'vocab' => [],
                'grammar' => 'Listen to your Day 28 recording and check your tense choices: mark any '
                    .'sentence where you used Present Continuous for a habit or a state verb by mistake, '
                    .'then say the corrected version aloud.',
                'tense' => TenseKey::PresentContinuous,
            ],
            30 => [
                'title' => 'Month 1 Milestone — The Real Conversation',
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
                'speak' => 'Hold an unscripted 2-minute conversation with your practice partner about '
                    .'yourself and your day. This is the official Month 1 milestone — score it against the '
                    .'Month 1 Test rubric.',
                'write' => "Write a short reflection: 'What I can say now that I could not say 30 days ago.'",
                'vocab_intro' => 'Month 1 vocabulary is complete — 150 out of 150 words introduced. No new '
                    .'words today; this is a milestone day.',
                'vocab' => [],
                'grammar' => "Closing review: in today's conversation, use Present Simple for facts and "
                    ."routines, e.g. 'I study every day', and Present Continuous for what is happening now, "
                    ."e.g. 'I am feeling confident.' Notice how naturally you now switch between the two.",
                'tense' => TenseKey::PresentContinuous,
            ],
        ];
    }
}
