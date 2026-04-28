<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white shadow-sm p-4 flex justify-between items-center">
            <div class="flex space-x-6 items-center">
                <h1 class="text-xl font-bold text-blue-600">Mini CRM</h1>
                <router-link to="/dashboard" class="text-blue-600 font-bold border-b-2 border-blue-600">Dashboard</router-link>
                <router-link to="/companies" class="text-gray-600 hover:text-blue-600">Companies</router-link>
                <router-link to="/contacts" class="text-gray-600 hover:text-blue-600">Contacts</router-link>
            </div>
            <button @click="handleLogout" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">Logout</button>
        </nav>

        <div class="p-8 max-w-5xl mx-auto">
            <h2 class="text-3xl font-bold text-gray-800 mb-8">Welcome Back, Boss! 🌕</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-blue-500 flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-bold uppercase tracking-wider">Total Companies</p>
                        <h3 class="text-4xl font-black text-gray-800 mt-2">{{ stats.total_companies }}</h3>
                    </div>
                    <div class="bg-blue-100 p-4 rounded-full">
                        <span class="text-3xl">🏢</span>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-green-500 flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-bold uppercase tracking-wider">Total Contacts</p>
                        <h3 class="text-4xl font-black text-gray-800 mt-2">{{ stats.total_contacts }}</h3>
                    </div>
                    <div class="bg-green-100 p-4 rounded-full">
                        <span class="text-3xl">👥</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const stats = ref({ total_companies: 0, total_contacts: 0 });

const fetchStats = async () => {
    try {
        const response = await axios.get('/api/dashboard/stats', {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
        });
        stats.value = response.data;
    } catch (error) {
        console.error("Failed to load stats");
    }
};

onMounted(fetchStats);

const handleLogout = () => {
    localStorage.removeItem('token');
    router.push('/');
};
</script>