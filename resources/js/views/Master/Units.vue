<script setup>
import { ref, onMounted } from 'vue';
import api from '../../api';
import Badge from '../../components/Badge.vue';
import Pagination from '../../components/Pagination.vue';
import { errorMessage } from '../../utils/format';

const items = ref({ data: [], meta: {} });
const loading = ref(true);
const params = ref({ page: 1, search: '' });

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/master/units', { params: { ...params.value } });
        items.value = data;
    } finally {
        loading.value = false;
    }
}

function del(u) {
    if (!confirm(`Delete unit "${u.name}"?`)) return;
    api.delete(`/master/units/${u.id}`).then(load).catch((e) => alert(errorMessage(e)));
}

onMounted(load);
</script>

<template>
    <div class="cmp-card">
        <div class="cmp-card-header">
            <div>
                <h2 class="cmp-card-title">Units</h2>
                <p class="cmp-card-sub">Functions and departments across the organization.</p>
            </div>
            <div class="ml-auto flex items-center gap-2">
                <input v-model="params.search" placeholder="Search…" class="cmp-input w-52" />
                <RouterLink :to="{ name: 'master.units.create' }" class="cmp-btn cmp-btn-primary cmp-btn-sm">New Unit</RouterLink>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="cmp-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Parent</th>
                        <th>Children</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="u in items.data" :key="u.id">
                        <td class="font-mono text-xs font-semibold text-slate-500">{{ u.code }}</td>
                        <td class="font-semibold text-slate-800">{{ u.name }}</td>
                        <td><Badge :label="u.type === 'function' ? 'Function' : 'Department'" :color="u.type === 'function' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-700'" /></td>
                        <td class="text-slate-600">{{ u.parent?.name || '—' }}</td>
                        <td class="text-slate-600 tabular-nums">{{ u.children_count }}</td>
                        <td>
                            <div class="cmp-actions">
                                <RouterLink :to="{ name: 'master.units.edit', params: { id: u.id } }" class="cmp-btn cmp-btn-secondary cmp-btn-sm">Edit</RouterLink>
                                <button @click="del(u)" class="cmp-btn cmp-btn-ghost cmp-btn-sm text-red-500 hover:bg-red-50 hover:text-red-700">Delete</button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!items.data.length"><td colspan="6" class="cmp-empty">No units found</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
