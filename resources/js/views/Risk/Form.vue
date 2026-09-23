<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../api';
import { useFilterStore } from '../../stores/filters';
import Badge from '../../components/Badge.vue';
import { errorMessage, RISK_CATEGORY, RISK_STATUS } from '../../utils/format';

const route = useRoute();
const router = useRouter();
const filters = useFilterStore();
const isEdit = computed(() => !!route.params.id);

const form = ref({
    risk_code: '',
    unit_id: '',
    site_id: '',
    title: '',
    description: '',
    category: 'operational',
    likelihood: 2,
    impact: 2,
    mitigation: '',
    contingency: '',
    pic_user_id: '',
    status: 'identified',
    due_date: '',
    year: new Date().getFullYear(),
    quarter: 1,
    evidence: '',
});

const computedScore = computed(() => form.value.likelihood * form.value.impact);
const computedLevel = computed(() => {
    const s = computedScore.value;
    if (s >= 15) return 'critical';
    if (s >= 9) return 'high';
    if (s >= 4) return 'medium';
    return 'low';
});

const levelBadge = computed(() => {
    const map = {
        critical: { label: 'Critical', color: 'bg-red-100 text-red-700' },
        high: { label: 'High', color: 'bg-orange-100 text-orange-700' },
        medium: { label: 'Medium', color: 'bg-amber-100 text-amber-700' },
        low: { label: 'Low', color: 'bg-emerald-100 text-emerald-700' },
    };
    return map[computedLevel.value] || { label: '', color: '' };
});

const loading = ref(false);
const saving = ref(false);
const error = ref('');

onMounted(async () => {
    await filters.load();
    if (isEdit.value) {
        loading.value = true;
        try {
            const { data } = await api.get(`/risk/${route.params.id}`);
            const r = data.data;
            form.value = {
                risk_code: r.risk_code,
                unit_id: r.unit_id || '',
                site_id: r.site_id || '',
                title: r.title,
                description: r.description || '',
                category: r.category,
                likelihood: r.likelihood,
                impact: r.impact,
                mitigation: r.mitigation || '',
                contingency: r.contingency || '',
                pic_user_id: r.pic_user_id || '',
                status: r.status,
                due_date: r.due_date ? r.due_date.slice(0, 10) : '',
                year: r.year,
                quarter: r.quarter || 1,
                evidence: r.evidence || '',
            };
        } catch (e) {
            error.value = errorMessage(e);
        } finally {
            loading.value = false;
        }
    }
});

async function save() {
    error.value = '';
    saving.value = true;
    try {
        if (isEdit.value) {
            await api.put(`/risk/${route.params.id}`, form.value);
        } else {
            await api.post('/risk', form.value);
        }
        router.push('/risk');
    } catch (e) {
        error.value = errorMessage(e, 'Failed to save risk');
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div v-if="loading" class="cmp-card cmp-card-pad text-center text-slate-400">Loading…</div>

    <div v-else class="cmp-card max-w-5xl">
        <div class="cmp-card-header">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-red-50 border border-red-100 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-800">{{ isEdit ? 'Edit Risk' : 'New Risk Register' }}</h2>
                    <p class="text-xs text-slate-400">{{ isEdit ? 'Update the risk details below.' : 'Register a new risk with matrix scoring.' }}</p>
                </div>
            </div>
        </div>

        <div v-if="error" class="m-5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 flex items-start gap-2.5">
            <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ error }}</span>
        </div>

        <form @submit.prevent="save" class="p-5 lg:p-6 space-y-5">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                <div class="col-span-2">
                    <label class="cmp-label">Risk Code *</label>
                    <input v-model="form.risk_code" required placeholder="RK-26-001" class="cmp-input" />
                </div>
                <div>
                    <label class="cmp-label">Year</label>
                    <input v-model.number="form.year" type="number" class="cmp-input" />
                </div>
                <div>
                    <label class="cmp-label">Quarter</label>
                    <select v-model="form.quarter" class="cmp-select">
                        <option v-for="q in 4" :key="q" :value="q">Q{{ q }}</option>
                    </select>
                </div>
                <div class="col-span-2 md:col-span-4">
                    <label class="cmp-label">Title *</label>
                    <input v-model="form.title" required class="cmp-input" />
                </div>
                <div class="col-span-2 md:col-span-4">
                    <label class="cmp-label">Description</label>
                    <textarea v-model="form.description" rows="2" class="cmp-textarea"></textarea>
                </div>
                <div>
                    <label class="cmp-label">Category *</label>
                    <select v-model="form.category" class="cmp-select">
                        <option v-for="(v, k) in RISK_CATEGORY" :key="k" :value="k">{{ v.label }}</option>
                    </select>
                </div>
                <div>
                    <label class="cmp-label">Unit</label>
                    <select v-model="form.unit_id" class="cmp-select">
                        <option value="">—</option>
                        <optgroup v-for="f in filters.options.units" :key="f.id" :label="f.name">
                            <option :value="f.id">{{ f.name }}</option>
                            <option v-for="child in f.children || []" :key="child.id" :value="child.id">— {{ child.name }}</option>
                        </optgroup>
                    </select>
                </div>
                <div>
                    <label class="cmp-label">Site</label>
                    <select v-model="form.site_id" class="cmp-select">
                        <option value="">—</option>
                        <option v-for="s in filters.options.sites" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="cmp-label">Status *</label>
                    <select v-model="form.status" class="cmp-select">
                        <option v-for="(v, k) in RISK_STATUS" :key="k" :value="k">{{ v.label }}</option>
                    </select>
                </div>

                <!-- Risk matrix -->
                <div class="col-span-2 md:col-span-4 rounded-xl border border-slate-200 bg-gradient-to-br from-slate-50 to-white p-5">
                    <div class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h8v8H3zM13 3h8v8h-8zM3 13h8v8H3zM13 13h8v8h-8z" /></svg>
                        Risk Matrix
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-[1fr_1fr_auto] gap-5 items-end">
                        <div>
                            <label class="cmp-label">Likelihood (1–5) *</label>
                            <input v-model.number="form.likelihood" type="number" min="1" max="5" required class="cmp-input" />
                        </div>
                        <div>
                            <label class="cmp-label">Impact (1–5) *</label>
                            <input v-model.number="form.impact" type="number" min="1" max="5" required class="cmp-input" />
                        </div>
                        <div class="flex items-center gap-4 py-2">
                            <div>
                                <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Score</div>
                                <div class="text-2xl font-extrabold text-slate-900 tabular-nums">{{ computedScore }}</div>
                            </div>
                            <Badge :label="levelBadge.label" :color="levelBadge.color" />
                        </div>
                    </div>
                </div>

                <div class="col-span-2 md:col-span-2">
                    <label class="cmp-label">Mitigation</label>
                    <textarea v-model="form.mitigation" rows="2" class="cmp-textarea"></textarea>
                </div>
                <div class="col-span-2 md:col-span-2">
                    <label class="cmp-label">Contingency</label>
                    <textarea v-model="form.contingency" rows="2" class="cmp-textarea"></textarea>
                </div>
                <div>
                    <label class="cmp-label">PIC</label>
                    <select v-model="form.pic_user_id" class="cmp-select">
                        <option value="">—</option>
                        <option v-for="u in filters.options.users" :key="u.id" :value="u.id">{{ u.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="cmp-label">Due Date</label>
                    <input v-model="form.due_date" type="date" class="cmp-input" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-200">
                <button type="button" @click="router.back()" class="cmp-btn cmp-btn-secondary">Cancel</button>
                <button :disabled="saving" class="cmp-btn cmp-btn-primary">
                    <svg v-if="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    {{ saving ? 'Saving…' : 'Save Risk' }}
                </button>
            </div>
        </form>
    </div>
</template>