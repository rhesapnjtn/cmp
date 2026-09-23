import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('../views/Login.vue'),
        meta: { guest: true },
    },
    {
        path: '/',
        component: () => import('../components/AppLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            { path: '', name: 'dashboard', component: () => import('../views/Dashboard.vue'), meta: { title: 'Dashboard' } },
            { path: 'planning', name: 'planning.index', component: () => import('../views/Planning/Index.vue'), meta: { title: 'Planning', permission: 'planning.view' } },
            { path: 'planning/new', name: 'planning.create', component: () => import('../views/Planning/Form.vue'), meta: { title: 'New Plan', permission: 'planning.manage' } },
            { path: 'planning/:id', name: 'planning.edit', component: () => import('../views/Planning/Form.vue'), meta: { title: 'Edit Plan', permission: 'planning.view' } },
            { path: 'risk', name: 'risk.index', component: () => import('../views/Risk/Index.vue'), meta: { title: 'Risk Register', permission: 'risk.view' } },
            { path: 'risk/new', name: 'risk.create', component: () => import('../views/Risk/Form.vue'), meta: { title: 'New Risk', permission: 'risk.manage' } },
            { path: 'risk/:id', name: 'risk.edit', component: () => import('../views/Risk/Form.vue'), meta: { title: 'Edit Risk', permission: 'risk.view' } },
            { path: 'ogb', name: 'ogb.index', component: () => import('../views/Ogb/Index.vue'), meta: { title: 'Ongoing Business (OGB)', permission: 'ogb.view' } },
            { path: 'ogb/new', name: 'ogb.create', component: () => import('../views/Ogb/Form.vue'), meta: { title: 'New Contract', permission: 'ogb.manage' } },
            { path: 'ogb/:id', name: 'ogb.edit', component: () => import('../views/Ogb/Form.vue'), meta: { title: 'Edit Contract', permission: 'ogb.view' } },
            { path: 'hsse', name: 'hsse.index', component: () => import('../views/Hsse/Index.vue'), meta: { title: 'HSSE Committee', permission: 'hsse.view' } },
            { path: 'hsse/new', name: 'hsse.create', component: () => import('../views/Hsse/Form.vue'), meta: { title: 'New Meeting', permission: 'hsse.manage' } },
            { path: 'hsse/:id', name: 'hsse.edit', component: () => import('../views/Hsse/Form.vue'), meta: { title: 'Edit Meeting', permission: 'hsse.view' } },
            { path: 'compliance', name: 'compliance.index', component: () => import('../views/Compliance/Index.vue'), meta: { title: 'Compliance', permission: 'compliance.view' } },
            { path: 'compliance/new', name: 'compliance.create', component: () => import('../views/Compliance/Form.vue'), meta: { title: 'New Compliance', permission: 'compliance.manage' } },
            { path: 'compliance/:id', name: 'compliance.edit', component: () => import('../views/Compliance/Form.vue'), meta: { title: 'Edit Compliance', permission: 'compliance.view' } },
            { path: 'reports', name: 'reports', component: () => import('../views/Reports.vue'), meta: { title: 'Reports', permission: 'reports.view' } },
            { path: 'master/units', name: 'master.units', component: () => import('../views/Master/Units.vue'), meta: { title: 'Units', permission: 'masterdata.manage' } },
            { path: 'master/units/new', name: 'master.units.create', component: () => import('../views/Master/UnitForm.vue'), meta: { title: 'New Unit', permission: 'masterdata.manage' } },
            { path: 'master/units/:id', name: 'master.units.edit', component: () => import('../views/Master/UnitForm.vue'), meta: { title: 'Edit Unit', permission: 'masterdata.manage' } },
            { path: 'master/zones', name: 'master.zones', component: () => import('../views/Master/Zones.vue'), meta: { title: 'Zones', permission: 'masterdata.manage' } },
            { path: 'master/zones/new', name: 'master.zones.create', component: () => import('../views/Master/ZoneForm.vue'), meta: { title: 'New Zone', permission: 'masterdata.manage' } },
            { path: 'master/zones/:id', name: 'master.zones.edit', component: () => import('../views/Master/ZoneForm.vue'), meta: { title: 'Edit Zone', permission: 'masterdata.manage' } },
            { path: 'master/sites', name: 'master.sites', component: () => import('../views/Master/Sites.vue'), meta: { title: 'Sites', permission: 'masterdata.manage' } },
            { path: 'master/sites/new', name: 'master.sites.create', component: () => import('../views/Master/SiteForm.vue'), meta: { title: 'New Site', permission: 'masterdata.manage' } },
            { path: 'master/sites/:id', name: 'master.sites.edit', component: () => import('../views/Master/SiteForm.vue'), meta: { title: 'Edit Site', permission: 'masterdata.manage' } },
            { path: 'master/users', name: 'master.users', component: () => import('../views/Master/Users.vue'), meta: { title: 'Users', permission: 'users.manage' } },
            { path: 'master/users/new', name: 'master.users.create', component: () => import('../views/Master/UserForm.vue'), meta: { title: 'New User', permission: 'users.manage' } },
            { path: 'master/users/:id', name: 'master.users.edit', component: () => import('../views/Master/UserForm.vue'), meta: { title: 'Edit User', permission: 'users.manage' } },
        ],
    },
    { path: '/:pathMatch(.*)*', name: 'notfound', component: () => import('../views/NotFound.vue') },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();

    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (to.meta.guest && auth.isAuthenticated) {
        return { name: 'dashboard' };
    }

    if (to.meta.permission && auth.isAuthenticated && !auth.can(to.meta.permission)) {
        return { name: 'dashboard' };
    }

    document.title = `${to.meta.title || 'CMP'} — Centralized Monitoring Platform`;
});

export default router;