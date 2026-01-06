<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    trainings: {
        type: Array,
        required: true,
    },
});

const showBlankModal = ref(false);

const blankForm = useForm({
    name: '',
    notes: '',
});

const startFromTraining = (trainingId) => {
    useForm({
        training_id: trainingId,
    }).post(route('sessions.store'));
};

const openBlankModal = () => {
    showBlankModal.value = true;
    blankForm.reset();
};

const closeBlankModal = () => {
    showBlankModal.value = false;
    blankForm.reset();
};

const submitBlankSession = () => {
    blankForm.post(route('sessions.store'), {
        preserveScroll: true,
        onSuccess: () => {
            closeBlankModal();
        },
    });
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Start Session" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Start Training Session
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Start Blank Session Button -->
                        <div class="mb-8 flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">
                                    Choose a Training Template
                                </h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    Select a training to start a session, or create a blank session
                                    to add exercises on the fly.
                                </p>
                            </div>
                            <PrimaryButton @click="openBlankModal">
                                Start Blank Session
                            </PrimaryButton>
                        </div>

                        <!-- Training List -->
                        <div
                            v-if="trainings.length > 0"
                            class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                        >
                            <div
                                v-for="training in trainings"
                                :key="training.id"
                                class="group relative rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition-all hover:border-indigo-500 hover:shadow-md"
                            >
                                <div class="mb-4">
                                    <h4 class="text-lg font-semibold text-gray-900">
                                        {{ training.name }}
                                    </h4>
                                    <p
                                        v-if="training.description"
                                        class="mt-1 text-sm text-gray-600"
                                    >
                                        {{ training.description }}
                                    </p>
                                </div>

                                <div class="mb-4 flex items-center space-x-4 text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <svg
                                            class="mr-1.5 h-5 w-5"
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
                                        <span>{{ training.exercise_count }} exercises</span>
                                    </div>
                                </div>

                                <div class="text-xs text-gray-400">
                                    Last updated: {{ formatDate(training.updated_at) }}
                                </div>

                                <div class="mt-4">
                                    <PrimaryButton
                                        class="w-full"
                                        @click="startFromTraining(training.id)"
                                    >
                                        Start Session
                                    </PrimaryButton>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div
                            v-else
                            class="rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-12 text-center"
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
                                No training templates yet
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Create a training template first, or start a blank session to add
                                exercises as you go.
                            </p>
                            <div class="mt-6">
                                <SecondaryButton @click="$inertia.visit(route('trainings.create'))">
                                    Create Training Template
                                </SecondaryButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Blank Session Modal -->
        <Modal :show="showBlankModal" @close="closeBlankModal" max-width="2xl">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Start Blank Session</h2>

                <p class="mt-1 text-sm text-gray-600">
                    Create a freestyle training session. You can add exercises during the workout.
                </p>

                <form @submit.prevent="submitBlankSession" class="mt-6">
                    <div>
                        <InputLabel for="name" value="Session Name" />
                        <TextInput
                            id="name"
                            v-model="blankForm.name"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="e.g., Morning Workout, Leg Day"
                            required
                            autofocus
                        />
                        <InputError :message="blankForm.errors.name" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <InputLabel for="notes" value="Notes (Optional)" />
                        <textarea
                            id="notes"
                            v-model="blankForm.notes"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            rows="3"
                            placeholder="Add any notes for this session..."
                        ></textarea>
                        <InputError :message="blankForm.errors.notes" class="mt-2" />
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <SecondaryButton type="button" @click="closeBlankModal">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="blankForm.processing">
                            Start Session
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
