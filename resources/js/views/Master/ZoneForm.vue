<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../api';
import { errorMessage } from '../../utils/format';

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);

const form = ref({ code: '', name: '' });
const loading = ref(isEdit.value);
const saving = ref(false);
const error = ref('');

async function load() {
    if (!isEdit.value) return;
    loading.value = true;
    try {
        const { data } = await api.get(`/master/zones/${route.params.id}`);
        const z = data.data;
        form.value = { code: z.code, name: z.name };
    } catch (e) {
        error.value = errorMessage(e, 'Failed to load zone');
    } finally {
        loading.value = false;
    }
}

async function save() {
    error.value = '';
    saving.value = true;
    try {
        if (isEdit.value) {
            await api.put(`/master/zones/${route.params.id}`, form.value);
        } else {
            await api.post('/master/zones', form.value);
        }
        router.push({ name: 'master.zones' });
    } catch (e) {
        error.value = errorMessage(e, 'Failed to save zone');
    } finally {
        saving.value = false;
    }
}

onMounted(load);
</script>

<template>
    <div v-if="loading" class="cmp-card cmp-card-pad text-center text-slate-400">Loading…</div>

    <div v-else class="cmp-card max-w-2xl">
        <div class="cmp-card-header">
            <div>
                <h2 class="cmp-card-title">{{ isEdit ? 'Edit Zone' : 'New Zone' }}</h2>
                <p class="cmp-card-sub">{{ isEdit ? 'Update the zone details.' : 'Create a geographical zone.' }}</p>
            </div>
        </div>

        <div v-if="error" class="m-5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">{{ error }}</div>

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
            </div>

            <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-200">
                <button type="button" @click="router.push({ name: 'master.zones' })" class="cmp-btn cmp-btn-secondary">Cancel</button>
                <button :disabled="saving" class="cmp-btn cmp-btn-primary">
                    {{ saving ? 'Saving…' : 'Save Zone' }}
                </button>
            </div>
        </form>
    </div>
</template>
