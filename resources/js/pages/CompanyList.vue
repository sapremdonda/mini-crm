<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white shadow-sm p-4 flex justify-between items-center">
            <div class="flex space-x-6 items-center">
                <h1 class="text-xl font-bold text-blue-600">Mini CRM</h1>
                <router-link to="/dashboard" class="text-gray-600 hover:text-blue-600">Dashboard</router-link>
                <router-link to="/companies" class="text-blue-600 font-bold border-b-2 border-blue-600">Companies</router-link>
                <router-link to="/contacts" class="text-gray-600 hover:text-blue-600">Contacts</router-link>
            </div>
            <button @click="handleLogout" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">Logout</button>
        </nav>

        <div class="p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-gray-800">Companies</h2>
                <div class="space-x-2">
                    <button v-if="userRole === 'admin'" @click="toggleTrash" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition font-bold">
                        {{ viewingTrash ? 'View Active' : 'View Trash' }}
                    </button>
                    <button v-if="userRole === 'admin' && !viewingTrash" @click="openAddModal" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition font-bold">
                        + Add New Company
                    </button>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-blue-500">
                <div v-if="!viewingTrash" class="mb-4">
                    <input v-model="search" @input="fetchCompanies" type="text" placeholder="Search by name or email..." class="w-full border p-2 rounded outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <p v-if="companies.length === 0" class="text-gray-500 text-center py-4">No companies found.</p>
                
                <table v-else class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-700">
                            <th class="p-3 border-b">Logo</th>
                            <th class="p-3 border-b cursor-pointer" @click="sortData('name')">Name ↕</th>
                            <th class="p-3 border-b">Email</th>
                            <th v-if="userRole === 'admin'" class="p-3 border-b text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="company in companies" :key="company.id" class="hover:bg-gray-50">
                            <td class="p-3 border-b">
                                <img v-if="company.logo" :src="'/storage/' + company.logo" alt="logo" class="h-10 w-10 object-cover rounded-full border">
                                <span v-else class="text-xs text-gray-400">No Image</span>
                            </td>
                            <td class="p-3 border-b font-semibold">{{ company.name }}</td>
                            <td class="p-3 border-b text-gray-600">{{ company.email }}</td>
                            <td v-if="userRole === 'admin'" class="p-3 border-b text-right">
                                <template v-if="viewingTrash">
                                    <button @click="restoreCompany(company.id)" class="text-green-500 hover:text-green-700 font-bold px-2">Restore</button>
                                </template>
                                <template v-else>
                                    <button @click="openEditModal(company)" class="text-blue-500 hover:text-blue-700 font-bold px-2 border-r border-gray-300">Edit</button>
                                    <button @click="deleteCompany(company.id)" class="text-red-500 hover:text-red-700 font-bold px-2">Delete</button>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="!viewingTrash && lastPage > 1" class="flex justify-between items-center mt-4">
                    <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1" class="px-4 py-2 bg-gray-200 rounded disabled:opacity-50">Previous</button>
                    <span>Page {{ currentPage }} of {{ lastPage }}</span>
                    <button @click="changePage(currentPage + 1)" :disabled="currentPage === lastPage" class="px-4 py-2 bg-gray-200 rounded disabled:opacity-50">Next</button>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg p-8 w-full max-w-md shadow-xl">
                <h2 class="text-2xl font-bold mb-4">{{ isEditing ? 'Edit Company' : 'Add New Company' }}</h2>
                <form @submit.prevent="saveCompany">
                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-1 text-gray-700">Company Name *</label>
                        <input v-model="form.name" type="text" class="w-full border p-2 rounded outline-none" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-1 text-gray-700">Email</label>
                        <input v-model="form.email" type="email" class="w-full border p-2 rounded outline-none">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-1 text-gray-700">Phone</label>
                        <input v-model="form.phone" type="text" class="w-full border p-2 rounded outline-none">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-1 text-gray-700">Logo Image</label>
                        <input type="file" @change="handleFileUpload" class="w-full border p-2 rounded outline-none" accept="image/*">
                    </div>

                    <div class="flex justify-end space-x-2 pt-4">
                        <button type="button" @click="showModal = false" class="bg-gray-300 px-4 py-2 rounded">Cancel</button>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded font-bold">{{ isEditing ? 'Update' : 'Save' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2'; 

const router = useRouter();
const companies = ref([]);
const showModal = ref(false);
const isEditing = ref(false);
const editId = ref(null);
const viewingTrash = ref(false);
const userRole = ref('user');

const form = ref({ name: '', email: '', phone: '' });
const logoFile = ref(null); 
const search = ref('');
const sortBy = ref('name');
const sortDir = ref('asc');
const currentPage = ref(1);
const lastPage = ref(1);

const getAuthHeader = () => {
    return { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}`, 'Accept': 'application/json' } };
};

const fetchUserRole = async () => {
    try {
        const response = await axios.get('/api/me', getAuthHeader());
        userRole.value = response.data.role;
    } catch (error) {
        console.error("Could not verify role.");
    }
};

const fetchCompanies = async () => {
    if (viewingTrash.value) {
        const response = await axios.get('/api/companies/trashed', getAuthHeader());
        companies.value = response.data.data;
        return;
    }
    try {
        const response = await axios.get(`/api/companies?page=${currentPage.value}&search=${search.value}&sort_by=${sortBy.value}&sort_dir=${sortDir.value}`, getAuthHeader());
        companies.value = response.data.data;
        currentPage.value = response.data.current_page;
        lastPage.value = response.data.last_page;
    } catch (error) { console.error(error); }
};

const toggleTrash = () => { viewingTrash.value = !viewingTrash.value; fetchCompanies(); };
const sortData = (column) => {
    if (sortBy.value === column) sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    else { sortBy.value = column; sortDir.value = 'asc'; }
    fetchCompanies();
};
const changePage = (page) => { currentPage.value = page; fetchCompanies(); };
const handleFileUpload = (event) => { logoFile.value = event.target.files[0]; };

const openAddModal = () => { isEditing.value = false; editId.value = null; form.value = { name: '', email: '', phone: '' }; logoFile.value = null; showModal.value = true; };
const openEditModal = (company) => { isEditing.value = true; editId.value = company.id; form.value = { name: company.name, email: company.email, phone: company.phone }; logoFile.value = null; showModal.value = true; };

const saveCompany = async () => {
    const formData = new FormData();
    formData.append('name', form.value.name);
    if(form.value.email) formData.append('email', form.value.email);
    if(form.value.phone) formData.append('phone', form.value.phone);
    if(logoFile.value) formData.append('logo', logoFile.value);
    const config = { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}`, 'Content-Type': 'multipart/form-data', 'Accept': 'application/json' } };

    try {
        if (isEditing.value) {
            formData.append('_method', 'PUT'); 
            await axios.post(`/api/companies/${editId.value}`, formData, config);
        } else { await axios.post('/api/companies', formData, config); }
        Swal.fire('Success!', 'Company saved.', 'success');
        showModal.value = false;
        fetchCompanies();
    } catch (error) { Swal.fire('Error!', 'Check inputs.', 'error'); }
};

const deleteCompany = async (id) => {
    const result = await Swal.fire({ title: 'Are you sure?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Delete' });
    if (result.isConfirmed) { await axios.delete(`/api/companies/${id}`, getAuthHeader()); fetchCompanies(); Swal.fire('Deleted!', '', 'success'); }
};
const restoreCompany = async (id) => { await axios.post(`/api/companies/${id}/restore`, {}, getAuthHeader()); fetchCompanies(); Swal.fire('Restored!', '', 'success'); };

onMounted(() => {
    fetchUserRole(); 
    fetchCompanies();
});

const handleLogout = () => { localStorage.removeItem('token'); router.push('/'); };
</script>