<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import api from '../api';
import { useFilterStore } from '../stores/filters';
import Badge from '../components/Badge.vue';
import Progress from '../components/Progress.vue';
import Apex from '../components/Apex.vue';
import { formatCurrency, formatDate, RISK_LEVEL, CONTRACT_STATUS } from '../utils/format';

const filters = useFilterStore();
const data = ref(null);
const loading = ref(true);

const palette = { indigo: '#4f46e5', emerald: '#10b981', amber: '#f59e0b', red: '#ef4444', blue: '#0ea5e9', violet: '#8b5cf6', slate: '#94a3b8' };

const kpis = computed(() => {
    if (!data.value) return [];
    const p = data.value.planning;
    const r = data.value.risk;
    const o = data.value.ogb;
    const h = data.value.hsse;
    const c = data.value.compliance;

    return [
        { label: 'Planning Realization', value: p.total ? `${p.done}/${p.total}` : '0', sub: `${p.avgProgress}% avg progress`, accent: 'bg-brand-600', chip: 'bg-brand-50 text-brand-600', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', badge: `${p.overdue} overdue`, badgeColor: 'bg-amber-50 text-amber-700 border-amber-100' },
        { label: 'High Risk Exposure', value: `${r.high + r.critical}`, sub: `${r.total} risks registered · ${r.open} open`, accent: 'bg-red-500', chip: 'bg-red-50 text-red-600', icon: 'M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', badge: `${r.critical} critical`, badgeColor: 'bg-red-50 text-red-700 border-red-100' },
        { label: 'OGB Contract Value', value: formatCurrency(o.totalValue), sub: `Used ${formatCurrency(o.usedValue)} · work ${o.avgWorkProgress}%`, accent: 'bg-emerald-500', chip: 'bg-emerald-50 text-emerald-600', icon: 'M17 20h5v-9l-5-6v15zm0 0v-6h5M3 20h5V9l5 6m0 0v5', badge: `${o.expiring} expiring`, badgeColor: 'bg-amber-50 text-amber-700 border-amber-100' },
        { label: 'HSSE Realization', value: `${h.done}/${h.total}`, sub: `${h.pt}% of target met`, accent: 'bg-violet-500', chip: 'bg-violet-50 text-violet-600', icon: 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z', badge: `${h.missed} missed`, badgeColor: 'bg-red-50 text-red-700 border-red-100' },
        { label: 'Compliance', value: `${c.rate}%`, sub: `${c.compliant}/${c.total} compliant`, accent: 'bg-blue-500', chip: 'bg-blue-50 text-blue-600', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', badge: `${c.overdue} overdue`, badgeColor: 'bg-red-50 text-red-700 border-red-100' },
    ];
});

const planningChart = computed(() => {
    const t = data.value?.trends?.plans || [];
    return {
        options: { chart: { type: 'bar', toolbar: { show: false } }, xaxis: { categories: t.map((q) => `Q${q.quarter}`) }, plotOptions: { bar: { columnWidth: '45%', borderRadius: 4 } }, colors: [palette.slate, palette.indigo], legend: { position: 'top' } },
        series: [
            { name: 'Total', data: t.map((q) => q.total) },
            { name: 'Done', data: t.map((q) => q.done) },
        ],
    };
});

const riskLevelChart = computed(() => {
    const lv = data.value?.risk?.byLevel || {};
    const cats = ['critical', 'high', 'medium', 'low'];
    const labels = { critical: 'Critical', high: 'High', medium: 'Medium', low: 'Low' };
    const colors = { critical: palette.red, high: palette.amber, medium: palette.blue, low: palette.emerald };
    const series = cats.map((c) => lv[c] || 0);

    return {
        options: { chart: { type: 'donut' }, labels: cats.map((c) => labels[c]), colors: cats.map((c) => colors[c]), legend: { position: 'bottom' }, dataLabels: { formatter: (v) => v + '%' } },
        series,
    };
});

const hsseChart = computed(() => {
    const t = data.value?.trends?.hsse || [];
    return {
        options: { chart: { type: 'bar', toolbar: { show: false } }, xaxis: { categories: t.map((q) => `Q${q.quarter}`) }, plotOptions: { bar: { columnWidth: '50%', borderRadius: 4 } }, colors: [palette.violet, palette.emerald], legend: { position: 'top' } },
        series: [
            { name: 'Target', data: t.map((q) => q.target) },
            { name: 'Realized', data: t.map((q) => q.done) },
        ],
    };
});

const complianceChart = computed(() => {
    const byStatus = data.value?.compliance?.byStatus || [];
    const map = { compliant: 'Compliant', partial: 'Partial', non_compliant: 'Non Compliant', in_progress: 'In Progress', na: 'N/A' };
    const colors = { compliant: palette.emerald, partial: palette.amber, non_compliant: palette.red, in_progress: palette.blue, na: palette.slate };

    return {
        options: { chart: { type: 'pie' }, labels: byStatus.map((s) => map[s.status] || s.status), colors: byStatus.map((s) => colors[s.status] || palette.slate), legend: { position: 'bottom' } },
        series: byStatus.map((s) => s.total),
    };
});

const ogbChart = computed(() => {
    const byStatus = data.value?.ogb?.byStatus || [];
    const map = { active: 'Active', expiring: 'Expiring', expired: 'Expired', completed: 'Completed', terminated: 'Terminated' };
    const colors = { active: palette.emerald, expiring: palette.amber, expired: palette.red, completed: palette.blue, terminated: palette.slate };

    return {
        options: { chart: { type: 'donut' }, labels: byStatus.map((s) => map[s.status] || s.status), colors: byStatus.map((s) => colors[s.status] || palette.slate), legend: { position: 'bottom' } },
        series: byStatus.map((s) => s.total),
    };
});

const contractStatusColor = (status) => {
    const map = {
        active: 'bg-emerald-100 text-emerald-700',
        expiring: 'bg-amber-100 text-amber-700',
        expired: 'bg-red-100 text-red-700',
        completed: 'bg-blue-100 text-blue-700',
        terminated: 'bg-slate-200 text-slate-600',
    };
    return map[status] || 'bg-slate-100 text-slate-700';
};

async function load() {
    loading.value = true;
    try {
        const { data: d } = await api.get('/dashboard', { params: filters.queryParams });
        data.value = d;
    } finally {
        loading.value = false;
    }
}

watch(() => filters.selected, load, { deep: true });
onMounted(load);
</script>

<template>
    <div v-if="loading" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
            <div v-for="i in 5" :key="i" class="h-32 bg-slate-200 animate-pulse rounded-xl"></div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div v-for="i in 5" :key="i" class="h-64 bg-slate-200 animate-pulse rounded-xl"></div>
        </div>
    </div>

    <template v-else-if="data">
        <!-- KPI cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
            <div v-for="k in kpis" :key="k.label" class="cmp-kpi hover:shadow-md transition-shadow">
                <div class="cmp-kpi-accent" :class="k.accent"></div>
                <div class="cmp-kpi-body">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="k.chip">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" :d="k.icon" /></svg>
                        </div>
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wide leading-snug">{{ k.label }}</span>
                    </div>
                    <div class="mt-1 text-[28px] font-extrabold text-slate-900 leading-none tabular-nums tracking-tight">{{ k.value }}</div>
                    <div class="text-xs text-slate-500">{{ k.sub }}</div>
                    <div>
                        <Badge :label="k.badge" :color="k.badgeColor" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="cmp-card lg:col-span-1">
                <div class="cmp-card-header">
                    <h3 class="cmp-card-title">Planning Per Quarter</h3>
                </div>
                <div class="cmp-card-pad">
                    <Apex :options="planningChart.options" :series="planningChart.series" height="250" />
                </div>
            </div>
            <div class="cmp-card">
                <div class="cmp-card-header">
                    <h3 class="cmp-card-title">Risk Level Distribution</h3>
                </div>
                <div class="cmp-card-pad">
                    <Apex :options="riskLevelChart.options" :series="riskLevelChart.series" height="250" />
                </div>
            </div>
            <div class="cmp-card">
                <div class="cmp-card-header">
                    <h3 class="cmp-card-title">HSSE Target vs Realization</h3>
                </div>
                <div class="cmp-card-pad">
                    <Apex :options="hsseChart.options" :series="hsseChart.series" height="250" />
                </div>
            </div>
        </div>

        <!-- Charts row 2 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="cmp-card">
                <div class="cmp-card-header">
                    <h3 class="cmp-card-title">Compliance Status</h3>
                </div>
                <div class="cmp-card-pad">
                    <Apex :options="complianceChart.options" :series="complianceChart.series" height="230" />
                </div>
            </div>
            <div class="cmp-card">
                <div class="cmp-card-header">
                    <h3 class="cmp-card-title">OGB Contract Status</h3>
                </div>
                <div class="cmp-card-pad">
                    <Apex :options="ogbChart.options" :series="ogbChart.series" height="230" />
                </div>
            </div>

            <!-- Top risks -->
            <div class="cmp-card">
                <div class="cmp-card-header">
                    <h3 class="cmp-card-title">Top Risks</h3>
                    <RouterLink to="/risk" class="ml-auto text-xs font-semibold text-brand-600 hover:text-brand-800">View all →</RouterLink>
                </div>
                <div class="cmp-card-pad space-y-1">
                    <div v-for="r in data.risk.topRisks" :key="r.risk_code" class="flex items-center gap-3 py-2.5 border-b border-slate-100 last:border-b-0">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-slate-700 truncate">{{ r.title }}</p>
                            <p class="text-xs text-slate-400">{{ r.risk_code }}</p>
                        </div>
                        <Badge :label="(RISK_LEVEL[r.risk_level] || {}).label" :color="(RISK_LEVEL[r.risk_level] || {}).color" />
                    </div>
                    <div v-if="!data.risk.topRisks.length" class="cmp-empty">No risks</div>
                </div>
            </div>
        </div>

        <!-- Bottom tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Planning by unit -->
            <div class="cmp-card">
                <div class="cmp-card-header">
                    <h3 class="cmp-card-title">Planning Progress by Unit</h3>
                    <RouterLink to="/planning" class="ml-auto text-xs font-semibold text-brand-600 hover:text-brand-800">View all →</RouterLink>
                </div>
                <div class="cmp-card-pad">
                    <div v-for="u in data.planning.byUnit" :key="u.unit_id" class="py-2.5 border-b border-slate-100 last:border-b-0">
                        <div class="flex justify-between items-baseline mb-1.5">
                            <span class="text-sm font-semibold text-slate-700">{{ u.unit }}</span>
                            <span class="text-slate-500 text-xs font-medium">{{ u.total }} plans</span>
                        </div>
                        <Progress :value="u.progress" :show-label="true" />
                    </div>
                    <div v-if="!data.planning.byUnit.length" class="cmp-empty">No data</div>
                </div>
            </div>

            <!-- Expiring contracts -->
            <div class="cmp-card">
                <div class="cmp-card-header">
                    <h3 class="cmp-card-title">Expiring / Active Contracts</h3>
                    <RouterLink to="/ogb" class="ml-auto text-xs font-semibold text-brand-600 hover:text-brand-800">View all →</RouterLink>
                </div>
                <div class="overflow-x-auto">
                    <table class="cmp-table">
                        <thead>
                            <tr>
                                <th>Contract</th>
                                <th>Vendor</th>
                                <th>Expiry</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="c in data.ogb.expiringSoon" :key="c.id">
                                <td>
                                    <p class="font-semibold text-slate-700 truncate max-w-[160px]">{{ c.name }}</p>
                                    <p class="text-xs text-slate-400">{{ c.contract_number }}</p>
                                </td>
                                <td class="text-slate-500">{{ c.vendor }}</td>
                                <td class="text-slate-600">{{ formatDate(c.end_date) }}</td>
                                <td><Badge :label="(CONTRACT_STATUS[c.status] || {}).label" :color="contractStatusColor(c.status)" /></td>
                            </tr>
                            <tr v-if="!data.ogb.expiringSoon.length"><td colspan="4" class="cmp-empty">No contracts</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </template>
</template>