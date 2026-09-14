<?php

namespace App\Http\Controllers;

use App\Actions\BuildLearnerDashboardData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the learner's own progress dashboard.
     */
    public function index(Request $request, BuildLearnerDashboardData $builder): Response
    {
        $learnerProgram = $request->user()->learnerProgram()->firstOrFail();

        return Inertia::render('dashboard', $builder->handle($learnerProgram));
    }
}
