<?php

namespace Database\Seeders;

use App\Models\Month;
use App\Models\Week;
use Illuminate\Database\Seeder;
use RuntimeException;

class MonthSeeder extends Seeder
{
    /**
     * The standing daily-task rhythm, shared by every month (only the
     * month-specific emphasis sentences below change).
     */
    private const DAILY_RHYTHM = 'Each day follows the standard five-task rhythm (about 45-60 minutes '
        .'total): Read a short passage or dialogue aloud, then Vocabulary — learn new words, say each aloud, '
        .'and use one in a sentence — then Listen to a short audio or video clip on the day\'s topic and shadow '
        .'it, then Speak as the main practice block with a practice partner or an AI roleplay, and finish with '
        .'Write — a few sentences or a diary line.';

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $months = $this->months();

        $seenWeekNumbers = [];
        $nextWeekNumber = 1;

        foreach ($months as $monthData) {
            $month = Month::create([
                'month_number' => $monthData['month_number'],
                'theme' => $monthData['theme'],
                'goals' => $monthData['goals'],
                'expected_outcomes' => $monthData['expected_outcomes'],
                'daily_emphasis' => self::DAILY_RHYTHM.' '.$monthData['daily_emphasis'],
                'speaking_practice_ideas' => $monthData['speaking_practice_ideas'],
                'reading_materials_ideas' => $monthData['reading_materials_ideas'],
                'vocabulary_target' => $monthData['vocabulary_target'],
            ]);

            $monthStartDay = ($monthData['month_number'] - 1) * 30 + 1;
            $dayCursor = $monthStartDay;
            $createdWeeks = [];

            foreach ($monthData['weeks'] as $index => $weekData) {
                $weekInMonth = $index + 1;
                $length = $weekInMonth === 4 ? 9 : 7;
                $startDay = $dayCursor;
                $endDay = $dayCursor + $length - 1;
                $dayCursor = $endDay + 1;

                $weekNumber = $nextWeekNumber++;

                if (isset($seenWeekNumbers[$weekNumber])) {
                    throw new RuntimeException("Duplicate week_number {$weekNumber} generated.");
                }
                $seenWeekNumbers[$weekNumber] = true;

                $createdWeeks[] = Week::create([
                    'month_id' => $month->id,
                    'week_number' => $weekNumber,
                    'week_in_month' => $weekInMonth,
                    'start_day_number' => $startDay,
                    'end_day_number' => $endDay,
                    'title' => $weekData['title'],
                    'mastery_description' => $weekData['mastery_description'],
                ]);
            }

            // Assertion: this month's 4 weeks must partition days 1-30 of the
            // month with no gaps or overlaps.
            $expectedDay = $monthStartDay;
            foreach ($createdWeeks as $week) {
                if ($week->start_day_number !== $expectedDay) {
                    throw new RuntimeException(
                        "Month {$monthData['month_number']}: week {$week->week_number} starts at day ".
                        "{$week->start_day_number}, expected {$expectedDay}."
                    );
                }
                $expectedDay = $week->end_day_number + 1;
            }
            $monthEndDay = $monthStartDay + 29;
            if ($expectedDay !== $monthEndDay + 1) {
                throw new RuntimeException(
                    "Month {$monthData['month_number']}: weeks do not fully cover days ".
                    "{$monthStartDay}-{$monthEndDay}."
                );
            }
        }

        // Assertion: week_number must run 1-24 with no duplicates or gaps
        // across the whole seeder.
        if (array_keys($seenWeekNumbers) !== range(1, 24)) {
            throw new RuntimeException(
                'Week numbers must run 1-24 with no duplicates or gaps across the whole seeder.'
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function months(): array
    {
        return [
            [
                'month_number' => 1,
                'theme' => 'Foundation & Confidence',
                'goals' => [
                    'Speak out loud in English every day without fear of mistakes.',
                    'Learn 150 foundation words (Word Bank Groups 1-6) and use them actively.',
                    'Master greetings, numbers, time, days/months, and simple questions.',
                    'Build the daily 45-60 minute habit — same time, same place.',
                ],
                'expected_outcomes' => [
                    'Introduce yourself in 6-8 sentences without preparing first.',
                    'Ask and answer 20 basic personal questions confidently.',
                    'Tell the time, date, and count without translating in your head.',
                    'Read a 40-60 word simple paragraph aloud without stopping.',
                ],
                'daily_emphasis' => 'This month, reading starts with alphabet sounds and single words in '
                    .'Weeks 1-2, moving to 3-line dialogues in Weeks 3-4. Listening audio should be slow, '
                    .'clear, and beginner-level. Every speaking block is done with a practice partner first — '
                    .'the mirror and AI come second this month.',
                'speaking_practice_ideas' => [
                    'Mirror Minute — talk to yourself for 60 seconds using only known words.',
                    'Q&A Ping-Pong — your practice partner asks 10 simple questions; answer only in full sentences.',
                    'Echo & Repeat — repeat each sentence of a slow audio clip, matching its rhythm.',
                    'Five Introductions — introduce yourself 5 different ways: formal, informal, and on the phone.',
                ],
                'reading_materials_ideas' => [
                    'Graded Level-1 stories ("The Thirsty Crow", "The Lion and the Mouse") — or ask an AI to '
                        .'generate a fresh 50-word story any day.',
                    'Self-written flash cards: one word, one picture in your head, one sentence.',
                    'A printed numbers/date/time chart, read aloud daily.',
                ],
                'vocabulary_target' => '150 words — Word Bank Groups 1-6 (Greetings & Politeness, Numbers, '
                    .'Time/Days/Months, Family & People, Common Verbs 1, Question Words & Basics). About 5 new '
                    .'words a day.',
                'weeks' => [
                    [
                        'title' => 'Sounds & Greetings',
                        'mastery_description' => 'You are comfortable with English sounds, greetings, and a '
                            .'4-line self-introduction.',
                    ],
                    [
                        'title' => 'Numbers, Time & Family',
                        'mastery_description' => 'You talk about numbers, clock time, dates, and family '
                            .'members without hesitation.',
                    ],
                    [
                        'title' => 'Daily Actions',
                        'mastery_description' => 'You use simple present-tense action sentences (I go, I eat, '
                            .'I work) naturally.',
                    ],
                    [
                        'title' => 'First Real Conversation',
                        'mastery_description' => 'You hold an unscripted 2-minute conversation about yourself '
                            .'and your day.',
                    ],
                ],
            ],
            [
                'month_number' => 2,
                'theme' => 'Everyday Fluency',
                'goals' => [
                    'Grow active vocabulary to 300 words (add Word Bank Groups 7-12).',
                    'Handle everyday situations: shopping, food, feelings, and small talk.',
                    'Use simple past and future patterns without grammar drilling — by pattern, not rule.',
                    'Understand normal-speed simple conversations, not just slow audio.',
                ],
                'expected_outcomes' => [
                    'Describe your day in the past, present, and future forms.',
                    'Shop, order food, and ask for directions in English.',
                    'Follow a short, simple video or voice note without subtitles.',
                    'Write a 5-6 line diary entry unaided.',
                ],
                'daily_emphasis' => 'Reading shifts to short blog-style paragraphs and everyday dialogues at '
                    .'the market, a tea stall, or at home. Listening moves to normal-speed simple vlogs or AI '
                    .'voice replies. Speaking practice adds roleplay — the practice partner plays different '
                    .'characters (shopkeeper, neighbour, friend) instead of just asking questions.',
                'speaking_practice_ideas' => [
                    'Market Roleplay — your practice partner is the shopkeeper; buy 3 items using English only.',
                    'Yesterday-Today-Tomorrow — describe one activity in all three tenses, daily.',
                    'Describe & Guess — describe an object or person; your practice partner guesses what it is.',
                    'Voice-Note Diary — send yourself a 60-second spoken diary entry.',
                ],
                'reading_materials_ideas' => [
                    'Simple "day in the life" blog paragraphs (ask an AI for a 60-word one on any topic).',
                    'Everyday dialogues: at a market, a tea stall, or on the phone with a friend.',
                    'Simplified message-forward style texts, rewritten in easy English.',
                ],
                'vocabulary_target' => '150 more words (300 total) — Word Bank Groups 7-12 (Adjectives, Food '
                    .'& Drink, Money & Shopping, Feelings & Emotions, Common Verbs 2, Connectors & Small Words).',
                'weeks' => [
                    [
                        'title' => 'Daily Routine & Time Expressions',
                        'mastery_description' => 'You narrate your routine using "usually", "every day", and '
                            .'"at 7 o\'clock".',
                    ],
                    [
                        'title' => 'Shopping & Money Talk',
                        'mastery_description' => 'You ask prices, bargain, and complete a shopping conversation '
                            .'in English.',
                    ],
                    [
                        'title' => 'Food, Feelings & Small Talk',
                        'mastery_description' => 'You order food and make 2 minutes of comfortable small talk.',
                    ],
                    [
                        'title' => 'Past & Future',
                        'mastery_description' => 'You tell what you did yesterday and your plan for tomorrow, '
                            .'correctly enough to be understood.',
                    ],
                ],
            ],
            [
                'month_number' => 3,
                'theme' => 'Real-Life Conversations',
                'goals' => [
                    'Speak comfortably in real situations — small talk, phone calls, and polite requests.',
                    'Learn 125 new words (Word Bank Groups 13-17).',
                    'Handle a phone call: answer, take a message, and ask for repetition politely.',
                    'Write and read a short, friendly message asking for help or information.',
                ],
                'expected_outcomes' => [
                    'Make small talk with a neighbour or shopkeeper ("How\'s the weather today?").',
                    'Answer a phone call and take down a message correctly.',
                    'Write a clean 5-line message (greeting, body, closing) asking for help or information.',
                    'Understand and repeat back a 3-step spoken instruction.',
                ],
                'daily_emphasis' => 'Reading materials become real-life texts: messages, notices, and simple '
                    .'instructions. Listening moves to real-world audio — a doctor giving advice, a friend '
                    .'making plans, or an AI voice acting as a stranger. Writing becomes the most important '
                    .'block this month.',
                'speaking_practice_ideas' => [
                    'Phone Call Simulation — your practice partner calls your phone; answer naturally.',
                    'Instruction Relay — your practice partner gives a 3-step instruction (a recipe, a '
                        .'direction); repeat it back, then "do" it.',
                    'Message-Taking Drill — your practice partner leaves a spoken message; write it down, '
                        .'then read it back.',
                    'Everyday Small Talk — 2 minutes of casual chat before every practice session.',
                ],
                'reading_materials_ideas' => [
                    'Sample short friendly messages (asking for help, a thank-you note, an update to family).',
                    'Notice-board style announcements (society, clinic, market).',
                    'A simple paragraph from a health pamphlet or public notice.',
                ],
                'vocabulary_target' => '125 more words (425 total) — Word Bank Groups 13-17 (Health & Body, '
                    .'Technology & Phone, Weather & Seasons, Making Plans & Invitations, Travel & Commute).',
                'weeks' => [
                    [
                        'title' => 'Everyday Small Talk & Politeness',
                        'mastery_description' => 'You greet neighbours, introduce yourself to someone new, '
                            .'and make light conversation.',
                    ],
                    [
                        'title' => 'Phone Calls',
                        'mastery_description' => 'You answer a call clearly, spell your name and details, and '
                            .'end a call politely.',
                    ],
                    [
                        'title' => 'Health & Everyday Help',
                        'mastery_description' => 'You describe a symptom or problem, ask for help, and '
                            .'understand simple advice.',
                    ],
                    [
                        'title' => 'Plans & Instructions',
                        'mastery_description' => 'You make and accept a plan, follow spoken instructions, and '
                            .'ask for clarification.',
                    ],
                ],
            ],
            [
                'month_number' => 4,
                'theme' => 'Vocabulary Expansion',
                'goals' => [
                    'Use technology and money/banking words confidently in daily conversation.',
                    'Finish the Word Bank: the last 75 words (Groups 18-20).',
                    'Explain a full everyday task out loud, step by step, in your own words.',
                    'Read and understand everyday messages (bank SMS, app notifications, weather updates) '
                        .'without help.',
                ],
                'expected_outcomes' => [
                    'Explain how to use a phone app or website, step by step.',
                    'Handle a simple banking or payment conversation (asking about a balance, a transaction).',
                    'Read a weather update or bank SMS and summarise it in your own words.',
                    'Word Bank complete — 500/500 words introduced.',
                ],
                'daily_emphasis' => 'This is the vocabulary-expansion month: every reading and listening '
                    .'exercise is real-life flavoured, from bank messages and weather updates to home-repair '
                    .'conversations. Speaking practice becomes role-based — you play a customer or resident '
                    .'while the practice partner plays a bank clerk, shopkeeper, or neighbour.',
                'speaking_practice_ideas' => [
                    'Explain-the-Process Challenge — explain "how I pay an electricity bill" or "how I book '
                        .'a ticket" in 10 sentences.',
                    'Bank/Shop Visit Roleplay — your practice partner plays a bank clerk or shopkeeper; ask '
                        .'a question and understand the answer.',
                    'Word Speed-Round — your practice partner shows or says a word; use it in a sentence in '
                        .'under 10 seconds.',
                    'Home Problem Roleplay — describe a home problem (a leaking tap, no electricity) to a '
                        .'repair person.',
                ],
                'reading_materials_ideas' => [
                    'A sample bank SMS or app notification.',
                    'A sample weather forecast or weather app screen.',
                    'A short, simplified home-maintenance or utility-bill notice.',
                ],
                'vocabulary_target' => '75 remaining Word Bank words (500 total, complete) — Groups 18-20 '
                    .'(Home & Household, Common Verbs 3, Confidence & Social Words).',
                'weeks' => [
                    [
                        'title' => 'Technology & Everyday Apps',
                        'mastery_description' => 'You use phone, internet, and app-related words in full '
                            .'sentences.',
                    ],
                    [
                        'title' => 'Money & Banking',
                        'mastery_description' => 'You explain a payment, a bill, or a bank visit clearly.',
                    ],
                    [
                        'title' => 'Weather & Home Talk',
                        'mastery_description' => 'You talk about weather, household chores, and home problems.',
                    ],
                    [
                        'title' => 'Explain the Process',
                        'mastery_description' => 'You narrate a full everyday task (cooking, banking, travel '
                            .'booking) in 10 or more connected sentences.',
                    ],
                ],
            ],
            [
                'month_number' => 5,
                'theme' => 'Applied Communication',
                'goals' => [
                    'Handle realistic everyday conversations confidently, including disagreements and '
                        .'complaints.',
                    'Share simple opinions and give reasons in English.',
                    'Practise reading numbers, dates, and addresses aloud, accurately.',
                    'Combine everything learned so far into full, realistic scenarios.',
                ],
                'expected_outcomes' => [
                    'Explain a simple problem (a wrong bill, a delayed delivery, a mistake) calmly and clearly.',
                    'Share an opinion and give one or two reasons ("I think... because...").',
                    'Read back numbers, dates, and addresses with zero repeated mistakes.',
                    'Complete 3 realistic everyday roleplays with confidence.',
                ],
                'daily_emphasis' => 'There is no new word list this month, so more time goes to application '
                    .'— full roleplays, not just word drills. Weekly milestone check-ins get stricter this '
                    .'month; treat each roleplay like a real conversation.',
                'speaking_practice_ideas' => [
                    'Opinion Round — your practice partner asks "What do you think about...?"; answer with '
                        .'a reason, daily.',
                    'Complaint Roleplay — your practice partner plays someone you need to raise a problem '
                        .'with; stay calm and polite.',
                    'Confirm-the-Details Drill — your practice partner reads out an address, phone number, '
                        .'or date; repeat it back to confirm.',
                    '3-Scenario Marathon — once a week, run 3 Roleplay Scenarios back-to-back, with no script.',
                ],
                'reading_materials_ideas' => [
                    'A sample complaint message and a polite reply.',
                    'A short opinion piece or forum post on an everyday topic.',
                    'A short daily-routine or diary-style text.',
                ],
                'vocabulary_target' => 'No new list this month — review and strengthen the full 500-word '
                    .'bank; drill any word you still hesitate on.',
                'weeks' => [
                    [
                        'title' => 'Handling Problems Politely',
                        'mastery_description' => 'You raise a problem and ask for a solution without sounding '
                            .'rude.',
                    ],
                    [
                        'title' => 'Sharing Opinions',
                        'mastery_description' => 'You give a simple opinion and one reason on an everyday '
                            .'topic.',
                    ],
                    [
                        'title' => 'Difficult Conversations',
                        'mastery_description' => 'You handle a complaint or disagreement calmly, and '
                            .'apologise when needed.',
                    ],
                    [
                        'title' => 'Detail Confirmation',
                        'mastery_description' => 'You read and confirm numbers, dates, and addresses aloud, '
                            .'clearly and correctly.',
                    ],
                ],
            ],
            [
                'month_number' => 6,
                'theme' => 'Fluency & Confidence Mastery',
                'goals' => [
                    'Master a confident, natural self-introduction and everyday conversation.',
                    'Review and strengthen every weak word from the last 5 months.',
                    'Practise speaking at natural speed, volume, and clarity.',
                    'Get comfortable with spontaneous, unscripted conversation with a new person.',
                ],
                'expected_outcomes' => [
                    'Complete a full unscripted conversation in English without freezing.',
                    'Answer 15 or more common everyday questions smoothly.',
                    'Introduce yourself confidently to a new person, in person or on a call.',
                    'Hold a 5-minute conversation on a familiar topic without switching to Hindi.',
                ],
                'daily_emphasis' => 'There are no new vocabulary lists this month — this is a consolidation '
                    .'and performance month. Every day includes one timed self-introduction and one '
                    .'spontaneous question, building speed and comfort under mild pressure, the way a real '
                    .'conversation feels.',
                'speaking_practice_ideas' => [
                    '60-Second Self-Intro Drill — timed daily, aiming for smoothness, not memorisation.',
                    'Full Unscripted Conversation — your practice partner plays a stranger, using the '
                        .'Confidence Q&A material.',
                    'AI Conversation Practice — the same exercise using the AI Practice Prompts, for a '
                        .'"stranger" feel.',
                    'Record & Review — record an answer, play it back, and note one thing to improve.',
                ],
                'reading_materials_ideas' => [
                    'Sample self-introduction paragraphs.',
                    'Everyday conversation starters and small-talk topics.',
                    'Model answers from the Confidence Q&A material.',
                ],
                'vocabulary_target' => 'No new list. Build a personal "Top 30 Weak Words" list and drill only '
                    .'those daily.',
                'weeks' => [
                    [
                        'title' => 'Self-Introduction Mastery',
                        'mastery_description' => 'You deliver a confident self-introduction and answer '
                            .'simple personal questions smoothly.',
                    ],
                    [
                        'title' => 'Everyday Topics',
                        'mastery_description' => 'You talk comfortably about family, routine, likes, and '
                            .'interests.',
                    ],
                    [
                        'title' => 'Full Conversations',
                        'mastery_description' => 'You complete a full unscripted conversation with a practice '
                            .'partner or AI, recorded and reviewed.',
                    ],
                    [
                        'title' => 'Final Polish',
                        'mastery_description' => 'You fix your remaining weak words and speak at natural '
                            .'speed and with confidence.',
                    ],
                ],
            ],
        ];
    }
}
