<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../api';
import { useFilterStore } from '../../stores/filters';
import { useAuthStore } from '../../stores/auth';
import Badge from '../../components/Badge.vue';
import { errorMessage, COMPLIANCE_STATUS, COMPLIANCE_CATEGORY, assetUrl, formatDateTime } from '../../utils/format';

const route = useRoute();
const router = useRouter();
const filters = useFilterStore();
const auth = useAuthStore();
const isEdit = computed(() => !!route.params.id);

const form = ref({
    requirement: '',
    regulation: '',
    category: 'regulatory',
    unit_id: '',
    pic_user_id: '',
    deadline: '',
    status: 'in_progress',
    progress: 0,
    year: new Date().getFullYear(),
    quarter: 1,
    notes: '',
});

const evidenceList = ref([]);
const file = ref(null);
const uploading = ref(false);
const loading = ref(false);
const saving = ref(false);
const error = ref('');

onMounted(async () => {
    await filters.load();
    if (isEdit.value) {
        loading.value = true;
        try {
            const { data } = await api.get(`/compliance/${route.params.id}`);
            const c = data.data;
            form.value = {
                requirement: c.requirement,
                regulation: c.regulation || '',
                category: c.category,
                unit_id: c.unit_id || '',
                pic_user_id: c.pic_user_id || '',
                deadline: c.deadline ? c.deadline.slice(0, 10) : '',
                status: c.status,
                progress: c.progress,
                year: c.year,
                quarter: c.quarter || 1,
                notes: c.notes || '',
            };
            evidenceList.value = c.evidence || [];
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
            await api.put(`/compliance/${route.params.id}`, form.value);
        } else {
            await api.post('/compliance', form.value);
        }
        router.push('/compliance');
    } catch (e) {
        error.value = errorMessage(e, 'Failed to save compliance item');
    } finally {
        saving.value = false;
    }
}

async function uploadEvidence() {
    if (!file.value || !isEdit.value) return;
    uploading.value = true;
    error.value = '';
    try {
        const fd = new FormData();
        fd.append('evidence', file.value);
        const { data } = await api.post(`/compliance/${route.params.id}/evidence`, fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        evidenceList.value.unshift(data.data);
        file.value = null;
    } catch (e) {
        error.value = errorMessage(e, 'Upload failed');
    } finally {
        uploading.value = false;
    }
}

function removeEvidence(ev) {
    if (!confirm('Delete this evidence?')) return;
    api.delete(`/compliance/${route.params.id}/evidence/${ev.id}`).then(() => {
        evidenceList.value = evidenceList.value.filter((e) => e.id !== ev.id);
    });
}
</script>

<template>
    <div v-if="loading" class="cmp-card cmp-card-pad text-center text-slate-400">Loading…</div>

    <div v-else class="cmp-card max-w-3xl">
        <div class="cmp-card-header">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-800">{{ isEdit ? 'Edit Compliance' : 'New Compliance Item' }}</h2>
                    <p class="text-xs text-slate-400">{{ isEdit ? 'Update the compliance details below.' : 'Register a new compliance requirement.' }}</p>
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
                    <label class="cmp-label">Requirement *</label>
                    <input v-model="form.requirement" required class="cmp-input" />
                </div>
                <div>
                    <label class="cmp-label">Regulation / Source</label>
                    <input v-model="form.regulation" class="cmp-input" />
                </div>
                <div>
                    <label class="cmp-label">Category *</label>
                    <select v-model="form.category" class="cmp-select">
                        <option v-for="(v, k) in COMPLIANCE_CATEGORY" :key="k" :value="k">{{ v.label }}</option>
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
                    <label class="cmp-label">PIC</label>
                    <select v-model="form.pic_user_id" class="cmp-select">
                        <option value="">—</option>
                        <option v-for="u in filters.options.users" :key="u.id" :value="u.id">{{ u.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="cmp-label">Deadline</label>
                    <input v-model="form.deadline" type="date" class="cmp-input" />
                </div>
                <div>
                    <label class="cmp-label">Status *</label>
                    <select v-model="form.status" class="cmp-select">
                        <option v-for="(v, k) in COMPLIANCE_STATUS" :key="k" :value="k">{{ v.label }}</option>
                    </select>
                </div>
                <div>
                    <label class="cmp-label">Progress (%)</label>
                    <input v-model.number="form.progress" type="number" min="0" max="100" class="cmp-input" />
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
                    <label class="cmp-label">Notes</label>
                    <textarea v-model="form.notes" rows="2" class="cmp-textarea"></textarea>
                </div>
            </div>

            <div v-if="isEdit" class="rounded-xl border border-slate-200 p-4">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                    <h3 class="text-sm font-bold text-slate-700">Supporting Evidence</h3>
                </div>
                <div v-if="evidenceList.length" class="space-y-2 mb-3">
                    <div v-for="ev in evidenceList" :key="ev.id" class="flex items-center gap-3 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5">
                        <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <a :href="assetUrl(ev.file_path)" target="_blank" class="flex-1 min-w-0 text-sm font-medium text-brand-600 hover:underline truncate">{{ ev.file_name }}</a>
                        <span class="text-xs text-slate-400 whitespace-nowrap">{{ formatDateTime(ev.created_at) }}</span>
                        <button v-if="auth.can('compliance.manage')" @click="removeEvidence(ev)" class="text-red-500 hover:text-red-700 text-xs font-semibold shrink-0">Remove</button>
                    </div>
                </div>
                <p v-else class="text-sm text-slate-400 mb-3">No evidence uploaded yet.</p>
                <div class="flex items-center gap-3">
                    <input type="file" @change="file = $event.target.files[0]" class="text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-brand-700 file:text-sm file:font-semibold hover:file:bg-brand-100" />
                    <button type="button" :disabled="uploading || !file" @click="uploadEvidence" class="cmp-btn cmp-btn-secondary cmp-btn-sm">
                        {{ uploading ? 'Uploading…' : 'Upload' }}
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-200">
                <button type="button" @click="router.back()" class="cmp-btn cmp-btn-secondary">Cancel</button>
                <button :disabled="saving" class="cmp-btn cmp-btn-primary">
                    <svg v-if="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    {{ saving ? 'Saving…' : 'Save Compliance' }}
                </button>
            </div>
        </form>
    </div>
</template>