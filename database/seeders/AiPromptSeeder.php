<?php

namespace Database\Seeders;

use App\Models\AiPrompt;
use App\Models\AiPromptCategory;
use Illuminate\Database\Seeder;

class AiPromptSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach ($this->categories() as $categoryIndex => $category) {
            $aiPromptCategory = AiPromptCategory::create([
                'name' => $category['name'],
                'order' => $categoryIndex + 1,
            ]);

            foreach ($category['prompts'] as $promptIndex => $promptText) {
                AiPrompt::create([
                    'ai_prompt_category_id' => $aiPromptCategory->id,
                    'prompt_text' => $promptText,
                    'order' => $promptIndex + 1,
                ]);
            }
        }
    }

    /**
     * 30 copy-paste prompts for practising with any general-purpose AI chat tool,
     * across 6 categories. Bracketed placeholders like "[paste your message]" are
     * intentional — the learner fills them in before sending the prompt.
     *
     * @return list<array{name: string, prompts: list<string>}>
     */
    private function categories(): array
    {
        return [
            [
                'name' => 'Daily Conversation Partner',
                'prompts' => [
                    'I am learning English. Please talk to me in very simple English, like you are talking to a beginner. Ask me easy questions about my day, one at a time, and wait for my answer before asking the next question.',
                    "Let's have a simple conversation in English about my family. Use short sentences. If I make a mistake, don't stop me — tell me the correct sentence only after I finish speaking.",
                    'Please give me one simple topic and I will speak about it for one minute. After I finish, tell me in simple English: one thing I did well, and one thing to improve.',
                    'Ask me 10 simple personal questions in English, one by one, like a friendly chat. Wait for each answer before asking the next question.',
                    'I want to practise describing my day. I will type what I did today in simple English. Please check if my sentences are correct, and rewrite any wrong ones using easy words.',
                ],
            ],
            [
                'name' => 'Roleplay Simulation',
                'prompts' => [
                    'Roleplay: you are a shopkeeper and I am buying vegetables. Speak in simple English, one message at a time, and wait for my reply before continuing.',
                    'Roleplay: you are a doctor and I am describing a mild health problem. Keep your English simple and give me time to reply.',
                    'Roleplay: you are my neighbour and we are making weekend plans on the phone. Give me a simple, friendly conversation, one line at a time.',
                    'Roleplay: you are a customer support agent and I am asking about a late delivery. Please use simple, everyday English.',
                    'Roleplay: you are a bank staff member and I am asking about a payment. Please talk to me about it in simple English, one step at a time.',
                    'Roleplay: you are a new neighbour meeting me for the first time. Introduce yourself and ask me simple questions, one at a time.',
                ],
            ],
            [
                'name' => 'Pronunciation & Speaking Feedback',
                'prompts' => [
                    'I will type a sentence. Please tell me which words are commonly mispronounced by Hindi speakers, and explain simply how to say them correctly.',
                    "Give me 10 short English sentences that are good for practising the 'th' sound.",
                    'Give me 10 short sentences that mix the v and w sounds, so I can practise saying them correctly.',
                    "I say 'wery' instead of 'very' and 'dis' instead of 'this'. Please explain, step by step in simple words, exactly how to fix these two sounds.",
                ],
            ],
            [
                'name' => 'Vocabulary Quiz',
                'prompts' => [
                    'Give me a mini vocabulary quiz of 10 simple English words related to everyday life. Ask me the meaning of one word at a time, and tell me if I am right or wrong.',
                    'I will type 10 English words. Please give me one simple example sentence for each, using easy everyday situations.',
                    'Please test me on these words: [paste 10 words from Word Bank]. Ask me to use each one in a sentence, one at a time.',
                    "Play a 'guess the word' game with me: describe a simple English word in easy sentences, and I will guess what the word is.",
                ],
            ],
            [
                'name' => 'Writing & Mistake Feedback',
                'prompts' => [
                    'I wrote this message in English: [paste your message]. Please check if it sounds polite and clear. Point out only mistakes that change the meaning or sound rude — skip small grammar issues.',
                    'Please give me a simple template for writing a message asking a friend or family member for help. Keep the language very easy.',
                    'I want to message a shopkeeper about a delayed delivery. Please give me 3 different simple, polite ways to say it.',
                    'Check this message for tone: [paste your message]. Tell me if it sounds too rude, too casual, or just right.',
                    'I am a Hindi speaker learning English. I will type sentences during the day. Please only correct mistakes that would confuse a listener — ignore small grammar mistakes that don\'t affect meaning.',
                    'Here are some sentences I am not sure about: [paste sentences]. Please tell me simply if each one is correct or wrong, and give the correct version.',
                    'I often make mistakes with tenses. Please give me 5 simple practice sentences using the past tense, and check my answers when I write them.',
                ],
            ],
            [
                'name' => 'Spontaneous Conversation',
                'prompts' => [
                    'You are a stranger I just met at a family function. Ask me 8 simple questions about myself, one at a time, in simple English. Wait for my answer each time, and give short feedback at the end.',
                    'You are a new neighbour. Ask me about my family, my daily routine, and my interests. Keep questions simple and one at a time.',
                    "I will answer 'Tell me about yourself' in English. Please tell me if my answer sounds clear and confident, and suggest one way to make it better.",
                    'Give me 5 tricky personal questions that beginners often struggle with, in very simple English, one at a time.',
                ],
            ],
        ];
    }
}
