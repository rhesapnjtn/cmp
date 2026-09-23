<script setup>
import { ref, watch, onMounted } from 'vue';
import api from '../../api';
import { useFilterStore } from '../../stores/filters';
import { useAuthStore } from '../../stores/auth';
import Badge from '../../components/Badge.vue';
import Pagination from '../../components/Pagination.vue';
import { formatDate, RISK_LEVEL, RISK_STATUS, RISK_CATEGORY, errorMessage } from '../../utils/format';

const filters = useFilterStore();
const auth = useAuthStore();
const items = ref({ data: [], meta: {} });
const loading = ref(true);
const params = ref({ search: '', level: '', category: '', status: '', page: 1 });

const scoreText = (s) => (s >= 9 ? 'text-red-600' : s >= 4 ? 'text-amber-600' : 'text-emerald-600');

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/risk', { params: { ...filters.queryParams, ...params.value } });
        items.value = data;
    } finally {
        loading.value = false;
    }
}

function del(item) {
    if (!confirm(`Delete risk "${item.title}"?`)) return;
    api.delete(`/risk/${item.id}`).then(load).catch((e) => alert(errorMessage(e)));
}

watch(() => [filters.selected, params.value], load, { deep: true });
onMounted(load);
</script>

<template>
    <div class="cmp-card">
        <div class="cmp-toolbar">
            <select v-model="params.level" class="cmp-select w-36">
                <option value="">All Levels</option>
                <option v-for="(v, k) in RISK_LEVEL" :key="k" :value="k">{{ v.label }}</option>
            </select>
            <select v-model="params.category" class="cmp-select w-40">
                <option value="">All Categories</option>
                <option v-for="(v, k) in RISK_CATEGORY" :key="k" :value="k">{{ v.label }}</option>
            </select>
            <select v-model="params.status" class="cmp-select w-40">
                <option value="">All Statuses</option>
                <option v-for="(v, k) in RISK_STATUS" :key="k" :value="k">{{ v.label }}</option>
            </select>

            <div class="ml-auto flex items-center gap-2">
                <input v-model="params.search" @input="params.page=1" placeholder="Search risk…" class="cmp-input w-52" />
                <RouterLink v-if="auth.can('risk.manage')" :to="'/risk/new'" class="cmp-btn cmp-btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M12 4v16m8-8H4" /></svg>
                    New Risk
                </RouterLink>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="cmp-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Risk</th>
                        <th>Unit</th>
                        <th>Level</th>
                        <th>Score</th>
                        <th>Status</th>
                        <th>Due</th>
                        <th>PIC</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in items.data" :key="item.id">
                        <td class="text-xs font-mono font-semibold text-slate-500">{{ item.risk_code }}</td>
                        <td>
                            <p class="font-semibold text-slate-800">{{ item.title }}</p>
                            <Badge :label="RISK_CATEGORY[item.category]?.label" :color="RISK_CATEGORY[item.category]?.color" />
                        </td>
                        <td class="text-slate-600">{{ item.unit?.name || '—' }}</td>
                        <td><Badge :label="RISK_LEVEL[item.risk_level]?.label" :color="RISK_LEVEL[item.risk_level]?.color" /></td>
                        <td><span class="font-extrabold tabular-nums" :class="scoreText(item.risk_score)">{{ item.risk_score }}</span></td>
                        <td><Badge :label="RISK_STATUS[item.status]?.label" :color="RISK_STATUS[item.status]?.color" /></td>
                        <td class="text-slate-600 whitespace-nowrap">{{ formatDate(item.due_date) }}</td>
                        <td class="text-slate-600">{{ item.pic?.name || '—' }}</td>
                        <td>
                            <div class="cmp-actions">
                                <RouterLink :to="`/risk/${item.id}`" class="cmp-btn cmp-btn-secondary cmp-btn-sm">Edit</RouterLink>
                                <button v-if="auth.can('risk.manage')" @click="del(item)" class="cmp-btn cmp-btn-ghost cmp-btn-sm text-red-500 hover:bg-red-50 hover:text-red-700">Delete</button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!items.data.length"><td colspan="9" class="cmp-empty">No risks found</td></tr>
                </tbody>
            </table>
        </div>

        <Pagination :page="items.current_page || 1" :last-page="items.last_page || 1" :total="items.total || 0" @page="params.page = $event" />
    </div>
</template>