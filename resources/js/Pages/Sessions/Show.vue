<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ExerciseStepper from '@/Components/ExerciseStepper.vue';
import SessionProgressFooter from '@/Components/SessionProgressFooter.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import RestTimer from '@/Components/RestTimer.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useSessionStore } from '@/Composables/useSessionStore.js';

const props = defineProps({
    session: {
        type: Object,
        required: true,
    },
});

// Initialize session store
const {
    session: sessionData,
    exercises,
    loading,
    error,
    isCompleted,
    addSet,
    updateSet,
    deleteSet,
    getSessionStats,
} = useSessionStore(props.session);

// Stepper state
const currentExerciseIndex = ref(0);

// Current exercise
const currentExercise = computed(() => exercises.value[currentExerciseIndex.value] || null);

// Rest timer state
const showRestTimer = ref(false);
const restTimerSeconds = ref(90);
const restStartTime = ref(null);
const previousSetId = ref(null);

// Edit mode state
const editingSetId = ref(null);

// Form state for adding new set
const setForm = ref({
    reps: '',
    weight: '',
    duration_seconds: '',
    distance: '',
    notes: '',
});

const setFormErrors = ref({});

// Form state for editing set
const editForm = ref({
    reps: '',
    weight: '',
    duration_seconds: '',
    distance: '',
    notes: '',
});

const editFormErrors = ref({});

// Helper functions
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
    if (isCompleted.value) {
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

// Stepper navigation
const handleJumpTo = (index) => {
    currentExerciseIndex.value = index;
};

// Add a new set
const handleAddSet = async () => {
    if (!currentExercise.value) return;

    setFormErrors.value = {};

    // Calculate rest time from previous set if timer was running
    let restSecondsActual = null;
    if (previousSetId.value && restStartTime.value) {
        restSecondsActual = Math.floor((Date.now() - restStartTime.value) / 1000);
    }

    try {
        const setData = {
            reps: setForm.value.reps ? Number(setForm.value.reps) : null,
            weight: setForm.value.weight ? Number(setForm.value.weight) : null,
            duration_seconds: setForm.value.duration_seconds
                ? Number(setForm.value.duration_seconds)
                : null,
            distance: setForm.value.distance ? Number(setForm.value.distance) : null,
            notes: setForm.value.notes || null,
            rest_seconds_actual: restSecondsActual,
        };

        await addSet(currentExercise.value.id, setData);
        // Clear form
        setForm.value = {
            reps: '',
            weight: '',
            duration_seconds: '',
            distance: '',
            notes: '',
        };

        // Start rest timer
        const sets = currentExercise.value.sets;
        if (sets.length > 0) {
            previousSetId.value = sets[sets.length - 1].id;
            restStartTime.value = Date.now();
            restTimerSeconds.value = currentExercise.value.default_rest_seconds || 90;
            showRestTimer.value = true;
        }
    } catch (err) {
        if (err.response?.data?.errors) {
            setFormErrors.value = err.response.data.errors;
        }
    }
};

// Start editing a set
const startEditSet = (set) => {
    editingSetId.value = set.id;
    editForm.value = {
        reps: set.reps ? String(set.reps) : '',
        weight: set.weight ? String(set.weight) : '',
        duration_seconds: set.duration_seconds ? String(set.duration_seconds) : '',
        distance: set.distance ? String(set.distance) : '',
        notes: set.notes || '',
    };
};

// Cancel editing
const cancelEditSet = () => {
    editingSetId.value = null;
    editForm.value = {
        reps: '',
        weight: '',
        duration_seconds: '',
        distance: '',
        notes: '',
    };
    editFormErrors.value = {};
};

// Update a set
const handleUpdateSet = async (setId) => {
    editFormErrors.value = {};

    try {
        const setData = {
            reps: editForm.value.reps ? Number(editForm.value.reps) : null,
            weight: editForm.value.weight ? Number(editForm.value.weight) : null,
            duration_seconds: editForm.value.duration_seconds
                ? Number(editForm.value.duration_seconds)
                : null,
            distance: editForm.value.distance ? Number(editForm.value.distance) : null,
            notes: editForm.value.notes || null,
        };

        await updateSet(setId, setData);

        cancelEditSet();
    } catch (err) {
        if (err.response?.data?.errors) {
            editFormErrors.value = err.response.data.errors;
        }
    }
};

// Delete a set
const handleDeleteSet = async (setId) => {
    if (!confirm('Are you sure you want to delete this set?')) return;

    try {
        await deleteSet(setId, currentExercise.value.id);
    } catch (err) {
        console.error('Failed to delete set:', err);
    }
};

// Rest timer handlers
const handleRestComplete = () => {
    showRestTimer.value = false;
};

const handleRestSkip = async (elapsedSeconds) => {
    if (previousSetId.value && elapsedSeconds > 0) {
        try {
            const result = await updateSet(previousSetId.value, {
                rest_seconds_actual: elapsedSeconds,
            });
        } catch (err) {
            console.error('❌ Failed to update rest time:', err);
        }
    }
    showRestTimer.value = false;
};

const closeRestTimer = () => {
    showRestTimer.value = false;
};

// Complete session
const completeSession = () => {
    if (!confirm('Are you sure you want to complete this training session?')) return;

    router.patch(
        route('sessions.complete', sessionData.value.id),
        {},
        {
            preserveScroll: false,
        }
    );
};

// Quick fill from previous set
const fillFromPreviousSet = () => {
    if (!currentExercise.value || currentExercise.value.sets.length === 0) return;

    const lastSet = currentExercise.value.sets[currentExercise.value.sets.length - 1];
    setForm.value = {
        reps: lastSet.reps ? String(lastSet.reps) : '',
        weight: lastSet.weight ? String(lastSet.weight) : '',
        duration_seconds: lastSet.duration_seconds ? String(lastSet.duration_seconds) : '',
        distance: lastSet.distance ? String(lastSet.distance) : '',
        notes: '',
    };
};

// Watch for exercise changes to close rest timer
watch(currentExerciseIndex, () => {
    showRestTimer.value = false;
});
</script>

<template>
    <Head :title="`Session: ${sessionData.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        {{ sessionData.name }}
                    </h2>
                    <div class="mt-1 flex items-center space-x-2">
                        <span
                            :class="`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${sessionStatus.class}`"
                        >
                            {{ sessionStatus.text }}
                        </span>
                        <span v-if="sessionData.training" class="text-sm text-gray-600">
                            Based on: {{ sessionData.training.name }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Link v-if="isCompleted" :href="route('sessions.index')">
                        <SecondaryButton> Back to History </SecondaryButton>
                    </Link>
                    <PrimaryButton v-if="!isCompleted" @click="completeSession">
                        Finish Workout
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Error Display -->
                <div v-if="error" class="mb-6 rounded-lg bg-red-50 p-4 text-sm text-red-800">
                    {{ error }}
                </div>

                <!-- Exercise Stepper Navigation -->
                <div
                    v-if="exercises.length > 0"
                    class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg"
                >
                    <div class="p-6">
                        <ExerciseStepper
                            :exercises="exercises"
                            :current-index="currentExerciseIndex"
                            @update:current-index="currentExerciseIndex = $event"
                            @jump-to="handleJumpTo"
                        />
                    </div>
                </div>

                <!-- Current Exercise View -->
                <div v-if="currentExercise" class="space-y-6">
                    <!-- Exercise Info Card -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900">
                                        {{ currentExercise.exercise.name }}
                                    </h3>
                                    <div class="mt-2 flex items-center space-x-3">
                                        <span
                                            :class="
                                                getExerciseTypeBadgeClass(
                                                    currentExercise.exercise.type
                                                )
                                            "
                                        >
                                            {{ currentExercise.exercise.type }}
                                        </span>
                                        <span
                                            v-if="currentExercise.exercise.muscle_group"
                                            class="text-sm text-gray-600"
                                        >
                                            {{ currentExercise.exercise.muscle_group }}
                                        </span>
                                    </div>
                                    <p
                                        v-if="currentExercise.notes"
                                        class="mt-3 text-sm text-gray-600 italic"
                                    >
                                        {{ currentExercise.notes }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">Progress</p>
                                    <p class="text-2xl font-bold text-indigo-600">
                                        {{ currentExercise.sets.length
                                        }}{{
                                            currentExercise.template_sets
                                                ? `/${currentExercise.template_sets}`
                                                : ''
                                        }}
                                    </p>
                                    <p class="text-xs text-gray-500">sets</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sets Table -->
                    <div
                        v-if="currentExercise.sets.length > 0"
                        class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                    >
                        <div class="p-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Completed Sets</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                            >
                                                Set
                                            </th>
                                            <th
                                                v-if="
                                                    needsRepsWeight(currentExercise.exercise.type)
                                                "
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                            >
                                                Reps
                                            </th>
                                            <th
                                                v-if="
                                                    needsRepsWeight(currentExercise.exercise.type)
                                                "
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                            >
                                                Weight
                                            </th>
                                            <th
                                                v-if="needsDuration(currentExercise.exercise.type)"
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                            >
                                                Duration
                                            </th>
                                            <th
                                                v-if="needsDistance(currentExercise.exercise.type)"
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                            >
                                                Distance
                                            </th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                            >
                                                Rest
                                            </th>
                                            <th
                                                v-if="!isCompleted"
                                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase"
                                            >
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr
                                            v-for="set in currentExercise.sets"
                                            :key="set.id"
                                            :class="[
                                                editingSetId === set.id ? 'bg-blue-50' : '',
                                                // Highlight template sets with a subtle background
                                                !editingSetId &&
                                                currentExercise.template_sets &&
                                                set.set_index <= currentExercise.template_sets
                                                    ? 'bg-indigo-50/30'
                                                    : '',
                                            ]"
                                        >
                                            <!-- Normal Display Mode -->
                                            <template v-if="editingSetId !== set.id">
                                                <td
                                                    class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900"
                                                >
                                                    {{ set.set_index }}
                                                </td>
                                                <td
                                                    v-if="
                                                        needsRepsWeight(
                                                            currentExercise.exercise.type
                                                        )
                                                    "
                                                    class="px-4 py-3 whitespace-nowrap text-sm text-gray-900"
                                                >
                                                    {{ set.reps || '-' }}
                                                </td>
                                                <td
                                                    v-if="
                                                        needsRepsWeight(
                                                            currentExercise.exercise.type
                                                        )
                                                    "
                                                    class="px-4 py-3 whitespace-nowrap text-sm text-gray-900"
                                                >
                                                    {{ set.weight ? `${set.weight} kg` : '-' }}
                                                </td>
                                                <td
                                                    v-if="
                                                        needsDuration(currentExercise.exercise.type)
                                                    "
                                                    class="px-4 py-3 whitespace-nowrap text-sm text-gray-900"
                                                >
                                                    {{ formatTime(set.duration_seconds) }}
                                                </td>
                                                <td
                                                    v-if="
                                                        needsDistance(currentExercise.exercise.type)
                                                    "
                                                    class="px-4 py-3 whitespace-nowrap text-sm text-gray-900"
                                                >
                                                    {{ set.distance ? `${set.distance} km` : '-' }}
                                                </td>
                                                <td
                                                    class="px-4 py-3 whitespace-nowrap text-sm text-gray-500"
                                                >
                                                    {{ formatTime(set.rest_seconds_actual) }}
                                                </td>
                                                <td
                                                    v-if="!isCompleted"
                                                    class="px-4 py-3 whitespace-nowrap text-right text-sm space-x-3"
                                                >
                                                    <button
                                                        @click="startEditSet(set)"
                                                        class="text-indigo-600 hover:text-indigo-900 font-medium"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button
                                                        @click="handleDeleteSet(set.id)"
                                                        class="text-red-600 hover:text-red-900 font-medium"
                                                    >
                                                        Delete
                                                    </button>
                                                </td>
                                            </template>

                                            <!-- Edit Mode -->
                                            <template v-else>
                                                <td
                                                    class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900"
                                                >
                                                    {{ set.set_index }}
                                                </td>
                                                <td
                                                    v-if="
                                                        needsRepsWeight(
                                                            currentExercise.exercise.type
                                                        )
                                                    "
                                                    class="px-4 py-3"
                                                >
                                                    <TextInput
                                                        v-model="editForm.reps"
                                                        type="text"
                                                        inputmode="numeric"
                                                        class="w-20"
                                                    />
                                                </td>
                                                <td
                                                    v-if="
                                                        needsRepsWeight(
                                                            currentExercise.exercise.type
                                                        )
                                                    "
                                                    class="px-4 py-3"
                                                >
                                                    <TextInput
                                                        v-model="editForm.weight"
                                                        type="text"
                                                        inputmode="decimal"
                                                        class="w-24"
                                                    />
                                                </td>
                                                <td
                                                    v-if="
                                                        needsDuration(currentExercise.exercise.type)
                                                    "
                                                    class="px-4 py-3"
                                                >
                                                    <TextInput
                                                        v-model="editForm.duration_seconds"
                                                        type="text"
                                                        inputmode="numeric"
                                                        class="w-24"
                                                    />
                                                </td>
                                                <td
                                                    v-if="
                                                        needsDistance(currentExercise.exercise.type)
                                                    "
                                                    class="px-4 py-3"
                                                >
                                                    <TextInput
                                                        v-model="editForm.distance"
                                                        type="text"
                                                        inputmode="decimal"
                                                        class="w-24"
                                                    />
                                                </td>
                                                <td
                                                    class="px-4 py-3 whitespace-nowrap text-sm text-gray-500"
                                                >
                                                    {{ formatTime(set.rest_seconds_actual) }}
                                                </td>
                                                <td
                                                    class="px-4 py-3 whitespace-nowrap text-right text-sm space-x-3"
                                                >
                                                    <button
                                                        @click="handleUpdateSet(set.id)"
                                                        class="text-green-600 hover:text-green-900 font-medium"
                                                        :disabled="loading"
                                                    >
                                                        Save
                                                    </button>
                                                    <button
                                                        @click="cancelEditSet"
                                                        class="text-gray-600 hover:text-gray-900 font-medium"
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
                    </div>

                    <!-- Add Set Form -->
                    <div
                        v-if="!isCompleted"
                        class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                    >
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-semibold text-gray-900">Add New Set</h4>
                                <button
                                    v-if="currentExercise.sets.length > 0"
                                    @click="fillFromPreviousSet"
                                    class="text-sm font-medium text-indigo-600 hover:text-indigo-800"
                                >
                                    Same as Last Set
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                <!-- Reps -->
                                <div v-if="needsRepsWeight(currentExercise.exercise.type)">
                                    <InputLabel for="reps" value="Reps" />
                                    <TextInput
                                        id="reps"
                                        v-model="setForm.reps"
                                        type="text"
                                        inputmode="numeric"
                                        class="mt-1 w-full"
                                        placeholder="10"
                                    />
                                    <InputError :message="setFormErrors.reps?.[0]" class="mt-1" />
                                </div>

                                <!-- Weight -->
                                <div v-if="needsRepsWeight(currentExercise.exercise.type)">
                                    <InputLabel for="weight" value="Weight (kg)" />
                                    <TextInput
                                        id="weight"
                                        v-model="setForm.weight"
                                        type="text"
                                        inputmode="decimal"
                                        class="mt-1 w-full"
                                        placeholder="50"
                                    />
                                    <InputError :message="setFormErrors.weight?.[0]" class="mt-1" />
                                </div>

                                <!-- Duration -->
                                <div v-if="needsDuration(currentExercise.exercise.type)">
                                    <InputLabel for="duration" value="Duration (sec)" />
                                    <TextInput
                                        id="duration"
                                        v-model="setForm.duration_seconds"
                                        type="text"
                                        inputmode="numeric"
                                        class="mt-1 w-full"
                                        placeholder="60"
                                    />
                                    <InputError
                                        :message="setFormErrors.duration_seconds?.[0]"
                                        class="mt-1"
                                    />
                                </div>

                                <!-- Distance -->
                                <div v-if="needsDistance(currentExercise.exercise.type)">
                                    <InputLabel for="distance" value="Distance (km)" />
                                    <TextInput
                                        id="distance"
                                        v-model="setForm.distance"
                                        type="text"
                                        inputmode="decimal"
                                        class="mt-1 w-full"
                                        placeholder="5.0"
                                    />
                                    <InputError
                                        :message="setFormErrors.distance?.[0]"
                                        class="mt-1"
                                    />
                                </div>

                                <!-- Notes -->
                                <div class="sm:col-span-2 lg:col-span-4">
                                    <InputLabel for="notes" value="Notes (optional)" />
                                    <TextInput
                                        id="notes"
                                        v-model="setForm.notes"
                                        type="text"
                                        class="mt-1 w-full"
                                        placeholder="Felt strong..."
                                    />
                                    <InputError :message="setFormErrors.notes?.[0]" class="mt-1" />
                                </div>
                            </div>

                            <div class="mt-6">
                                <PrimaryButton
                                    @click="handleAddSet"
                                    :disabled="loading"
                                    class="w-full sm:w-auto justify-center text-base py-3 px-8"
                                >
                                    <span v-if="loading">Adding...</span>
                                    <span v-else>Add Set</span>
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <svg
                            class="mx-auto h-16 w-16 text-gray-400"
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
                        <h3 class="mt-4 text-lg font-semibold text-gray-900">
                            No exercises in this session
                        </h3>
                        <p class="mt-2 text-sm text-gray-600">
                            This is a blank session. Add exercises during your workout.
                        </p>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="mt-6 mb-24">
                    <SecondaryButton @click="router.visit(route('dashboard'))">
                        Back to Dashboard
                    </SecondaryButton>
                </div>
            </div>
        </div>

        <!-- Progress Summary Footer -->
        <SessionProgressFooter
            v-if="!isCompleted && exercises.length > 0"
            :stats="getSessionStats"
            :is-visible="!showRestTimer"
            @finish="completeSession"
        />

        <!-- Rest Timer -->
        <RestTimer
            :show="showRestTimer"
            :target-seconds="restTimerSeconds"
            @complete="handleRestComplete"
            @skip="handleRestSkip"
            @close="closeRestTimer"
        />
    </AuthenticatedLayout>
</template>
