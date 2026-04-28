import { createRouter, createWebHistory } from 'vue-router';
import Login from './pages/Login.vue';
import Dashboard from './pages/Dashboard.vue';
import CompanyList from './pages/CompanyList.vue'; 
import ContactList from './pages/ContactList.vue';

const routes = [
    { path: '/', name: 'Login', component: Login },
    { path: '/dashboard', name: 'Dashboard', component: Dashboard },
    { path: '/companies', name: 'Companies', component: CompanyList },
    { path: '/contacts', name: 'Contacts', component: ContactList } // Don't forget to import ContactList at the top!
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// The Bouncer
router.beforeEach((to, from, next) => {
    const isAuthenticated = localStorage.getItem('token');
    if (to.name !== 'Login' && !isAuthenticated) next({ name: 'Login' });
    else next();
});

export default router;