/**
 * Shapes shared by the content-library browsing pages (Word Bank, Fix Common
 * Mistakes, Roleplay Scenarios, AI Practice Prompts, Confidence Q&A). This
 * content is global reference material — identical for every user regardless
 * of role — so none of these types carry any per-learner progress state.
 */

/** Mirrors Laravel's default `LengthAwarePaginator::toArray()` JSON shape. */
export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type WordBankGroupOption = {
    id: number;
    name: string;
};

export type WordBankEntry = {
    id: number;
    term: string;
    native_meaning: string;
    native_transliteration: string;
    example_sentence: string;
    group: WordBankGroupOption;
};

export type MistakeTagOption = {
    value: string;
    label: string;
};

export type CommonMistake = {
    id: number;
    wrong_sentence: string;
    corrected_sentence: string;
    explanation: string;
    tag: string;
};

export type RoleplayDomainOption = {
    value: string;
    label: string;
};

export type RoleplayScenarioSummary = {
    id: number;
    title: string;
    domain: string;
    tip_preview: string;
};

export type RoleplayLine = {
    id: number;
    side: 'a' | 'b';
    speaker_label: string;
    line_text: string;
};

export type RoleplayScenarioDetail = {
    id: number;
    title: string;
    domain: string;
    domain_label: string;
    tip: string;
    lines: RoleplayLine[];
};

export type AiPrompt = {
    id: number;
    prompt_text: string;
};

export type AiPromptCategory = {
    id: number;
    name: string;
    prompts: AiPrompt[];
};

export type ConfidenceQuestion = {
    id: number;
    question: string;
    example_answer: string;
};

export type ConfidenceTopic = {
    id: number;
    name: string;
    questions: ConfidenceQuestion[];
};
