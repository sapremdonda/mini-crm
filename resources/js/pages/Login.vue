<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md w-96">
            <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Mini CRM Login</h2>
            
            <div v-if="errorMessage" class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm">
                {{ errorMessage }}
            </div>

            <form @submit.prevent="handleLogin">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <input v-model="form.email" type="email" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input v-model="form.password" type="password" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200" :disabled="isLoading">
                    {{ isLoading ? 'Logging in...' : 'Sign In' }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();

const form = ref({
    email: '',
    password: ''
});
const errorMessage = ref('');
const isLoading = ref(false);

const handleLogin = async () => {
    isLoading.value = true;
    errorMessage.value = '';

    try {
        const response = await axios.post('/api/login', {
            email: form.value.email,
            password: form.value.password
        });

        localStorage.setItem('token', response.data.token);
        router.push('/dashboard');
        
    } catch (error) {
        if (error.response && error.response.status === 422) {
            errorMessage.value = error.response.data.message || "Incorrect credentials.";
        } else {
            errorMessage.value = "Something went wrong. Is the Laravel server running?";
        }
    } finally {
        isLoading.value = false;
    }
};
</script>