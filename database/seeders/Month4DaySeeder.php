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

class Month4DaySeeder extends Seeder
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
        $month = Month::where('month_number', 4)->first();

        if (! $month) {
            throw new RuntimeException('Month 4 not found — MonthSeeder must run before Month4DaySeeder.');
        }

        $weeks = Week::where('month_id', $month->id)->orderBy('week_number')->get();

        if ($weeks->count() !== 4) {
            throw new RuntimeException('Expected exactly 4 weeks for Month 4 before seeding days.');
        }

        // day_number is globally unique across the whole program (1-180), so
        // local day 1-30 within this month must be offset onto the global
        // range that MonthSeeder already assigned to Month 4's weeks.
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

        $this->assertMonth4Integrity($month);
    }

    /**
     * Assertion: exactly 30 Day rows and 150 DayTask rows (30 x 5) exist for
     * Month 4, and every day has exactly one task per SkillArea.
     */
    private function assertMonth4Integrity(Month $month): void
    {
        $dayCount = Day::where('month_id', $month->id)->count();
        if ($dayCount !== 30) {
            throw new RuntimeException("Expected 30 Day rows for Month 4, found {$dayCount}.");
        }

        $dayIds = Day::where('month_id', $month->id)->pluck('id');
        $dayTaskCount = DayTask::whereIn('day_id', $dayIds)->count();
        if ($dayTaskCount !== 150) {
            throw new RuntimeException("Expected 150 DayTask rows for Month 4, found {$dayTaskCount}.");
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
     * Month 4 — full day-by-day content (local days 1-30, offset to global
     * days 91-120). Vocabulary terms are drawn from Word Bank Groups 18-20
     * (Home & Household, Common Verbs 3, Confidence & Social Words), with
     * Hindi glosses matching WordBankSeeder exactly. This is a lighter
     * vocabulary month (75 words vs 125-150 in earlier months), so several
     * days introduce only 2-3 new words while reading/listening/speaking
     * content carries the technology/banking/home theme.
     *
     * @return array<int, array<string, mixed>>
     */
    private function days(): array
    {
        return [
            1 => [
                'title' => 'Explaining How Things Work',
                'total_minutes' => 45,
                'read' => "Read aloud: 'Let me explain how this app works. Can you describe the problem you "
                    ."are facing?'",
                'listen' => 'Watch a short video of someone explaining how to use a mobile app.',
                'speak' => 'Explain to your practice partner how to use one app on your phone, step by step.',
                'write' => 'Write 4 sentences explaining how an app or website works.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['explain', 'समझाना (samjhaana)'],
                    ['describe', 'वर्णन करना (varnan karna)'],
                ],
            ],
            2 => [
                'title' => 'Handling Everyday Tasks',
                'total_minutes' => 45,
                'read' => "Read aloud: 'I can handle this myself. She manages her time well, even with a "
                    ."busy phone full of notifications.'",
                'listen' => 'Watch a short video about managing daily tasks and notifications.',
                'speak' => 'Tell your practice partner one everyday task you handle well and how you manage '
                    .'your time with your phone.',
                'write' => 'Write 4 sentences about handling or managing tasks.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['handle', 'संभालना (sambhaalna)'],
                    ['manage', 'प्रबंधित करना (prabandhit karna)'],
                ],
            ],
            3 => [
                'title' => 'Organizing Your Day',
                'total_minutes' => 48,
                'read' => "Read aloud: 'I organize my apps every month. Please arrange the icons on your "
                    ."phone's home screen.'",
                'listen' => 'Watch a short video about organizing a phone or a daily schedule using an app.',
                'speak' => 'Describe to your practice partner how you organize or arrange things on your '
                    .'phone.',
                'write' => 'Write 4 sentences about organizing or arranging.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['organize', 'व्यवस्थित करना (vyavasthit karna)'],
                    ['arrange', 'व्यवस्था करना (vyavastha karna)'],
                ],
            ],
            4 => [
                'title' => 'Deliveries & Collecting Things',
                'total_minutes' => 45,
                'read' => "Read aloud: 'He delivered the parcel himself. Please collect your things from the "
                    ."delivery app.'",
                'listen' => 'Watch a short video about tracking an online delivery on an app.',
                'speak' => 'Tell your practice partner about a delivery you tracked or collected using an '
                    .'app.',
                'write' => 'Write 4 sentences about a delivery app experience.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['deliver', 'पहुँचाना (pahunchaana)'],
                    ['collect', 'इकट्ठा करना (ikattha karna)'],
                ],
            ],
            5 => [
                'title' => 'Getting Help & Support',
                'total_minutes' => 48,
                'read' => "Read aloud: 'They provide free wifi here. My family supports me when I have a "
                    ."technology problem.'",
                'listen' => 'Watch a short video about calling customer support for a phone or app problem.',
                'speak' => 'Roleplay calling app support: explain your problem and ask them to provide help.',
                'write' => 'Write 4 sentences about getting help or support with technology.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['provide', 'प्रदान करना (pradaan karna)'],
                    ['support', 'सहायता करना (sahaayata karna)'],
                ],
            ],
            6 => [
                'title' => 'Solving Problems & Responding',
                'total_minutes' => 45,
                'read' => "Read aloud: 'Let's solve this problem together. Please inform me if you're late. "
                    ."She responded very kindly to my message.'",
                'listen' => 'Watch a short video of a customer-support chat solving a technology problem.',
                'speak' => 'Roleplay solving a phone or app problem with your practice partner, then respond '
                    .'politely to their message.',
                'write' => 'Write 5 sentences about solving a problem or responding to someone.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['solve', 'हल करना (hal karna)'],
                    ['inform', 'सूचित करना (soochit karna)'],
                    ['respond', 'जवाब देना (jawaab dena)'],
                ],
            ],
            7 => [
                'title' => 'Week 1 Review — Explain How to Use an App/Website Milestone',
                'total_minutes' => 58,
                'read' => "Re-read all of this week's words aloud.",
                'listen' => 'Watch the Day 1 app-explanation video again and notice what feels easier now.',
                'speak' => 'Explain how to use a phone app or website, step by step, in 8 or more sentences, '
                    .'to your practice partner as if they have never used it. Your practice partner confirms '
                    .'the Week 1 milestone.',
                'write' => 'Write the explanation you just gave, from memory.',
                'vocab_intro' => 'No new words today — review all Week 1 words aloud and notice which ones '
                    .'feel easier now.',
                'vocab' => [],
            ],
            8 => [
                'title' => 'Speaking Confidently at the Bank',
                'total_minutes' => 45,
                'read' => "Read aloud: 'Let me introduce myself to the bank clerk. I want to compare two "
                    ."account options. I prefer the one with no extra charges.'",
                'listen' => 'Watch a short video of someone speaking confidently to a bank clerk.',
                'speak' => 'Roleplay introducing yourself to a bank clerk (your practice partner) and '
                    .'comparing two account options.',
                'write' => 'Write 4 sentences comparing two banking options.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['introduce (oneself)', 'अपना परिचय देना (apna parichay dena)'],
                    ['compare', 'तुलना करना (tulna karna)'],
                    ['prefer', 'पसंद करना (pasand karna)'],
                    ['point of view', 'नज़रिया (nazariya)'],
                ],
            ],
            9 => [
                'title' => 'Explaining Your Banking Needs',
                'total_minutes' => 48,
                'read' => "Read aloud: 'Based on my experience, this app is easy to use. My goal is to open "
                    ."a savings account. Can I tell you about my background?'",
                'listen' => 'Watch a short video of a customer explaining their banking needs to a bank '
                    .'employee.',
                'speak' => 'Explain to your practice partner (playing a bank clerk) why you need a '
                    .'particular type of account.',
                'write' => 'Write 4 sentences about your banking goal.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['experience', 'अनुभव (anubhav)'],
                    ['achievement', 'उपलब्धि (upalabdhi)'],
                    ['goal', 'लक्ष्य (lakshya)'],
                    ['background', 'पृष्ठभूमि (prishthbhoomi)'],
                ],
            ],
            10 => [
                'title' => 'Building Trust with Bank Staff',
                'total_minutes' => 45,
                'read' => "Read aloud: 'Managing money is one of my interests. I try to be honest about my "
                    ."budget, and I respect good advice.'",
                'listen' => 'Watch a short video about being honest and clear when discussing money.',
                'speak' => 'Tell your practice partner one thing you are honest about when it comes to '
                    .'money.',
                'write' => 'Write 4 sentences about honesty or respect in money matters.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['hobby', 'शौक़ (shauk)'],
                    ['interest', 'रुचि (ruchi)'],
                    ['honest', 'ईमानदार (imaandaar)'],
                    ['respect', 'सम्मान (sammaan)'],
                ],
            ],
            11 => [
                'title' => 'Staying Positive in Difficult Conversations',
                'total_minutes' => 48,
                'read' => "Read aloud: 'I appreciate your help with this transaction. Let me congratulate "
                    ."you on your new account. It always encourages me to ask questions bravely.'",
                'listen' => 'Watch a short video of a customer thanking a bank employee for their help.',
                'speak' => 'Thank your practice partner (the bank clerk) for solving a problem, and '
                    .'encourage them the way they helped you.',
                'write' => 'Write 4 sentences of appreciation or encouragement.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['appreciate', 'सराहना करना (saraahna karna)'],
                    ['congratulate', 'बधाई देना (badhai dena)'],
                    ['encourage', 'प्रोत्साहित करना (protsaahit karna)'],
                    ['brave', 'बहादुर (bahadur)'],
                ],
            ],
            12 => [
                'title' => 'Staying Calm While You Wait',
                'total_minutes' => 45,
                'read' => "Read aloud: 'Be patient while you wait in line at the bank. The clerk was "
                    ."generous with her time and cheerful throughout.'",
                'listen' => 'Watch a short video of someone waiting patiently at a bank counter.',
                'speak' => 'Tell your practice partner about a time you had to be patient at a bank or shop.',
                'write' => 'Write 4 sentences about staying patient and calm.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['patient (adj)', 'धैर्यवान (dhairyavaan)'],
                    ['sincere', 'सच्चा (sachcha)'],
                    ['generous', 'उदार (udaar)'],
                    ['cheerful', 'ख़ुशमिज़ाज (khushmizaaj)'],
                ],
            ],
            13 => [
                'title' => 'Being Confident with Your Money',
                'total_minutes' => 50,
                'read' => "Read aloud: 'Stay optimistic about your savings. I am determined to be more "
                    .'independent with my money and responsible with my spending. That was a genuine '
                    ."mistake, but I fixed it.'",
                'listen' => 'Watch a short video about being confident and responsible with money.',
                'speak' => 'Tell your practice partner one way you are becoming more responsible or '
                    .'independent with your money.',
                'write' => 'Write 5 sentences about being confident and responsible with money.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['optimistic', 'आशावादी (aashaavaadi)'],
                    ['determined', 'दृढ़ निश्चयी (dridh nishchayi)'],
                    ['independent', 'स्वतंत्र (swatantra)'],
                    ['responsible', 'ज़िम्मेदार (zimmedaar)'],
                    ['genuine', 'सच्चा (sachcha)'],
                ],
            ],
            14 => [
                'title' => 'Week 2 Review — Explain a Payment or Bank Visit Milestone',
                'total_minutes' => 58,
                'read' => "Re-read all of this week's words aloud.",
                'listen' => 'Watch the Day 8 bank-clerk video again and notice what feels easier now.',
                'speak' => 'Roleplay a full bank visit with your practice partner: ask about your balance, '
                    .'explain a transaction problem, and end the conversation politely and confidently. Your '
                    .'practice partner confirms the Week 2 milestone.',
                'write' => 'Write the bank visit conversation, from memory, in 8 or more lines.',
                'vocab_intro' => 'No new words today — review all Week 2 words aloud and notice which ones '
                    .'feel easier now.',
                'vocab' => [],
            ],
            15 => [
                'title' => 'Talking About Your Home',
                'total_minutes' => 45,
                'read' => "Read aloud: 'This is my house. The rent is due this week. Our landlord lives "
                    ."nearby. The tap needs repair.'",
                'listen' => 'Watch a short video of someone describing their home and rent.',
                'speak' => 'Describe your home to your practice partner: is it rented, and who is your '
                    .'landlord?',
                'write' => 'Write 4 sentences about your home.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['house', 'घर (ghar)'],
                    ['rent', 'किराया (kiraaya)'],
                    ['landlord', 'मकान मालिक (makaan maalik)'],
                    ['repair', 'मरम्मत (marammat)'],
                ],
            ],
            16 => [
                'title' => 'Home Utilities & Bills',
                'total_minutes' => 48,
                'read' => "Read aloud: 'The electricity went off again. Water supply comes in the morning. "
                    ."Please pay the electricity bill. We bought new furniture.'",
                'listen' => 'Watch a short video about paying a household bill or dealing with a power cut.',
                'speak' => 'Tell your practice partner about your last electricity or water bill, and one '
                    .'time the power went off.',
                'write' => 'Write 4 sentences about household utilities or bills.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['electricity', 'बिजली (bijli)'],
                    ['water supply', 'पानी की सप्लाई (paani ki supply)'],
                    ['bill (household)', 'बिल (bill)'],
                    ['furniture', 'फ़र्नीचर (furniture)'],
                ],
            ],
            17 => [
                'title' => 'Rooms & Cleaning',
                'total_minutes' => 45,
                'read' => "Read aloud: 'She is cooking in the kitchen. The bedroom is upstairs. The bathroom "
                    ."tap is leaking. Bring the broom, please.'",
                'listen' => 'Watch a short video of a tour of a small home, room by room.',
                'speak' => 'Describe the rooms in your home to your practice partner.',
                'write' => "Write 4 sentences describing your home's rooms.",
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['kitchen', 'रसोई (rasoi)'],
                    ['bedroom', 'बेडरूम (bedroom)'],
                    ['bathroom', 'बाथरूम (bathroom)'],
                    ['broom', 'झाड़ू (jhaadu)'],
                ],
            ],
            18 => [
                'title' => 'Reporting a Home Problem',
                'total_minutes' => 48,
                'read' => "Read aloud: 'Turn off the tap. There is a small leak in the pipe. Turn on the "
                    ."light switch. Please switch on the fan.'",
                'listen' => 'Watch a short video of someone reporting a leaking tap to a repair person.',
                'speak' => 'Roleplay describing a home problem — a leaking tap or a broken switch — to your '
                    .'practice partner playing a repair person.',
                'write' => 'Write a 5-line dialogue reporting a home problem.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['tap', 'नल (nal)'],
                    ['leak', 'रिसाव (leak)'],
                    ['switch', 'स्विच (switch)'],
                    ['fan', 'पंखा (pankha)'],
                ],
            ],
            19 => [
                'title' => 'Around the Neighbourhood',
                'total_minutes' => 45,
                'read' => "Read aloud: 'We need a new gas cylinder. Please take out the garbage. Our "
                    ."neighbourhood is quiet.'",
                'listen' => 'Watch a short video about everyday life in a neighbourhood.',
                'speak' => 'Describe your neighbourhood to your practice partner — what you like about it.',
                'write' => 'Write 4 sentences about your neighbourhood.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['gas', 'गैस (gas)'],
                    ['garbage', 'कचरा (kachra)'],
                    ['neighbourhood', 'मोहल्ला (mohalla)'],
                    ['society (residential)', 'सोसाइटी (society)'],
                ],
            ],
            20 => [
                'title' => 'Society Life: Maintenance & Neighbours',
                'total_minutes' => 48,
                'read' => "Read aloud: 'Maintenance charges are due. Ask the guard for help. We are moving "
                    ."to a new house. Can I borrow your ladder? He lent me his umbrella.'",
                'listen' => 'Watch a short video about paying maintenance charges or asking a security guard '
                    .'for help.',
                'speak' => 'Tell your practice partner about a time you borrowed or lent something to a '
                    .'neighbour.',
                'write' => 'Write 5 sentences about maintenance, borrowing, or lending.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['maintenance', 'रखरखाव (rakh-rakhaav)'],
                    ['guard (security)', 'गार्ड (guard)'],
                    ['move (house)', 'शिफ़्ट होना (shift hona)'],
                    ['borrow', 'उधार लेना (udhaar lena)'],
                    ['lend', 'उधार देना (udhaar dena)'],
                ],
            ],
            21 => [
                'title' => 'Week 3 Review — Describe a Home Problem to a Repair Person Milestone',
                'total_minutes' => 58,
                'read' => "Re-read all of this week's words aloud.",
                'listen' => 'Watch the Day 18 leaking-tap video again and notice what feels easier now.',
                'speak' => 'Describe a home problem — a leaking tap, no electricity, or a broken fan — to '
                    .'your practice partner playing a repair person, and agree on when they will fix it. Your '
                    .'practice partner confirms the Week 3 milestone.',
                'write' => 'Write the repair conversation, from memory, in 8 or more lines.',
                'vocab_intro' => 'No new words today — review all Week 3 words aloud.',
                'vocab' => [],
            ],
            22 => [
                'title' => 'Explaining How I Pay a Bill',
                'total_minutes' => 50,
                'read' => "Read aloud: 'I always ensure I pay my bill on time. Finishing this course was a "
                    ."big achievement for me.'",
                'listen' => 'Watch a short video of someone explaining how they pay an electricity bill step '
                    .'by step.',
                'speak' => 'Explain to your practice partner, in 6 or more sentences, how you pay one of '
                    .'your household bills, step by step.',
                'write' => 'Write your bill-paying steps as 5 sentences.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['ensure', 'सुनिश्चित करना (sunishchit karna)'],
                    ['achieve', 'हासिल करना (haasil karna)'],
                ],
            ],
            23 => [
                'title' => 'Explaining How I Book a Ticket',
                'total_minutes' => 50,
                'read' => "Read aloud: 'Let's review our plan before we book. I monitor my daily progress on "
                    ."the app.'",
                'listen' => 'Watch a short video of someone booking a bus or train ticket online.',
                'speak' => 'Explain to your practice partner, step by step, how you book a ticket online.',
                'write' => 'Write your ticket-booking steps as 5 sentences.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['review', 'समीक्षा करना (samiksha karna)'],
                    ['monitor', 'निगरानी करना (nigraani karna)'],
                ],
            ],
            24 => [
                'title' => 'Negotiating and Recommending',
                'total_minutes' => 45,
                'read' => "Read aloud: 'We negotiated a fair price for the repair. I recommend this app for "
                    ."online payments.'",
                'listen' => 'Watch a short video of someone negotiating a price or recommending a service.',
                'speak' => 'Tell your practice partner about something you negotiated recently, and '
                    .'recommend one app or service you trust.',
                'write' => 'Write 4 sentences about negotiating or recommending something.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['negotiate', 'बातचीत करना (baatcheet karna)'],
                    ['recommend', 'सिफ़ारिश करना (sifaarish karna)'],
                ],
            ],
            25 => [
                'title' => 'Celebrating Success, Apologizing for Mistakes',
                'total_minutes' => 48,
                'read' => "Read aloud: 'We celebrate every festival with our neighbours. I apologized for "
                    ."my mistake with the bill.'",
                'listen' => 'Watch a short video of someone apologizing politely for a small mistake.',
                'speak' => 'Tell your practice partner about something you celebrated recently, and '
                    .'something you apologized for.',
                'write' => 'Write 4 sentences about celebrating or apologizing.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['celebrate', 'जश्न मनाना (jashn manaana)'],
                    ['apologize', 'माफ़ी माँगना (maafi maangna)'],
                ],
            ],
            26 => [
                'title' => 'Forgiving & Sharing',
                'total_minutes' => 48,
                'read' => "Read aloud: 'Please forgive me for being late. Please share your experience with "
                    ."this app.'",
                'listen' => 'Watch a short video of two friends forgiving each other after a small '
                    .'disagreement.',
                'speak' => 'Tell your practice partner about a time you forgave someone or shared something '
                    .'useful.',
                'write' => 'Write 4 sentences about forgiving or sharing.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['forgive', 'माफ़ करना (maaf karna)'],
                    ['share', 'साझा करना (saanjha karna)'],
                ],
            ],
            27 => [
                'title' => 'Thanking & Trusting',
                'total_minutes' => 45,
                'read' => "Read aloud: 'I want to thank you for your help with this process. I trust you "
                    ."completely to explain it clearly.'",
                'listen' => 'Watch a short video of someone thanking a helpful bank clerk or repair person.',
                'speak' => 'Thank your practice partner for their help this month, and tell them one process '
                    .'you now trust yourself to explain.',
                'write' => 'Write 4 sentences of thanks, and one about trust.',
                'vocab_intro' => "Learn today's words: say each one aloud three times, then use at least one "
                    .'in a sentence of your own.',
                'vocab' => [
                    ['thank', 'धन्यवाद देना (dhanyavaad dena)'],
                    ['trust', 'भरोसा करना (bharosa karna)'],
                ],
            ],
            28 => [
                'title' => 'Full Mock Conversation Rehearsal',
                'total_minutes' => 53,
                'read' => 'Read a complete sample explanation aloud — how to pay a bill, book a ticket, or '
                    .'use an app, in 10 or more sentences.',
                'listen' => 'Listen to the sample explanation audio twice.',
                'speak' => 'Rehearse explaining a full everyday process with your practice partner, aiming '
                    .'for natural flow, not a memorised script.',
                'write' => "Write your personal '10 weak words or phrases' list from Month 4 in your "
                    .'notebook.',
                'vocab_intro' => 'No new words today — look back through Days 1-27 and pick your personal 10 '
                    .'hardest words to review.',
                'vocab' => [],
            ],
            29 => [
                'title' => 'Record & Review',
                'total_minutes' => 48,
                'read' => "Instead of reading something new, record yourself reading yesterday's writing "
                    .'aloud.',
                'listen' => 'Play back your Day 28 recording and listen for clarity and step-by-step order.',
                'speak' => 'Repeat the full process explanation, recorded on your phone, focusing on clear, '
                    .'connected steps.',
                'write' => 'Write one thing that has improved since the start of Month 4.',
                'vocab_intro' => "No new words today — review all Week 4 words once more before tomorrow's "
                    .'milestone.',
                'vocab' => [],
            ],
            30 => [
                'title' => 'Month 4 Milestone — Explain the Process',
                'minutes' => [
                    'reading' => 0,
                    'vocabulary' => 3,
                    'listening' => 0,
                    'speaking' => 40,
                    'writing' => 15,
                ],
                'read' => 'No reading exercise today — this is a performance day.',
                'listen' => 'No listening exercise today — this is a performance day.',
                'speak' => 'Narrate a full everyday task — cooking a simple meal, paying a bill, or booking '
                    .'a ticket — in 10 or more connected sentences, unscripted, to your practice partner. '
                    .'This is the official Month 4 milestone — score it against the Month 4 Test rubric.',
                'write' => "Write a short reflection: 'What I can now explain step by step, in English, that "
                    ."I could not explain three months ago.'",
                'vocab_intro' => 'Word Bank complete — 500 out of 500 words introduced across Months 1-4. No '
                    .'new words today; this is a milestone day.',
                'vocab' => [],
            ],
        ];
    }
}
