<script setup>
import { ref, onMounted } from 'vue';
import api from '../../api';
import Badge from '../../components/Badge.vue';
import Pagination from '../../components/Pagination.vue';
import { errorMessage } from '../../utils/format';

const items = ref({ data: [], meta: {} });
const zones = ref([]);
const loading = ref(true);
const params = ref({ page: 1, search: '' });
const zoneFilter = ref('');

async function load() {
    loading.value = true;
    try {
        const zoneRes = await api.get('/master/zones');
        zones.value = zoneRes.data;
        const { data } = await api.get('/master/sites', { params: { ...params.value, zone_id: zoneFilter.value } });
        items.value = data;
    } finally {
        loading.value = false;
    }
}

function del(s) {
    if (!confirm(`Delete site "${s.name}"?`)) return;
    api.delete(`/master/sites/${s.id}`).then(load).catch((e) => alert(errorMessage(e)));
}

onMounted(load);
</script>

<template>
    <div class="cmp-card">
        <div class="cmp-card-header">
            <div>
                <h2 class="cmp-card-title">Sites</h2>
                <p class="cmp-card-sub">Operational sites grouped under zones.</p>
            </div>
            <div class="ml-auto flex items-center gap-2">
                <input v-model="params.search" placeholder="Search sites…" class="cmp-input w-52" />
                <select v-model="zoneFilter" @change="load" class="cmp-input w-44">
                    <option value="">All zones</option>
                    <option v-for="z in zones" :key="z.id" :value="z.id">{{ z.name }}</option>
                </select>
                <RouterLink :to="{ name: 'master.sites.create' }" class="cmp-btn cmp-btn-primary cmp-btn-sm">New Site</RouterLink>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="cmp-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Zone</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="s in items.data" :key="s.id">
                        <td class="font-mono text-xs font-semibold text-slate-500">{{ s.code }}</td>
                        <td class="font-semibold text-slate-800">{{ s.name }}</td>
                        <td><Badge :label="s.zone?.name || '—'" color="bg-slate-100 text-slate-700" /></td>
                        <td>
                            <div class="cmp-actions cmp-actions-right">
                                <RouterLink :to="{ name: 'master.sites.edit', params: { id: s.id } }" class="cmp-btn cmp-btn-secondary cmp-btn-sm">Edit</RouterLink>
                                <button @click="del(s)" class="cmp-btn cmp-btn-ghost cmp-btn-sm text-red-500 hover:bg-red-50 hover:text-red-700">Delete</button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!items.data.length"><td colspan="4" class="cmp-empty">No sites found</td></tr>
                </tbody>
            </table>
        </div>
        <Pagination :page="items.current_page || 1" :last-page="items.last_page || 1" :total="items.total || 0" @page="params.page = $event" />
    </div>
</template>
