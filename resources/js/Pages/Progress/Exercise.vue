<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import LineChart from '@/Components/charts/LineChart.vue';
import BarChart from '@/Components/charts/BarChart.vue';
import StatCard from '@/Components/charts/StatCard.vue';
import {
    ChartBarIcon,
    TrophyIcon,
    ClockIcon,
    FireIcon,
    ScaleIcon,
    ArrowTrendingUpIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    exercise: Object,
    filters: Object,
    maxWeightData: Array,
    volumeData: Array,
    avgRestTime: Number,
    personalRecords: Object,
    summaryStats: Object,
});

const filterForm = useForm({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
});

const applyFilters = () => {
    filterForm.get(route('exercises.stats', props.exercise.id), {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    filterForm.start_date = null;
    filterForm.end_date = null;
    filterForm.get(route('exercises.stats', props.exercise.id));
};

// Badge color based on exercise type
const badgeColor = computed(() => {
    const colors = {
        strength: 'bg-blue-100 text-blue-800',
        cardio: 'bg-red-100 text-red-800',
        flexibility: 'bg-green-100 text-green-800',
        other: 'bg-gray-100 text-gray-800',
    };
    return colors[props.exercise.type] || colors.other;
});

// Prepare chart data for max weight progression
const maxWeightChartData = computed(() => {
    if (!props.maxWeightData || props.maxWeightData.length === 0) {
        return {
            labels: [],
            datasets: [],
        };
    }

    return {
        labels: props.maxWeightData.map((item) => {
            const date = new Date(item.date);
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
            });
        }),
        datasets: [
            {
                label: 'Max Weight (kg)',
                data: props.maxWeightData.map((item) => item.max_weight),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4,
            },
        ],
    };
});

// Prepare chart data for session volume
const volumeChartData = computed(() => {
    if (!props.volumeData || props.volumeData.length === 0) {
        return {
            labels: [],
            datasets: [],
        };
    }

    return {
        labels: props.volumeData.map((item) => {
            const date = new Date(item.date);
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
            });
        }),
        datasets: [
            {
                label: 'Total Volume (kg)',
                data: props.volumeData.map((item) => item.total_volume),
                backgroundColor: 'rgba(99, 102, 241, 0.8)',
                borderColor: 'rgb(99, 102, 241)',
                borderWidth: 1,
            },
        ],
    };
});

// Format average rest time
const formattedRestTime = computed(() => {
    if (!props.avgRestTime) return 'N/A';
    const minutes = Math.floor(props.avgRestTime / 60);
    const seconds = props.avgRestTime % 60;
    if (minutes > 0) {
        return `${minutes}m ${seconds}s`;
    }
    return `${seconds}s`;
});

// Check if we have data to display
const hasData = computed(() => {
    return (
        (props.maxWeightData && props.maxWeightData.length > 0) ||
        (props.volumeData && props.volumeData.length > 0) ||
        props.summaryStats.total_sessions > 0
    );
});
</script>

<template>
    <Head :title="`${exercise.name} - Progress`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-semibold leading-tight text-gray-800">
                            {{ exercise.name }}
                        </h2>
                        <span
                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                            :class="badgeColor"
                        >
                            {{ exercise.type }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-gray-600">Performance tracking and analytics</p>
                </div>
                <Link :href="route('exercises.index')">
                    <SecondaryButton> Back to Exercises </SecondaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <!-- Date Range Filters -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900">Date Range Filter</h3>
                        <form @submit.prevent="applyFilters" class="mt-4 space-y-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <InputLabel for="start_date" value="Start Date" />
                                    <TextInput
                                        id="start_date"
                                        v-model="filterForm.start_date"
                                        type="date"
                                        class="mt-1 block w-full"
                                    />
                                </div>
                                <div>
                                    <InputLabel for="end_date" value="End Date" />
                                    <TextInput
                                        id="end_date"
                                        v-model="filterForm.end_date"
                                        type="date"
                                        class="mt-1 block w-full"
                                    />
                                </div>
                                <div class="flex items-end gap-2">
                                    <PrimaryButton type="submit" :disabled="filterForm.processing">
                                        Apply
                                    </PrimaryButton>
                                    <SecondaryButton
                                        type="button"
                                        @click="resetFilters"
                                        :disabled="filterForm.processing"
                                    >
                                        Reset
                                    </SecondaryButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!hasData" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <ChartBarIcon class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-semibold text-gray-900">No data available</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Complete some workouts with this exercise to see your progress.
                        </p>
                        <div class="mt-6">
                            <Link :href="route('sessions.start')">
                                <PrimaryButton> Start a Session </PrimaryButton>
                            </Link>
                        </div>
                    </div>
                </div>

                <template v-else>
                    <!-- Summary Stats Cards -->
                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <StatCard
                            title="Total Sessions"
                            :value="summaryStats.total_sessions"
                            :icon="ChartBarIcon"
                            icon-color="indigo"
                        />
                        <StatCard
                            title="Total Sets"
                            :value="summaryStats.total_sets"
                            :icon="FireIcon"
                            icon-color="red"
                        />
                        <StatCard
                            title="Total Volume"
                            :value="`${summaryStats.total_volume} kg`"
                            :icon="ScaleIcon"
                            icon-color="blue"
                        />
                        <StatCard
                            title="Avg Rest Time"
                            :value="formattedRestTime"
                            :icon="ClockIcon"
                            icon-color="green"
                        />
                    </div>

                    <!-- Personal Records -->
                    <div
                        v-if="
                            personalRecords.max_weight ||
                            personalRecords.max_reps ||
                            personalRecords.max_volume
                        "
                        class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                    >
                        <div class="p-6">
                            <div class="flex items-center gap-2 mb-4">
                                <TrophyIcon class="h-6 w-6 text-yellow-500" />
                                <h3 class="text-lg font-medium text-gray-900">Personal Records</h3>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div
                                    v-if="personalRecords.max_weight"
                                    class="rounded-lg bg-yellow-50 p-4"
                                >
                                    <p class="text-sm font-medium text-gray-600">Max Weight</p>
                                    <p class="mt-1 text-2xl font-bold text-gray-900">
                                        {{ personalRecords.max_weight.weight }}
                                        kg
                                    </p>
                                    <p
                                        v-if="personalRecords.max_weight.reps"
                                        class="text-sm text-gray-600"
                                    >
                                        {{ personalRecords.max_weight.reps }}
                                        reps
                                    </p>
                                </div>
                                <div
                                    v-if="personalRecords.max_reps"
                                    class="rounded-lg bg-blue-50 p-4"
                                >
                                    <p class="text-sm font-medium text-gray-600">Max Reps</p>
                                    <p class="mt-1 text-2xl font-bold text-gray-900">
                                        {{ personalRecords.max_reps.reps }}
                                        reps
                                    </p>
                                    <p
                                        v-if="personalRecords.max_reps.weight"
                                        class="text-sm text-gray-600"
                                    >
                                        {{ personalRecords.max_reps.weight }}
                                        kg
                                    </p>
                                </div>
                                <div
                                    v-if="personalRecords.max_volume"
                                    class="rounded-lg bg-green-50 p-4"
                                >
                                    <p class="text-sm font-medium text-gray-600">
                                        Max Volume (Single Set)
                                    </p>
                                    <p class="mt-1 text-2xl font-bold text-gray-900">
                                        {{ personalRecords.max_volume.volume }}
                                        kg
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        {{ personalRecords.max_volume.reps }}
                                        reps ×
                                        {{ personalRecords.max_volume.weight }}
                                        kg
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Max Weight Progression Chart -->
                    <div
                        v-if="maxWeightData && maxWeightData.length > 0"
                        class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                    >
                        <div class="p-6">
                            <div class="flex items-center gap-2 mb-4">
                                <ArrowTrendingUpIcon class="h-6 w-6 text-blue-600" />
                                <h3 class="text-lg font-medium text-gray-900">
                                    Max Weight Progression
                                </h3>
                            </div>
                            <LineChart :data="maxWeightChartData" />
                        </div>
                    </div>

                    <!-- Session Volume Chart -->
                    <div
                        v-if="volumeData && volumeData.length > 0"
                        class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                    >
                        <div class="p-6">
                            <div class="flex items-center gap-2 mb-4">
                                <ScaleIcon class="h-6 w-6 text-indigo-600" />
                                <h3 class="text-lg font-medium text-gray-900">
                                    Total Volume per Session
                                </h3>
                            </div>
                            <BarChart :data="volumeChartData" />
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
