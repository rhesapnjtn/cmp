<script setup>
import { ref, watch, onMounted } from 'vue';
import api from '../../api';
import { useFilterStore } from '../../stores/filters';
import { useAuthStore } from '../../stores/auth';
import Badge from '../../components/Badge.vue';
import Progress from '../../components/Progress.vue';
import Pagination from '../../components/Pagination.vue';
import { formatDate, MEETING_STATUS, errorMessage } from '../../utils/format';

const filters = useFilterStore();
const auth = useAuthStore();
const items = ref({ data: [], meta: {} });
const summary = ref([]);
const loading = ref(true);
const params = ref({ level: '', status: '', page: 1 });

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/hsse', { params: { ...filters.queryParams, ...params.value } });
        items.value = data.data;
        summary.value = data.summary || [];
    } finally {
        loading.value = false;
    }
}

function del(item) {
    if (!confirm(`Delete meeting "${item.title}"?`)) return;
    api.delete(`/hsse/${item.id}`).then(load).catch((e) => alert(errorMessage(e)));
}

watch(() => [filters.selected, params.value], load, { deep: true });
onMounted(load);
</script>

<template>
    <div class="space-y-4">
        <!-- Summary cards per level -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div v-for="s in summary" :key="s.level" class="cmp-kpi hover:shadow-md transition-shadow">
                <div class="cmp-kpi-accent bg-violet-500"></div>
                <div class="cmp-kpi-body">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-800">Level {{ s.level }}</h3>
                        <span class="text-xs font-bold text-slate-500 tabular-nums">{{ s.realized }} / {{ s.target }}</span>
                    </div>
                    <Progress :value="s.target ? (s.realized / s.target) * 100 : 0" color="bg-violet-500" :show-label="true" />
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-slate-500">{{ Math.max(s.target - s.realized, 0) }} remaining</span>
                        <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                        <span class="text-red-500 font-semibold">{{ s.missed }} missed</span>
                    </div>
                </div>
            </div>
            <div v-if="!summary.length" class="md:col-span-3 cmp-card cmp-card-pad text-center text-slate-400 text-sm">No HSSE data</div>
        </div>

        <div class="cmp-card">
            <div class="cmp-toolbar">
                <button @click="params.level=''" class="cmp-pill transition-colors" :class="!params.level ? 'bg-brand-600 text-white border-brand-600 shadow-sm' : 'bg-white border-slate-200 text-slate-600 hover:border-brand-300 hover:text-brand-700'">All Levels</button>
                <button v-for="l in [1,2,3]" :key="l" @click="params.level=l" class="cmp-pill transition-colors" :class="params.level===l ? 'bg-brand-600 text-white border-brand-600 shadow-sm' : 'bg-white border-slate-200 text-slate-600 hover:border-brand-300 hover:text-brand-700'">Level {{ l }}</button>
                <select v-model="params.status" class="ml-auto cmp-select w-36">
                    <option value="">All Statuses</option>
                    <option v-for="(v, k) in MEETING_STATUS" :key="k" :value="k">{{ v.label }}</option>
                </select>
                <RouterLink v-if="auth.can('hsse.manage')" :to="'/hsse/new'" class="cmp-btn cmp-btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M12 4v16m8-8H4" /></svg>
                    New Meeting
                </RouterLink>
            </div>

            <div class="overflow-x-auto">
                <table class="cmp-table">
                    <thead>
                        <tr>
                            <th>Level</th>
                            <th>Meeting</th>
                            <th>Unit</th>
                            <th>Zone / Site</th>
                            <th>Quarter</th>
                            <th>Target</th>
                            <th>Planned</th>
                            <th>Realized</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in items.data" :key="item.id">
                            <td><Badge :label="`L${item.level}`" color="bg-violet-100 text-violet-700" /></td>
                            <td class="font-semibold text-slate-800">{{ item.title }}</td>
                            <td class="text-slate-600">{{ item.unit?.name || '—' }}</td>
                            <td class="text-slate-600">{{ item.zone?.name || '—' }} / {{ item.site?.name || '—' }}</td>
                            <td class="text-slate-600">Q{{ item.quarter }}</td>
                            <td class="text-slate-600 tabular-nums">{{ item.target_count }}</td>
                            <td class="text-slate-600 whitespace-nowrap">{{ formatDate(item.planned_date) }}</td>
                            <td class="text-slate-600 whitespace-nowrap">{{ formatDate(item.realized_date) }}</td>
                            <td><Badge :label="MEETING_STATUS[item.status]?.label" :color="MEETING_STATUS[item.status]?.color" /></td>
                            <td>
                                <div class="cmp-actions">
                                    <RouterLink :to="`/hsse/${item.id}`" class="cmp-btn cmp-btn-secondary cmp-btn-sm">Edit</RouterLink>
                                    <button v-if="auth.can('hsse.manage')" @click="del(item)" class="cmp-btn cmp-btn-ghost cmp-btn-sm text-red-500 hover:bg-red-50 hover:text-red-700">Delete</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!items.data.length"><td colspan="10" class="cmp-empty">No meetings found</td></tr>
                    </tbody>
                </table>
            </div>

            <Pagination :page="items.current_page || 1" :last-page="items.last_page || 1" :total="items.total || 0" @page="params.page = $event" />
        </div>
    </div>
</template>