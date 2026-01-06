<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    trainings: Array,
    filters: Object,
});

// Search form
const searchForm = useForm({
    search: props.filters.search || '',
});

const performSearch = () => {
    searchForm.get(route('trainings.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchForm.search = '';
    performSearch();
};

const hasActiveFilters = computed(() => {
    return searchForm.search;
});

// Delete confirmation
const confirmingDeletion = ref(false);
const trainingToDelete = ref(null);

const confirmDelete = (training) => {
    trainingToDelete.value = training;
    confirmingDeletion.value = true;
};

const closeDeleteModal = () => {
    confirmingDeletion.value = false;
    trainingToDelete.value = null;
};

const deleteTraining = () => {
    if (trainingToDelete.value) {
        router.delete(route('trainings.destroy', trainingToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => closeDeleteModal(),
        });
    }
};
</script>

<template>
    <Head title="Trainings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Trainings</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Header with Create Button -->
                        <div class="mb-6 flex items-center justify-between">
                            <h3 class="text-lg font-medium">Your Training Templates</h3>
                            <Link :href="route('trainings.create')">
                                <PrimaryButton> Create Training </PrimaryButton>
                            </Link>
                        </div>

                        <!-- Search Bar -->
                        <form @submit.prevent="performSearch" class="mb-6">
                            <div class="flex gap-4">
                                <div class="flex-1">
                                    <TextInput
                                        id="search"
                                        v-model="searchForm.search"
                                        type="text"
                                        placeholder="Search trainings by name..."
                                        class="w-full"
                                    />
                                </div>
                                <PrimaryButton
                                    type="submit"
                                    :class="{ 'opacity-25': searchForm.processing }"
                                    :disabled="searchForm.processing"
                                >
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
                        </form>

                        <!-- Trainings Table -->
                        <div v-if="trainings.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Name
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Description
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Exercises
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr
                                        v-for="training in trainings"
                                        :key="training.id"
                                        @click="router.visit(route('trainings.show', training.id))"
                                        class="cursor-pointer hover:bg-gray-50"
                                    >
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="font-medium text-gray-900">
                                                {{ training.name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="max-w-xs truncate text-sm text-gray-500">
                                                {{ training.description || '—' }}
                                            </div>
                                        </td>
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-sm text-gray-500"
                                        >
                                            <span
                                                class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800"
                                            >
                                                {{ training.training_exercises_count }}
                                                {{
                                                    training.training_exercises_count === 1
                                                        ? 'exercise'
                                                        : 'exercises'
                                                }}
                                            </span>
                                        </td>
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium"
                                            @click.stop
                                        >
                                            <Link
                                                :href="route('trainings.edit', training.id)"
                                                class="mr-4 text-indigo-600 hover:text-indigo-900"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                @click="confirmDelete(training)"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">
                                {{ hasActiveFilters ? 'No trainings found' : 'No trainings yet' }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{
                                    hasActiveFilters
                                        ? 'Try adjusting your search criteria.'
                                        : 'Get started by creating a new training template.'
                                }}
                            </p>
                            <div v-if="!hasActiveFilters" class="mt-6">
                                <Link :href="route('trainings.create')">
                                    <PrimaryButton> Create Your First Training </PrimaryButton>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="confirmingDeletion" @close="closeDeleteModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Delete Training</h2>

                <p class="mt-3 text-sm text-gray-600">
                    Are you sure you want to delete
                    <span class="font-semibold">{{ trainingToDelete?.name }}</span
                    >? This action cannot be undone.
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="closeDeleteModal"> Cancel </SecondaryButton>

                    <DangerButton @click="deleteTraining"> Delete Training </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
