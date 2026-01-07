<script setup>
import { computed } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
    isVisible: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['finish']);

const progressPercentage = computed(() => {
    if (props.stats.totalExercises === 0) return 0;
    return Math.round((props.stats.completedExercises / props.stats.totalExercises) * 100);
});

const formatElapsedTime = computed(() => {
    const minutes = props.stats.elapsedMinutes;
    if (minutes < 60) {
        return `${minutes}m`;
    }
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return `${hours}h ${mins}m`;
});
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="translate-y-full opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-full opacity-0"
    >
        <div
            v-show="isVisible"
            class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t-2 border-gray-200 shadow-lg"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between gap-4 flex-wrap sm:flex-nowrap">
                    <!-- Progress Stats -->
                    <div class="flex items-center space-x-6 flex-1">
                        <!-- Exercises Progress -->
                        <div class="flex items-center space-x-2">
                            <svg
                                class="h-5 w-5 text-indigo-600"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                <path
                                    fill-rule="evenodd"
                                    d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <div class="text-left">
                                <p class="text-xs text-gray-500">Exercises</p>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ stats.completedExercises }} / {{ stats.totalExercises }}
                                </p>
                            </div>
                        </div>

                        <!-- Total Sets -->
                        <div class="flex items-center space-x-2">
                            <svg
                                class="h-5 w-5 text-green-600"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <div class="text-left">
                                <p class="text-xs text-gray-500">Total Sets</p>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ stats.totalSets }}
                                </p>
                            </div>
                        </div>

                        <!-- Elapsed Time -->
                        <div class="flex items-center space-x-2">
                            <svg
                                class="h-5 w-5 text-blue-600"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <div class="text-left">
                                <p class="text-xs text-gray-500">Time</p>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ formatElapsedTime }}
                                </p>
                            </div>
                        </div>

                        <!-- Progress Bar (on larger screens) -->
                        <div class="hidden lg:flex items-center space-x-3 flex-1 max-w-xs">
                            <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-indigo-600 transition-all duration-500"
                                    :style="{ width: `${progressPercentage}%` }"
                                />
                            </div>
                            <span class="text-sm font-semibold text-gray-900 whitespace-nowrap">
                                {{ progressPercentage }}%
                            </span>
                        </div>
                    </div>

                    <!-- Finish Button -->
                    <div>
                        <PrimaryButton @click="emit('finish')" class="whitespace-nowrap">
                            Finish Workout
                        </PrimaryButton>
                    </div>
                </div>

                <!-- Progress Bar (on mobile) -->
                <div class="mt-3 lg:hidden flex items-center space-x-3">
                    <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div
                            class="h-full bg-indigo-600 transition-all duration-500"
                            :style="{ width: `${progressPercentage}%` }"
                        />
                    </div>
                    <span class="text-sm font-semibold text-gray-900">
                        {{ progressPercentage }}%
                    </span>
                </div>
            </div>
        </div>
    </Transition>
</template>
