<?php

namespace Database\Seeders;

use App\Enums\SkillArea;
use App\Models\Day;
use App\Models\DayTask;
use App\Models\DayTaskVocabularyItem;
use App\Models\Month;
use App\Models\Week;
use Illuminate\Database\Seeder;
use RuntimeException;

class Month5DaySeeder extends Seeder
{
    /**
     * Proportional minute split across the 5 daily tasks (out of 56 parts):
     * read/vocabulary/listen/speak/write ~= 8/12/10/16/10. Speaking (the
     * main practice block) absorbs any rounding remainder.
     *
     * @var array<string, int>
     */
    private const MINUTE_RATIOS = [
        'reading' => 8,
        'vocabulary' => 12,
        'listening' => 10,
        'speaking' => 16,
        'writing' => 10,
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $month = Month::where('month_number', 5)->first();

        if (! $month) {
            throw new RuntimeException('Month 5 not found — MonthSeeder must run before Month5DaySeeder.');
        }

        $weeks = Week::where('month_id', $month->id)->orderBy('week_number')->get();

        if ($weeks->count() !== 4) {
            throw new RuntimeException('Expected exactly 4 weeks for Month 5 before seeding days.');
        }

        // day_number is globally unique across the whole program (1-180), so
        // local day 1-30 within this month must be offset onto the global
        // range that MonthSeeder already assigned to Month 5's weeks.
        $offset = ($month->month_number - 1) * 30;

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
        }

        $this->assertMonth5Integrity($month);
    }

    /**
     * Assertion: exactly 30 Day rows and 150 DayTask rows (30 x 5) exist for
     * Month 5, and every day has exactly one task per SkillArea.
     */
    private function assertMonth5Integrity(Month $month): void
    {
        $dayCount = Day::where('month_id', $month->id)->count();
        if ($dayCount !== 30) {
            throw new RuntimeException("Expected 30 Day rows for Month 5, found {$dayCount}.");
        }

        $dayIds = Day::where('month_id', $month->id)->pluck('id');
        $dayTaskCount = DayTask::whereIn('day_id', $dayIds)->count();
        if ($dayTaskCount !== 150) {
            throw new RuntimeException("Expected 150 DayTask rows for Month 5, found {$dayTaskCount}.");
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
     * Month 5 — full day-by-day content (local days 1-30, offset to global
     * days 121-150). No new Word Bank vocabulary this month (per MonthSeeder's
     * vocabulary_target for Month 5) — every day's Vocabulary task points the
     * learner back to specific, real vocabulary from earlier months relevant
     * to that day's roleplay, rather than fabricating a new list. Reading/
     * listening/speaking/writing content carries the applied-communication
     * theme (handling problems, opinions, difficult conversations, detail
     * confirmation).
     *
     * @return array<int, array<string, mixed>>
     */
    private function days(): array
    {
        return [
            1 => [
                'title' => 'Explaining a Problem Calmly',
                'total_minutes' => 45,
                'read' => "Read aloud: 'Excuse me, I think there is a mistake with my bill. Could you please "
                    ."check it again?'",
                'listen' => 'Watch a short video of a customer calmly explaining a billing mistake to a '
                    .'shopkeeper.',
                'speak' => 'Roleplay explaining a wrong bill to your practice partner (the shopkeeper), '
                    .'staying calm and polite throughout.',
                'write' => 'Write a 5-line dialogue explaining a problem calmly.',
                'vocab_intro' => 'No new words this month — review the Money & Shopping words from Month 2 '
                    .'(price, discount, receipt, change) before today\'s roleplay.',
                'vocab' => [],
            ],
            2 => [
                'title' => 'Asking for a Solution',
                'total_minutes' => 45,
                'read' => "Read aloud: 'Would it be possible to fix this today? Could you please replace "
                    ."this item? What can we do about this?'",
                'listen' => 'Watch a short video of someone politely asking for a solution to a problem.',
                'speak' => 'Practise asking for 3 different solutions to a problem with your practice '
                    ."partner, using 'Could you please...' and 'Would it be possible to...'.",
                'write' => 'Write 4 sentences asking for a solution to a problem.',
                'vocab_intro' => 'No new words today — review the Common Verbs 3 words from Month 4 (solve, '
                    .'fix, ensure) that are useful when asking for a solution.',
                'vocab' => [],
            ],
            3 => [
                'title' => 'A Delayed Delivery',
                'total_minutes' => 48,
                'read' => "Read aloud: 'My delivery is two days late. Can you tell me when it will arrive? I "
                    ."need it urgently.'",
                'listen' => 'Watch a short video of a customer calling about a delayed delivery.',
                'speak' => 'Roleplay calling about a delayed delivery with your practice partner as the '
                    .'delivery company.',
                'write' => 'Write the delayed-delivery phone call as a 6-line dialogue.',
                'vocab_intro' => 'No new words today — review the Travel & Commute words from Month 3 '
                    .'(delay, on time, arrive) before today\'s roleplay.',
                'vocab' => [],
            ],
            4 => [
                'title' => 'A Mistake on the Bill',
                'total_minutes' => 45,
                'read' => "Read aloud: 'I was charged twice for the same item. This total does not look "
                    ."correct. Can you please check the receipt again?'",
                'listen' => 'Watch a short video of a customer pointing out a billing mistake at a '
                    .'restaurant.',
                'speak' => 'Roleplay finding a mistake on a restaurant or shop bill with your practice '
                    .'partner, and asking them to correct it.',
                'write' => 'Write a 5-line dialogue about a mistake on a bill.',
                'vocab_intro' => 'No new words today — review the Food & Drink and Money & Shopping words '
                    .'from Month 2 (bill, total, order) before today\'s roleplay.',
                'vocab' => [],
            ],
            5 => [
                'title' => 'Staying Polite Under Pressure',
                'total_minutes' => 48,
                'read' => "Read aloud: 'I understand you are busy, but this is important to me. I would "
                    ."really appreciate your help with this.'",
                'listen' => 'Watch a short video of someone staying calm and polite even when the other '
                    .'person is slow to help.',
                'speak' => 'Roleplay a situation where your practice partner is slow or unhelpful; stay calm '
                    .'and polite for the whole conversation.',
                'write' => 'Write 4 sentences you could use to stay polite when someone is not helping '
                    .'quickly.',
                'vocab_intro' => 'No new words today — review the Confidence & Social Words from Month 4 '
                    .'(patient, sincere, respect) that help you stay polite under pressure.',
                'vocab' => [],
            ],
            6 => [
                'title' => 'Full Problem-Solving Roleplay',
                'total_minutes' => 45,
                'read' => 'Read a complete sample problem-solving dialogue aloud, from explaining the '
                    .'problem to agreeing on a solution.',
                'listen' => 'Listen to the sample dialogue audio twice.',
                'speak' => 'Roleplay a full problem from start to finish with your practice partner: explain '
                    .'the problem, ask for a solution, and agree on next steps, calmly and politely.',
                'write' => 'Write the full roleplay dialogue you just had, in 8 or more lines.',
                'vocab_intro' => 'No new words today — review any words from this week that you still find '
                    .'difficult.',
                'vocab' => [],
            ],
            7 => [
                'title' => 'Week 1 Review — Handling Problems Politely Milestone',
                'total_minutes' => 58,
                'read' => 'Re-read the example phrases from this week aloud.',
                'listen' => 'Watch the Day 1 billing-problem video again and notice what feels easier now.',
                'speak' => 'Raise a real or imagined problem with your practice partner and ask for a '
                    .'solution, without sounding rude, in an unscripted 3-minute conversation. Your practice '
                    .'partner confirms the Week 1 milestone.',
                'write' => 'Write the problem conversation you just had, from memory, in 8 or more lines.',
                'vocab_intro' => 'No new words this week — review any Money & Shopping, Travel, or '
                    .'Confidence words that came up while handling problems this week.',
                'vocab' => [],
            ],
            8 => [
                'title' => 'What Do You Think? Giving a Simple Opinion',
                'total_minutes' => 45,
                'read' => "Read aloud: 'I think this app is very useful because it saves time. What do you "
                    ."think about it?'",
                'listen' => 'Watch a short video of someone sharing a simple opinion with a reason.',
                'speak' => "Your practice partner asks 'What do you think about...?' for 3 everyday topics; "
                    ."answer each with 'I think... because...'.",
                'write' => 'Write 4 opinion sentences, each with a reason.',
                'vocab_intro' => 'No new words today — review the Technology & Phone words from Month 3 that '
                    .'might come up while sharing opinions about apps.',
                'vocab' => [],
            ],
            9 => [
                'title' => 'Agreeing and Disagreeing Politely',
                'total_minutes' => 48,
                'read' => "Read aloud: 'I agree with you, actually. I see your point, but I disagree a "
                    ."little. That's a fair point, however I think differently.'",
                'listen' => 'Watch a short video of two people agreeing and disagreeing politely in a '
                    .'conversation.',
                'speak' => 'Share an opinion with your practice partner; have them agree with one part and '
                    .'disagree with another, politely.',
                'write' => 'Write a 4-line dialogue where two people agree and disagree politely.',
                'vocab_intro' => 'No new words today — review the Making Plans & Invitations words from '
                    .'Month 3 (agree, disagree, suggestion, opinion).',
                'vocab' => [],
            ],
            10 => [
                'title' => 'Comparing Two Options and Giving Your Opinion',
                'total_minutes' => 45,
                'read' => "Read aloud: 'I prefer the smaller shop because it is closer. Between these two, I "
                    ."think the second one is better.'",
                'listen' => 'Watch a short video comparing two products or two options.',
                'speak' => 'Compare two options (two shops, two phones, two routes) with your practice '
                    .'partner and give your opinion on which is better.',
                'write' => 'Write 4 sentences comparing two options and giving your opinion.',
                'vocab_intro' => 'No new words today — review the Adjectives from Month 2 (cheap, expensive, '
                    .'important, different) that are useful for comparing options.',
                'vocab' => [],
            ],
            11 => [
                'title' => 'Opinions About Everyday Topics',
                'total_minutes' => 48,
                'read' => "Read aloud: 'In my opinion, the weather this year is stranger than usual. I think "
                    ."online shopping is very convenient.'",
                'listen' => 'Watch a short video of people discussing everyday topics like weather, food, or '
                    .'technology.',
                'speak' => 'Talk to your practice partner about your opinion on 3 everyday topics: the '
                    .'weather, food, and technology.',
                'write' => 'Write 5 sentences sharing your opinion on 3 different everyday topics.',
                'vocab_intro' => 'No new words today — review any Weather, Food, or Technology words from '
                    .'Months 2-3 that help you discuss these topics.',
                'vocab' => [],
            ],
            12 => [
                'title' => 'Giving Two Reasons for Your Opinion',
                'total_minutes' => 45,
                'read' => "Read aloud: 'I think this plan is good for two reasons. First, it saves money. "
                    ."Second, it saves time.'",
                'listen' => 'Watch a short video of someone giving two clear reasons for their opinion.',
                'speak' => "Give your practice partner an opinion with two reasons, using 'first' and "
                    ."'second' to organise your answer.",
                'write' => 'Write an opinion with two clearly organised reasons.',
                'vocab_intro' => 'No new words today — review the Connectors from Month 2 (first, next, '
                    .'because, so) that help you organise reasons.',
                'vocab' => [],
            ],
            13 => [
                'title' => 'Opinion Roleplay: A Group Discussion',
                'total_minutes' => 50,
                'read' => 'Read a short 3-person discussion transcript aloud, taking each part in turn.',
                'listen' => 'Listen to a natural group discussion where people share different opinions.',
                'speak' => 'Your practice partner asks follow-up questions after your opinion, like a group '
                    .'discussion; keep answering with reasons for 3 minutes.',
                'write' => 'Write the discussion you just had, from memory, in 8 or more lines.',
                'vocab_intro' => 'No new words today — review any opinion phrases from this week that you '
                    .'still find difficult.',
                'vocab' => [],
            ],
            14 => [
                'title' => 'Week 2 Review — Sharing Opinions Milestone',
                'total_minutes' => 58,
                'read' => 'Re-read the example opinion phrases from this week aloud.',
                'listen' => 'Watch the Day 8 opinion video again and notice what feels easier now.',
                'speak' => 'Share your opinion on 2 everyday topics with your practice partner, giving at '
                    .'least one reason for each, in an unscripted conversation. Your practice partner '
                    .'confirms the Week 2 milestone.',
                'write' => 'Write both opinions and their reasons, in a clean paragraph.',
                'vocab_intro' => 'No new words this week — review any opinion-related vocabulary you used '
                    .'this week.',
                'vocab' => [],
            ],
            15 => [
                'title' => 'Raising a Disagreement Calmly',
                'total_minutes' => 45,
                'read' => "Read aloud: 'I see it differently, if that's okay to say. I understand your "
                    ."point, but I don't fully agree.'",
                'listen' => 'Watch a short video of someone disagreeing calmly and respectfully.',
                'speak' => 'Disagree with an opinion your practice partner gives you, calmly and '
                    .'respectfully, without raising your voice.',
                'write' => 'Write 4 sentences you could use to disagree calmly.',
                'vocab_intro' => 'No new words today — review the Feelings & Emotions words from Month 2 '
                    .'(calm, frustrated, patient) that help during disagreements.',
                'vocab' => [],
            ],
            16 => [
                'title' => 'Handling a Complaint You Receive',
                'total_minutes' => 48,
                'read' => "Read aloud: 'I am sorry to hear that. Let me see what I can do. Thank you for "
                    ."telling me — I will fix it right away.'",
                'listen' => 'Watch a short video of someone responding well to a customer complaint.',
                'speak' => 'Your practice partner complains about something (late delivery, wrong item); '
                    .'respond calmly and offer to help.',
                'write' => 'Write a 5-line dialogue responding to a complaint.',
                'vocab_intro' => 'No new words today — review the Common Verbs 3 words from Month 4 '
                    .'(apologize, solve, ensure) that help when responding to complaints.',
                'vocab' => [],
            ],
            17 => [
                'title' => 'Making a Complaint Politely',
                'total_minutes' => 45,
                'read' => "Read aloud: 'I'm sorry to bother you, but I have a complaint. This is not what I "
                    ."ordered. I would like a replacement.'",
                'listen' => 'Watch a short video of a customer making a polite complaint.',
                'speak' => 'Make a polite complaint to your practice partner (playing a shopkeeper or '
                    .'customer service agent) about a real or imagined problem.',
                'write' => 'Write your complaint as a 5-line dialogue.',
                'vocab_intro' => 'No new words today — review the Money & Shopping words from Month 2 '
                    .'(receipt, total, change) useful for complaints.',
                'vocab' => [],
            ],
            18 => [
                'title' => 'Apologizing Sincerely',
                'total_minutes' => 48,
                'read' => "Read aloud: 'I am really sorry for the mistake. It won't happen again. Please "
                    ."forgive the delay — I should have told you sooner.'",
                'listen' => 'Watch a short video of someone apologizing sincerely for a mistake.',
                'speak' => 'Apologize to your practice partner for a real or imagined mistake, and explain '
                    .'what you will do differently.',
                'write' => 'Write a sincere 4-sentence apology for a mistake.',
                'vocab_intro' => 'No new words today — review the Common Verbs 3 words from Month 4 '
                    .'(apologize, forgive, trust) before today\'s practice.',
                'vocab' => [],
            ],
            19 => [
                'title' => 'Staying Calm When Someone Is Upset',
                'total_minutes' => 45,
                'read' => "Read aloud: 'I understand you are upset, and I want to help. Let's take a moment "
                    ."and find a solution together.'",
                'listen' => 'Watch a short video of someone staying calm while another person is upset or '
                    .'frustrated.',
                'speak' => 'Roleplay a situation where your practice partner is upset about something; stay '
                    .'calm, listen, and offer a solution.',
                'write' => 'Write 4 sentences you could say to calm down an upset conversation.',
                'vocab_intro' => 'No new words today — review the Feelings & Emotions words from Month 2 '
                    .'(frustrated, disappointed, calm) before today\'s roleplay.',
                'vocab' => [],
            ],
            20 => [
                'title' => 'Full Difficult-Conversation Roleplay',
                'total_minutes' => 48,
                'read' => 'Read a complete sample difficult-conversation transcript aloud — a complaint, a '
                    .'disagreement, and an apology.',
                'listen' => 'Listen to the sample conversation audio twice.',
                'speak' => 'Roleplay a full difficult conversation with your practice partner, including a '
                    .'complaint or disagreement and a sincere apology, staying calm throughout.',
                'write' => 'Write the full roleplay dialogue you just had, in 8 or more lines.',
                'vocab_intro' => 'No new words today — review any words from this week that you still find '
                    .'difficult.',
                'vocab' => [],
            ],
            21 => [
                'title' => 'Week 3 Review — Difficult Conversations Milestone',
                'total_minutes' => 58,
                'read' => 'Re-read the example phrases from this week aloud.',
                'listen' => 'Watch the Day 20 sample conversation again and notice what feels easier now.',
                'speak' => 'Handle a complaint or disagreement with your practice partner calmly, and '
                    .'apologise where needed, in an unscripted 3-minute conversation. Your practice partner '
                    .'confirms the Week 3 milestone.',
                'write' => 'Write the conversation you just had, from memory, in 8 or more lines.',
                'vocab_intro' => 'No new words this week — review any difficult-conversation phrases you '
                    .'used this week.',
                'vocab' => [],
            ],
            22 => [
                'title' => 'Reading Numbers Aloud Clearly',
                'total_minutes' => 50,
                'read' => 'Read these numbers aloud clearly: 15, 240, 1,050, 3,600, 72,000.',
                'listen' => 'Watch a short video of someone reading out large numbers clearly, one digit '
                    .'group at a time.',
                'speak' => 'Your practice partner reads 5 numbers aloud; repeat each one back correctly.',
                'write' => 'Write 5 numbers in words, then read them aloud one more time.',
                'vocab_intro' => 'No new words today — review the Numbers words from Month 1 (hundred, '
                    .'thousand, first, second) before today\'s practice.',
                'vocab' => [],
            ],
            23 => [
                'title' => 'Reading Dates Aloud Clearly',
                'total_minutes' => 50,
                'read' => 'Read these dates aloud clearly: 5th March, 12th July, 1st January, 30th '
                    .'September.',
                'listen' => 'Watch a short video of someone confirming an appointment date over the phone.',
                'speak' => 'Your practice partner says 5 dates aloud; repeat each one back correctly to '
                    .'confirm.',
                'write' => 'Write 5 dates in words, then read them aloud one more time.',
                'vocab_intro' => 'No new words today — review the Time, Days & Months words from Month 1 '
                    .'before today\'s practice.',
                'vocab' => [],
            ],
            24 => [
                'title' => 'Reading Phone Numbers Aloud',
                'total_minutes' => 45,
                'read' => 'Read this phone number aloud, digit by digit, with a short pause after every 3 or '
                    .'4 digits.',
                'listen' => 'Watch a short video of someone reading out a phone number clearly over a call.',
                'speak' => 'Your practice partner reads a phone number aloud; write it down, then read it '
                    .'back to confirm.',
                'write' => 'Write down 3 phone numbers (real or invented) in digits, then in words.',
                'vocab_intro' => 'No new words today — review the Technology & Phone words from Month 3 '
                    .'(call, contact, network) before today\'s practice.',
                'vocab' => [],
            ],
            25 => [
                'title' => 'Reading Addresses Aloud',
                'total_minutes' => 48,
                'read' => "Read this sample address aloud clearly: 'House number 24, Green Society, near "
                    ."the market, second floor.'",
                'listen' => 'Watch a short video of someone giving their home address clearly over the '
                    .'phone.',
                'speak' => 'Tell your practice partner your address clearly, then ask them to repeat it back '
                    .'to confirm.',
                'write' => 'Write your address in full sentences, exactly as you would say it aloud.',
                'vocab_intro' => 'No new words today — review the Travel & Commute and Home & Household '
                    .'words from Months 3-4 (near, far, society) before today\'s practice.',
                'vocab' => [],
            ],
            26 => [
                'title' => "Confirming Details: 'Did You Say...?'",
                'total_minutes' => 48,
                'read' => "Read these confirmation phrases aloud: 'Did you say 15 or 50?' 'Can you repeat "
                    ."that, please?' 'Just to confirm, that's...'.",
                'listen' => 'Watch a short video of two people confirming a detail that was hard to hear.',
                'speak' => 'Your practice partner says a number, date, or address slightly unclearly; ask '
                    .'them to confirm it using today\'s phrases.',
                'write' => 'Write 4 sentences you could use to confirm a detail you were not sure about.',
                'vocab_intro' => 'No new words today — review the Making Plans & Invitations words from '
                    .'Month 3 (confirm, remind) before today\'s practice.',
                'vocab' => [],
            ],
            27 => [
                'title' => 'Full Detail-Confirmation Drill',
                'total_minutes' => 45,
                'read' => 'Read a mixed list of numbers, dates, and an address aloud, without stopping.',
                'listen' => 'Listen to a recording of mixed numbers, dates, and an address, and write down '
                    .'what you hear.',
                'speak' => 'Your practice partner reads out 3 numbers, 2 dates, and 1 address in a row; '
                    .'repeat each one back to confirm, with zero mistakes.',
                'write' => 'Write everything your practice partner read out, checking it against what they '
                    .'said.',
                'vocab_intro' => 'No new words today — review any numbers, dates, or address phrases you '
                    .'still find difficult.',
                'vocab' => [],
            ],
            28 => [
                'title' => '3-Scenario Marathon Rehearsal',
                'total_minutes' => 53,
                'read' => 'Read through your notes from all 4 weeks of Month 5 before you begin.',
                'listen' => 'Listen to 3 short sample roleplay recordings back-to-back: a problem, an '
                    .'opinion, and a detail-confirmation.',
                'speak' => 'Run 3 Roleplay Scenarios back-to-back with your practice partner, with no '
                    .'script: a problem, a shared opinion, and a detail-confirmation drill.',
                'write' => "Write your personal '10 weak phrases' list from Month 5 in your notebook.",
                'vocab_intro' => 'No new words today — look back through this month and pick your personal '
                    .'10 hardest phrases to review.',
                'vocab' => [],
            ],
            29 => [
                'title' => 'Record & Review',
                'total_minutes' => 48,
                'read' => "Instead of reading something new, record yourself reading yesterday's writing "
                    .'aloud.',
                'listen' => 'Play back your Day 28 recording and listen for calmness, clarity, and '
                    .'politeness.',
                'speak' => 'Repeat the 3-scenario marathon, recorded on your phone, aiming for confident, '
                    .'unscripted answers.',
                'write' => 'Write one thing that has improved in how you handle problems, opinions, and '
                    .'details since the start of Month 5.',
                'vocab_intro' => 'No new words today — review all Month 5 phrases once more before '
                    .'tomorrow\'s milestone conversation.',
                'vocab' => [],
            ],
            30 => [
                'title' => 'Month 5 Milestone — Applied Communication',
                'minutes' => [
                    'reading' => 0,
                    'vocabulary' => 3,
                    'listening' => 0,
                    'speaking' => 40,
                    'writing' => 15,
                ],
                'read' => 'No reading exercise today — this is a performance day.',
                'listen' => 'No listening exercise today — this is a performance day.',
                'speak' => 'Complete 3 realistic, unscripted roleplays back-to-back with your practice '
                    .'partner: raise and solve a problem, share an opinion with a reason, and confirm a set '
                    .'of numbers, a date, and an address. This is the official Month 5 milestone — score it '
                    .'against the Month 5 Test rubric.',
                'write' => "Write a short reflection: 'How I now handle problems, opinions, and difficult "
                    ."conversations, compared to a month ago.'",
                'vocab_intro' => 'No new vocabulary this month — the full 500-word Word Bank has been '
                    .'reviewed and strengthened through application. This is a milestone day.',
                'vocab' => [],
            ],
        ];
    }
}
