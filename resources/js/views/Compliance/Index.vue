<script setup>
import { ref, watch, onMounted } from 'vue';
import api from '../../api';
import { useFilterStore } from '../../stores/filters';
import { useAuthStore } from '../../stores/auth';
import Badge from '../../components/Badge.vue';
import Progress from '../../components/Progress.vue';
import Pagination from '../../components/Pagination.vue';
import { formatDate, COMPLIANCE_STATUS, COMPLIANCE_CATEGORY, errorMessage } from '../../utils/format';

const filters = useFilterStore();
const auth = useAuthStore();
const items = ref({ data: [], meta: {} });
const loading = ref(true);
const params = ref({ search: '', status: '', category: '', overdue: false, page: 1 });

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/compliance', { params: { ...filters.queryParams, ...params.value } });
        items.value = data;
    } finally {
        loading.value = false;
    }
}

function del(item) {
    if (!confirm(`Delete compliance "${item.requirement}"?`)) return;
    api.delete(`/compliance/${item.id}`).then(load).catch((e) => alert(errorMessage(e)));
}

watch(() => [filters.selected, params.value], load, { deep: true });
onMounted(load);
</script>

<template>
    <div class="cmp-card">
        <div class="cmp-toolbar">
            <select v-model="params.status" class="cmp-select w-40">
                <option value="">All Statuses</option>
                <option v-for="(v, k) in COMPLIANCE_STATUS" :key="k" :value="k">{{ v.label }}</option>
            </select>
            <select v-model="params.category" class="cmp-select w-40">
                <option value="">All Categories</option>
                <option v-for="(v, k) in COMPLIANCE_CATEGORY" :key="k" :value="k">{{ v.label }}</option>
            </select>
            <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer select-none">
                <input v-model="params.overdue" type="checkbox" class="w-4 h-4 rounded border-slate-300" />
                Overdue
            </label>

            <div class="ml-auto flex items-center gap-2">
                <input v-model="params.search" @input="params.page=1" placeholder="Search requirement…" class="cmp-input w-56" />
                <RouterLink v-if="auth.can('compliance.manage')" :to="'/compliance/new'" class="cmp-btn cmp-btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M12 4v16m8-8H4" /></svg>
                    New Compliance
                </RouterLink>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="cmp-table">
                <thead>
                    <tr>
                        <th>Requirement</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>PIC</th>
                        <th>Deadline</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in items.data" :key="item.id">
                        <td>
                            <p class="font-semibold text-slate-800">{{ item.requirement }}</p>
                            <p class="text-xs text-slate-400">{{ item.regulation }}</p>
                        </td>
                        <td><Badge :label="COMPLIANCE_CATEGORY[item.category]?.label" :color="COMPLIANCE_CATEGORY[item.category]?.color" /></td>
                        <td class="text-slate-600">{{ item.unit?.name || '—' }}</td>
                        <td class="text-slate-600">{{ item.pic?.name || '—' }}</td>
                        <td class="text-slate-600 whitespace-nowrap">{{ formatDate(item.deadline) }}</td>
                        <td class="w-28"><Progress :value="item.progress" show-label /></td>
                        <td><Badge :label="COMPLIANCE_STATUS[item.status]?.label" :color="COMPLIANCE_STATUS[item.status]?.color" /></td>
                        <td>
                            <div class="cmp-actions">
                                <RouterLink :to="`/compliance/${item.id}`" class="cmp-btn cmp-btn-secondary cmp-btn-sm">Edit</RouterLink>
                                <button v-if="auth.can('compliance.manage')" @click="del(item)" class="cmp-btn cmp-btn-ghost cmp-btn-sm text-red-500 hover:bg-red-50 hover:text-red-700">Delete</button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!items.data.length"><td colspan="8" class="cmp-empty">No compliance items found</td></tr>
                </tbody>
            </table>
        </div>

        <Pagination :page="items.current_page || 1" :last-page="items.last_page || 1" :total="items.total || 0" @page="params.page = $event" />
    </div>
</template>