<template>
    <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <component
                        :is="icon"
                        class="h-6 w-6"
                        :class="iconColorClass"
                        aria-hidden="true"
                    />
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="truncate text-sm font-medium text-gray-500">
                            {{ title }}
                        </dt>
                        <dd>
                            <div class="text-lg font-medium text-gray-900">
                                {{ value }}
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        <div v-if="trend" class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
                <span class="font-medium" :class="trendColorClass">
                    {{ trend }}
                </span>
                <span class="text-gray-600"> {{ trendLabel }} </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    icon: {
        type: Object,
        required: true,
    },
    iconColor: {
        type: String,
        default: 'blue',
        validator: (value) => ['blue', 'green', 'red', 'yellow', 'gray', 'indigo'].includes(value),
    },
    trend: {
        type: String,
        default: null,
    },
    trendLabel: {
        type: String,
        default: 'from last period',
    },
    trendType: {
        type: String,
        default: 'neutral',
        validator: (value) => ['increase', 'decrease', 'neutral'].includes(value),
    },
});

const iconColorClass = computed(() => {
    const colors = {
        blue: 'text-blue-600',
        green: 'text-green-600',
        red: 'text-red-600',
        yellow: 'text-yellow-600',
        gray: 'text-gray-600',
        indigo: 'text-indigo-600',
    };
    return colors[props.iconColor] || colors.blue;
});

const trendColorClass = computed(() => {
    const colors = {
        increase: 'text-green-600',
        decrease: 'text-red-600',
        neutral: 'text-gray-600',
    };
    return colors[props.trendType] || colors.neutral;
});
</script>
