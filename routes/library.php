<?php

// Content-library browsing routes (Word Bank, Mistakes, Roleplay, AI Prompts, Confidence Q&A) —
// read-only reference content, identical for every user regardless of role (learner or coach).

use App\Http\Controllers\AiPromptController;
use App\Http\Controllers\ConfidenceQaController;
use App\Http\Controllers\MistakeController;
use App\Http\Controllers\RoleplayController;
use App\Http\Controllers\TenseController;
use App\Http\Controllers\WordBankController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('word-bank', [WordBankController::class, 'index'])->name('word-bank.index');

    Route::get('mistakes', [MistakeController::class, 'index'])->name('mistakes.index');

    Route::get('roleplay', [RoleplayController::class, 'index'])->name('roleplay.index');
    Route::get('roleplay/{roleplayScenario}', [RoleplayController::class, 'show'])->name('roleplay.show');

    Route::get('ai-prompts', [AiPromptController::class, 'index'])->name('ai-prompts.index');

    Route::get('confidence-qa', [ConfidenceQaController::class, 'index'])->name('confidence-qa.index');

    Route::get('tenses', [TenseController::class, 'index'])->name('tenses.index');
    Route::get('tenses/{tense:key}', [TenseController::class, 'show'])->name('tenses.show');
});
