<?php

namespace Database\Seeders;

use App\Enums\RoleplayDomain;
use App\Enums\RoleplaySide;
use App\Models\RoleplayLine;
use App\Models\RoleplayScenario;
use Illuminate\Database\Seeder;

class RoleplaySeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach ($this->scenarios() as $scenarioIndex => $scenario) {
            $roleplayScenario = RoleplayScenario::create([
                'title' => $scenario['title'],
                'domain' => $scenario['domain'],
                'tip' => $scenario['tip'],
                'order' => $scenarioIndex + 1,
            ]);

            foreach ($scenario['lines'] as $lineIndex => $line) {
                RoleplayLine::create([
                    'roleplay_scenario_id' => $roleplayScenario->id,
                    'side' => $line[0],
                    'speaker_label' => $line[1],
                    'line_text' => $line[2],
                    'order' => $lineIndex + 1,
                ]);
            }
        }
    }

    /**
     * 14 roleplay scenarios across 5 domains. Side A is always the learner's own
     * line ("You"); side B is the other party. Speaker labels are always a
     * generic role (Friend, Caller, Shopkeeper, Doctor, Neighbour, etc.), never a
     * relationship term.
     *
     * @return list<array{title: string, domain: RoleplayDomain, tip: string, lines: list<array{0: RoleplaySide, 1: string, 2: string}>}>
     */
    private function scenarios(): array
    {
        return [
            [
                'title' => 'A call from an old friend',
                'domain' => RoleplayDomain::PhoneMessages,
                'tip' => "A warm, simple reply like \"It's so nice to hear from you\" works for almost any surprise call.",
                'lines' => [
                    [RoleplaySide::B, 'Friend', "Hi! It's Sunita — remember me from school?"],
                    [RoleplaySide::A, 'You', 'Sunita! Yes, of course I remember. How are you?'],
                    [RoleplaySide::B, 'Friend', "I'm good! I got your number from Priya. How have you been?"],
                    [RoleplaySide::A, 'You', "I've been well, thank you. It's so nice to hear from you after so long."],
                ],
            ],
            [
                'title' => 'Taking a message for a family member',
                'domain' => RoleplayDomain::PhoneMessages,
                'tip' => "Always repeat the caller's name and message back before ending the call.",
                'lines' => [
                    [RoleplaySide::B, 'Caller', 'Hello, is Rohan there, please?'],
                    [RoleplaySide::A, 'You', "They're not home right now. May I take a message?"],
                    [RoleplaySide::B, 'Caller', 'Yes, please tell them Anil called about the cricket match on Sunday.'],
                    [RoleplaySide::A, 'You', "Sure, I'll tell them as soon as they're back."],
                ],
            ],
            [
                'title' => 'Confirming an appointment by phone',
                'domain' => RoleplayDomain::PhoneMessages,
                'tip' => 'Repeating the time back out loud is the easiest way to avoid a missed appointment.',
                'lines' => [
                    [RoleplaySide::A, 'You', "Hello, I'm calling to confirm my appointment for tomorrow at 4 p.m."],
                    [RoleplaySide::B, 'Receptionist', "Yes, that's confirmed. Please arrive 10 minutes early."],
                    [RoleplaySide::A, 'You', 'Okay, thank you. See you tomorrow.'],
                ],
            ],
            [
                'title' => 'Bargaining at the market',
                'domain' => RoleplayDomain::ShoppingMoney,
                'tip' => 'A polite "can you do..." is the most common way to bargain without sounding rude.',
                'lines' => [
                    [RoleplaySide::A, 'You', 'How much for this vegetable basket?'],
                    [RoleplaySide::B, 'Vendor', '150 rupees.'],
                    [RoleplaySide::A, 'You', "That's a bit high. Can you do 120?"],
                    [RoleplaySide::B, 'Vendor', 'Okay, 130, final price.'],
                    [RoleplaySide::A, 'You', "Alright, I'll take it."],
                ],
            ],
            [
                'title' => 'A wrong bill at the shop',
                'domain' => RoleplayDomain::ShoppingMoney,
                'tip' => 'Point out the mistake calmly and specifically — say exactly what looks wrong.',
                'lines' => [
                    [RoleplaySide::A, 'You', 'Excuse me, I think this bill is wrong. I only bought three items, but it shows four.'],
                    [RoleplaySide::B, 'Shopkeeper', "Let me check... you're right, I'm sorry. Let me correct it."],
                    [RoleplaySide::A, 'You', 'No problem, thank you for checking.'],
                ],
            ],
            [
                'title' => 'Asking about a bank transaction',
                'domain' => RoleplayDomain::ShoppingMoney,
                'tip' => 'Have your account details ready before you call — it saves time on both sides.',
                'lines' => [
                    [RoleplaySide::A, 'You', "Hello, I want to ask about a payment that hasn't reached my account yet."],
                    [RoleplaySide::B, 'Bank Staff', 'Sure, may I have your account number and the transaction date?'],
                    [RoleplaySide::A, 'You', "Yes, it's 4471, and the date was the 3rd of this month."],
                    [RoleplaySide::B, 'Bank Staff', 'Thank you, let me check that for you.'],
                ],
            ],
            [
                'title' => 'Describing a symptom to a doctor',
                'domain' => RoleplayDomain::HealthEverydayHelp,
                'tip' => 'Describe symptoms in the order they started — it helps the listener follow along.',
                'lines' => [
                    [RoleplaySide::A, 'You', "Doctor, I've had a mild fever and a headache since yesterday."],
                    [RoleplaySide::B, 'Doctor', 'Any cough or body pain?'],
                    [RoleplaySide::A, 'You', 'A little body pain, but no cough.'],
                    [RoleplaySide::B, 'Doctor', "Okay, I'll prescribe something. Take rest and drink plenty of water."],
                ],
            ],
            [
                'title' => 'Asking a neighbour for help',
                'domain' => RoleplayDomain::HealthEverydayHelp,
                'tip' => 'Small, specific requests ("for an hour", "keep an eye") are easier for people to say yes to.',
                'lines' => [
                    [RoleplaySide::A, 'You', 'Hi Rekha, could you please keep an eye on my house for an hour? I need to step out.'],
                    [RoleplaySide::B, 'Neighbour', 'Of course, no problem at all.'],
                    [RoleplaySide::A, 'You', 'Thank you so much, I really appreciate it.'],
                ],
            ],
            [
                'title' => 'A visit to the pharmacy',
                'domain' => RoleplayDomain::HealthEverydayHelp,
                'tip' => 'Repeating the dosage back ("one tablet, twice a day") shows you understood correctly.',
                'lines' => [
                    [RoleplaySide::A, 'You', 'I need something for a mild headache, please.'],
                    [RoleplaySide::B, 'Pharmacist', 'Here you go. Take one tablet after food, twice a day.'],
                    [RoleplaySide::A, 'You', 'Thank you. How much do I owe you?'],
                    [RoleplaySide::B, 'Pharmacist', "That'll be 40 rupees."],
                ],
            ],
            [
                'title' => 'A late delivery',
                'domain' => RoleplayDomain::ProblemsComplaints,
                'tip' => 'Stating the order number early speeds up almost any delivery conversation.',
                'lines' => [
                    [RoleplaySide::A, 'You', "Hello, I ordered a package five days ago and it still hasn't arrived."],
                    [RoleplaySide::B, 'Support', "I'm sorry for the delay. Could you share your order number?"],
                    [RoleplaySide::A, 'You', "Yes, it's OD-2291."],
                    [RoleplaySide::B, 'Support', "Thank you, I can see it's on the way and should reach you by tomorrow."],
                ],
            ],
            [
                'title' => 'A noisy neighbour',
                'domain' => RoleplayDomain::ProblemsComplaints,
                'tip' => 'Starting with "I\'m sorry to bother you" softens almost any request to a neighbour.',
                'lines' => [
                    [RoleplaySide::A, 'You', "Hi, I'm sorry to bother you, but could you please lower the music a little? It's quite late."],
                    [RoleplaySide::B, 'Neighbour', "Oh, I didn't realise, sorry! I'll turn it down now."],
                    [RoleplaySide::A, 'You', 'Thank you, I really appreciate it.'],
                ],
            ],
            [
                'title' => 'Reporting a mistake',
                'domain' => RoleplayDomain::ProblemsComplaints,
                'tip' => 'Say what looks wrong and why you think so — it helps the other person check faster.',
                'lines' => [
                    [RoleplaySide::A, 'You', "I think there's a mistake in this month's electricity bill. It's much higher than usual."],
                    [RoleplaySide::B, 'Utility Staff', 'Let me check your meter reading... yes, there was an error. We\'ll correct it.'],
                    [RoleplaySide::A, 'You', 'Thank you for checking so quickly.'],
                ],
            ],
            [
                'title' => 'Meeting a new neighbour',
                'domain' => RoleplayDomain::SocialFamily,
                'tip' => 'A warm welcome and a quick self-introduction go a long way with a new neighbour.',
                'lines' => [
                    [RoleplaySide::B, 'Neighbour', "Hi, we just moved in next door. I'm Kavita."],
                    [RoleplaySide::A, 'You', 'Nice to meet you, Kavita! I live just next door. Welcome to the colony.'],
                    [RoleplaySide::B, 'Neighbour', "Thank you, that's very kind of you."],
                ],
            ],
            [
                'title' => 'Making weekend plans',
                'domain' => RoleplayDomain::SocialFamily,
                'tip' => 'Confirming both the day and the time avoids any last-minute confusion.',
                'lines' => [
                    [RoleplaySide::B, 'Friend', "Are you free this Sunday? Let's meet for tea."],
                    [RoleplaySide::A, 'You', 'Sunday works for me. What time were you thinking?'],
                    [RoleplaySide::B, 'Friend', 'How about 5 in the evening?'],
                    [RoleplaySide::A, 'You', 'Perfect, see you then.'],
                ],
            ],
        ];
    }
}
