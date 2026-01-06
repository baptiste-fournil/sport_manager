<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import RestTimer from '@/Components/RestTimer.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed, ref, reactive } from 'vue';

const props = defineProps({
    session: {
        type: Object,
        required: true,
    },
});

// Expanded exercises state (accordion)
const expandedExercises = ref(new Set(props.session.exercises.map((ex) => ex.id)));

// Rest timer state
const showRestTimer = ref(false);
const restTimerSeconds = ref(90);
const restStartTime = ref(null);
const previousSetId = ref(null);

// Edit mode state
const editingSetId = ref(null);

// Set forms for each exercise (for adding new sets)
const setForms = reactive({});
props.session.exercises.forEach((exercise) => {
    setForms[exercise.id] = useForm({
        reps: '',
        weight: '',
        duration_seconds: '',
        distance: '',
        notes: '',
        rest_seconds_actual: null,
    });
});

// Edit form (for updating existing sets)
const editForm = useForm({
    reps: '',
    weight: '',
    duration_seconds: '',
    distance: '',
    notes: '',
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatTime = (seconds) => {
    if (!seconds) return '-';
    const minutes = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${minutes}:${secs.toString().padStart(2, '0')}`;
};

const getExerciseTypeBadgeClass = (type) => {
    const baseClasses = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium';
    switch (type) {
        case 'strength':
            return `${baseClasses} bg-blue-100 text-blue-800`;
        case 'cardio':
            return `${baseClasses} bg-red-100 text-red-800`;
        case 'flexibility':
            return `${baseClasses} bg-green-100 text-green-800`;
        case 'balance':
            return `${baseClasses} bg-yellow-100 text-yellow-800`;
        default:
            return `${baseClasses} bg-gray-100 text-gray-800`;
    }
};

const sessionStatus = computed(() => {
    if (props.session.is_completed) {
        return {
            text: 'Completed',
            class: 'bg-green-100 text-green-800',
        };
    }
    return {
        text: 'In Progress',
        class: 'bg-yellow-100 text-yellow-800',
    };
});

// Check if exercise type needs specific fields
const needsRepsWeight = (type) => {
    const cardioTypes = ['cardio', 'running', 'cycling'];
    return !cardioTypes.includes(type?.toLowerCase());
};
const needsDuration = (type) => {
    const cardioTypes = ['cardio', 'flexibility', 'balance', 'mobility', 'running', 'cycling'];
    return cardioTypes.includes(type?.toLowerCase());
};
const needsDistance = (type) => {
    const distanceTypes = ['cardio', 'running', 'cycling'];
    return distanceTypes.includes(type?.toLowerCase());
};

// Toggle exercise accordion
const toggleExercise = (exerciseId) => {
    if (expandedExercises.value.has(exerciseId)) {
        expandedExercises.value.delete(exerciseId);
    } else {
        expandedExercises.value.add(exerciseId);
    }
};

// Add a new set to an exercise
const addSet = (sessionExerciseId, exerciseType) => {
    const form = setForms[sessionExerciseId];

    // Calculate rest time from previous set if timer was running
    if (previousSetId.value && restStartTime.value) {
        const elapsedSeconds = Math.floor((Date.now() - restStartTime.value) / 1000);
        form.rest_seconds_actual = elapsedSeconds;
    }

    form.post(route('session-sets.store', sessionExerciseId), {
        preserveScroll: true,
        onSuccess: () => {
            // Clear form
            form.reset();

            // Reload the page data to get updated sets
            router.reload({
                only: ['session'],
                preserveScroll: true,
                onSuccess: () => {
                    // After reload, start rest timer for next set
                    const exercise = props.session.exercises.find(
                        (ex) => ex.id === sessionExerciseId
                    );
                    if (exercise && exercise.sets.length > 0) {
                        const latestSet = exercise.sets[exercise.sets.length - 1];
                        previousSetId.value = latestSet.id;
                        restStartTime.value = Date.now();
                        restTimerSeconds.value = exercise.default_rest_seconds || 90;
                        showRestTimer.value = true;
                    }
                },
            });
        },
    });
};

// Start editing a set
const startEditSet = (set) => {
    editingSetId.value = set.id;
    editForm.reps = set.reps || '';
    editForm.weight = set.weight || '';
    editForm.duration_seconds = set.duration_seconds || '';
    editForm.distance = set.distance || '';
    editForm.notes = set.notes || '';
};

// Cancel editing a set
const cancelEditSet = () => {
    editingSetId.value = null;
    editForm.reset();
};

// Update an existing set
const updateSet = (setId) => {
    editForm.patch(route('session-sets.update', setId), {
        preserveScroll: true,
        onSuccess: () => {
            // Reload the page data to get updated sets
            router.reload({
                only: ['session'],
                preserveScroll: true,
                onSuccess: () => {
                    editingSetId.value = null;
                    editForm.reset();
                },
            });
        },
    });
};

// Delete a set
const deleteSet = (setId) => {
    if (!confirm('Are you sure you want to delete this set?')) return;

    router.delete(route('session-sets.destroy', setId), {
        preserveScroll: true,
        onSuccess: () => {
            // Reload the page data to get updated sets
            router.reload({
                only: ['session'],
                preserveScroll: true,
            });
        },
    });
};

// Handle rest timer completion
const handleRestComplete = () => {
    // Timer finished naturally
    showRestTimer.value = false;
};

// Handle rest timer skip
const handleRestSkip = (elapsedSeconds) => {
    // Store the elapsed time if we have a previous set
    if (previousSetId.value && elapsedSeconds > 0) {
        router.patch(
            route('session-sets.update', previousSetId.value),
            { rest_seconds_actual: elapsedSeconds },
            { preserveScroll: true }
        );
    }
    showRestTimer.value = false;
};

// Close rest timer
const closeRestTimer = () => {
    showRestTimer.value = false;
};

// Complete the session
const completeSession = () => {
    if (!confirm('Are you sure you want to complete this training session?')) return;

    router.patch(
        route('sessions.complete', props.session.id),
        {},
        {
            preserveScroll: false,
            onSuccess: () => {
                // Success message handled by backend
            },
        }
    );
};

// Quick fill from previous set
const fillFromPreviousSet = (sessionExerciseId) => {
    const exercise = props.session.exercises.find((ex) => ex.id === sessionExerciseId);
    if (!exercise || exercise.sets.length === 0) return;

    const lastSet = exercise.sets[exercise.sets.length - 1];
    const form = setForms[sessionExerciseId];

    form.reps = lastSet.reps || '';
    form.weight = lastSet.weight || '';
    form.duration_seconds = lastSet.duration_seconds || '';
    form.distance = lastSet.distance || '';
};
</script>

<template>
    <Head :title="`Session: ${session.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        {{ session.name }}
                    </h2>
                    <div class="mt-1 flex items-center space-x-2">
                        <span
                            :class="`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${sessionStatus.class}`"
                        >
                            {{ sessionStatus.text }}
                        </span>
                        <span v-if="session.training" class="text-sm text-gray-600">
                            Based on: {{ session.training.name }}
                        </span>
                    </div>
                </div>
                <div v-if="session.is_in_progress">
                    <PrimaryButton @click="completeSession"> Finish Workout </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Session Info Card -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900">Session Details</h3>
                        <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Started At</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ formatDate(session.started_at) }}
                                </dd>
                            </div>
                            <div v-if="session.completed_at">
                                <dt class="text-sm font-medium text-gray-500">Completed At</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ formatDate(session.completed_at) }}
                                </dd>
                            </div>
                            <div v-if="session.notes" class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Notes</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ session.notes }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Exercises List with Live Logging -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900">Exercises</h3>

                        <div v-if="session.exercises.length > 0" class="mt-4 space-y-4">
                            <div
                                v-for="(sessionExercise, index) in session.exercises"
                                :key="sessionExercise.id"
                                class="rounded-lg border border-gray-200 overflow-hidden"
                            >
                                <!-- Exercise Header (Accordion Toggle) -->
                                <button
                                    @click="toggleExercise(sessionExercise.id)"
                                    class="w-full bg-gray-50 p-4 text-left hover:bg-gray-100 transition-colors"
                                >
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-start space-x-3">
                                            <span
                                                class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700"
                                            >
                                                {{ index + 1 }}
                                            </span>
                                            <div>
                                                <h4 class="text-base font-semibold text-gray-900">
                                                    {{ sessionExercise.exercise.name }}
                                                </h4>
                                                <div class="mt-1 flex items-center space-x-2">
                                                    <span
                                                        :class="
                                                            getExerciseTypeBadgeClass(
                                                                sessionExercise.exercise.type
                                                            )
                                                        "
                                                    >
                                                        {{ sessionExercise.exercise.type }}
                                                    </span>
                                                    <span
                                                        v-if="sessionExercise.exercise.muscle_group"
                                                        class="text-sm text-gray-500"
                                                    >
                                                        {{ sessionExercise.exercise.muscle_group }}
                                                    </span>
                                                    <span class="text-sm text-gray-500">
                                                        • {{ sessionExercise.sets.length
                                                        }}{{
                                                            sessionExercise.template_sets
                                                                ? `/${sessionExercise.template_sets}`
                                                                : ''
                                                        }}
                                                        sets
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <svg
                                            :class="[
                                                'h-5 w-5 text-gray-400 transition-transform',
                                                expandedExercises.has(sessionExercise.id)
                                                    ? 'rotate-180'
                                                    : '',
                                            ]"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 9l-7 7-7-7"
                                            />
                                        </svg>
                                    </div>
                                </button>

                                <!-- Exercise Content (Collapsible) -->
                                <div
                                    v-show="expandedExercises.has(sessionExercise.id)"
                                    class="p-4 border-t border-gray-200"
                                >
                                    <p
                                        v-if="sessionExercise.notes"
                                        class="mb-4 text-sm text-gray-600 italic"
                                    >
                                        {{ sessionExercise.notes }}
                                    </p>

                                    <!-- Sets Table -->
                                    <div v-if="sessionExercise.sets.length > 0" class="mb-4">
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th
                                                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                        >
                                                            Set
                                                        </th>
                                                        <th
                                                            v-if="
                                                                needsRepsWeight(
                                                                    sessionExercise.exercise.type
                                                                )
                                                            "
                                                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                        >
                                                            Reps
                                                        </th>
                                                        <th
                                                            v-if="
                                                                needsRepsWeight(
                                                                    sessionExercise.exercise.type
                                                                )
                                                            "
                                                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                        >
                                                            Weight
                                                        </th>
                                                        <th
                                                            v-if="
                                                                needsDuration(
                                                                    sessionExercise.exercise.type
                                                                )
                                                            "
                                                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                        >
                                                            Duration
                                                        </th>
                                                        <th
                                                            v-if="
                                                                needsDistance(
                                                                    sessionExercise.exercise.type
                                                                )
                                                            "
                                                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                        >
                                                            Distance
                                                        </th>
                                                        <th
                                                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                        >
                                                            Rest
                                                        </th>
                                                        <th
                                                            v-if="!session.is_completed"
                                                            class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                        >
                                                            Actions
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    <tr
                                                        v-for="set in sessionExercise.sets"
                                                        :key="set.id"
                                                        :class="
                                                            editingSetId === set.id
                                                                ? 'bg-blue-50'
                                                                : ''
                                                        "
                                                    >
                                                        <!-- Normal Display Mode -->
                                                        <template v-if="editingSetId !== set.id">
                                                            <td
                                                                class="px-3 py-2 whitespace-nowrap text-sm text-gray-900"
                                                            >
                                                                {{ set.set_index }}
                                                            </td>
                                                            <td
                                                                v-if="
                                                                    needsRepsWeight(
                                                                        sessionExercise.exercise
                                                                            .type
                                                                    )
                                                                "
                                                                class="px-3 py-2 whitespace-nowrap text-sm text-gray-900"
                                                            >
                                                                {{ set.reps || '-' }}
                                                            </td>
                                                            <td
                                                                v-if="
                                                                    needsRepsWeight(
                                                                        sessionExercise.exercise
                                                                            .type
                                                                    )
                                                                "
                                                                class="px-3 py-2 whitespace-nowrap text-sm text-gray-900"
                                                            >
                                                                {{
                                                                    set.weight
                                                                        ? `${set.weight} kg`
                                                                        : '-'
                                                                }}
                                                            </td>
                                                            <td
                                                                v-if="
                                                                    needsDuration(
                                                                        sessionExercise.exercise
                                                                            .type
                                                                    )
                                                                "
                                                                class="px-3 py-2 whitespace-nowrap text-sm text-gray-900"
                                                            >
                                                                {{
                                                                    formatTime(set.duration_seconds)
                                                                }}
                                                            </td>
                                                            <td
                                                                v-if="
                                                                    needsDistance(
                                                                        sessionExercise.exercise
                                                                            .type
                                                                    )
                                                                "
                                                                class="px-3 py-2 whitespace-nowrap text-sm text-gray-900"
                                                            >
                                                                {{
                                                                    set.distance
                                                                        ? `${set.distance} km`
                                                                        : '-'
                                                                }}
                                                            </td>
                                                            <td
                                                                class="px-3 py-2 whitespace-nowrap text-sm text-gray-500"
                                                            >
                                                                {{
                                                                    formatTime(
                                                                        set.rest_seconds_actual
                                                                    )
                                                                }}
                                                            </td>
                                                            <td
                                                                v-if="!session.is_completed"
                                                                class="px-3 py-2 whitespace-nowrap text-right text-sm font-medium space-x-2"
                                                            >
                                                                <button
                                                                    @click="startEditSet(set)"
                                                                    class="text-indigo-600 hover:text-indigo-900"
                                                                >
                                                                    Edit
                                                                </button>
                                                                <button
                                                                    @click="deleteSet(set.id)"
                                                                    class="text-red-600 hover:text-red-900"
                                                                >
                                                                    Delete
                                                                </button>
                                                            </td>
                                                        </template>

                                                        <!-- Edit Mode -->
                                                        <template v-else>
                                                            <td
                                                                class="px-3 py-2 whitespace-nowrap text-sm text-gray-900"
                                                            >
                                                                {{ set.set_index }}
                                                            </td>
                                                            <td
                                                                v-if="
                                                                    needsRepsWeight(
                                                                        sessionExercise.exercise
                                                                            .type
                                                                    )
                                                                "
                                                                class="px-3 py-2"
                                                            >
                                                                <TextInput
                                                                    v-model="editForm.reps"
                                                                    type="number"
                                                                    class="w-20"
                                                                    min="1"
                                                                />
                                                            </td>
                                                            <td
                                                                v-if="
                                                                    needsRepsWeight(
                                                                        sessionExercise.exercise
                                                                            .type
                                                                    )
                                                                "
                                                                class="px-3 py-2"
                                                            >
                                                                <TextInput
                                                                    v-model="editForm.weight"
                                                                    type="number"
                                                                    class="w-24"
                                                                    step="0.5"
                                                                    min="0"
                                                                />
                                                            </td>
                                                            <td
                                                                v-if="
                                                                    needsDuration(
                                                                        sessionExercise.exercise
                                                                            .type
                                                                    )
                                                                "
                                                                class="px-3 py-2"
                                                            >
                                                                <TextInput
                                                                    v-model="
                                                                        editForm.duration_seconds
                                                                    "
                                                                    type="number"
                                                                    class="w-24"
                                                                    min="1"
                                                                    placeholder="seconds"
                                                                />
                                                            </td>
                                                            <td
                                                                v-if="
                                                                    needsDistance(
                                                                        sessionExercise.exercise
                                                                            .type
                                                                    )
                                                                "
                                                                class="px-3 py-2"
                                                            >
                                                                <TextInput
                                                                    v-model="editForm.distance"
                                                                    type="number"
                                                                    class="w-24"
                                                                    step="0.1"
                                                                    min="0"
                                                                />
                                                            </td>
                                                            <td
                                                                class="px-3 py-2 whitespace-nowrap text-sm text-gray-500"
                                                            >
                                                                {{
                                                                    formatTime(
                                                                        set.rest_seconds_actual
                                                                    )
                                                                }}
                                                            </td>
                                                            <td
                                                                class="px-3 py-2 whitespace-nowrap text-right text-sm font-medium space-x-2"
                                                            >
                                                                <button
                                                                    @click="updateSet(set.id)"
                                                                    class="text-green-600 hover:text-green-900"
                                                                    :disabled="editForm.processing"
                                                                >
                                                                    Save
                                                                </button>
                                                                <button
                                                                    @click="cancelEditSet"
                                                                    class="text-gray-600 hover:text-gray-900"
                                                                >
                                                                    Cancel
                                                                </button>
                                                            </td>
                                                        </template>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Add Set Form (Only if session in progress) -->
                                    <div
                                        v-if="!session.is_completed"
                                        class="mt-4 rounded-lg bg-gray-50 p-4"
                                    >
                                        <h5 class="text-sm font-medium text-gray-700 mb-3">
                                            Add New Set
                                        </h5>
                                        <form
                                            @submit.prevent="
                                                addSet(
                                                    sessionExercise.id,
                                                    sessionExercise.exercise.type
                                                )
                                            "
                                            class="space-y-3"
                                        >
                                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                                <!-- Reps (for most exercises) -->
                                                <div>
                                                    <InputLabel for="reps" value="Reps" />
                                                    <TextInput
                                                        v-model="setForms[sessionExercise.id].reps"
                                                        type="number"
                                                        class="mt-1 w-full"
                                                        min="1"
                                                        placeholder="10"
                                                    />
                                                    <InputError
                                                        :message="
                                                            setForms[sessionExercise.id].errors.reps
                                                        "
                                                        class="mt-1"
                                                    />
                                                </div>

                                                <!-- Weight (for strength exercises) -->
                                                <div>
                                                    <InputLabel for="weight" value="Weight (kg)" />
                                                    <TextInput
                                                        v-model="
                                                            setForms[sessionExercise.id].weight
                                                        "
                                                        type="number"
                                                        class="mt-1 w-full"
                                                        step="0.5"
                                                        min="0"
                                                        placeholder="50"
                                                    />
                                                    <InputError
                                                        :message="
                                                            setForms[sessionExercise.id].errors
                                                                .weight
                                                        "
                                                        class="mt-1"
                                                    />
                                                </div>

                                                <!-- Duration (for timed exercises) -->
                                                <div>
                                                    <InputLabel
                                                        for="duration"
                                                        value="Duration (sec)"
                                                    />
                                                    <TextInput
                                                        v-model="
                                                            setForms[sessionExercise.id]
                                                                .duration_seconds
                                                        "
                                                        type="number"
                                                        class="mt-1 w-full"
                                                        min="1"
                                                        placeholder="60"
                                                    />
                                                    <InputError
                                                        :message="
                                                            setForms[sessionExercise.id].errors
                                                                .duration_seconds
                                                        "
                                                        class="mt-1"
                                                    />
                                                </div>

                                                <!-- Distance (for cardio exercises) -->
                                                <div>
                                                    <InputLabel
                                                        for="distance"
                                                        value="Distance (km)"
                                                    />
                                                    <TextInput
                                                        v-model="
                                                            setForms[sessionExercise.id].distance
                                                        "
                                                        type="number"
                                                        class="mt-1 w-full"
                                                        step="0.1"
                                                        min="0"
                                                        placeholder="5.0"
                                                    />
                                                    <InputError
                                                        :message="
                                                            setForms[sessionExercise.id].errors
                                                                .distance
                                                        "
                                                        class="mt-1"
                                                    />
                                                </div>
                                            </div>

                                            <!-- Notes -->
                                            <div>
                                                <InputLabel for="notes" value="Notes (optional)" />
                                                <TextInput
                                                    v-model="setForms[sessionExercise.id].notes"
                                                    type="text"
                                                    class="mt-1 w-full"
                                                    placeholder="Felt strong, good form"
                                                />
                                                <InputError
                                                    :message="
                                                        setForms[sessionExercise.id].errors.notes
                                                    "
                                                    class="mt-1"
                                                />
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="flex items-center space-x-3">
                                                <PrimaryButton
                                                    type="submit"
                                                    :disabled="
                                                        setForms[sessionExercise.id].processing
                                                    "
                                                >
                                                    Add Set
                                                </PrimaryButton>
                                                <SecondaryButton
                                                    v-if="sessionExercise.sets.length > 0"
                                                    type="button"
                                                    @click="fillFromPreviousSet(sessionExercise.id)"
                                                >
                                                    Same as Last
                                                </SecondaryButton>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty state for exercises -->
                        <div
                            v-else
                            class="mt-4 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-8 text-center"
                        >
                            <svg
                                class="mx-auto h-12 w-12 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                />
                            </svg>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900">
                                No exercises yet
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                This is a blank session. Add exercises during your workout.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 flex justify-between">
                    <SecondaryButton @click="$inertia.visit(route('dashboard'))">
                        Back to Dashboard
                    </SecondaryButton>
                    <div v-if="session.training" class="text-sm text-gray-500">
                        <Link
                            :href="route('trainings.show', session.training.id)"
                            class="text-indigo-600 hover:text-indigo-900"
                        >
                            View Training Template
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rest Timer Modal -->
        <RestTimer
            :show="showRestTimer"
            :target-seconds="restTimerSeconds"
            @complete="handleRestComplete"
            @skip="handleRestSkip"
            @close="closeRestTimer"
        />
    </AuthenticatedLayout>
</template>
