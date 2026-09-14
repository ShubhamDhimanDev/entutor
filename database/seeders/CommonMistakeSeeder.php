<?php

namespace Database\Seeders;

use App\Enums\MistakeTag;
use App\Models\CommonMistake;
use Illuminate\Database\Seeder;

class CommonMistakeSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach ($this->mistakes() as $index => $mistake) {
            CommonMistake::create([
                'wrong_sentence' => $mistake[0],
                'corrected_sentence' => $mistake[1],
                'explanation' => $mistake[2],
                'tag' => $mistake[3],
                'order' => $index + 1,
            ]);
        }
    }

    /**
     * 31 common mistakes a Hindi-speaking learner makes, grouped conceptually by
     * tag: state-verb tense misuse, grammar basics, articles, prepositions, direct
     * translation from Hindi, vocabulary false friends, and pronunciation.
     *
     * @return list<array{0: string, 1: string, 2: string, 3: MistakeTag}>
     */
    private function mistakes(): array
    {
        return [
            ['I am understanding.', 'I understand.', "'Understand' is a state verb — it never takes -ing.", MistakeTag::Tense],
            ['I am having a car.', 'I have a car.', 'Possession uses simple present, never continuous.', MistakeTag::Tense],
            ['He is knowing the answer.', 'He knows the answer.', "'Know' is a state verb — it has no -ing form.", MistakeTag::Tense],
            ['I am not knowing.', "I don't know.", 'Negatives of state verbs use don\'t/doesn\'t, not am/is/are + not.', MistakeTag::Tense],
            ['I am belonging to Kanpur.', 'I belong to Kanpur. / I am from Kanpur.', "'Belong' is a state verb — it has no continuous form.", MistakeTag::Tense],
            ['I am having a headache.', 'I have a headache.', "States use simple present; also, 'a' is needed before a singular countable noun.", MistakeTag::Tense],
            ['Yesterday I go to market.', 'Yesterday I went to the market.', 'Past events need the past-tense form of the verb.', MistakeTag::Tense],
            ["I am waiting from 9 o'clock.", "I have been waiting since 9 o'clock.", "An action continuing from a past point uses 'have been' + since.", MistakeTag::Tense],
            ["He didn't gave the file.", "He didn't give the file.", 'After did/didn\'t, the main verb stays in its base form.', MistakeTag::GrammarBasics],
            ['I am agree with you.', 'I agree with you.', "'Agree' is already a full verb — it doesn't need 'am'.", MistakeTag::GrammarBasics],
            ['She is more better than him.', 'She is better than him.', "'Better' is already comparative — don't add 'more'.", MistakeTag::GrammarBasics],
            ['I want to purchase a shoes.', 'I want to buy a pair of shoes. / I want to buy shoes.', "'Shoes' is plural — it can't take 'a' directly.", MistakeTag::Articles],
            ['He is a honest man.', 'He is an honest man.', "Use 'an' before a vowel sound — 'honest' starts with a silent h.", MistakeTag::Articles],
            ['I have headache.', 'I have a headache.', "Singular countable nouns almost always need 'a' or 'the'.", MistakeTag::Articles],
            ['Discuss about the issue.', 'Discuss the issue.', "'Discuss' already means 'talk about' — adding 'about' repeats the meaning.", MistakeTag::Prepositions],
            ['He explained me the process.', 'He explained the process to me.', "'Explain' needs 'to' before the person it's explained to.", MistakeTag::Prepositions],
            ['Since two hours.', 'For two hours.', "Use 'for' with a length of time and 'since' with a starting point.", MistakeTag::Prepositions],
            ['Married with a doctor.', 'Married to a doctor.', "'Married' pairs with 'to', not 'with'.", MistakeTag::Prepositions],
            ['I will go tomorrow market.', 'I will go to the market tomorrow.', "'To' is required before a place name, and time words move to the end of the sentence.", MistakeTag::Prepositions],
            ['Open the light. / Close the fan.', 'Turn on the light. / Turn off the fan.', 'English uses turn on/off for lights and devices, not open/close.', MistakeTag::DirectTranslation],
            ['What is your good name?', 'What is your name?', "'Good name' is a direct translation from Hindi and sounds old-fashioned in English.", MistakeTag::DirectTranslation],
            ['Myself Priya.', 'I am Priya. / My name is Priya.', "'Myself' is a reflexive pronoun — it isn't used to introduce yourself.", MistakeTag::DirectTranslation],
            ['Please do one thing, come here.', 'Please come here.', 'This is a Hindi filler phrase with no natural English equivalent.', MistakeTag::DirectTranslation],
            ['He is my cousin brother.', 'He is my cousin.', "'Cousin' already covers any gender in English.", MistakeTag::DirectTranslation],
            ['Kindly do the needful.', 'Please take care of this. / Please handle this.', 'An old-fashioned, overly formal phrase best avoided in everyday English.', MistakeTag::Vocabulary],
            ['Please revert back at the earliest.', 'Please reply as soon as possible.', "'Revert' already means 'go back' — 'revert back' repeats the idea.", MistakeTag::Vocabulary],
            ['I have a doubt.', 'I have a question.', "In everyday English, 'doubt' means suspicion or disbelief, not a question.", MistakeTag::Vocabulary],
            ['Kindly confirm the same.', 'Please confirm this.', "'The same' used as a stand-in word is old legal-English style.", MistakeTag::Vocabulary],
            ['I passed out in 2015.', 'I graduated in 2015.', "'Pass out' means 'graduate' in Indian English, but internationally it means 'to faint'.", MistakeTag::Vocabulary],
            ["'Wery' good, 'vhat' is this?", 'Very good. What is this?', 'V and W are different sounds in English — v is made with the teeth on the lip, w with rounded lips.', MistakeTag::Pronunciation],
            ["'Dis' is good, I 'tink' so.", 'This is good, I think so.', "For the 'th' sound, the tongue goes lightly between the teeth instead of behind them.", MistakeTag::Pronunciation],
        ];
    }
}
