<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExerciseStoreRequest;
use App\Http\Requests\ExerciseUpdateRequest;
use App\Models\Exercise;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExerciseController extends Controller
{
    /**
     * Display a listing of the exercises.
     */
    public function index(Request $request): Response
    {
        $query = Exercise::where('user_id', $request->user()->id);

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $exercises = $query->orderBy('name')->get();

        return Inertia::render('Exercises/Index', [
            'exercises' => $exercises,
            'filters' => [
                'search' => $request->search,
                'type' => $request->type,
            ],
        ]);
    }

    /**
     * Show the form for creating a new exercise.
     */
    public function create(): Response
    {
        return Inertia::render('Exercises/Create');
    }

    /**
     * Store a newly created exercise in storage.
     */
    public function store(ExerciseStoreRequest $request): RedirectResponse
    {
        $request->user()->exercises()->create($request->validated());

        return redirect()->route('exercises.index')
            ->with('success', 'Exercise created successfully.');
    }

    /**
     * Display the specified exercise.
     */
    public function show(Exercise $exercise): Response
    {
        $this->authorize('view', $exercise);

        return Inertia::render('Exercises/Show', [
            'exercise' => $exercise,
        ]);
    }

    /**
     * Show the form for editing the specified exercise.
     */
    public function edit(Exercise $exercise): Response
    {
        $this->authorize('update', $exercise);

        return Inertia::render('Exercises/Edit', [
            'exercise' => $exercise,
        ]);
    }

    /**
     * Update the specified exercise in storage.
     */
    public function update(ExerciseUpdateRequest $request, Exercise $exercise): RedirectResponse
    {
        $this->authorize('update', $exercise);

        $exercise->update($request->validated());

        return redirect()->route('exercises.index')
            ->with('success', 'Exercise updated successfully.');
    }

    /**
     * Remove the specified exercise from storage.
     */
    public function destroy(Exercise $exercise): RedirectResponse
    {
        $this->authorize('delete', $exercise);

        $exercise->delete();

        return redirect()->route('exercises.index')
            ->with('success', 'Exercise deleted successfully.');
    }
}
