<script setup>
import { ref, watch, onMounted } from 'vue';
import api from '../../api';
import { useFilterStore } from '../../stores/filters';
import { useAuthStore } from '../../stores/auth';
import Badge from '../../components/Badge.vue';
import Progress from '../../components/Progress.vue';
import Pagination from '../../components/Pagination.vue';
import { formatCurrency, formatDate, CONTRACT_STATUS, CONTRACT_TYPE, errorMessage } from '../../utils/format';

const filters = useFilterStore();
const auth = useAuthStore();
const items = ref({ data: [], meta: {} });
const loading = ref(true);
const params = ref({ search: '', status: '', type: '', page: 1 });

function remaining(c) {
    return Number(c.contract_value) - Number(c.used_value);
}

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/ogb', { params: { ...filters.queryParams, ...params.value } });
        items.value = data;
    } finally {
        loading.value = false;
    }
}

function del(item) {
    if (!confirm(`Delete contract "${item.name}"?`)) return;
    api.delete(`/ogb/${item.id}`).then(load).catch((e) => alert(errorMessage(e)));
}

watch(() => [filters.selected, params.value], load, { deep: true });
onMounted(load);
</script>

<template>
    <div class="cmp-card">
        <div class="cmp-toolbar">
            <select v-model="params.status" class="cmp-select w-36">
                <option value="">All Statuses</option>
                <option v-for="(v, k) in CONTRACT_STATUS" :key="k" :value="k">{{ v.label }}</option>
            </select>
            <select v-model="params.type" class="cmp-select w-36">
                <option value="">All Types</option>
                <option v-for="(v, k) in CONTRACT_TYPE" :key="k" :value="k">{{ v.label }}</option>
            </select>

            <div class="ml-auto flex items-center gap-2">
                <input v-model="params.search" @input="params.page=1" placeholder="Search contract / vendor…" class="cmp-input w-56" />
                <RouterLink v-if="auth.can('ogb.manage')" :to="'/ogb/new'" class="cmp-btn cmp-btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M12 4v16m8-8H4" /></svg>
                    New Contract
                </RouterLink>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="cmp-table">
                <thead>
                    <tr>
                        <th>Contract</th>
                        <th>Type</th>
                        <th class="text-right">Value</th>
                        <th class="text-right">Used</th>
                        <th class="text-right">Remaining</th>
                        <th>Expiry</th>
                        <th>Maint. Progress</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in items.data" :key="item.id">
                        <td>
                            <p class="font-semibold text-slate-800">{{ item.name }}</p>
                            <p class="text-xs text-slate-400">{{ item.contract_number }} · {{ item.vendor }}</p>
                        </td>
                        <td><Badge :label="CONTRACT_TYPE[item.type]?.label" :color="CONTRACT_TYPE[item.type]?.color" /></td>
                        <td class="text-right font-bold text-slate-800 whitespace-nowrap tabular-nums">{{ formatCurrency(item.contract_value) }}</td>
                        <td class="text-right text-slate-600 whitespace-nowrap tabular-nums">{{ formatCurrency(item.used_value) }}</td>
                        <td class="text-right text-slate-600 whitespace-nowrap tabular-nums">{{ formatCurrency(remaining(item)) }}</td>
                        <td class="text-slate-600 whitespace-nowrap">{{ formatDate(item.end_date) }}</td>
                        <td class="w-32"><Progress :value="item.maintenance_progress" show-label /></td>
                        <td><Badge :label="CONTRACT_STATUS[item.status]?.label" :color="CONTRACT_STATUS[item.status]?.color" /></td>
                        <td>
                            <div class="cmp-actions">
                                <RouterLink :to="`/ogb/${item.id}`" class="cmp-btn cmp-btn-secondary cmp-btn-sm">Edit</RouterLink>
                                <button v-if="auth.can('ogb.manage')" @click="del(item)" class="cmp-btn cmp-btn-ghost cmp-btn-sm text-red-500 hover:bg-red-50 hover:text-red-700">Delete</button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!items.data.length"><td colspan="9" class="cmp-empty">No contracts found</td></tr>
                </tbody>
            </table>
        </div>

        <Pagination :page="items.current_page || 1" :last-page="items.last_page || 1" :total="items.total || 0" @page="params.page = $event" />
    </div>
</template>