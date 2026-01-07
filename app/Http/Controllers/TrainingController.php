<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrainingStoreRequest;
use App\Http\Requests\TrainingUpdateRequest;
use App\Models\Training;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrainingController extends Controller
{
    /**
     * Display a listing of the user's trainings.
     */
    public function index(Request $request): Response
    {
        $query = Training::where('user_id', $request->user()->id)
            ->withCount('trainingExercises');

        // Search filter
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $trainings = $query->orderBy('name')->get();

        return Inertia::render('Trainings/Index', [
            'trainings' => $trainings,
            'filters' => [
                'search' => $request->search,
            ],
        ]);
    }

    /**
     * Show the form for creating a new training.
     */
    public function create(): Response
    {
        return Inertia::render('Trainings/Create');
    }

    /**
     * Store a newly created training in storage.
     */
    public function store(TrainingStoreRequest $request): RedirectResponse
    {
        $training = $request->user()->trainings()->create($request->validated());

        return redirect()->route('trainings.show', $training)
            ->with('success', 'Training created successfully.');
    }

    /**
     * Display the specified training with its exercises.
     */
    public function show(Request $request, Training $training): Response
    {
        $this->authorize('view', $training);

        $training->load([
            'trainingExercises' => function ($query) {
                $query->orderBy('order_index');
            },
            'trainingExercises.exercise',
        ]);

        // Get user's exercises for the exercise selector
        $exercises = $request->user()->exercises()
            ->orderBy('name')
            ->get();

        return Inertia::render('Trainings/Show', [
            'training' => $training,
            'exercises' => $exercises,
        ]);
    }

    /**
     * Show the form for editing the specified training.
     */
    public function edit(Training $training): Response
    {
        $this->authorize('update', $training);

        return Inertia::render('Trainings/Edit', [
            'training' => $training,
        ]);
    }

    /**
     * Update the specified training in storage.
     */
    public function update(TrainingUpdateRequest $request, Training $training): RedirectResponse
    {
        $this->authorize('update', $training);

        $training->update($request->validated());

        return redirect()->route('trainings.show', $training)
            ->with('success', 'Training updated successfully.');
    }

    /**
     * Remove the specified training from storage.
     */
    public function destroy(Training $training): RedirectResponse
    {
        $this->authorize('delete', $training);

        $training->delete();

        return redirect()->route('trainings.index')
            ->with('success', 'Training deleted successfully.');
    }
}
