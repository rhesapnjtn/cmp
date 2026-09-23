<script setup>
import { ref, watch, onMounted } from 'vue';
import api from '../../api';
import { useFilterStore } from '../../stores/filters';
import { useAuthStore } from '../../stores/auth';
import Badge from '../../components/Badge.vue';
import Progress from '../../components/Progress.vue';
import Pagination from '../../components/Pagination.vue';
import { formatDate, PLAN_STATUS, errorMessage } from '../../utils/format';

const filters = useFilterStore();
const auth = useAuthStore();
const items = ref({ data: [], meta: {} });
const loading = ref(true);
const params = ref({ status: '', search: '', unrealized: false, page: 1 });

const activeTab = (s) => (s === '' ? !params.value.status && !params.value.unrealized : params.value.status === s && !params.value.unrealized);

function setTab(s, unrealized = false) {
    params.value.status = s;
    params.value.unrealized = unrealized;
}

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/planning', {
            params: { ...filters.queryParams, ...params.value },
        });
        items.value = data;
    } finally {
        loading.value = false;
    }
}

function del(item) {
    if (!confirm(`Delete plan "${item.title}"?`)) return;
    api.delete(`/planning/${item.id}`).then(load).catch((e) => alert(errorMessage(e)));
}

watch(() => [filters.selected, params.value], load, { deep: true });
onMounted(load);
</script>

<template>
    <div class="cmp-card">
        <div class="cmp-toolbar">
            <button @click="setTab('')" class="cmp-pill transition-colors" :class="activeTab('') ? 'bg-brand-600 text-white border-brand-600 shadow-sm' : 'bg-white border-slate-200 text-slate-600 hover:border-brand-300 hover:text-brand-700'">All</button>
            <button v-for="(v, k) in PLAN_STATUS" :key="k" @click="setTab(k)" class="cmp-pill transition-colors" :class="activeTab(k) ? 'bg-brand-600 text-white border-brand-600 shadow-sm' : 'bg-white border-slate-200 text-slate-600 hover:border-brand-300 hover:text-brand-700'">{{ v.label }}</button>
            <button @click="setTab('', true)" class="cmp-pill transition-colors" :class="activeTab('unrealized') ? 'bg-brand-600 text-white border-brand-600 shadow-sm' : 'bg-white border-slate-200 text-slate-600 hover:border-brand-300 hover:text-brand-700'">Unrealized</button>

            <div class="ml-auto flex items-center gap-2">
                <input v-model="params.search" @input="params.page=1" placeholder="Search plan…" class="cmp-input w-52" />
                <RouterLink v-if="auth.can('planning.manage')" :to="'/planning/new'" class="cmp-btn cmp-btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M12 4v16m8-8H4" /></svg>
                    New Plan
                </RouterLink>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="cmp-table">
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Unit</th>
                        <th>Quarter</th>
                        <th>Target</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th>PIC</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in items.data" :key="item.id">
                        <td>
                            <p class="font-semibold text-slate-800">{{ item.title }}</p>
                            <p class="text-xs text-slate-400">{{ item.category }}</p>
                        </td>
                        <td class="text-slate-600">{{ item.unit?.name || '—' }}</td>
                        <td class="text-slate-600">Q{{ item.quarter || '—' }}</td>
                        <td class="text-slate-600 whitespace-nowrap">{{ formatDate(item.target_date) }}</td>
                        <td><Progress :value="item.progress" show-label /></td>
                        <td><Badge :label="PLAN_STATUS[item.status]?.label" :color="PLAN_STATUS[item.status]?.color" /></td>
                        <td class="text-slate-600">{{ item.pic?.name || '—' }}</td>
                        <td>
                            <div class="cmp-actions">
                                <RouterLink :to="`/planning/${item.id}`" class="cmp-btn cmp-btn-secondary cmp-btn-sm">Edit</RouterLink>
                                <button v-if="auth.can('planning.manage')" @click="del(item)" class="cmp-btn cmp-btn-ghost cmp-btn-sm text-red-500 hover:bg-red-50 hover:text-red-700">Delete</button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!items.data.length">
                        <td colspan="8" class="cmp-empty">No plans found</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :page="items.current_page || 1" :last-page="items.last_page || 1" :total="items.total || 0" @page="params.page = $event" />
    </div>
</template>