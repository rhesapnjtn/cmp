<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../api';
import { useFilterStore } from '../../stores/filters';
import { errorMessage, MEETING_STATUS } from '../../utils/format';

const route = useRoute();
const router = useRouter();
const filters = useFilterStore();
const isEdit = computed(() => !!route.params.id);

const form = ref({
    level: 1,
    title: '',
    unit_id: '',
    zone_id: '',
    site_id: '',
    quarter: 1,
    year: new Date().getFullYear(),
    target_count: 1,
    planned_date: '',
    realized_date: '',
    status: 'scheduled',
    notes: '',
});

const loading = ref(false);
const saving = ref(false);
const error = ref('');

onMounted(async () => {
    await filters.load();
    if (isEdit.value) {
        loading.value = true;
        try {
            const { data } = await api.get(`/hsse/${route.params.id}`);
            const m = data.data;
            form.value = {
                level: m.level,
                title: m.title,
                unit_id: m.unit_id || '',
                zone_id: m.zone_id || '',
                site_id: m.site_id || '',
                quarter: m.quarter,
                year: m.year,
                target_count: m.target_count,
                planned_date: m.planned_date ? m.planned_date.slice(0, 10) : '',
                realized_date: m.realized_date ? m.realized_date.slice(0, 10) : '',
                status: m.status,
                notes: m.notes || '',
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
            await api.put(`/hsse/${route.params.id}`, form.value);
        } else {
            await api.post('/hsse', form.value);
        }
        router.push('/hsse');
    } catch (e) {
        error.value = errorMessage(e, 'Failed to save meeting');
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div v-if="loading" class="cmp-card cmp-card-pad text-center text-slate-400">Loading…</div>

    <div v-else class="cmp-card max-w-3xl">
        <div class="cmp-card-header">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-violet-50 border border-violet-100 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-800">{{ isEdit ? 'Edit Meeting' : 'New HSSE Committee Meeting' }}</h2>
                    <p class="text-xs text-slate-400">{{ isEdit ? 'Update the meeting details below.' : 'Schedule a new HSSE committee meeting.' }}</p>
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
                    <label class="cmp-label">Level *</label>
                    <select v-model="form.level" class="cmp-select">
                        <option :value="1">Level 1</option>
                        <option :value="2">Level 2</option>
                        <option :value="3">Level 3</option>
                    </select>
                </div>
                <div>
                    <label class="cmp-label">Quarter *</label>
                    <select v-model="form.quarter" class="cmp-select">
                        <option v-for="q in 4" :key="q" :value="q">Q{{ q }}</option>
                    </select>
                </div>
                <div>
                    <label class="cmp-label">Year</label>
                    <input v-model.number="form.year" type="number" class="cmp-input" />
                </div>
                <div class="md:col-span-3">
                    <label class="cmp-label">Meeting Title *</label>
                    <input v-model="form.title" required class="cmp-input" />
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
                    <label class="cmp-label">Zone</label>
                    <select v-model="form.zone_id" class="cmp-select">
                        <option value="">—</option>
                        <option v-for="z in filters.options.zones" :key="z.id" :value="z.id">{{ z.name }}</option>
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
                    <label class="cmp-label">Target Count *</label>
                    <input v-model.number="form.target_count" type="number" min="1" required class="cmp-input" />
                </div>
                <div>
                    <label class="cmp-label">Status *</label>
                    <select v-model="form.status" class="cmp-select">
                        <option v-for="(v, k) in MEETING_STATUS" :key="k" :value="k">{{ v.label }}</option>
                    </select>
                </div>
                <div>
                    <label class="cmp-label">Planned Date</label>
                    <input v-model="form.planned_date" type="date" class="cmp-input" />
                </div>
                <div class="md:col-span-3">
                    <label class="cmp-label">Realized Date</label>
                    <input v-model="form.realized_date" type="date" class="cmp-input" />
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
                    {{ saving ? 'Saving…' : 'Save Meeting' }}
                </button>
            </div>
        </form>
    </div>
</template>