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

const form = ref({ code: '', name: '', type: 'function', parent_id: '' });
const loading = ref(isEdit.value);
const saving = ref(false);
const error = ref('');

const units = computed(() => filters.options.units || []);
const parents = computed(() =>
    units.value.filter((u) => u.type === 'function' && String(u.id) !== String(form.value.parent_id))
);

async function load() {
    if (!isEdit.value) return;
    loading.value = true;
    try {
        const { data } = await api.get(`/master/units/${route.params.id}`);
        const u = data.data;
        form.value = { code: u.code, name: u.name, type: u.type, parent_id: u.parent_id || '' };
    } catch (e) {
        error.value = errorMessage(e, 'Failed to load unit');
    } finally {
        loading.value = false;
    }
}

async function save() {
    error.value = '';
    saving.value = true;
    try {
        if (isEdit.value) {
            await api.put(`/master/units/${route.params.id}`, form.value);
        } else {
            await api.post('/master/units', form.value);
        }
        router.push({ name: 'master.units' });
    } catch (e) {
        error.value = errorMessage(e, 'Failed to save unit');
    } finally {
        saving.value = false;
    }
}

onMounted(async () => {
    await filters.load();
    load();
});
</script>

<template>
    <div v-if="loading" class="cmp-card cmp-card-pad text-center text-slate-400">Loading…</div>

    <div v-else class="cmp-card max-w-2xl">
        <div class="cmp-card-header">
            <div>
                <h2 class="cmp-card-title">{{ isEdit ? 'Edit Unit' : 'New Unit' }}</h2>
                <p class="cmp-card-sub">{{ isEdit ? 'Update the unit details.' : 'Create a function or department used across modules.' }}</p>
            </div>
        </div>

        <div v-if="error" class="cmp-alert cmp-alert-danger mx-5">{{ error }}</div>

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
                <div>
                    <label class="cmp-label">Type *</label>
                    <select v-model="form.type" class="cmp-select">
                        <option value="function">Function</option>
                        <option value="department">Department</option>
                    </select>
                </div>
                <div>
                    <label class="cmp-label">Parent</label>
                    <select v-model="form.parent_id" class="cmp-select">
                        <option value="">—</option>
                        <option v-for="p in parents" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-200">
                <button type="button" @click="router.push({ name: 'master.units' })" class="cmp-btn cmp-btn-secondary">Cancel</button>
                <button :disabled="saving" class="cmp-btn cmp-btn-primary">
                    {{ saving ? 'Saving…' : 'Save Unit' }}
                </button>
            </div>
        </form>
    </div>
</template>
