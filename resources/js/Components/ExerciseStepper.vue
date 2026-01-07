<script setup>
import { computed } from 'vue';

const props = defineProps({
    exercises: {
        type: Array,
        required: true,
    },
    currentIndex: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits(['update:currentIndex', 'jumpTo']);

const canGoPrevious = computed(() => props.currentIndex > 0);
const canGoNext = computed(() => props.currentIndex < props.exercises.length - 1);

const goToPrevious = () => {
    if (canGoPrevious.value) {
        emit('update:currentIndex', props.currentIndex - 1);
    }
};

const goToNext = () => {
    if (canGoNext.value) {
        emit('update:currentIndex', props.currentIndex + 1);
    }
};

const jumpToExercise = (index) => {
    emit('jumpTo', index);
};

const getStepStatus = (index) => {
    if (index < props.currentIndex) return 'completed';
    if (index === props.currentIndex) return 'current';
    return 'upcoming';
};

const getStepClasses = (index) => {
    const status = getStepStatus(index);
    const baseClasses =
        'flex h-10 w-10 items-center justify-center rounded-full text-sm font-semibold transition-all';

    if (status === 'completed') {
        return `${baseClasses} bg-green-600 text-white cursor-pointer hover:bg-green-700`;
    }
    if (status === 'current') {
        return `${baseClasses} bg-indigo-600 text-white ring-4 ring-indigo-100`;
    }
    return `${baseClasses} bg-gray-200 text-gray-600 cursor-pointer hover:bg-gray-300`;
};
</script>

<template>
    <div class="space-y-4">
        <!-- Progress Bar -->
        <div class="relative">
            <div class="flex items-center justify-between">
                <div
                    v-for="(exercise, index) in exercises"
                    :key="exercise.id"
                    class="flex flex-col items-center"
                    :class="index !== exercises.length - 1 ? 'flex-1' : ''"
                >
                    <!-- Step Circle -->
                    <button
                        :class="getStepClasses(index)"
                        @click="jumpToExercise(index)"
                        :disabled="index === currentIndex"
                    >
                        <span v-if="getStepStatus(index) === 'completed'">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </span>
                        <span v-else>{{ index + 1 }}</span>
                    </button>

                    <!-- Connecting Line -->
                    <div
                        v-if="index !== exercises.length - 1"
                        class="absolute top-5 h-0.5 w-full -z-10"
                        :class="index < currentIndex ? 'bg-green-600' : 'bg-gray-200'"
                        :style="{
                            left: `${(100 / exercises.length) * index + 50 / exercises.length}%`,
                            width: `${100 / exercises.length}%`,
                        }"
                    />
                </div>
            </div>

            <!-- Exercise Names (on desktop) -->
            <div class="mt-3 hidden sm:flex items-center justify-between">
                <div
                    v-for="(exercise, index) in exercises"
                    :key="`name-${exercise.id}`"
                    class="flex-1 text-center"
                >
                    <p
                        class="text-xs font-medium truncate px-1"
                        :class="
                            index === currentIndex
                                ? 'text-indigo-600'
                                : index < currentIndex
                                  ? 'text-green-600'
                                  : 'text-gray-500'
                        "
                    >
                        {{ exercise.exercise.name }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons (Mobile Optimized) -->
        <div class="flex items-center justify-between space-x-4">
            <button
                @click="goToPrevious"
                :disabled="!canGoPrevious"
                class="flex-1 sm:flex-initial rounded-lg px-6 py-3 text-sm font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                :class="
                    canGoPrevious
                        ? 'bg-gray-200 text-gray-900 hover:bg-gray-300'
                        : 'bg-gray-100 text-gray-400'
                "
            >
                <span class="flex items-center justify-center">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 19l-7-7 7-7"
                        />
                    </svg>
                    Previous
                </span>
            </button>

            <div class="text-center px-2">
                <p class="text-sm font-medium text-gray-900">
                    Exercise {{ currentIndex + 1 }} of {{ exercises.length }}
                </p>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ exercises[currentIndex]?.exercise?.name }}
                </p>
            </div>

            <button
                @click="goToNext"
                :disabled="!canGoNext"
                class="flex-1 sm:flex-initial rounded-lg px-6 py-3 text-sm font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                :class="
                    canGoNext
                        ? 'bg-indigo-600 text-white hover:bg-indigo-700'
                        : 'bg-gray-100 text-gray-400'
                "
            >
                <span class="flex items-center justify-center">
                    Next
                    <svg class="h-5 w-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5l7 7-7 7"
                        />
                    </svg>
                </span>
            </button>
        </div>
    </div>
</template>
