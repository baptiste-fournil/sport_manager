<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import axios from 'axios';

const props = defineProps({
    training: Object,
    exercises: Array,
    auth: Object,
});

// Add exercise modal state
const showAddExerciseModal = ref(false);
const addExerciseForm = ref({
    exercise_id: '',
    default_sets: 3,
    default_reps: 10,
    default_rest_seconds: 90,
    notes: '',
});
const addExerciseErrors = ref({});
const addingExercise = ref(false);

const openAddExerciseModal = () => {
    showAddExerciseModal.value = true;
    addExerciseForm.value = {
        exercise_id: '',
        default_sets: 3,
        default_reps: 10,
        default_rest_seconds: 90,
        notes: '',
    };
    addExerciseErrors.value = {};
};

const closeAddExerciseModal = () => {
    showAddExerciseModal.value = false;
};

const addExercise = async () => {
    if (!addExerciseForm.value.exercise_id) {
        addExerciseErrors.value.exercise_id = 'Please select an exercise.';
        return;
    }

    addingExercise.value = true;
    addExerciseErrors.value = {};

    try {
        await axios.post(route('training-exercises.store', props.training.id), addExerciseForm.value);
        
        // Reload the page to show the updated training
        router.reload({ only: ['training'] });
        
        closeAddExerciseModal();
    } catch (error) {
        if (error.response?.data?.errors) {
            addExerciseErrors.value = error.response.data.errors;
        } else {
            console.error('Failed to add exercise:', error);
        }
    } finally {
        addingExercise.value = false;
    }
};

// Edit exercise modal state
const showEditExerciseModal = ref(false);
const editingExercise = ref(null);
const editExerciseForm = ref({
    default_sets: null,
    default_reps: null,
    default_rest_seconds: null,
    notes: '',
});
const editExerciseErrors = ref({});
const updatingExercise = ref(false);

const openEditExerciseModal = (trainingExercise) => {
    editingExercise.value = trainingExercise;
    editExerciseForm.value = {
        default_sets: trainingExercise.default_sets,
        default_reps: trainingExercise.default_reps,
        default_rest_seconds: trainingExercise.default_rest_seconds,
        notes: trainingExercise.notes || '',
    };
    editExerciseErrors.value = {};
    showEditExerciseModal.value = true;
};

const closeEditExerciseModal = () => {
    showEditExerciseModal.value = false;
    editingExercise.value = null;
};

const updateExercise = async () => {
    updatingExercise.value = true;
    editExerciseErrors.value = {};

    try {
        await axios.patch(
            route('training-exercises.update', editingExercise.value.id),
            editExerciseForm.value
        );
        
        router.reload({ only: ['training'] });
        closeEditExerciseModal();
    } catch (error) {
        if (error.response?.data?.errors) {
            editExerciseErrors.value = error.response.data.errors;
        } else {
            console.error('Failed to update exercise:', error);
        }
    } finally {
        updatingExercise.value = false;
    }
};

// Delete exercise confirmation
const confirmingExerciseDeletion = ref(false);
const exerciseToDelete = ref(null);

const confirmDeleteExercise = (trainingExercise) => {
    exerciseToDelete.value = trainingExercise;
    confirmingExerciseDeletion.value = true;
};

const closeDeleteExerciseModal = () => {
    confirmingExerciseDeletion.value = false;
    exerciseToDelete.value = null;
};

const deleteExercise = async () => {
    try {
        await axios.delete(route('training-exercises.destroy', exerciseToDelete.value.id));
        router.reload({ only: ['training'] });
        closeDeleteExerciseModal();
    } catch (error) {
        console.error('Failed to delete exercise:', error);
    }
};

// Reordering exercises
const movingExercise = ref(false);

const moveExerciseUp = async (trainingExercise, index) => {
    if (index === 0 || movingExercise.value) return;

    movingExercise.value = true;

    const exercises = [...props.training.training_exercises];
    const temp = exercises[index];
    exercises[index] = exercises[index - 1];
    exercises[index - 1] = temp;

    await reorderExercises(exercises);
};

const moveExerciseDown = async (trainingExercise, index) => {
    if (index === props.training.training_exercises.length - 1 || movingExercise.value) return;

    movingExercise.value = true;

    const exercises = [...props.training.training_exercises];
    const temp = exercises[index];
    exercises[index] = exercises[index + 1];
    exercises[index + 1] = temp;

    await reorderExercises(exercises);
};

const reorderExercises = async (exercises) => {
    try {
        const payload = {
            exercises: exercises.map((ex, idx) => ({
                id: ex.id,
                order_index: idx,
            })),
        };

        await axios.patch(route('training-exercises.reorder', props.training.id), payload);
        router.reload({ only: ['training'] });
    } catch (error) {
        console.error('Failed to reorder exercises:', error);
    } finally {
        movingExercise.value = false;
    }
};

// Helper computed
const availableExercises = computed(() => {
    // Return all exercises - allow duplicates for different set/rep schemes
    return props.exercises;
});

const selectedExerciseName = computed(() => {
    const selected = props.exercises.find(ex => ex.id === parseInt(addExerciseForm.value.exercise_id));
    return selected ? selected.name : '';
});

// Type badge color helper
const getTypeBadgeColor = (type) => {
    const colors = {
        strength: 'bg-blue-100 text-blue-800',
        cardio: 'bg-red-100 text-red-800',
        flexibility: 'bg-green-100 text-green-800',
        other: 'bg-gray-100 text-gray-800',
    };
    return colors[type] || colors.other;
};
</script>

<template>
    <Head :title="training.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ training.name }}
                </h2>
                <Link :href="route('trainings.edit', training.id)">
                    <SecondaryButton>
                        Edit Details
                    </SecondaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Training Info Card -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="space-y-4">
                            <div v-if="training.description">
                                <h3 class="text-sm font-medium text-gray-500">Description</h3>
                                <p class="mt-1 text-sm text-gray-900">{{ training.description }}</p>
                            </div>
                            <div v-if="training.notes">
                                <h3 class="text-sm font-medium text-gray-500">Planning Notes</h3>
                                <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ training.notes }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Exercises Builder Card -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-6 flex items-center justify-between">
                            <h3 class="text-lg font-medium">Exercises</h3>
                            <PrimaryButton @click="openAddExerciseModal">
                                Add Exercise
                            </PrimaryButton>
                        </div>

                        <!-- Exercises List -->
                        <div v-if="training.training_exercises.length > 0" class="space-y-4">
                            <div
                                v-for="(trainingExercise, index) in training.training_exercises"
                                :key="trainingExercise.id"
                                class="rounded-lg border border-gray-200 bg-gray-50 p-4"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 text-sm font-semibold text-white">
                                                {{ index + 1 }}
                                            </span>
                                            <div>
                                                <h4 class="font-medium text-gray-900">
                                                    {{ trainingExercise.exercise.name }}
                                                </h4>
                                                <span
                                                    :class="getTypeBadgeColor(trainingExercise.exercise.type)"
                                                    class="mt-1 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                                >
                                                    {{ trainingExercise.exercise.type }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="ml-11 mt-3 grid grid-cols-3 gap-4 text-sm">
                                            <div>
                                                <span class="font-medium text-gray-500">Sets:</span>
                                                <span class="ml-2 text-gray-900">{{ trainingExercise.default_sets || '—' }}</span>
                                            </div>
                                            <div>
                                                <span class="font-medium text-gray-500">Reps:</span>
                                                <span class="ml-2 text-gray-900">{{ trainingExercise.default_reps || '—' }}</span>
                                            </div>
                                            <div>
                                                <span class="font-medium text-gray-500">Rest:</span>
                                                <span class="ml-2 text-gray-900">{{ trainingExercise.default_rest_seconds }}s</span>
                                            </div>
                                        </div>

                                        <div v-if="trainingExercise.notes" class="ml-11 mt-2">
                                            <p class="text-sm text-gray-600">{{ trainingExercise.notes }}</p>
                                        </div>
                                    </div>

                                    <div class="ml-4 flex flex-col gap-2">
                                        <!-- Move Up/Down Buttons -->
                                        <div class="flex gap-1">
                                            <button
                                                @click="moveExerciseUp(trainingExercise, index)"
                                                :disabled="index === 0 || movingExercise"
                                                class="rounded bg-gray-200 p-1 text-gray-600 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
                                                title="Move up"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                </svg>
                                            </button>
                                            <button
                                                @click="moveExerciseDown(trainingExercise, index)"
                                                :disabled="index === training.training_exercises.length - 1 || movingExercise"
                                                class="rounded bg-gray-200 p-1 text-gray-600 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
                                                title="Move down"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Edit/Delete Buttons -->
                                        <button
                                            @click="openEditExerciseModal(trainingExercise)"
                                            class="text-sm text-indigo-600 hover:text-indigo-900"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="confirmDeleteExercise(trainingExercise)"
                                            class="text-sm text-red-600 hover:text-red-900"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="py-12 text-center">
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
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No exercises yet</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Get started by adding exercises to this training template.
                            </p>
                            <div class="mt-6">
                                <PrimaryButton @click="openAddExerciseModal">
                                    Add Your First Exercise
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="flex justify-start">
                    <Link :href="route('trainings.index')">
                        <SecondaryButton>
                            Back to Trainings
                        </SecondaryButton>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Add Exercise Modal -->
        <Modal :show="showAddExerciseModal" @close="closeAddExerciseModal" max-width="2xl">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Add Exercise</h2>

                <form @submit.prevent="addExercise" class="mt-6 space-y-6">
                    <!-- Exercise Selection -->
                    <div>
                        <InputLabel for="exercise_id" value="Select Exercise" />
                        <select
                            id="exercise_id"
                            v-model="addExerciseForm.exercise_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                            <option value="">Choose an exercise...</option>
                            <option
                                v-for="exercise in availableExercises"
                                :key="exercise.id"
                                :value="exercise.id"
                            >
                                {{ exercise.name }} ({{ exercise.type }})
                            </option>
                        </select>
                        <InputError :message="addExerciseErrors.exercise_id?.[0]" class="mt-2" />
                        <p v-if="availableExercises.length === 0" class="mt-2 text-sm text-amber-600">
                            You don't have any exercises yet. 
                            <Link :href="route('exercises.create')" class="font-medium underline">
                                Create your first exercise
                            </Link>
                        </p>
                    </div>

                    <!-- Default Sets -->
                    <div>
                        <InputLabel for="default_sets" value="Default Sets" />
                        <TextInput
                            id="default_sets"
                            v-model.number="addExerciseForm.default_sets"
                            type="number"
                            min="1"
                            max="20"
                            class="mt-1 block w-full"
                            placeholder="3"
                        />
                        <InputError :message="addExerciseErrors.default_sets?.[0]" class="mt-2" />
                    </div>

                    <!-- Default Reps -->
                    <div>
                        <InputLabel for="default_reps" value="Default Reps" />
                        <TextInput
                            id="default_reps"
                            v-model.number="addExerciseForm.default_reps"
                            type="number"
                            min="1"
                            max="500"
                            class="mt-1 block w-full"
                            placeholder="10"
                        />
                        <InputError :message="addExerciseErrors.default_reps?.[0]" class="mt-2" />
                    </div>

                    <!-- Default Rest -->
                    <div>
                        <InputLabel for="default_rest_seconds" value="Default Rest (seconds)" />
                        <TextInput
                            id="default_rest_seconds"
                            v-model.number="addExerciseForm.default_rest_seconds"
                            type="number"
                            min="0"
                            max="600"
                            class="mt-1 block w-full"
                            placeholder="90"
                        />
                        <InputError :message="addExerciseErrors.default_rest_seconds?.[0]" class="mt-2" />
                    </div>

                    <!-- Notes -->
                    <div>
                        <InputLabel for="notes" value="Notes" />
                        <textarea
                            id="notes"
                            v-model="addExerciseForm.notes"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            rows="3"
                            placeholder="Optional notes or tips for this exercise"
                        ></textarea>
                        <InputError :message="addExerciseErrors.notes?.[0]" class="mt-2" />
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3">
                        <SecondaryButton type="button" @click="closeAddExerciseModal">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton
                            :class="{ 'opacity-25': addingExercise }"
                            :disabled="addingExercise || !addExerciseForm.exercise_id"
                        >
                            Add Exercise
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Exercise Modal -->
        <Modal :show="showEditExerciseModal" @close="closeEditExerciseModal" max-width="2xl">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Edit Exercise: {{ editingExercise?.exercise.name }}
                </h2>

                <form @submit.prevent="updateExercise" class="mt-6 space-y-6">
                    <!-- Default Sets -->
                    <div>
                        <InputLabel for="edit_default_sets" value="Default Sets" />
                        <TextInput
                            id="edit_default_sets"
                            v-model.number="editExerciseForm.default_sets"
                            type="number"
                            min="1"
                            max="20"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="editExerciseErrors.default_sets?.[0]" class="mt-2" />
                    </div>

                    <!-- Default Reps -->
                    <div>
                        <InputLabel for="edit_default_reps" value="Default Reps" />
                        <TextInput
                            id="edit_default_reps"
                            v-model.number="editExerciseForm.default_reps"
                            type="number"
                            min="1"
                            max="500"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="editExerciseErrors.default_reps?.[0]" class="mt-2" />
                    </div>

                    <!-- Default Rest -->
                    <div>
                        <InputLabel for="edit_default_rest_seconds" value="Default Rest (seconds)" />
                        <TextInput
                            id="edit_default_rest_seconds"
                            v-model.number="editExerciseForm.default_rest_seconds"
                            type="number"
                            min="0"
                            max="600"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="editExerciseErrors.default_rest_seconds?.[0]" class="mt-2" />
                    </div>

                    <!-- Notes -->
                    <div>
                        <InputLabel for="edit_notes" value="Notes" />
                        <textarea
                            id="edit_notes"
                            v-model="editExerciseForm.notes"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            rows="3"
                        ></textarea>
                        <InputError :message="editExerciseErrors.notes?.[0]" class="mt-2" />
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3">
                        <SecondaryButton type="button" @click="closeEditExerciseModal">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton
                            :class="{ 'opacity-25': updatingExercise }"
                            :disabled="updatingExercise"
                        >
                            Update Exercise
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Exercise Confirmation Modal -->
        <Modal :show="confirmingExerciseDeletion" @close="closeDeleteExerciseModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Remove Exercise</h2>

                <p class="mt-3 text-sm text-gray-600">
                    Are you sure you want to remove
                    <span class="font-semibold">{{ exerciseToDelete?.exercise.name }}</span>
                    from this training? This will not delete the exercise from your catalog.
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="closeDeleteExerciseModal">
                        Cancel
                    </SecondaryButton>

                    <DangerButton @click="deleteExercise">
                        Remove Exercise
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
