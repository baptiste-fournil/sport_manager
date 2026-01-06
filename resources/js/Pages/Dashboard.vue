<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Quick Actions Card -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <!-- Start Session -->
                            <Link
                                :href="route('sessions.start')"
                                class="group relative rounded-lg border-2 border-dashed border-gray-300 p-6 text-center hover:border-indigo-500 transition-colors"
                            >
                                <svg
                                    class="mx-auto h-12 w-12 text-gray-400 group-hover:text-indigo-500"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                                <h4
                                    class="mt-2 text-sm font-semibold text-gray-900 group-hover:text-indigo-600"
                                >
                                    Start Session
                                </h4>
                                <p class="mt-1 text-xs text-gray-500">Begin a new workout</p>
                            </Link>

                            <!-- Manage Exercises -->
                            <Link
                                :href="route('exercises.index')"
                                class="group relative rounded-lg border-2 border-dashed border-gray-300 p-6 text-center hover:border-indigo-500 transition-colors"
                            >
                                <svg
                                    class="mx-auto h-12 w-12 text-gray-400 group-hover:text-indigo-500"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                    />
                                </svg>
                                <h4
                                    class="mt-2 text-sm font-semibold text-gray-900 group-hover:text-indigo-600"
                                >
                                    Exercise Catalog
                                </h4>
                                <p class="mt-1 text-xs text-gray-500">Manage your exercises</p>
                            </Link>

                            <!-- Manage Trainings -->
                            <Link
                                :href="route('trainings.index')"
                                class="group relative rounded-lg border-2 border-dashed border-gray-300 p-6 text-center hover:border-indigo-500 transition-colors"
                            >
                                <svg
                                    class="mx-auto h-12 w-12 text-gray-400 group-hover:text-indigo-500"
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
                                <h4
                                    class="mt-2 text-sm font-semibold text-gray-900 group-hover:text-indigo-600"
                                >
                                    Training Templates
                                </h4>
                                <p class="mt-1 text-xs text-gray-500">Build workout plans</p>
                            </Link>

                            <!-- View History (Placeholder) -->
                            <div
                                class="relative rounded-lg border-2 border-dashed border-gray-200 p-6 text-center opacity-50"
                            >
                                <svg
                                    class="mx-auto h-12 w-12 text-gray-300"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                    />
                                </svg>
                                <h4 class="mt-2 text-sm font-semibold text-gray-500">
                                    Session History
                                </h4>
                                <p class="mt-1 text-xs text-gray-400">Coming in Phase 6</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Welcome Card -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900">
                            Welcome to Sport Training Manager
                        </h3>
                        <p class="mt-2 text-sm text-gray-600">
                            Track your workouts, monitor your progress, and achieve your fitness
                            goals.
                        </p>
                        <div class="mt-4">
                            <PrimaryButton @click="$inertia.visit(route('sessions.start'))">
                                Start Your First Workout
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
