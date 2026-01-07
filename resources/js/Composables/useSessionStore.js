import { ref, computed } from 'vue';
import axios from 'axios';

export function useSessionStore(initialSession) {
    const session = ref(initialSession);
    const loading = ref(false);
    const error = ref(null);

    // Computed properties
    const exercises = computed(() => session.value.exercises || []);

    const isCompleted = computed(() => session.value.is_completed);

    // Get current exercise by index
    const getExerciseByIndex = (index) => {
        return exercises.value[index] || null;
    };

    // Add a new set to an exercise
    const addSet = async (sessionExerciseId, setData) => {
        console.log('🔵 addSet called', { sessionExerciseId, setData });
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.post(
                `/api/session-exercises/${sessionExerciseId}/sets`,
                setData
            );
            console.log('✅ API response:', response.data);

            // Update the local state with the new set
            const exerciseIndex = session.value.exercises.findIndex(
                (ex) => ex.id === sessionExerciseId
            );
            console.log('📍 Exercise index:', exerciseIndex);
            console.log('📊 Current exercises:', session.value.exercises);

            if (exerciseIndex !== -1) {
                session.value.exercises[exerciseIndex].sets.push(response.data.set);
                console.log('✨ Updated sets:', session.value.exercises[exerciseIndex].sets);
            } else {
                console.error('❌ Exercise not found!');
            }

            loading.value = false;
            return response.data;
        } catch (err) {
            console.error('❌ Error adding set:', err);
            error.value = err.response?.data?.message || 'Failed to add set';
            loading.value = false;
            throw err;
        }
    };

    // Update an existing set
    const updateSet = async (setId, setData) => {
        console.log('🔄 updateSet called', { setId, setData });
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.patch(`/api/session-sets/${setId}`, setData);
            console.log('✅ Update API response:', response.data);

            // Update the local state with the updated set
            for (const exercise of session.value.exercises) {
                const setIndex = exercise.sets.findIndex((s) => s.id === setId);
                if (setIndex !== -1) {
                    console.log('📍 Found set at index:', setIndex);
                    console.log('🔍 Old set:', exercise.sets[setIndex]);
                    exercise.sets[setIndex] = response.data.set;
                    console.log('✨ New set:', exercise.sets[setIndex]);
                    console.log('📊 All sets after update:', exercise.sets);
                    break;
                }
            }

            loading.value = false;
            return response.data;
        } catch (err) {
            console.error('❌ Error updating set:', err);
            error.value = err.response?.data?.message || 'Failed to update set';
            loading.value = false;
            throw err;
        }
    };

    // Delete a set
    const deleteSet = async (setId, sessionExerciseId) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.delete(`/api/session-sets/${setId}`);

            // Update the local state - replace all sets for this exercise
            const exerciseIndex = session.value.exercises.findIndex(
                (ex) => ex.id === sessionExerciseId
            );
            if (exerciseIndex !== -1) {
                session.value.exercises[exerciseIndex].sets = response.data.sets;
            }

            loading.value = false;
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to delete set';
            loading.value = false;
            throw err;
        }
    };

    // Refresh session data from server
    const refreshSession = async () => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get(`/api/sessions/${session.value.id}`);
            session.value = response.data.session;
            loading.value = false;
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to refresh session';
            loading.value = false;
            throw err;
        }
    };

    // Get stats for progress summary
    const getSessionStats = computed(() => {
        const totalExercises = exercises.value.length;
        const completedExercises = exercises.value.filter((ex) => ex.sets.length > 0).length;
        const totalSets = exercises.value.reduce((sum, ex) => sum + ex.sets.length, 0);

        // Calculate elapsed time
        const startedAt = new Date(session.value.started_at);
        const now = new Date();
        const elapsedMinutes = Math.floor((now - startedAt) / 1000 / 60);

        return {
            totalExercises,
            completedExercises,
            totalSets,
            elapsedMinutes,
        };
    });

    return {
        session,
        exercises,
        loading,
        error,
        isCompleted,
        getExerciseByIndex,
        addSet,
        updateSet,
        deleteSet,
        refreshSession,
        getSessionStats,
    };
}
