<?php

namespace Database\Seeders;

use App\Models\ConfidenceQuestion;
use App\Models\ConfidenceTopic;
use Illuminate\Database\Seeder;

class ConfidenceQaSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach ($this->topics() as $topicIndex => $topic) {
            $confidenceTopic = ConfidenceTopic::create([
                'name' => $topic['name'],
                'order' => $topicIndex + 1,
            ]);

            foreach ($topic['questions'] as $questionIndex => $question) {
                ConfidenceQuestion::create([
                    'confidence_topic_id' => $confidenceTopic->id,
                    'question' => $question[0],
                    'example_answer' => $question[1],
                    'order' => $questionIndex + 1,
                ]);
            }
        }
    }

    /**
     * 35 confidence-building Q&A pairs across 6 topics, giving the learner a
     * ready spoken answer for common questions. "___" marks a blank the learner
     * fills in with their own detail; bracketed phrases in a question (e.g.
     * "[a task]") mark a generic placeholder a coach/interviewer would replace
     * with a real topic when asking it aloud.
     *
     * @return list<array{name: string, questions: list<array{0: string, 1: string}>}>
     */
    private function topics(): array
    {
        return [
            [
                'name' => 'Everyday Questions (Ask Anyone)',
                'questions' => [
                    ['Tell me about yourself.', 'My name is ___. I am from ___. I have been learning English for the last few months. I enjoy cooking and spending time with my family.'],
                    ['What do you do every day?', 'I wake up early, take care of the house, and in the afternoon I practise English for an hour.'],
                    ['What are you good at?', 'I am organised and patient. For example, I have practised English every day for months without missing a day.'],
                    ['What is something you find difficult?', 'Speaking quickly used to be hard for me, but I have practised daily and I am much more confident now.'],
                    ['What are your future plans?', 'I want to keep improving my English and eventually feel completely comfortable speaking with anyone.'],
                    ['Do you have any experience with [a task]?', "I haven't done this before, but I learn quickly and I am ready to try."],
                    ['How do you spend your free time?', 'I like reading, cooking new dishes, and talking with my family in the evening.'],
                    ['What have you been doing recently?', 'I have been focusing on my English and spending time with my family. I feel ready to try new things now.'],
                    ['Do you have any questions for me?', 'I always try to have one or two ready — for example, "What do you usually do on weekends?" or "How long have you lived here?"'],
                    ['Can you say that again, more slowly?', 'Sorry, could you say that again a little slower, please?'],
                ],
            ],
            [
                'name' => 'Family & Home',
                'questions' => [
                    ['Tell me about your family.', 'I live with my family in ___. We are a close family, and we support each other.'],
                    ['Where are you from?', 'I am from ___, in Northern India. I have lived here for ___ years.'],
                    ['What does a typical day look like for you?', 'I usually wake up early, manage the house, and spend some time learning something new every day.'],
                    ['Who do you live with?', 'I live with my family, and we support each other a lot.'],
                    ['What is your home like?', 'We have a comfortable home with a small garden. I enjoy keeping it tidy and welcoming.'],
                ],
            ],
            [
                'name' => 'Health & Wellbeing',
                'questions' => [
                    ['How are you feeling today?', "I'm feeling good today, thank you. How about you?"],
                    ['Do you exercise or stay active?', 'I try to walk every morning and stay active around the house.'],
                    ["What do you do when you're feeling unwell?", "I rest, drink warm water, and see a doctor if it doesn't improve in a day or two."],
                    ['How do you handle stress?', 'I talk to my family, take a short walk, or just take a few deep breaths.'],
                    ['What do you do to relax?', 'I like listening to music, cooking, or having a quiet cup of tea.'],
                ],
            ],
            [
                'name' => 'Opinions & Preferences',
                'questions' => [
                    ['What do you think about [a simple topic]?', "I think it's a good idea, because it saves time for everyone."],
                    ['Do you prefer mornings or evenings?', 'I prefer mornings, because I feel fresh and get more done.'],
                    ['What is your favourite thing to cook?', 'My favourite thing to cook is ___, because it reminds me of home.'],
                    ['What kind of movies or shows do you like?', 'I enjoy family dramas and comedies — something light and enjoyable.'],
                    ['Do you agree or disagree with [a statement]? Why?', 'I agree, because it makes things simpler for everyone involved.'],
                ],
            ],
            [
                'name' => 'Plans & Future',
                'questions' => [
                    ['What are you planning to do this weekend?', 'This weekend, I am planning to visit my family and rest a little.'],
                    ['Where do you see yourself in a year?', 'In a year, I hope to be speaking English confidently and trying new things.'],
                    ["Do you have any goals you're working towards?", 'Yes, my main goal right now is to become fluent and comfortable speaking English every day.'],
                    ['What would you like to learn next?', 'I would like to learn more about using a computer and the internet confidently.'],
                    ['If you could change one thing about your routine, what would it be?', 'I would like to add more time for reading and learning something new.'],
                ],
            ],
            [
                'name' => 'Meeting New People',
                'questions' => [
                    ['How do you introduce yourself to someone new?', "Hello, my name is ___. It's nice to meet you. I try to keep it short, smile, and make eye contact."],
                    ["What do you say when you don't understand someone?", "Sorry, I didn't quite catch that. Could you please repeat it?"],
                    ['How do you start a conversation with a stranger?', 'A simple comment works well, like "It\'s a lovely day today" or "Have you been waiting long?"'],
                    ['How do you end a conversation politely?', 'It was really nice talking to you. I should get going now — take care!'],
                    ['What do you say if you make a mistake while speaking?', 'That\'s okay — a simple "Sorry, let me say that again" is all you need. Everyone makes mistakes while learning.'],
                ],
            ],
        ];
    }
}
