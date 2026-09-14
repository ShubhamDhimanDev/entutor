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

class Month6DaySeeder extends Seeder
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
        $month = Month::where('month_number', 6)->first();

        if (! $month) {
            throw new RuntimeException('Month 6 not found — MonthSeeder must run before Month6DaySeeder.');
        }

        $weeks = Week::where('month_id', $month->id)->orderBy('week_number')->get();

        if ($weeks->count() !== 4) {
            throw new RuntimeException('Expected exactly 4 weeks for Month 6 before seeding days.');
        }

        // day_number is globally unique across the whole program (1-180), so
        // local day 1-30 within this month must be offset onto the global
        // range that MonthSeeder already assigned to Month 6's weeks.
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

        $this->assertMonth6Integrity($month);
    }

    /**
     * Assertion: exactly 30 Day rows and 150 DayTask rows (30 x 5) exist for
     * Month 6, and every day has exactly one task per SkillArea.
     */
    private function assertMonth6Integrity(Month $month): void
    {
        $dayCount = Day::where('month_id', $month->id)->count();
        if ($dayCount !== 30) {
            throw new RuntimeException("Expected 30 Day rows for Month 6, found {$dayCount}.");
        }

        $dayIds = Day::where('month_id', $month->id)->pluck('id');
        $dayTaskCount = DayTask::whereIn('day_id', $dayIds)->count();
        if ($dayTaskCount !== 150) {
            throw new RuntimeException("Expected 150 DayTask rows for Month 6, found {$dayTaskCount}.");
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
     * Month 6 — full day-by-day content (local days 1-30, offset to global
     * days 151-180). No new Word Bank vocabulary this month (per MonthSeeder's
     * vocabulary_target for Month 6) — every day's Vocabulary task points the
     * learner back to a specific, real area of earlier vocabulary to drill as
     * part of their personal "Top 30 Weak Words" list, rather than fabricating
     * a new list. Every day also includes a timed self-introduction and a
     * spontaneous question, per Month 6's daily_emphasis.
     *
     * @return array<int, array<string, mixed>>
     */
    private function days(): array
    {
        return [
            1 => [
                'title' => '60-Second Self-Introduction Drill',
                'total_minutes' => 45,
                'read' => 'Read a sample 60-second self-introduction aloud, timing yourself with a clock or '
                    .'phone.',
                'listen' => 'Listen to a model 60-second self-introduction recording.',
                'speak' => 'Deliver your own 60-second self-introduction to your practice partner, timed, '
                    .'aiming for smoothness rather than a memorised script. Then answer one spontaneous '
                    .'question they ask.',
                'write' => 'Write your 60-second self-introduction as a clean paragraph.',
                'vocab_intro' => 'No new words this month — today, review any self-introduction phrases you '
                    .'still hesitate on.',
                'vocab' => [],
            ],
            2 => [
                'title' => "Answering 'Tell Me About Yourself'",
                'total_minutes' => 45,
                'read' => "Read a sample answer to 'Tell me about yourself' aloud.",
                'listen' => "Listen to a model answer to 'Tell me about yourself'.",
                'speak' => "Your practice partner asks 'Tell me about yourself'; answer smoothly in under 90 "
                    .'seconds. Then answer one spontaneous follow-up question.',
                'write' => "Write your own answer to 'Tell me about yourself' in 6-8 sentences.",
                'vocab_intro' => 'No new words today — review personal-question vocabulary from Month 1 '
                    .'(name, age, city, family) that answers this question.',
                'vocab' => [],
            ],
            3 => [
                'title' => 'Answering Common Personal Questions Smoothly',
                'total_minutes' => 48,
                'read' => 'Read a list of 10 common personal questions aloud.',
                'listen' => 'Listen to someone answering personal questions smoothly and quickly.',
                'speak' => 'Your practice partner asks 8 of the 10 questions in random order; answer each '
                    .'one smoothly without long pauses. Then answer one spontaneous question.',
                'write' => 'Write answers to any 3 questions you found hardest to answer quickly.',
                'vocab_intro' => 'No new words today — review the Question Words from Month 1 (what, where, '
                    .'when, who, why, how) before today\'s practice.',
                'vocab' => [],
            ],
            4 => [
                'title' => 'Introducing Yourself on a Phone Call',
                'total_minutes' => 45,
                'read' => "Read a sample phone self-introduction aloud: 'Hello, this is ___ speaking. Nice "
                    ."to talk to you.'",
                'listen' => 'Listen to a model phone introduction and small talk opening.',
                'speak' => 'Your practice partner calls you; introduce yourself confidently over the phone '
                    .'and make 1 minute of small talk. Then answer one spontaneous question.',
                'write' => 'Write your phone self-introduction as a short script, then read it aloud once '
                    .'without looking.',
                'vocab_intro' => 'No new words today — review the Technology & Phone words from Month 3 '
                    .'(call, contact) that are useful on a phone introduction.',
                'vocab' => [],
            ],
            5 => [
                'title' => 'Introducing Yourself to a Group',
                'total_minutes' => 48,
                'read' => 'Read a sample group self-introduction aloud, slightly more formal than a '
                    .'one-to-one introduction.',
                'listen' => 'Listen to someone introducing themselves to a small group.',
                'speak' => 'Introduce yourself to your practice partner as if meeting a small group for the '
                    .'first time, speaking a little louder and slower than usual. Then answer one spontaneous '
                    .'question.',
                'write' => 'Write your group self-introduction, adjusting the tone to be slightly more '
                    .'formal.',
                'vocab_intro' => 'No new words today — review the Confidence & Social Words from Month 4 '
                    .'(sincere, confident, respect) that suit a group introduction.',
                'vocab' => [],
            ],
            6 => [
                'title' => 'Spontaneous Questions After Your Introduction',
                'total_minutes' => 45,
                'read' => 'Read 8 example spontaneous follow-up questions someone might ask after an '
                    .'introduction.',
                'listen' => 'Listen to a conversation where someone answers spontaneous follow-up questions '
                    .'confidently.',
                'speak' => 'Give your 60-second introduction, then let your practice partner ask 3 '
                    .'spontaneous follow-up questions in a row; answer each one without preparing.',
                'write' => 'Write down the 3 follow-up questions you were asked and your answers.',
                'vocab_intro' => 'No new words today — review any words you hesitated on while answering '
                    .'spontaneous questions this week.',
                'vocab' => [],
            ],
            7 => [
                'title' => 'Week 1 Review — Self-Introduction Mastery Milestone',
                'total_minutes' => 58,
                'read' => 'Re-read your written self-introduction from Day 1 aloud, and notice what you '
                    .'would change now.',
                'listen' => 'Watch the Day 1 model self-introduction again and compare it with your own.',
                'speak' => 'Deliver a confident, unscripted self-introduction to your practice partner and '
                    .'answer 3 spontaneous personal questions smoothly. Your practice partner confirms the '
                    .'Week 1 milestone.',
                'write' => 'Rewrite your self-introduction, improving it based on what you learned this '
                    .'week.',
                'vocab_intro' => 'No new words this week — review any self-introduction or personal-question '
                    .'vocabulary you still hesitate on.',
                'vocab' => [],
            ],
            8 => [
                'title' => 'Talking About Your Family Comfortably',
                'total_minutes' => 45,
                'read' => "Read a sample paragraph about someone's family aloud, at natural speed.",
                'listen' => 'Listen to someone talking comfortably about their family for 1 minute.',
                'speak' => 'Talk about your family to your practice partner for 1 minute without stopping. '
                    .'Then answer one spontaneous question about a family member.',
                'write' => 'Write a smooth 6-sentence paragraph about your family.',
                'vocab_intro' => 'No new words today — review the Family & People words from Month 1 before '
                    .'today\'s practice.',
                'vocab' => [],
            ],
            9 => [
                'title' => 'Talking About Your Daily Routine Comfortably',
                'total_minutes' => 45,
                'read' => 'Read a sample paragraph about a daily routine aloud, at natural speed.',
                'listen' => 'Listen to someone describing their daily routine comfortably and quickly.',
                'speak' => 'Describe your daily routine to your practice partner for 1 minute without '
                    .'stopping. Then answer one spontaneous question about your day.',
                'write' => 'Write a smooth 6-sentence paragraph about your daily routine.',
                'vocab_intro' => 'No new words today — review the Daily Routine and Time Expression words '
                    .'from Month 2 before today\'s practice.',
                'vocab' => [],
            ],
            10 => [
                'title' => 'Talking About Your Likes and Dislikes',
                'total_minutes' => 48,
                'read' => 'Read a sample paragraph about likes and dislikes aloud, at natural speed.',
                'listen' => 'Listen to someone talking comfortably about what they like and dislike.',
                'speak' => 'Tell your practice partner 3 things you like and 2 things you dislike, with '
                    .'reasons, for 1 minute without stopping. Then answer one spontaneous question.',
                'write' => 'Write a smooth 5-sentence paragraph about your likes and dislikes.',
                'vocab_intro' => 'No new words today — review the Feelings & Emotions words from Month 2 '
                    .'before today\'s practice.',
                'vocab' => [],
            ],
            11 => [
                'title' => 'Talking About Your Hobbies and Interests',
                'total_minutes' => 45,
                'read' => 'Read a sample paragraph about hobbies and interests aloud, at natural speed.',
                'listen' => 'Listen to someone talking comfortably about their hobbies.',
                'speak' => 'Talk about your hobbies and interests to your practice partner for 1 minute '
                    .'without stopping. Then answer one spontaneous question about a hobby.',
                'write' => 'Write a smooth 5-sentence paragraph about your hobbies and interests.',
                'vocab_intro' => 'No new words today — review the Confidence & Social Words from Month 4 '
                    .'(hobby, interest, experience) before today\'s practice.',
                'vocab' => [],
            ],
            12 => [
                'title' => 'Talking About Your Work or Studies',
                'total_minutes' => 48,
                'read' => 'Read a sample paragraph about work or studies aloud, at natural speed.',
                'listen' => 'Listen to someone talking comfortably about their work or studies.',
                'speak' => 'Talk about your work or studies to your practice partner for 1 minute without '
                    .'stopping. Then answer one spontaneous question about your work or studies.',
                'write' => 'Write a smooth 5-sentence paragraph about your work or studies.',
                'vocab_intro' => 'No new words today — review any work- or study-related vocabulary you have '
                    .'used across the program.',
                'vocab' => [],
            ],
            13 => [
                'title' => 'Mixed Everyday-Topics Q&A',
                'total_minutes' => 50,
                'read' => 'Read a mixed list of 10 everyday-topic questions (family, routine, likes, '
                    .'hobbies, work) aloud.',
                'listen' => 'Listen to someone answering mixed everyday-topic questions smoothly, one after '
                    .'another.',
                'speak' => 'Your practice partner asks questions from all 5 everyday topics, mixed up, one '
                    .'after another; answer each smoothly for 3 minutes total.',
                'write' => "Write your 3 favourite answers from today's practice.",
                'vocab_intro' => 'No new words today — review any everyday-topic vocabulary from this week '
                    .'that you still hesitate on.',
                'vocab' => [],
            ],
            14 => [
                'title' => 'Week 2 Review — Everyday Topics Milestone',
                'total_minutes' => 58,
                'read' => 'Re-read your favourite paragraph from this week aloud.',
                'listen' => "Watch any one of this week's model videos again and compare it with your own "
                    .'speaking.',
                'speak' => 'Talk comfortably with your practice partner about family, routine, likes, and '
                    .'interests in one unscripted 4-minute conversation, moving naturally between topics. '
                    .'Your practice partner confirms the Week 2 milestone.',
                'write' => 'Write the conversation you just had, from memory, in 8 or more lines.',
                'vocab_intro' => 'No new words this week — review any everyday-topic vocabulary you still '
                    .'hesitate on.',
                'vocab' => [],
            ],
            15 => [
                'title' => 'Full Conversation With a Stranger: Warm-Up',
                'total_minutes' => 45,
                'read' => "Read a sample opening for a conversation with someone new: 'Hi, I don't think "
                    ."we've met. I'm ___.'",
                'listen' => 'Listen to a full unscripted conversation between two people meeting for the '
                    .'first time.',
                'speak' => 'Your practice partner plays a stranger; start a full conversation from greeting '
                    .'to a personal topic, for 3 minutes, without a script.',
                'write' => 'Write the conversation you just had, from memory.',
                'vocab_intro' => 'No new words today — review any greeting or small-talk vocabulary from '
                    .'Months 1 and 3.',
                'vocab' => [],
            ],
            16 => [
                'title' => 'Full Conversation: Small Talk to Personal Questions',
                'total_minutes' => 48,
                'read' => 'Read a sample conversation that moves from small talk into personal questions.',
                'listen' => 'Listen to a full unscripted conversation that moves from small talk to personal '
                    .'topics.',
                'speak' => 'Have a full conversation with your practice partner that starts with small talk '
                    .'and moves naturally into personal questions, for 4 minutes.',
                'write' => 'Write the conversation you just had, from memory.',
                'vocab_intro' => 'No new words today — review any personal-question vocabulary you still '
                    .'hesitate on.',
                'vocab' => [],
            ],
            17 => [
                'title' => 'Full Conversation: Handling an Unexpected Topic',
                'total_minutes' => 45,
                'read' => "Read example responses to an unexpected change of topic: 'That's an interesting "
                    ."question — let me think.'",
                'listen' => 'Listen to a conversation where someone handles a sudden change of topic '
                    .'smoothly.',
                'speak' => 'Have a full conversation with your practice partner; they should suddenly change '
                    .'the topic at least once. Handle it smoothly without switching to Hindi.',
                'write' => 'Write down the unexpected topic and how you responded to it.',
                'vocab_intro' => 'No new words today — review the filler phrases from Month 1 (let me think, '
                    .'actually, I mean) that help with unexpected topics.',
                'vocab' => [],
            ],
            18 => [
                'title' => "AI Conversation Practice: A New 'Stranger' Each Time",
                'total_minutes' => 48,
                'read' => "Read the instructions for today's AI Conversation Practice prompt before you "
                    .'begin.',
                'listen' => 'Listen back to any previous AI conversation recording you have, if available.',
                'speak' => 'Have a full unscripted conversation using the AI Practice Prompts, treating the '
                    .'AI like a new stranger you have just met, for 4 minutes.',
                'write' => 'Write one thing that felt different about talking to the AI compared to your '
                    .'practice partner.',
                'vocab_intro' => 'No new words today — review any vocabulary that came up unexpectedly '
                    .'during your AI conversation.',
                'vocab' => [],
            ],
            19 => [
                'title' => 'Record a Full Conversation',
                'total_minutes' => 45,
                'read' => 'Read your notes from the past 3 days before you begin, without memorising '
                    .'anything.',
                'listen' => 'Listen to a model 5-minute conversation on a familiar topic.',
                'speak' => 'Record a full 5-minute unscripted conversation with your practice partner on a '
                    .'familiar topic, without switching to Hindi.',
                'write' => 'Write 3 things you noticed while speaking, to check when you review the '
                    .'recording tomorrow.',
                'vocab_intro' => 'No new words today — review any words you noticed yourself struggling '
                    .'with while recording.',
                'vocab' => [],
            ],
            20 => [
                'title' => 'Review Your Recording & Fix One Habit',
                'total_minutes' => 48,
                'read' => 'Instead of reading something new, read the notes you wrote yesterday about your '
                    .'recording.',
                'listen' => 'Play back your Day 19 recording fully and listen for one habit you would like '
                    .'to improve.',
                'speak' => "Repeat a 2-minute section of yesterday's conversation with your practice "
                    .'partner, correcting the one habit you identified.',
                'write' => 'Write the one habit you are fixing, and one sentence showing the improved '
                    .'version.',
                'vocab_intro' => 'No new words today — review any words connected to the habit you are '
                    .'fixing.',
                'vocab' => [],
            ],
            21 => [
                'title' => 'Week 3 Review — Full Conversations Milestone',
                'total_minutes' => 58,
                'read' => 'Re-read your Day 19 notes aloud.',
                'listen' => 'Watch the Day 15 stranger-conversation video again and compare it with your own '
                    .'progress.',
                'speak' => 'Complete a full unscripted conversation with your practice partner, recorded, '
                    .'covering at least 3 different everyday topics without switching to Hindi. Your practice '
                    .'partner confirms the Week 3 milestone.',
                'write' => 'Write a short review of your recorded conversation: what went well, and what '
                    .'you want to improve.',
                'vocab_intro' => 'No new words this week — review any vocabulary that came up across this '
                    ."week's full conversations.",
                'vocab' => [],
            ],
            22 => [
                'title' => 'Identify Your Top 30 Weak Words',
                'total_minutes' => 50,
                'read' => 'Look back through your notebook from Months 1-5 and read any words you have '
                    .'marked as difficult.',
                'listen' => 'No specific listening clip today — instead, listen back to any earlier '
                    .'recording and note unclear words.',
                'speak' => 'Tell your practice partner your Top 30 Weak Words list and use 5 of them in '
                    .'sentences aloud.',
                'write' => 'Write your full Top 30 Weak Words list in your notebook.',
                'vocab_intro' => 'No new words this month — today, build your personal Top 30 Weak Words '
                    .'list from across the whole program.',
                'vocab' => [],
            ],
            23 => [
                'title' => 'Drill Your Weak Words: Set 1',
                'total_minutes' => 50,
                'read' => 'Read the first 15 words from your Top 30 Weak Words list aloud, three times each.',
                'listen' => 'If possible, listen to each weak word pronounced correctly using an online '
                    .'dictionary or AI voice.',
                'speak' => 'Use each of your first 15 weak words in a spoken sentence with your practice '
                    .'partner.',
                'write' => 'Write one sentence for each of your first 15 weak words.',
                'vocab_intro' => 'No new words today — drill the first 15 words from your personal Top 30 '
                    .'Weak Words list.',
                'vocab' => [],
            ],
            24 => [
                'title' => 'Drill Your Weak Words: Set 2',
                'total_minutes' => 45,
                'read' => 'Read the second 15 words from your Top 30 Weak Words list aloud, three times '
                    .'each.',
                'listen' => 'If possible, listen to each weak word pronounced correctly using an online '
                    .'dictionary or AI voice.',
                'speak' => 'Use each of your second 15 weak words in a spoken sentence with your practice '
                    .'partner.',
                'write' => 'Write one sentence for each of your second 15 weak words.',
                'vocab_intro' => 'No new words today — drill the second 15 words from your personal Top 30 '
                    .'Weak Words list.',
                'vocab' => [],
            ],
            25 => [
                'title' => 'Speaking at Natural Speed',
                'total_minutes' => 48,
                'read' => 'Read a familiar paragraph aloud, first slowly, then again at natural '
                    .'conversational speed.',
                'listen' => "Listen to a fluent speaker's natural conversational speed and compare it with "
                    .'your own.',
                'speak' => 'Have a 2-minute conversation with your practice partner, aiming to match a '
                    .'natural, unhurried conversational speed rather than speaking too fast or too slow.',
                'write' => 'Write one sentence about how your speaking speed has changed since Month 1.',
                'vocab_intro' => 'No new words today — review any words you tend to rush or slow down on '
                    .'unnecessarily.',
                'vocab' => [],
            ],
            26 => [
                'title' => 'Speaking with Natural Volume and Clarity',
                'total_minutes' => 45,
                'read' => 'Read a paragraph aloud at a slightly louder volume than feels comfortable, '
                    .'focusing on clear pronunciation.',
                'listen' => "Listen to a clip and notice the speaker's volume and clarity.",
                'speak' => 'Have a 2-minute conversation with your practice partner at a clear, confident '
                    .'volume, without mumbling.',
                'write' => 'Write one sentence about how your clarity has improved since Month 1.',
                'vocab_intro' => 'No new words today — review any words you tend to mumble or pronounce '
                    .'unclearly.',
                'vocab' => [],
            ],
            27 => [
                'title' => 'Confidence Under Pressure: Rapid-Fire Questions',
                'total_minutes' => 48,
                'read' => 'Read 10 example rapid-fire personal questions aloud.',
                'listen' => 'Listen to someone answering rapid-fire questions confidently, one after '
                    .'another.',
                'speak' => 'Your practice partner asks 10 rapid-fire questions from across the whole program '
                    .'with almost no pause between them; answer each one as quickly and smoothly as you can.',
                'write' => 'Write down any 2 questions that were hardest to answer quickly, and a better '
                    .'answer for each.',
                'vocab_intro' => 'No new words today — review any vocabulary from the rapid-fire questions '
                    .'that slowed you down.',
                'vocab' => [],
            ],
            28 => [
                'title' => 'Full Dress Rehearsal',
                'total_minutes' => 53,
                'read' => 'Read through your Top 30 Weak Words list one final time before you begin.',
                'listen' => 'Listen to a model 5-minute natural conversation one final time.',
                'speak' => 'Rehearse a full 5-minute unscripted conversation with your practice partner, '
                    .'covering an introduction, an everyday topic, an opinion, and a spontaneous question, at '
                    .'natural speed and volume.',
                'write' => 'Write a short checklist of the 3 things you most want to remember tomorrow.',
                'vocab_intro' => 'No new words today — review your Top 30 Weak Words list one final time.',
                'vocab' => [],
            ],
            29 => [
                'title' => 'Record & Final Review',
                'total_minutes' => 48,
                'read' => 'Instead of reading something new, read your Day 28 checklist aloud.',
                'listen' => 'Play back your Day 28 rehearsal recording and check it against your checklist.',
                'speak' => 'Repeat the full 5-minute conversation, recorded on your phone, applying the 3 '
                    .'things from your checklist.',
                'write' => 'Write one final reflection: how your English has changed from Day 1 of the '
                    .'program to today.',
                'vocab_intro' => 'No new words today — review your Top 30 Weak Words list one last time '
                    .'before the final milestone.',
                'vocab' => [],
            ],
            30 => [
                'title' => 'Month 6 & Program Milestone — The Final Conversation',
                'minutes' => [
                    'reading' => 0,
                    'vocabulary' => 3,
                    'listening' => 0,
                    'speaking' => 40,
                    'writing' => 15,
                ],
                'read' => 'No reading exercise today — this is a performance day.',
                'listen' => 'No listening exercise today — this is a performance day.',
                'speak' => 'Hold a full, unscripted 5-minute conversation with your practice partner (or a '
                    .'new person, if possible) covering your introduction, your daily life, an opinion, and '
                    .'at least one spontaneous question, without switching to Hindi. This is the official '
                    .'Month 6 and full-program milestone — score it against the Month 6 Test rubric.',
                'write' => "Write a final reflection: 'What I could not say on Day 1, and what I can say "
                    ."now, 180 days later.'",
                'vocab_intro' => 'The full 500-word Word Bank has been reviewed and mastered. No new words '
                    .'today; this is the final milestone day of the program.',
                'vocab' => [],
            ],
        ];
    }
}
