<script setup>
import { ref, watch, onMounted } from 'vue';
import api from '../api';
import { useFilterStore } from '../stores/filters';
import Progress from '../components/Progress.vue';
import { formatCurrency } from '../utils/format';

const filters = useFilterStore();
const rows = ref([]);
const meta = ref({});
const loading = ref(true);
const exporting = ref(false);

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/reports', { params: filters.queryParams });
        rows.value = data.data;
        meta.value = data.meta || {};
    } finally {
        loading.value = false;
    }
}

function exportExcel() {
    exporting.value = true;
    api.get('/reports/export', { params: filters.queryParams, responseType: 'blob' })
        .then((res) => {
            const url = URL.createObjectURL(res.data);
            const a = document.createElement('a');
            a.href = url;
            a.download = `cmp-report-${meta.value.year || new Date().getFullYear()}.xlsx`;
            a.click();
            URL.revokeObjectURL(url);
        })
        .finally(() => {
            exporting.value = false;
        });
}

watch(() => filters.selected, load, { deep: true });
onMounted(load);
</script>

<template>
    <div class="space-y-4">
        <div class="cmp-card">
            <div class="cmp-card-header">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-amber-50 border border-amber-100 flex items-center justify-center">
                        <svg class="w-4.5 h-4.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    </div>
                    <div>
                        <h2 class="cmp-card-title">Performance Summary</h2>
                        <p class="cmp-card-sub">Per-department scorecard across all modules for {{ meta.year || filters.selected.year }}</p>
                    </div>
                </div>
                <button @click="exportExcel" :disabled="exporting" class="ml-auto cmp-btn cmp-btn-primary cmp-btn-sm">
                    <svg v-if="exporting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    {{ exporting ? 'Exporting…' : 'Export Excel' }}
                </button>
            </div>
        </div>

        <div class="cmp-card overflow-x-auto">
            <table class="cmp-table">
                <thead>
                    <tr>
                        <th>Unit</th>
                        <th>Planning</th>
                        <th>Risk</th>
                        <th class="text-right">OGB Value</th>
                        <th class="text-right">OGB Used</th>
                        <th>HSSE</th>
                        <th>Compliance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in rows" :key="r.unit">
                        <td class="font-semibold text-slate-800 whitespace-nowrap">{{ r.unit }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-24"><Progress :value="r.plan_progress" show-label /></div>
                                <span class="text-xs text-slate-500 tabular-nums">{{ r.plans_done }}/{{ r.plans_total }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="text-slate-600">{{ r.risks_total }} risks</span>
                            <span class="text-xs text-orange-600 font-semibold"> · {{ r.risks_high }} high/critical</span>
                            <span class="text-xs text-slate-400"> · {{ r.risks_closed }} closed</span>
                        </td>
                        <td class="text-right text-slate-600 tabular-nums">{{ formatCurrency(r.contract_value) }}</td>
                        <td class="text-right text-slate-600 tabular-nums">{{ formatCurrency(r.contract_used) }}</td>
                        <td>
                            <span class="text-slate-600 tabular-nums">{{ r.hsse_realized }}/{{ r.hsse_target }}</span>
                            <Progress class="mt-1 w-24" :value="r.hsse_target ? (r.hsse_realized / r.hsse_target) * 100 : 0" show-label />
                        </td>
                        <td>
                            <span class="text-slate-600 tabular-nums">{{ r.compliance_compliant }}/{{ r.compliance_total }}</span>
                            <span v-if="r.compliance_non" class="text-xs text-red-600 font-semibold"> · {{ r.compliance_non }} non</span>
                        </td>
                    </tr>
                    <tr v-if="!rows.length"><td colspan="7" class="cmp-empty">No report data for the selected filters</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</template>