<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../api';
import { useFilterStore } from '../../stores/filters';
import { errorMessage } from '../../utils/format';

const route = useRoute();
const router = useRouter();
const filters = useFilterStore();
const isEdit = computed(() => !!route.params.id);

const form = ref({
    unit_id: '',
    title: '',
    description: '',
    category: '',
    target_date: '',
    status: 'plan',
    progress: 0,
    year: new Date().getFullYear(),
    quarter: 1,
    pic_user_id: '',
    evidence: '',
    notes: '',
});

const loading = ref(false);
const saving = ref(false);
const error = ref('');

const functionUnits = computed(() => filters.options.units || []);

onMounted(async () => {
    await filters.load();
    if (isEdit.value) {
        loading.value = true;
        try {
            const { data } = await api.get(`/planning/${route.params.id}`);
            const p = data.data;
            form.value = {
                unit_id: p.unit_id || '',
                title: p.title,
                description: p.description || '',
                category: p.category || '',
                target_date: p.target_date ? p.target_date.slice(0, 10) : '',
                status: p.status,
                progress: p.progress,
                year: p.year,
                quarter: p.quarter || 1,
                pic_user_id: p.pic_user_id || '',
                evidence: p.evidence || '',
                notes: p.notes || '',
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
        const payload = { ...form.value };
        if (isEdit.value) {
            await api.put(`/planning/${route.params.id}`, payload);
        } else {
            await api.post('/planning', payload);
        }
        router.push('/planning');
    } catch (e) {
        error.value = errorMessage(e, 'Failed to save plan');
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
                <div class="w-9 h-9 rounded-lg bg-brand-50 border border-brand-100 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-800">{{ isEdit ? 'Edit Plan' : 'New Monitoring Plan' }}</h2>
                    <p class="text-xs text-slate-400">{{ isEdit ? 'Update the plan details below.' : 'Create a new annual monitoring plan.' }}</p>
                </div>
            </div>
        </div>

        <div v-if="error" class="m-5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 flex items-start gap-2.5">
            <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ error }}</span>
        </div>

        <form @submit.prevent="save" class="p-5 lg:p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="cmp-label">Title *</label>
                    <input v-model="form.title" required class="cmp-input" />
                </div>
                <div class="md:col-span-2">
                    <label class="cmp-label">Description</label>
                    <textarea v-model="form.description" rows="3" class="cmp-textarea"></textarea>
                </div>
                <div>
                    <label class="cmp-label">Function / Department *</label>
                    <select v-model="form.unit_id" required class="cmp-select">
                        <option value="" disabled>Select unit</option>
                        <optgroup v-for="f in functionUnits" :key="f.id" :label="f.name">
                            <option :value="f.id">{{ f.name }}</option>
                            <option v-for="child in f.children || []" :key="child.id" :value="child.id">— {{ child.name }}</option>
                        </optgroup>
                    </select>
                </div>
                <div>
                    <label class="cmp-label">Category</label>
                    <input v-model="form.category" placeholder="e.g. Maintenance, Project" class="cmp-input" />
                </div>
                <div>
                    <label class="cmp-label">Status *</label>
                    <select v-model="form.status" class="cmp-select">
                        <option value="plan">Plan</option>
                        <option value="done">Done</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="cmp-label">Progress (%)</label>
                    <input v-model.number="form.progress" type="number" min="0" max="100" class="cmp-input" />
                </div>
                <div>
                    <label class="cmp-label">Target Date</label>
                    <input v-model="form.target_date" type="date" class="cmp-input" />
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
                <div class="md:col-span-2">
                    <label class="cmp-label">PIC</label>
                    <select v-model="form.pic_user_id" class="cmp-select">
                        <option value="">—</option>
                        <option v-for="u in filters.options.users" :key="u.id" :value="u.id">{{ u.name }}</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="cmp-label">Notes</label>
                    <textarea v-model="form.notes" rows="2" class="cmp-textarea"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-200">
                <button type="button" @click="router.back()" class="cmp-btn cmp-btn-secondary">Cancel</button>
                <button :disabled="saving" class="cmp-btn cmp-btn-primary">
                    <svg v-if="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    {{ saving ? 'Saving…' : 'Save Plan' }}
                </button>
            </div>
        </form>
    </div>
</template>