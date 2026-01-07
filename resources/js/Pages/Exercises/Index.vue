<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    exercises: Array,
    filters: Object,
});

// Search and filter state
const searchForm = useForm({
    search: props.filters.search || '',
    type: props.filters.type || '',
});

const performSearch = () => {
    searchForm.get(route('exercises.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchForm.search = '';
    searchForm.type = '';
    performSearch();
};

// Delete confirmation
const confirmingDeletion = ref(false);
const exerciseToDelete = ref(null);

const confirmDelete = (exercise) => {
    exerciseToDelete.value = exercise;
    confirmingDeletion.value = true;
};

const closeDeleteModal = () => {
    confirmingDeletion.value = false;
    exerciseToDelete.value = null;
};

const deleteExercise = () => {
    if (exerciseToDelete.value) {
        router.delete(route('exercises.destroy', exerciseToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => closeDeleteModal(),
        });
    }
};

const hasActiveFilters = computed(() => {
    return searchForm.search || searchForm.type;
});

const getExerciseTypeLabel = (type) => {
    const labels = {
        strength: 'Strength',
        cardio: 'Cardio',
        flexibility: 'Flexibility',
        other: 'Other',
    };
    return labels[type] || type;
};

const getExerciseTypeBadgeClass = (type) => {
    const classes = {
        strength: 'bg-blue-100 text-blue-800',
        cardio: 'bg-red-100 text-red-800',
        flexibility: 'bg-green-100 text-green-800',
        other: 'bg-gray-100 text-gray-800',
    };
    return classes[type] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Exercises" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Exercise Catalog</h2>
                <Link :href="route('exercises.create')">
                    <PrimaryButton>Create Exercise</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Search and Filter Section -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="performSearch" class="space-y-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <!-- Search Input -->
                                <div>
                                    <label
                                        for="search"
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Search by name
                                    </label>
                                    <TextInput
                                        id="search"
                                        v-model="searchForm.search"
                                        type="text"
                                        class="mt-1 block w-full"
                                        placeholder="Exercise name..."
                                    />
                                </div>

                                <!-- Type Filter -->
                                <div>
                                    <label
                                        for="type"
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Filter by type
                                    </label>
                                    <select
                                        id="type"
                                        v-model="searchForm.type"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">All types</option>
                                        <option value="strength">Strength</option>
                                        <option value="cardio">Cardio</option>
                                        <option value="flexibility">Flexibility</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-end space-x-2">
                                    <PrimaryButton type="submit" class="flex-1">
                                        Search
                                    </PrimaryButton>
                                    <SecondaryButton
                                        v-if="hasActiveFilters"
                                        type="button"
                                        @click="clearFilters"
                                    >
                                        Clear
                                    </SecondaryButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Exercises List -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="exercises.length === 0" class="text-center py-8">
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
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"
                                />
                            </svg>
                            <p class="mt-4 text-sm text-gray-500">
                                {{
                                    hasActiveFilters
                                        ? 'No exercises found matching your search.'
                                        : 'No exercises yet. Create your first exercise!'
                                }}
                            </p>
                            <Link
                                v-if="!hasActiveFilters"
                                :href="route('exercises.create')"
                                class="mt-4 inline-block"
                            >
                                <PrimaryButton>Create Exercise</PrimaryButton>
                            </Link>
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Name
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Type
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Muscle Group
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Description
                                        </th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr
                                        v-for="exercise in exercises"
                                        :key="exercise.id"
                                        class="hover:bg-gray-50"
                                    >
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ exercise.name }}
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span
                                                :class="getExerciseTypeBadgeClass(exercise.type)"
                                                class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                            >
                                                {{ getExerciseTypeLabel(exercise.type) }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="text-sm text-gray-500">
                                                {{ exercise.muscle_group || '-' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="max-w-xs truncate text-sm text-gray-500">
                                                {{ exercise.description || '-' }}
                                            </div>
                                        </td>
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium"
                                        >
                                            <div class="flex justify-end space-x-2">
                                                <Link
                                                    :href="route('exercises.stats', exercise.id)"
                                                    class="text-green-600 hover:text-green-900"
                                                >
                                                    Stats
                                                </Link>
                                                <Link
                                                    :href="route('exercises.edit', exercise.id)"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    @click="confirmDelete(exercise)"
                                                    class="text-red-600 hover:text-red-900"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="confirmingDeletion" @close="closeDeleteModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Delete Exercise</h2>

                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to delete "{{ exerciseToDelete?.name }}"? This action
                    cannot be undone.
                </p>

                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="closeDeleteModal"> Cancel </SecondaryButton>

                    <DangerButton @click="deleteExercise"> Delete Exercise </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
