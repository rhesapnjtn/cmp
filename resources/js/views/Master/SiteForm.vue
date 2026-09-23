<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../api';
import { errorMessage } from '../../utils/format';

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);

const form = ref({ zone_id: '', code: '', name: '' });
const zones = ref([]);
const loading = ref(isEdit.value);
const saving = ref(false);
const error = ref('');

async function loadZones() {
    try {
        const { data } = await api.get('/master/zones');
        zones.value = data.data;
    } catch (e) {
        error.value = errorMessage(e, 'Failed to load zones');
    }
}

async function load() {
    if (!isEdit.value) return;
    loading.value = true;
    try {
        const { data } = await api.get(`/master/sites/${route.params.id}`);
        const s = data.data;
        form.value = { zone_id: s.zone_id || '', code: s.code, name: s.name };
    } catch (e) {
        error.value = errorMessage(e, 'Failed to load site');
    } finally {
        loading.value = false;
    }
}

async function save() {
    error.value = '';
    saving.value = true;
    try {
        if (isEdit.value) {
            await api.put(`/master/sites/${route.params.id}`, form.value);
        } else {
            await api.post('/master/sites', form.value);
        }
        router.push({ name: 'master.sites' });
    } catch (e) {
        error.value = errorMessage(e, 'Failed to save site');
    } finally {
        saving.value = false;
    }
}

onMounted(async () => {
    await loadZones();
    load();
});
</script>

<template>
    <div v-if="loading" class="cmp-card cmp-card-pad text-center text-slate-400">Loading…</div>

    <div v-else class="cmp-card max-w-2xl">
        <div class="cmp-card-header">
            <div>
                <h2 class="cmp-card-title">{{ isEdit ? 'Edit Site' : 'New Site' }}</h2>
                <p class="cmp-card-sub">{{ isEdit ? 'Update the site details.' : 'Create a site grouped under a zone.' }}</p>
            </div>
        </div>

        <div v-if="error" class="cmp-card-alert cmp-card-alert-danger mx-5">{{ error }}</div>

        <form @submit.prevent="save" class="p-5 lg:p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="cmp-label">Code *</label>
                    <input v-model="form.code" required class="cmp-input" />
                </div>
                <div>
                    <label class="cmp-label">Name *</label>
                    <input v-model="form.name" required class="cmp-input" />
                </div>
                <div class="md:col-span-2">
                    <label class="cmp-label">Zone *</label>
                    <select v-model="form.zone_id" required class="cmp-select">
                        <option value="" disabled>Select zone</option>
                        <option v-for="z in zones" :key="z.id" :value="z.id">{{ z.name }}</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-200">
                <button type="button" @click="router.push({ name: 'master.sites' })" class="cmp-btn cmp-btn-secondary">Cancel</button>
                <button :disabled="saving" class="cmp-btn cmp-btn-primary">
                    {{ saving ? 'Saving…' : 'Save Site' }}
                </button>
            </div>
        </form>
    </div>
</template>
