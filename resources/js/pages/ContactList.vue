<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white shadow-sm p-4 flex justify-between items-center">
            <div class="flex space-x-6 items-center">
                <h1 class="text-xl font-bold text-blue-600">Mini CRM</h1>
                <router-link to="/dashboard" class="text-gray-600 hover:text-blue-600">Dashboard</router-link>
                <router-link to="/companies" class="text-gray-600 hover:text-blue-600">Companies</router-link>
                <router-link to="/contacts" class="text-blue-600 font-bold border-b-2 border-blue-600">Contacts</router-link>
            </div>
            <button @click="handleLogout" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">Logout</button>
        </nav>

        <div class="p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-gray-800">Contacts</h2>
                <button v-if="userRole === 'admin'" @click="openAddModal" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition font-bold">
                    + Add New Contact
                </button>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-blue-500">
                <div class="flex space-x-4 mb-4">
                    <input v-model="search" @input="fetchContacts" type="text" placeholder="Search by full name or email..." class="w-2/3 border p-2 rounded outline-none focus:ring-2 focus:ring-blue-500">
                    <select v-model="filterCompanyId" @change="fetchContacts" class="w-1/3 border p-2 rounded outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Companies</option>
                        <option v-for="company in companies" :key="company.id" :value="company.id">{{ company.name }}</option>
                    </select>
                </div>

                <p v-if="contacts.length === 0" class="text-gray-500 text-center py-4">No contacts found.</p>
                
                <table v-else class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-700">
                            <th class="p-3 border-b">Name</th>
                            <th class="p-3 border-b">Company</th>
                            <th class="p-3 border-b">Email</th>
                            <th class="p-3 border-b">Phone</th>
                            <th v-if="userRole === 'admin'" class="p-3 border-b text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="contact in contacts" :key="contact.id" class="hover:bg-gray-50">
                            <td class="p-3 border-b font-semibold">{{ contact.first_name }} {{ contact.last_name }}</td>
                            <td class="p-3 border-b text-blue-600 font-bold">{{ contact.company ? contact.company.name : 'N/A' }}</td>
                            <td class="p-3 border-b text-gray-600">{{ contact.email }}</td>
                            <td class="p-3 border-b text-gray-600">{{ contact.phone }}</td>
                            <td v-if="userRole === 'admin'" class="p-3 border-b text-right">
                                <button @click="openEditModal(contact)" class="text-blue-500 hover:text-blue-700 font-bold px-2 border-r border-gray-300">Edit</button>
                                <button @click="deleteContact(contact.id)" class="text-red-500 hover:text-red-700 font-bold px-2">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="lastPage > 1" class="flex justify-between items-center mt-4">
                    <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1" class="px-4 py-2 bg-gray-200 rounded disabled:opacity-50">Previous</button>
                    <span>Page {{ currentPage }} of {{ lastPage }}</span>
                    <button @click="changePage(currentPage + 1)" :disabled="currentPage === lastPage" class="px-4 py-2 bg-gray-200 rounded disabled:opacity-50">Next</button>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg p-8 w-full max-w-md shadow-xl">
                <h2 class="text-2xl font-bold mb-4">{{ isEditing ? 'Edit Contact' : 'Add New Contact' }}</h2>
                <form @submit.prevent="saveContact">
                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-1 text-gray-700">Assign to Company *</label>
                        <select v-model="form.company_id" class="w-full border p-2 rounded outline-none" required>
                            <option value="" disabled>Select a company</option>
                            <option v-for="company in companies" :key="company.id" :value="company.id">{{ company.name }}</option>
                        </select>
                    </div>
                    <div class="flex space-x-4 mb-4">
                        <div class="w-1/2">
                            <label class="block text-sm font-bold mb-1 text-gray-700">First Name *</label>
                            <input v-model="form.first_name" type="text" class="w-full border p-2 rounded outline-none" required>
                        </div>
                        <div class="w-1/2">
                            <label class="block text-sm font-bold mb-1 text-gray-700">Last Name *</label>
                            <input v-model="form.last_name" type="text" class="w-full border p-2 rounded outline-none" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-1 text-gray-700">Email</label>
                        <input v-model="form.email" type="email" class="w-full border p-2 rounded outline-none">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-1 text-gray-700">Phone</label>
                        <input v-model="form.phone" type="text" class="w-full border p-2 rounded outline-none">
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
const contacts = ref([]);
const companies = ref([]); 
const showModal = ref(false);
const isEditing = ref(false);
const editId = ref(null);
const userRole = ref('user');

const form = ref({ company_id: '', first_name: '', last_name: '', email: '', phone: '' });
const search = ref('');
const filterCompanyId = ref('');
const currentPage = ref(1);
const lastPage = ref(1);

const getAuthHeader = () => { return { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}`, 'Accept': 'application/json' } }; };

const fetchUserRole = async () => {
    try {
        const response = await axios.get('/api/me', getAuthHeader());
        userRole.value = response.data.role;
    } catch (error) { console.error("Could not verify role."); }
};

const fetchContacts = async () => {
    try {
        const response = await axios.get(`/api/contacts?page=${currentPage.value}&search=${search.value}&company_id=${filterCompanyId.value}`, getAuthHeader());
        contacts.value = response.data.data;
        currentPage.value = response.data.current_page;
        lastPage.value = response.data.last_page;
    } catch (error) { console.error(error); }
};

const fetchCompaniesForDropdown = async () => {
    try {
        const response = await axios.get(`/api/companies`, getAuthHeader());
        companies.value = response.data.data;
    } catch (error) { console.error(error); }
};

const changePage = (page) => { currentPage.value = page; fetchContacts(); };
const openAddModal = () => { isEditing.value = false; editId.value = null; form.value = { company_id: '', first_name: '', last_name: '', email: '', phone: '' }; showModal.value = true; };
const openEditModal = (contact) => { isEditing.value = true; editId.value = contact.id; form.value = { company_id: contact.company_id, first_name: contact.first_name, last_name: contact.last_name, email: contact.email, phone: contact.phone }; showModal.value = true; };

const saveContact = async () => {
    try {
        if (isEditing.value) { await axios.put(`/api/contacts/${editId.value}`, form.value, getAuthHeader()); } 
        else { await axios.post('/api/contacts', form.value, getAuthHeader()); }
        Swal.fire('Success!', 'Contact saved.', 'success');
        showModal.value = false;
        fetchContacts();
    } catch (error) { Swal.fire('Error!', 'Check inputs.', 'error'); }
};

const deleteContact = async (id) => {
    const result = await Swal.fire({ title: 'Are you sure?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Delete' });
    if (result.isConfirmed) { await axios.delete(`/api/contacts/${id}`, getAuthHeader()); fetchContacts(); Swal.fire('Deleted!', '', 'success'); }
};

onMounted(() => {
    fetchUserRole();
    fetchContacts();
    fetchCompaniesForDropdown();
});

const handleLogout = () => { localStorage.removeItem('token'); router.push('/'); };
</script>