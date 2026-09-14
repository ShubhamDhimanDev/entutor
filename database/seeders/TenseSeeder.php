<?php

namespace Database\Seeders;

use App\Enums\TenseAspect;
use App\Enums\TenseKey;
use App\Enums\TenseTime;
use App\Models\Tense;
use Illuminate\Database\Seeder;
use RuntimeException;

class TenseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach ($this->tenses() as $position => $tense) {
            $this->assertFilledIn($position + 1, $tense);

            Tense::create([
                'key' => $tense['key'],
                'name' => $tense['name'],
                'time' => $tense['time'],
                'aspect' => $tense['aspect'],
                'summary' => $tense['summary'],
                'structure_affirmative' => $tense['structure_affirmative'],
                'structure_negative' => $tense['structure_negative'],
                'structure_interrogative' => $tense['structure_interrogative'],
                'usage_rules' => $tense['usage_rules'],
                'signal_words' => $tense['signal_words'],
                'examples' => $tense['examples'],
                'common_confusions' => $tense['common_confusions'],
                'order' => $tense['order'],
            ]);
        }
    }

    /**
     * Assertion: no tense may ship with placeholder pedagogical content —
     * mirrors MonthSeeder's fail-fast style, so this seeder can't be wired
     * into DatabaseSeeder and silently ship blank tense content once
     * english-tutor's authoring pass is incomplete.
     *
     * @param  array<string, mixed>  $tense
     */
    private function assertFilledIn(int $position, array $tense): void
    {
        $label = "Tense #{$position} ({$tense['key']->value})";

        if ($tense['summary'] === '') {
            throw new RuntimeException("{$label} is missing 'summary' — english-tutor content is not filled in yet.");
        }

        if ($tense['structure_affirmative'] === '') {
            throw new RuntimeException("{$label} is missing 'structure_affirmative'.");
        }

        if ($tense['structure_negative'] === '') {
            throw new RuntimeException("{$label} is missing 'structure_negative'.");
        }

        if ($tense['structure_interrogative'] === '') {
            throw new RuntimeException("{$label} is missing 'structure_interrogative'.");
        }

        if ($tense['usage_rules'] === []) {
            throw new RuntimeException("{$label} has no 'usage_rules'.");
        }

        if ($tense['signal_words'] === []) {
            throw new RuntimeException("{$label} has no 'signal_words'.");
        }

        if ($tense['examples'] === []) {
            throw new RuntimeException("{$label} has no 'examples'.");
        }

        if ($tense['common_confusions'] === null) {
            throw new RuntimeException("{$label} is missing 'common_confusions'.");
        }
    }

    /**
     * The 12 canonical English tenses (present/past/future x
     * simple/continuous/perfect/perfect-continuous), in the fixed reference
     * order used throughout the app. Pedagogical content authored by the
     * english-tutor agent for Hindi-speaking adult learners.
     *
     * @return array<int, array<string, mixed>>
     */
    private function tenses(): array
    {
        return [
            [
                'key' => TenseKey::PresentSimple,
                'name' => 'Present Simple',
                'time' => TenseTime::Present,
                'aspect' => TenseAspect::Simple,
                'order' => 1,

                'summary' => 'Use the Present Simple for facts, habits, routines, and things that are generally true — actions that happen regularly, not just right now.',
                'structure_affirmative' => 'Subject + verb (base form); add -s/-es for he/she/it',
                'structure_negative' => 'Subject + do not/does not + verb (base form)',
                'structure_interrogative' => 'Do/Does + subject + verb (base form)?',
                'usage_rules' => [
                    'Use it for daily routines and habits, like what you do every day.',
                    'Use it for facts and general truths that stay the same, like scientific facts.',
                    'Use it for permanent situations, such as your job, your language, or where you live.',
                    'Use it for fixed schedules and timetables, like train or class times.',
                    'Use it with state verbs (know, believe, want, like) — these describe states, not actions, so they never take -ing.',
                    'Use it with frequency adverbs (always, usually, sometimes, never) to say how often something happens.',
                ],
                'signal_words' => ['always', 'usually', 'often', 'sometimes', 'rarely', 'never', 'every day', 'every week', 'on Mondays', 'generally'],
                'examples' => [
                    ['sentence' => 'She drinks tea every morning.', 'native_meaning' => 'वह हर सुबह चाय पीती है।'],
                    ['sentence' => "He doesn't like loud music.", 'native_meaning' => 'उसे तेज़ संगीत पसंद नहीं है।'],
                    ['sentence' => 'Do you work on Saturdays?', 'native_meaning' => 'क्या आप शनिवार को काम करते हैं?'],
                    ['sentence' => 'The sun rises in the east.', 'native_meaning' => 'सूरज पूर्व में उगता है।'],
                    ['sentence' => "I don't understand this word.", 'native_meaning' => 'मुझे यह शब्द समझ नहीं आता।'],
                ],
                'common_confusions' => "Present Simple is often confused with Present Continuous — use Present Simple for habits and permanent facts ('I work in Delhi'), and Present Continuous only for actions happening right now or temporary arrangements ('I am working from home this week'). Remember that state verbs like know, want, and believe stay in Present Simple even when the meaning is 'right now'.",
            ],
            [
                'key' => TenseKey::PresentContinuous,
                'name' => 'Present Continuous',
                'time' => TenseTime::Present,
                'aspect' => TenseAspect::Continuous,
                'order' => 2,

                'summary' => 'Use the Present Continuous for actions happening right now, temporary situations, and fixed plans in the near future.',
                'structure_affirmative' => 'Subject + am/is/are + verb-ing',
                'structure_negative' => 'Subject + am/is/are + not + verb-ing',
                'structure_interrogative' => 'Am/Is/Are + subject + verb-ing?',
                'usage_rules' => [
                    'Use it for an action happening exactly at the moment of speaking.',
                    "Use it for temporary situations that are true only around now, not forever, like 'I am staying with my cousin this week.'",
                    'Use it for fixed future plans and arrangements that already have a time and place decided.',
                    "Use it to describe a situation that is changing or developing, like 'The weather is getting colder.'",
                    'Do not use it with state verbs (know, love, believe, own) — use Present Simple instead, even when talking about right now.',
                ],
                'signal_words' => ['now', 'right now', 'at the moment', 'currently', 'these days', 'this week', 'still', 'Look!', 'Listen!', 'at present'],
                'examples' => [
                    ['sentence' => 'I am reading a new book these days.', 'native_meaning' => 'मैं आजकल एक नई किताब पढ़ रहा हूँ।'],
                    ['sentence' => "She isn't listening to me right now.", 'native_meaning' => 'वह अभी मेरी बात नहीं सुन रही है।'],
                    ['sentence' => 'Are you working on the report at the moment?', 'native_meaning' => 'क्या आप अभी रिपोर्ट पर काम कर रहे हैं?'],
                    ['sentence' => 'They are meeting the manager at 5 p.m. tomorrow.', 'native_meaning' => 'वे कल शाम 5 बजे प्रबंधक से मिल रहे हैं।'],
                    ['sentence' => 'Look, it is raining outside.', 'native_meaning' => 'देखो, बाहर बारिश हो रही है।'],
                ],
                'common_confusions' => "Learners often use Present Continuous for permanent facts or state verbs, producing mistakes like 'I am knowing the answer' instead of 'I know the answer.' Save Present Continuous for actions in progress right now or temporary plans, and use Present Simple for anything permanent, habitual, or a state.",
            ],
            [
                'key' => TenseKey::PresentPerfect,
                'name' => 'Present Perfect',
                'time' => TenseTime::Present,
                'aspect' => TenseAspect::Perfect,
                'order' => 3,

                'summary' => 'Use the Present Perfect to connect the past to the present — for actions that happened at an unspecified time, or that started in the past and are still true now.',
                'structure_affirmative' => 'Subject + have/has + past participle',
                'structure_negative' => 'Subject + have/has + not + past participle',
                'structure_interrogative' => 'Have/Has + subject + past participle?',
                'usage_rules' => [
                    "Use it for a past action with a result that matters now, like 'I have lost my keys' meaning you don't have them now.",
                    'Use it for life experiences when the exact time is not important, such as places visited or things done.',
                    "Use it with 'for' or 'since' for something that started in the past and is still true now.",
                    "Use it for recently completed actions, often with 'just', 'already', or 'yet'.",
                    "Do not use it with a specific finished time word like 'yesterday' or 'in 2019' — use Past Simple for those instead.",
                ],
                'signal_words' => ['already', 'yet', 'just', 'ever', 'never', 'since', 'for', 'so far', 'recently', 'lately'],
                'examples' => [
                    ['sentence' => 'I have finished my homework.', 'native_meaning' => 'मैंने अपना होमवर्क पूरा कर लिया है।'],
                    ['sentence' => "She hasn't called me yet.", 'native_meaning' => 'उसने अभी तक मुझे फोन नहीं किया है।'],
                    ['sentence' => 'Have you ever been to London?', 'native_meaning' => 'क्या आप कभी लंदन गए हैं?'],
                    ['sentence' => 'We have known each other since college.', 'native_meaning' => 'हम कॉलेज के समय से एक-दूसरे को जानते हैं।'],
                    ['sentence' => 'He has just left the office.', 'native_meaning' => 'वह अभी-अभी दफ्तर से निकला है।'],
                ],
                'common_confusions' => "Present Perfect vs. Past Simple is a common source of errors for Hindi speakers, since Hindi doesn't mark this distinction the same way. Use Past Simple when a specific finished time is stated or implied ('I called her yesterday'), and Present Perfect when the exact time doesn't matter or the result still connects to now ('I have called her already').",
            ],
            [
                'key' => TenseKey::PresentPerfectContinuous,
                'name' => 'Present Perfect Continuous',
                'time' => TenseTime::Present,
                'aspect' => TenseAspect::PerfectContinuous,
                'order' => 4,

                'summary' => 'Use the Present Perfect Continuous to emphasize how long an action has been going on, whether it is still continuing now or just stopped with a visible result.',
                'structure_affirmative' => 'Subject + have/has + been + verb-ing',
                'structure_negative' => 'Subject + have/has + not + been + verb-ing',
                'structure_interrogative' => 'Have/Has + subject + been + verb-ing?',
                'usage_rules' => [
                    "Use it to show how long an action has been happening, usually with 'for' or 'since'.",
                    'Use it for an action that started in the past and is still going on right now.',
                    "Use it when a recently stopped action explains a present result, like 'I'm tired because I have been running.'",
                    'Use it to emphasize repetition or continuity, not a single completed fact or amount.',
                    "Prefer Present Perfect Simple with state verbs instead, since they don't take -ing, like 'I have known him for years,' not 'I have been knowing him.'",
                ],
                'signal_words' => ['for', 'since', 'how long', 'all day', 'all week', 'lately', 'recently', 'still'],
                'examples' => [
                    ['sentence' => 'I have been waiting for you for twenty minutes.', 'native_meaning' => 'मैं बीस मिनट से आपका इंतज़ार कर रहा हूँ।'],
                    ['sentence' => "She hasn't been sleeping well lately.", 'native_meaning' => 'वह हाल ही में ठीक से सो नहीं पा रही है।'],
                    ['sentence' => 'How long have you been learning English?', 'native_meaning' => 'आप कितने समय से अंग्रेज़ी सीख रहे हैं?'],
                    ['sentence' => 'They have been working on this project since March.', 'native_meaning' => 'वे मार्च से इस परियोजना पर काम कर रहे हैं।'],
                    ['sentence' => 'My hands are dirty because I have been cooking.', 'native_meaning' => 'मेरे हाथ गंदे हैं क्योंकि मैं खाना बना रहा हूँ।'],
                ],
                'common_confusions' => "This tense is often confused with Present Perfect Simple — use the continuous form to highlight duration or an ongoing process ('I have been studying for three hours'), and the simple form to highlight the completed result or quantity ('I have studied three chapters'). Avoid it with state verbs like know, want, or believe.",
            ],
            [
                'key' => TenseKey::PastSimple,
                'name' => 'Past Simple',
                'time' => TenseTime::Past,
                'aspect' => TenseAspect::Simple,
                'order' => 5,

                'summary' => 'Use the Past Simple for actions or situations that started and finished at a specific, known time in the past.',
                'structure_affirmative' => 'Subject + verb (past form / -ed)',
                'structure_negative' => 'Subject + did not + verb (base form)',
                'structure_interrogative' => 'Did + subject + verb (base form)?',
                'usage_rules' => [
                    "Use it for a completed action at a definite past time, like 'I called her yesterday.'",
                    'Use it for a sequence of completed past events, such as telling a story.',
                    'Use it for past habits or repeated actions that no longer happen now.',
                    'Use it for a situation that was true for a period in the past but is not true anymore.',
                    'Pair it with a specific past-time word whenever possible — that time word is the main signal you need this tense, not Present Perfect.',
                ],
                'signal_words' => ['yesterday', 'last night', 'last week', 'last year', 'ago', 'in 2019', 'when I was a child', 'then', 'at that time'],
                'examples' => [
                    ['sentence' => 'I visited my grandparents last weekend.', 'native_meaning' => 'मैं पिछले सप्ताहांत अपने दादा-दादी से मिलने गया था।'],
                    ['sentence' => "She didn't finish her dinner.", 'native_meaning' => 'उसने अपना खाना पूरा नहीं किया।'],
                    ['sentence' => 'Did you see that movie last night?', 'native_meaning' => 'क्या आपने कल रात वह फिल्म देखी?'],
                    ['sentence' => 'He lived in Mumbai for ten years before moving to Delhi.', 'native_meaning' => 'दिल्ली जाने से पहले वह दस साल तक मुंबई में रहा।'],
                    ['sentence' => 'We went to the market and bought some vegetables.', 'native_meaning' => 'हम बाजार गए और कुछ सब्जियाँ खरीदीं।'],
                ],
                'common_confusions' => "Past Simple is often mixed up with Present Perfect — Past Simple needs (or implies) a specific finished time like 'yesterday' or 'in 2020', while Present Perfect is used when the exact time isn't stated. Compare: 'I lost my phone yesterday' (Past Simple) with 'I have lost my phone' (Present Perfect, time unknown).",
            ],
            [
                'key' => TenseKey::PastContinuous,
                'name' => 'Past Continuous',
                'time' => TenseTime::Past,
                'aspect' => TenseAspect::Continuous,
                'order' => 6,

                'summary' => 'Use the Past Continuous for an action that was in progress at a specific moment in the past, often interrupted by a shorter action.',
                'structure_affirmative' => 'Subject + was/were + verb-ing',
                'structure_negative' => 'Subject + was/were + not + verb-ing',
                'structure_interrogative' => 'Was/Were + subject + verb-ing?',
                'usage_rules' => [
                    "Use it for an action already in progress at a specific past time, like 'At 8 p.m., I was cooking dinner.'",
                    "Use it for a longer background action interrupted by a shorter, completed action, like 'I was watching TV when the phone rang.'",
                    "Use it for two actions happening at the same time in the past, often with 'while'.",
                    'Use it to set the scene or describe the background at the start of a story.',
                    "Do not use it with state verbs — use Past Simple instead, like 'I knew the answer,' not 'I was knowing the answer.'",
                ],
                'signal_words' => ['while', 'when', 'at that moment', "at 8 o'clock last night", 'all morning', 'all day yesterday'],
                'examples' => [
                    ['sentence' => 'I was reading a book when you called.', 'native_meaning' => 'जब आपने फोन किया, मैं एक किताब पढ़ रहा था।'],
                    ['sentence' => "They weren't listening during the meeting.", 'native_meaning' => 'वे बैठक के दौरान ध्यान नहीं दे रहे थे।'],
                    ['sentence' => 'What were you doing at 9 last night?', 'native_meaning' => 'आप कल रात 9 बजे क्या कर रहे थे?'],
                    ['sentence' => 'While she was cooking, he was setting the table.', 'native_meaning' => 'जब वह खाना बना रही थी, वह मेज़ लगा रहा था।'],
                    ['sentence' => 'It was raining heavily when we left home.', 'native_meaning' => 'जब हम घर से निकले, तब भारी बारिश हो रही थी।'],
                ],
                'common_confusions' => "Past Continuous is often confused with Past Simple in interrupted-action sentences — the longer background action takes Past Continuous and the shorter, completed action that interrupts it takes Past Simple: 'I was sleeping when the alarm rang,' not 'I slept when the alarm was ringing.'",
            ],
            [
                'key' => TenseKey::PastPerfect,
                'name' => 'Past Perfect',
                'time' => TenseTime::Past,
                'aspect' => TenseAspect::Perfect,
                'order' => 7,

                'summary' => "Use the Past Perfect for an action that happened before another action or point in the past — the 'earlier past' when a story has two past events.",
                'structure_affirmative' => 'Subject + had + past participle',
                'structure_negative' => 'Subject + had + not + past participle',
                'structure_interrogative' => 'Had + subject + past participle?',
                'usage_rules' => [
                    "Use it for an action completed before another past action or time, like 'The train had left before I reached the station.'",
                    'Use it to show which of two past events happened first, when the order matters.',
                    "Use it after 'when', 'after', 'before', or 'by the time' to make the earlier event clear.",
                    "Use it for the earlier action inside reported speech about the past, like 'He said he had finished.'",
                    "Don't use it just because something is in the past — it needs a second, later past-time point to compare against.",
                ],
                'signal_words' => ['before', 'after', 'already', 'by the time', 'by then', 'when', 'never', 'until that day'],
                'examples' => [
                    ['sentence' => 'I had already eaten when she arrived.', 'native_meaning' => 'जब वह आई, तब तक मैं खाना खा चुका था।'],
                    ['sentence' => "She hadn't finished her work before the deadline.", 'native_meaning' => 'समय सीमा से पहले उसने अपना काम पूरा नहीं किया था।'],
                    ['sentence' => 'Had you met him before that meeting?', 'native_meaning' => 'क्या आप उस बैठक से पहले उनसे मिले थे?'],
                    ['sentence' => 'By the time we reached the theatre, the film had started.', 'native_meaning' => 'जब तक हम थियेटर पहुँचे, फिल्म शुरू हो चुकी थी।'],
                    ['sentence' => 'He told me he had never seen snow.', 'native_meaning' => 'उसने मुझे बताया कि उसने कभी बर्फ नहीं देखी थी।'],
                ],
                'common_confusions' => "Past Perfect is often confused with Past Simple — use Past Perfect only for the earlier of two past events, to show a clear order ('I had left before he arrived'). If there is only one past event, or the order doesn't matter, Past Simple alone is correct ('I left at six').",
            ],
            [
                'key' => TenseKey::PastPerfectContinuous,
                'name' => 'Past Perfect Continuous',
                'time' => TenseTime::Past,
                'aspect' => TenseAspect::PerfectContinuous,
                'order' => 8,

                'summary' => 'Use the Past Perfect Continuous to emphasize how long an action had been going on before another point or action in the past.',
                'structure_affirmative' => 'Subject + had + been + verb-ing',
                'structure_negative' => 'Subject + had + not + been + verb-ing',
                'structure_interrogative' => 'Had + subject + been + verb-ing?',
                'usage_rules' => [
                    "Use it to show the duration of an action up to a specific point in the past, like 'I had been waiting for an hour when the bus finally came.'",
                    "Use it to explain the cause of a past situation through an earlier ongoing action, like 'She was tired because she had been working all night.'",
                    'Use it for a repeated or continuous activity that stopped just before another past event.',
                    "Pair it with 'for' or 'since' to make the duration explicit.",
                    "Avoid it with state verbs — use Past Perfect Simple instead, like 'I had known him for years,' not 'I had been knowing him.'",
                ],
                'signal_words' => ['for', 'since', 'how long', 'before', 'until then', 'all day', 'all week'],
                'examples' => [
                    ['sentence' => 'I had been studying for three hours before the exam started.', 'native_meaning' => 'परीक्षा शुरू होने से पहले मैं तीन घंटे से पढ़ाई कर रहा था।'],
                    ['sentence' => "They hadn't been working there long before the factory closed.", 'native_meaning' => 'फैक्ट्री बंद होने से पहले वे वहाँ ज़्यादा समय से काम नहीं कर रहे थे।'],
                    ['sentence' => 'Had you been waiting long when the train finally arrived?', 'native_meaning' => 'जब ट्रेन आखिरकार आई, तब क्या आप लंबे समय से इंतज़ार कर रहे थे?'],
                    ['sentence' => 'Her eyes were red because she had been crying.', 'native_meaning' => 'उसकी आँखें लाल थीं क्योंकि वह रो रही थी।'],
                    ['sentence' => 'We had been driving for six hours before we stopped for food.', 'native_meaning' => 'खाने के लिए रुकने से पहले हम छह घंटे से गाड़ी चला रहे थे।'],
                ],
                'common_confusions' => "This tense is often confused with Past Continuous — use Past Perfect Continuous when the ongoing action finished before another past event or point ('I had been reading for an hour before you called'), and Past Continuous when the action was still happening at that past moment ('I was reading when you called').",
            ],
            [
                'key' => TenseKey::FutureSimple,
                'name' => 'Future Simple',
                'time' => TenseTime::Future,
                'aspect' => TenseAspect::Simple,
                'order' => 9,

                'summary' => 'Use the Future Simple (will) for predictions, decisions made at the moment of speaking, promises, and offers about the future.',
                'structure_affirmative' => 'Subject + will + verb (base form)',
                'structure_negative' => "Subject + will not/won't + verb (base form)",
                'structure_interrogative' => 'Will + subject + verb (base form)?',
                'usage_rules' => [
                    "Use it for predictions about the future, especially opinions or guesses, like 'I think it will rain tomorrow.'",
                    "Use it for decisions made right at the moment of speaking, not planned in advance, like 'The phone is ringing — I'll answer it.'",
                    'Use it for promises, offers, and requests.',
                    "Use it for facts about the future that are outside anyone's control, like someone's age or a scheduled event.",
                    "Don't use it for fixed plans already decided before now — prefer 'going to' or Present Continuous for those.",
                ],
                'signal_words' => ['tomorrow', 'next week', 'next year', 'soon', 'in the future', 'I think', 'probably', 'I promise', "I'm sure"],
                'examples' => [
                    ['sentence' => 'I will call you when I reach home.', 'native_meaning' => 'जब मैं घर पहुँचूँगा, तब मैं आपको फोन करूँगा।'],
                    ['sentence' => "She won't be able to attend the meeting.", 'native_meaning' => 'वह बैठक में शामिल नहीं हो पाएगी।'],
                    ['sentence' => 'Will you help me carry these bags?', 'native_meaning' => 'क्या आप मुझे ये थैले उठाने में मदद करेंगे?'],
                    ['sentence' => 'I think prices will rise next year.', 'native_meaning' => 'मुझे लगता है अगले साल कीमतें बढ़ेंगी।'],
                    ['sentence' => "Don't worry, I'll finish the report by tonight.", 'native_meaning' => 'चिंता मत करो, मैं आज रात तक रिपोर्ट पूरी कर दूँगा।'],
                ],
                'common_confusions' => "Future Simple ('will') is often confused with 'going to' — use 'will' for decisions made right now, predictions, and promises, and 'going to' for plans already decided before speaking ('I'm going to visit my parents next week') or predictions with present evidence ('Look at those clouds — it's going to rain').",
            ],
            [
                'key' => TenseKey::FutureContinuous,
                'name' => 'Future Continuous',
                'time' => TenseTime::Future,
                'aspect' => TenseAspect::Continuous,
                'order' => 10,

                'summary' => 'Use the Future Continuous for an action that will be in progress at a specific time in the future.',
                'structure_affirmative' => 'Subject + will + be + verb-ing',
                'structure_negative' => 'Subject + will + not + be + verb-ing',
                'structure_interrogative' => 'Will + subject + be + verb-ing?',
                'usage_rules' => [
                    "Use it for an action that will be in progress at a particular future moment, like 'At 8 p.m. tonight, I will be flying to Delhi.'",
                    'Use it for planned or expected ongoing situations in the future.',
                    "Use it to ask politely about someone's plans without sounding like a direct request, like 'Will you be using the car this evening?'",
                    'Use it to describe something that will happen as a matter of routine or schedule, not a sudden decision.',
                    "Don't confuse it with Future Perfect — this tense shows an action still in progress at that future point, not something completed.",
                ],
                'signal_words' => ['at this time tomorrow', "at 8 o'clock", 'this time next week', 'while', 'when', 'still'],
                'examples' => [
                    ['sentence' => 'This time next week, I will be sitting on a beach.', 'native_meaning' => 'अगले हफ्ते इसी समय, मैं समुद्र तट पर बैठा होऊँगा।'],
                    ['sentence' => "She won't be working on Sunday.", 'native_meaning' => 'वह रविवार को काम नहीं कर रही होगी।'],
                    ['sentence' => 'Will you be attending the conference tomorrow?', 'native_meaning' => 'क्या आप कल सम्मेलन में शामिल हो रहे होंगे?'],
                    ['sentence' => 'We will be traveling when you arrive, so please wait.', 'native_meaning' => 'जब आप पहुँचेंगे, हम यात्रा कर रहे होंगे, इसलिए कृपया इंतज़ार करें।'],
                    ['sentence' => 'At noon, the team will still be discussing the budget.', 'native_meaning' => 'दोपहर में, टीम अभी भी बजट पर चर्चा कर रही होगी।'],
                ],
                'common_confusions' => "Future Continuous is sometimes confused with Future Simple — use the continuous form when an action will be in progress at a specific future time ('I will be sleeping at midnight'), and the simple form for a single completed future action or decision ('I will sleep early tonight').",
            ],
            [
                'key' => TenseKey::FuturePerfect,
                'name' => 'Future Perfect',
                'time' => TenseTime::Future,
                'aspect' => TenseAspect::Perfect,
                'order' => 11,

                'summary' => 'Use the Future Perfect for an action that will be completed before a specific point or deadline in the future.',
                'structure_affirmative' => 'Subject + will + have + past participle',
                'structure_negative' => 'Subject + will + not + have + past participle',
                'structure_interrogative' => 'Will + subject + have + past participle?',
                'usage_rules' => [
                    "Use it to show an action will be finished before a future deadline, like 'By next month, I will have finished this course.'",
                    "Use it with 'by' plus a future time to mark the deadline.",
                    'Use it to look back from a future point at something that will already be complete by then.',
                    'Use it for a planned achievement or milestone that has a clear end point.',
                    "Don't use it for an action still ongoing at that future point — that needs Future Perfect Continuous instead.",
                ],
                'signal_words' => ['by then', 'by next year', 'by the time', 'by tomorrow', 'before', 'already'],
                'examples' => [
                    ['sentence' => 'By next year, I will have finished my degree.', 'native_meaning' => 'अगले साल तक, मैं अपनी डिग्री पूरी कर चुका होऊँगा।'],
                    ['sentence' => "She won't have completed the project by Friday.", 'native_meaning' => 'वह शुक्रवार तक परियोजना पूरी नहीं कर चुकी होगी।'],
                    ['sentence' => 'Will you have submitted the form by tomorrow?', 'native_meaning' => 'क्या आप कल तक फॉर्म जमा कर चुके होंगे?'],
                    ['sentence' => 'By the time you wake up, I will have left for the airport.', 'native_meaning' => 'जब तक आप उठेंगे, मैं हवाई अड्डे के लिए निकल चुका होऊँगा।'],
                    ['sentence' => 'They will have built the new bridge by 2027.', 'native_meaning' => 'वे 2027 तक नया पुल बना चुके होंगे।'],
                ],
                'common_confusions' => "Future Perfect is often confused with Future Simple — use Future Perfect only when looking back from a future deadline at something already completed by then ('By 6 p.m., I will have finished'), and Future Simple for an action that simply happens in the future without that completed-by framing ('I will finish at 6 p.m.').",
            ],
            [
                'key' => TenseKey::FuturePerfectContinuous,
                'name' => 'Future Perfect Continuous',
                'time' => TenseTime::Future,
                'aspect' => TenseAspect::PerfectContinuous,
                'order' => 12,

                'summary' => 'Use the Future Perfect Continuous to emphasize the duration of an action that will still be continuing up to a specific point in the future.',
                'structure_affirmative' => 'Subject + will + have + been + verb-ing',
                'structure_negative' => 'Subject + will + not + have + been + verb-ing',
                'structure_interrogative' => 'Will + subject + have + been + verb-ing?',
                'usage_rules' => [
                    "Use it to show how long an action will have continued by a certain future time, like 'By next June, I will have been working here for ten years.'",
                    "Use it with 'for' to state the total duration up to that future point.",
                    'Use it when the action started in the past or present and is expected to keep going up to the future point.',
                    "Use it to explain an expected future state as the result of ongoing effort, like 'By the time she graduates, she will have been studying English for six years.'",
                    "Avoid it with state verbs — use Future Perfect Simple instead, like 'By then, I will have known her for ten years,' not 'will have been knowing.'",
                ],
                'signal_words' => ['by then', 'by next year', 'for', 'by the time', 'up to that point'],
                'examples' => [
                    ['sentence' => 'By December, I will have been living in this city for five years.', 'native_meaning' => 'दिसंबर तक, मैं इस शहर में पाँच साल से रह रहा होऊँगा।'],
                    ['sentence' => "She won't have been working here long enough for a promotion.", 'native_meaning' => 'पदोन्नति के लिए वह यहाँ इतने लंबे समय से काम नहीं कर रही होगी।'],
                    ['sentence' => 'Will you have been studying all night by the time the sun rises?', 'native_meaning' => 'जब सूरज उगेगा, तब तक क्या आप पूरी रात पढ़ाई कर रहे होंगे?'],
                    ['sentence' => 'By the time he retires, he will have been teaching for thirty years.', 'native_meaning' => 'जब तक वह सेवानिवृत्त होगा, वह तीस साल से पढ़ा रहा होगा।'],
                    ['sentence' => 'By next month, they will have been building the house for a year.', 'native_meaning' => 'अगले महीने तक, वे एक साल से घर बना रहे होंगे।'],
                ],
                'common_confusions' => "Future Perfect Continuous is often confused with Future Perfect Simple — use the continuous form to stress the ongoing duration of an action up to a future point ('I will have been working here for ten years'), and the simple form to stress the completed result or quantity by that point ('I will have completed ten years here').",
            ],
        ];
    }
}
