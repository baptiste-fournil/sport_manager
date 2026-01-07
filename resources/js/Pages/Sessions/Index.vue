<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ClockIcon, CheckCircleIcon, PlayCircleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    sessions: Object,
    trainings: Array,
    filters: Object,
});

// Search form
const searchForm = useForm({
    search: props.filters.search || '',
    status: props.filters.status || '',
    training_id: props.filters.training_id || '',
});

const performSearch = () => {
    searchForm.get(route('sessions.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchForm.search = '';
    searchForm.status = '';
    searchForm.training_id = '';
    performSearch();
};

const hasActiveFilters = computed(() => {
    return searchForm.search || searchForm.status || searchForm.training_id;
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusBadgeClass = (session) => {
    return session.completed_at
        ? 'inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800'
        : 'inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800';
};

const getStatusIcon = (session) => {
    return session.completed_at ? CheckCircleIcon : PlayCircleIcon;
};

const getStatusText = (session) => {
    return session.completed_at ? 'Completed' : 'In Progress';
};
</script>

<template>
    <Head title="Session History" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Session History</h2>
                <Link :href="route('sessions.start')">
                    <PrimaryButton> Start New Session </PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Search and Filters -->
                        <form @submit.prevent="performSearch" class="mb-6 space-y-4">
                            <!-- Search Bar -->
                            <div class="flex gap-4">
                                <div class="flex-1">
                                    <TextInput
                                        id="search"
                                        v-model="searchForm.search"
                                        type="text"
                                        placeholder="Search sessions by name..."
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

                            <!-- Filter Dropdowns -->
                            <div class="flex gap-4">
                                <!-- Status Filter -->
                                <div class="flex-1">
                                    <label
                                        for="status"
                                        class="mb-2 block text-sm font-medium text-gray-700"
                                    >
                                        Status
                                    </label>
                                    <select
                                        id="status"
                                        v-model="searchForm.status"
                                        @change="performSearch"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">All Sessions</option>
                                        <option value="completed">Completed</option>
                                        <option value="in-progress">In Progress</option>
                                    </select>
                                </div>

                                <!-- Training Filter -->
                                <div class="flex-1">
                                    <label
                                        for="training_id"
                                        class="mb-2 block text-sm font-medium text-gray-700"
                                    >
                                        Training Template
                                    </label>
                                    <select
                                        id="training_id"
                                        v-model="searchForm.training_id"
                                        @change="performSearch"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">All Trainings</option>
                                        <option
                                            v-for="training in trainings"
                                            :key="training.id"
                                            :value="training.id"
                                        >
                                            {{ training.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </form>

                        <!-- Sessions Grid -->
                        <div v-if="sessions.data.length > 0" class="space-y-4">
                            <div
                                v-for="session in sessions.data"
                                :key="session.id"
                                class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-md"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="mb-2 flex items-center gap-3">
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                {{ session.name || 'Workout Session' }}
                                            </h3>
                                            <span :class="getStatusBadgeClass(session)">
                                                <component
                                                    :is="getStatusIcon(session)"
                                                    class="mr-1 h-3 w-3"
                                                />
                                                {{ getStatusText(session) }}
                                            </span>
                                        </div>

                                        <div
                                            v-if="session.training"
                                            class="mb-3 text-sm text-gray-600"
                                        >
                                            <span class="font-medium">Template:</span>
                                            {{ session.training.name }}
                                        </div>

                                        <div class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                                            <div>
                                                <p class="text-gray-500">Exercises</p>
                                                <p class="font-semibold text-gray-900">
                                                    {{ session.session_exercises_count }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-gray-500">Total Sets</p>
                                                <p class="font-semibold text-gray-900">
                                                    {{ session.total_sets_count || 0 }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-gray-500">Started</p>
                                                <p class="font-semibold text-gray-900">
                                                    {{ formatDate(session.started_at) }}
                                                </p>
                                            </div>
                                            <div v-if="session.completed_at">
                                                <p class="text-gray-500">Duration</p>
                                                <p
                                                    class="flex items-center font-semibold text-gray-900"
                                                >
                                                    <ClockIcon class="mr-1 h-4 w-4" />
                                                    {{
                                                        Math.round(
                                                            (new Date(session.completed_at) -
                                                                new Date(session.started_at)) /
                                                                60000
                                                        )
                                                    }}
                                                    min
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <Link :href="route('sessions.show', session.id)">
                                        <SecondaryButton>
                                            {{ session.completed_at ? 'View' : 'Continue' }}
                                        </SecondaryButton>
                                    </Link>
                                </div>
                            </div>

                            <!-- Pagination -->
                            <div
                                v-if="sessions.links.length > 3"
                                class="mt-6 flex items-center justify-center gap-2"
                            >
                                <Link
                                    v-for="(link, index) in sessions.links"
                                    :key="index"
                                    :href="link.url || ''"
                                    :class="[
                                        'rounded-md px-4 py-2 text-sm font-medium',
                                        link.active
                                            ? 'bg-indigo-600 text-white'
                                            : link.url
                                              ? 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                                              : 'bg-gray-100 text-gray-400 cursor-not-allowed',
                                    ]"
                                    :disabled="!link.url"
                                    v-html="link.label"
                                />
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div
                            v-else
                            class="rounded-lg border-2 border-dashed border-gray-300 p-12 text-center"
                        >
                            <PlayCircleIcon class="mx-auto h-12 w-12 text-gray-400" />
                            <h3 class="mt-2 text-sm font-semibold text-gray-900">
                                No training sessions found
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{
                                    hasActiveFilters
                                        ? 'Try adjusting your filters or search query.'
                                        : 'Get started by creating your first training session.'
                                }}
                            </p>
                            <div class="mt-6">
                                <Link v-if="!hasActiveFilters" :href="route('sessions.start')">
                                    <PrimaryButton> Start Your First Session </PrimaryButton>
                                </Link>
                                <SecondaryButton v-else @click="clearFilters">
                                    Clear Filters
                                </SecondaryButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
