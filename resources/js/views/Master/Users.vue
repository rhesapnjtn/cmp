<script setup>
import { ref, onMounted } from 'vue';
import api from '../../api';
import Badge from '../../components/Badge.vue';
import Pagination from '../../components/Pagination.vue';
import { errorMessage } from '../../utils/format';

const items = ref({ data: [], meta: {} });
const roles = ref([]);
const loading = ref(true);
const params = ref({ page: 1, search: '' });

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/master/users', { params: { ...params.value } });
        items.value = data;
        if (!roles.value.length) {
            const { data: r } = await api.get('/master/roles');
            roles.value = r.data;
        }
    } finally {
        loading.value = false;
    }
}

function del(u) {
    if (!confirm(`Deactivate user "${u.name}"?`)) return;
    api.delete(`/master/users/${u.id}`).then(load).catch((e) => alert(errorMessage(e)));
}

onMounted(load);
</script>

<template>
    <div class="cmp-card">
        <div class="cmp-card-header">
            <div>
                <h2 class="cmp-card-title">Users</h2>
                <p class="cmp-card-sub">User accounts, roles and unit assignment.</p>
            </div>
            <div class="ml-auto flex items-center gap-2">
                <input v-model="params.search" placeholder="Search name / email…" class="cmp-input w-52" />
                <RouterLink :to="{ name: 'master.users.create' }" class="cmp-btn cmp-btn-primary cmp-btn-sm">New User</RouterLink>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="cmp-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Job Title</th>
                        <th>Unit</th>
                        <th>Roles</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="u in items.data" :key="u.id">
                        <td class="font-semibold text-slate-800">{{ u.name }}</td>
                        <td class="text-slate-600">{{ u.email }}</td>
                        <td class="text-slate-600">{{ u.job_title || '—' }}</td>
                        <td class="text-slate-600">{{ u.unit?.name || '—' }}</td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                <Badge v-for="r in u.roles || []" :key="r.id" :label="r.name" color="bg-brand-50 text-brand-700" />
                            </div>
                        </td>
                        <td><Badge :label="u.is_active ? 'Active' : 'Inactive'" :color="u.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600'" /></td>
                        <td>
                            <div class="cmp-actions cmp-actions-right">
                                <RouterLink :to="{ name: 'master.users.edit', params: { id: u.id } }" class="cmp-btn cmp-btn-secondary cmp-btn-sm">Edit</RouterLink>
                                <button @click="del(u)" class="cmp-btn cmp-btn-ghost cmp-btn-sm text-red-500 hover:bg-red-50 hover:text-red-700">Delete</button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!items.data.length"><td colspan="7" class="cmp-empty">No users found</td></tr>
                </tbody>
            </table>
        </div>
        <Pagination :page="items.current_page || 1" :last-page="items.last_page || 1" :total="items.total || 0" @page="params.page = $event" />
    </div>
</template>
