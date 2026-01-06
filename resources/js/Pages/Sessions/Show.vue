<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    session: {
        type: Object,
        required: true,
    },
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

                <!-- Session Status Message -->
                <div
                    v-if="session.is_in_progress"
                    class="mb-6 overflow-hidden bg-yellow-50 shadow-sm sm:rounded-lg border-l-4 border-yellow-400"
                >
                    <div class="p-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg
                                    class="h-5 w-5 text-yellow-400"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">
                                    Session in Progress
                                </h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>
                                        This training session is currently active. Phase 5 will
                                        implement the live logging interface where you can add sets,
                                        track rest time, and log performance.
                                    </p>
                                </div>
                                <div class="mt-4">
                                    <PrimaryButton disabled>
                                        Continue Workout (Coming in Phase 5)
                                    </PrimaryButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Exercises List -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900">Exercises</h3>

                        <div v-if="session.exercises.length > 0" class="mt-4 space-y-4">
                            <div
                                v-for="(sessionExercise, index) in session.exercises"
                                :key="sessionExercise.id"
                                class="rounded-lg border border-gray-200 p-4"
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
                                            </div>
                                            <p
                                                v-if="sessionExercise.notes"
                                                class="mt-2 text-sm text-gray-600"
                                            >
                                                {{ sessionExercise.notes }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sets placeholder -->
                                <div v-if="sessionExercise.sets.length > 0" class="mt-4">
                                    <p class="text-sm text-gray-500">
                                        {{ sessionExercise.sets.length }} set(s) logged
                                    </p>
                                </div>
                                <div v-else class="mt-4">
                                    <p class="text-sm text-gray-400 italic">No sets logged yet</p>
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
                                This is a blank session. Phase 5 will allow you to add exercises
                                during your workout.
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
    </AuthenticatedLayout>
</template>
