<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../api';
import { useFilterStore } from '../../stores/filters';
import { errorMessage, CONTRACT_STATUS, CONTRACT_TYPE } from '../../utils/format';

const route = useRoute();
const router = useRouter();
const filters = useFilterStore();
const isEdit = computed(() => !!route.params.id);

const form = ref({
    contract_number: '',
    name: '',
    type: 'ilj',
    unit_id: '',
    site_id: '',
    vendor: '',
    contract_value: 0,
    used_value: 0,
    currency: 'IDR',
    start_date: '',
    end_date: '',
    status: 'active',
    maintenance_type: '',
    maintenance_progress: 0,
    remaining_work: '',
    work_progress: 0,
    pic_user_id: '',
    year: new Date().getFullYear(),
    notes: '',
});

const loading = ref(false);
const saving = ref(false);
const error = ref('');

const usedPercent = computed(() => (Number(form.value.contract_value) ? (Number(form.value.used_value) / Number(form.value.contract_value)) * 100 : 0));

onMounted(async () => {
    await filters.load();
    if (isEdit.value) {
        loading.value = true;
        try {
            const { data } = await api.get(`/ogb/${route.params.id}`);
            const c = data.data;
            form.value = {
                contract_number: c.contract_number,
                name: c.name,
                type: c.type,
                unit_id: c.unit_id || '',
                site_id: c.site_id || '',
                vendor: c.vendor || '',
                contract_value: c.contract_value,
                used_value: c.used_value,
                currency: c.currency,
                start_date: c.start_date ? c.start_date.slice(0, 10) : '',
                end_date: c.end_date ? c.end_date.slice(0, 10) : '',
                status: c.status,
                maintenance_type: c.maintenance_type || '',
                maintenance_progress: c.maintenance_progress,
                remaining_work: c.remaining_work || '',
                work_progress: c.work_progress,
                pic_user_id: c.pic_user_id || '',
                year: c.year,
                notes: c.notes || '',
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
            await api.put(`/ogb/${route.params.id}`, form.value);
        } else {
            await api.post('/ogb', form.value);
        }
        router.push('/ogb');
    } catch (e) {
        error.value = errorMessage(e, 'Failed to save contract');
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div v-if="loading" class="cmp-card cmp-card-pad text-center text-slate-400">Loading…</div>

    <div v-else class="cmp-card max-w-4xl">
        <div class="cmp-card-header">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-9l-5-6v15zm0 0v-6h5M3 20h5V9l5 6m0 0v5" /></svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-800">{{ isEdit ? 'Edit Contract' : 'New OGB Contract' }}</h2>
                    <p class="text-xs text-slate-400">{{ isEdit ? 'Update the contract details below.' : 'Register a new ongoing business contract.' }}</p>
                </div>
            </div>
        </div>

        <div v-if="error" class="m-5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 flex items-start gap-2.5">
            <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ error }}</span>
        </div>

        <form @submit.prevent="save" class="p-5 lg:p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="cmp-label">Contract Number *</label>
                    <input v-model="form.contract_number" required placeholder="ILJ-2026-001" class="cmp-input" />
                </div>
                <div>
                    <label class="cmp-label">Type *</label>
                    <select v-model="form.type" class="cmp-select">
                        <option v-for="(v, k) in CONTRACT_TYPE" :key="k" :value="k">{{ v.label }}</option>
                    </select>
                </div>
                <div>
                    <label class="cmp-label">Status *</label>
                    <select v-model="form.status" class="cmp-select">
                        <option v-for="(v, k) in CONTRACT_STATUS" :key="k" :value="k">{{ v.label }}</option>
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="cmp-label">Contract Name *</label>
                    <input v-model="form.name" required class="cmp-input" />
                </div>
                <div>
                    <label class="cmp-label">Vendor</label>
                    <input v-model="form.vendor" class="cmp-input" />
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
                    <label class="cmp-label">Contract Value *</label>
                    <input v-model.number="form.contract_value" type="number" min="0" step="0.01" required class="cmp-input" />
                </div>
                <div>
                    <label class="cmp-label">Used Value *</label>
                    <input v-model.number="form.used_value" type="number" min="0" step="0.01" required class="cmp-input" />
                </div>
                <div>
                    <label class="cmp-label">Currency</label>
                    <select v-model="form.currency" class="cmp-select">
                        <option value="IDR">IDR</option>
                        <option value="USD">USD</option>
                    </select>
                </div>
                <div class="md:col-span-3">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="cmp-label mb-0">Budget Utilization</span>
                        <span class="text-xs font-bold text-slate-600 tabular-nums">{{ Math.round(usedPercent) }}%</span>
                    </div>
                    <div class="w-full rounded-full bg-slate-100 overflow-hidden ring-1 ring-inset ring-slate-200/60 h-2.5">
                        <div class="h-full rounded-full bg-emerald-500 transition-all duration-500" :style="{ width: Math.min(usedPercent, 100) + '%' }"></div>
                    </div>
                </div>
                <div>
                    <label class="cmp-label">Start Date</label>
                    <input v-model="form.start_date" type="date" class="cmp-input" />
                </div>
                <div>
                    <label class="cmp-label">End Date</label>
                    <input v-model="form.end_date" type="date" class="cmp-input" />
                </div>
                <div>
                    <label class="cmp-label">Year</label>
                    <input v-model.number="form.year" type="number" class="cmp-input" />
                </div>

                <div>
                    <label class="cmp-label">Maintenance Type</label>
                    <input v-model="form.maintenance_type" placeholder="e.g. Preventive & Corrective" class="cmp-input" />
                </div>
                <div>
                    <label class="cmp-label">Maintenance Progress (%)</label>
                    <input v-model.number="form.maintenance_progress" type="number" min="0" max="100" class="cmp-input" />
                </div>
                <div>
                    <label class="cmp-label">Work Progress (%)</label>
                    <input v-model.number="form.work_progress" type="number" min="0" max="100" class="cmp-input" />
                </div>
                <div class="md:col-span-2">
                    <label class="cmp-label">Remaining Work</label>
                    <textarea v-model="form.remaining_work" rows="2" class="cmp-textarea"></textarea>
                </div>
                <div>
                    <label class="cmp-label">PIC</label>
                    <select v-model="form.pic_user_id" class="cmp-select">
                        <option value="">—</option>
                        <option v-for="u in filters.options.users" :key="u.id" :value="u.id">{{ u.name }}</option>
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="cmp-label">Notes</label>
                    <textarea v-model="form.notes" rows="2" class="cmp-textarea"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-200">
                <button type="button" @click="router.back()" class="cmp-btn cmp-btn-secondary">Cancel</button>
                <button :disabled="saving" class="cmp-btn cmp-btn-primary">
                    <svg v-if="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    {{ saving ? 'Saving…' : 'Save Contract' }}
                </button>
            </div>
        </form>
    </div>
</template>