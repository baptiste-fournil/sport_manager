<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    targetSeconds: {
        type: Number,
        default: 90,
    },
});

const emit = defineEmits(['close', 'skip', 'complete']);

const remainingSeconds = ref(props.targetSeconds);
const isPaused = ref(false);
const intervalId = ref(null);
const startTime = ref(null);
const isMinimized = ref(false);

// Format time as MM:SS
const formattedTime = computed(() => {
    const minutes = Math.floor(remainingSeconds.value / 60);
    const seconds = remainingSeconds.value % 60;
    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
});

// Calculate progress percentage (0-100)
const progressPercentage = computed(() => {
    return ((props.targetSeconds - remainingSeconds.value) / props.targetSeconds) * 100;
});

// Check if timer is complete
const isComplete = computed(() => remainingSeconds.value <= 0);

// Start the countdown timer
const startTimer = () => {
    if (intervalId.value) return; // Already running

    startTime.value = Date.now();

    intervalId.value = setInterval(() => {
        if (!isPaused.value && remainingSeconds.value > 0) {
            remainingSeconds.value--;

            if (remainingSeconds.value === 0) {
                completeTimer();
            }
        }
    }, 1000);
};

// Pause the timer
const pause = () => {
    isPaused.value = true;
};

// Resume the timer
const resume = () => {
    isPaused.value = false;
};

// Skip rest (end timer early)
const skip = () => {
    const elapsedSeconds = startTime.value ? Math.floor((Date.now() - startTime.value) / 1000) : 0;
    stopTimer();
    emit('skip', elapsedSeconds);
    emit('close');
};

// Add time to the timer
const addTime = (seconds) => {
    remainingSeconds.value += seconds;
};

// Complete timer (when countdown reaches 0)
const completeTimer = () => {
    stopTimer();
    emit('complete');

    // Optional: Play sound or vibrate
    if ('vibrate' in navigator) {
        navigator.vibrate([200, 100, 200]);
    }
};

// Stop the timer and clean up
const stopTimer = () => {
    if (intervalId.value) {
        clearInterval(intervalId.value);
        intervalId.value = null;
    }
};

// Minimize/maximize the timer
const toggleMinimize = () => {
    isMinimized.value = !isMinimized.value;
};

// Close the timer modal
const close = () => {
    isMinimized.value = true;
};

// Watch for show prop changes to start/stop timer
watch(
    () => props.show,
    (newShow) => {
        if (newShow) {
            remainingSeconds.value = props.targetSeconds;
            isPaused.value = false;
            isMinimized.value = false;
            startTimer();
        } else {
            stopTimer();
        }
    }
);

// Clean up on component unmount
onUnmounted(() => {
    stopTimer();
});
</script>

<template>
    <!-- Minimized Timer Bar -->
    <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="translate-y-full"
        enter-to-class="translate-y-0"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="translate-y-0"
        leave-to-class="translate-y-full"
    >
        <div
            v-show="show && isMinimized"
            @click="toggleMinimize"
            class="fixed bottom-0 left-0 right-0 z-50 cursor-pointer bg-indigo-600 px-6 py-4 shadow-lg hover:bg-indigo-700 transition-colors"
        >
            <div class="mx-auto flex max-w-7xl items-center justify-between text-white">
                <div class="flex items-center space-x-3">
                    <svg class="h-5 w-5 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <span class="font-semibold">Rest Timer</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-2xl font-bold tabular-nums">{{ formattedTime }}</span>
                    <span class="text-sm opacity-75">Click to expand</span>
                </div>
            </div>
        </div>
    </Transition>

    <!-- Expanded Timer Modal -->
    <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-show="show && !isMinimized"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75 px-4"
        >
            <div class="w-full max-w-md rounded-lg bg-white p-8 shadow-2xl">
                <!-- Header -->
                <div class="mb-6 text-center">
                    <h2 class="text-2xl font-bold text-gray-900">Rest Timer</h2>
                    <p class="mt-1 text-sm text-gray-600">Take a break before your next set</p>
                </div>

                <!-- Timer Display -->
                <div class="mb-8">
                    <!-- Progress Ring -->
                    <div class="relative mx-auto h-48 w-48">
                        <svg class="h-full w-full -rotate-90 transform">
                            <!-- Background circle -->
                            <circle
                                cx="96"
                                cy="96"
                                r="88"
                                stroke="currentColor"
                                stroke-width="8"
                                fill="none"
                                class="text-gray-200"
                            />
                            <!-- Progress circle -->
                            <circle
                                cx="96"
                                cy="96"
                                r="88"
                                stroke="currentColor"
                                stroke-width="8"
                                fill="none"
                                :stroke-dasharray="552.92"
                                :stroke-dashoffset="552.92 - (552.92 * progressPercentage) / 100"
                                class="text-indigo-600 transition-all duration-1000"
                                stroke-linecap="round"
                            />
                        </svg>
                        <!-- Timer Text -->
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-5xl font-bold text-gray-900">{{
                                formattedTime
                            }}</span>
                        </div>
                    </div>
                </div>

                <!-- Controls -->
                <div class="space-y-4">
                    <!-- Pause/Resume and Skip buttons -->
                    <div class="flex gap-3">
                        <SecondaryButton
                            v-if="!isPaused"
                            @click="pause"
                            class="flex-1 justify-center"
                        >
                            Pause
                        </SecondaryButton>
                        <SecondaryButton v-else @click="resume" class="flex-1 justify-center">
                            Resume
                        </SecondaryButton>
                        <PrimaryButton @click="skip" class="flex-1 justify-center">
                            Skip Rest
                        </PrimaryButton>
                    </div>

                    <!-- Add time buttons -->
                    <div class="flex gap-3">
                        <button
                            @click="addTime(15)"
                            class="flex-1 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            +15s
                        </button>
                        <button
                            @click="addTime(30)"
                            class="flex-1 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            +30s
                        </button>
                        <button
                            @click="addTime(60)"
                            class="flex-1 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            +1m
                        </button>
                    </div>

                    <!-- Minimize button -->
                    <div class="mt-6 text-center">
                        <button
                            @click="close"
                            class="text-sm font-medium text-gray-600 hover:text-gray-900"
                        >
                            Minimize Timer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>
