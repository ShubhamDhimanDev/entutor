import type { TenseRef } from './library';

export type SkillArea =
    | 'reading'
    | 'vocabulary'
    | 'listening'
    | 'speaking'
    | 'writing'
    | 'grammar';

export type DaySummary = {
    id: number;
    day_number: number;
    title: string;
    week_id: number;
    month_id: number;
    done: boolean;
};

export type MonthNavItem = {
    id: number;
    month_number: number;
    theme: string;
};

export type WeeklyRating = {
    reading_rating: number;
    speaking_rating: number;
    listening_rating: number;
    confidence_rating: number;
    note: string | null;
    updated_at: string | null;
};

export type RecentRating = WeeklyRating & {
    week_id: number;
    week_number: number;
    week_title: string;
};

export type DashboardData = {
    program: {
        id: number;
        start_date: string;
    };
    daysDone: number;
    totalDays: number;
    weeksTicked: number;
    totalWeeks: number;
    testsRecorded: number;
    totalTests: number;
    currentDay: DaySummary | null;
    days: DaySummary[];
    months: MonthNavItem[];
    recentRatings: RecentRating[];
};

export type WeekProgress = {
    id: number;
    week_number: number;
    week_in_month: number;
    month_id: number;
    title: string;
    mastery_description: string;
    start_day_number: number;
    end_day_number: number;
    milestone_confirmed: boolean;
    milestone_confirmed_at: string | null;
    rating: WeeklyRating | null;
};

export type MonthTestProgress = {
    id: number;
    month_number: number;
    theme: string;
    record: {
        taken_on: string;
        total_score: number;
        band: string;
        band_label: string;
    } | null;
};

export type DayTask = {
    id: number;
    type: SkillArea;
    content: string;
    estimated_minutes: number;
    completed: boolean;
    vocabulary_items: Array<{
        id: number;
        term: string;
        native_meaning: string;
    }>;
    tense: TenseRef | null;
};

export type MonthlyTestSection = {
    id: number;
    skill: SkillArea;
    weight: number;
    content: string;
    order: number;
};

export type MonthlyTestRubric = {
    band_excellent_min: number;
    band_good_min: number;
    band_fair_min: number;
    sections: MonthlyTestSection[];
};

export type MonthlyTestRecord = {
    id: number;
    taken_on: string;
    total_score: number;
    band: string;
    band_label: string;
    scores: Array<{ skill: SkillArea; score: number }>;
};
