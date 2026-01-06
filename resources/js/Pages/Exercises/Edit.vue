<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    exercise: Object,
});

const form = useForm({
    name: props.exercise.name,
    description: props.exercise.description || '',
    type: props.exercise.type,
    muscle_group: props.exercise.muscle_group || '',
});

const submit = () => {
    form.patch(route('exercises.update', props.exercise.id));
};
</script>

<template>
    <Head title="Edit Exercise" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Exercise</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Name -->
                            <div>
                                <InputLabel for="name" value="Exercise Name *" />
                                <TextInput
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                    autofocus
                                    placeholder="e.g., Bench Press, Running, Yoga Flow"
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <!-- Type -->
                            <div>
                                <InputLabel for="type" value="Exercise Type *" />
                                <select
                                    id="type"
                                    v-model="form.type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="strength">Strength</option>
                                    <option value="cardio">Cardio</option>
                                    <option value="flexibility">Flexibility</option>
                                    <option value="other">Other</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.type" />
                            </div>

                            <!-- Muscle Group -->
                            <div>
                                <InputLabel for="muscle_group" value="Muscle Group" />
                                <TextInput
                                    id="muscle_group"
                                    v-model="form.muscle_group"
                                    type="text"
                                    class="mt-1 block w-full"
                                    placeholder="e.g., Chest, Legs, Full Body"
                                />
                                <InputError class="mt-2" :message="form.errors.muscle_group" />
                                <p class="mt-1 text-sm text-gray-500">
                                    Optional: Specify the primary muscle group targeted
                                </p>
                            </div>

                            <!-- Description -->
                            <div>
                                <InputLabel for="description" value="Description" />
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    rows="4"
                                    placeholder="Notes about form, variations, or equipment needed..."
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.description" />
                                <p class="mt-1 text-sm text-gray-500">
                                    Optional: Add notes about form, equipment, or variations
                                </p>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex items-center justify-end space-x-3">
                                <Link :href="route('exercises.index')">
                                    <SecondaryButton type="button"> Cancel </SecondaryButton>
                                </Link>
                                <PrimaryButton
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    Update Exercise
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
