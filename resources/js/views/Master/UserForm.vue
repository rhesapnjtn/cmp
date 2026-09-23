<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../api';
import { useFilterStore } from '../../stores/filters';
import { errorMessage, errorMessage400 } from '../../utils/format';
import Badge from '../../components/Badge.vue';

const route = useRoute();
const router = useRouter();
const filters = useFilterStore();
const isEdit = computed(() => !!route.params.id);

const form = ref({
    name: '', email: '', password: '', job_title: '', unit_id: '',
    is_active: true, roles: [],
});
const roles = ref([]);
const loading = ref(isEdit.value);
const saving = ref(false);
const error = ref('');

const units = computed(() => filters.options.units || []);

async function load() {
    if (!isEdit.value) return;
    loading.value = true;
    try {
        const { data } = await api.get(`/master/users/${route.params.id}`);
        const u = data.data;
        form.value = {
            name: u.name,
            email: u.email,
            password: '',
            job_title: u.job_title || '',
            unit_id: u.unit_id || '',
            is_active: u.is_active,
            roles: (u.roles || []).map((r) => r.id),
        };
    } catch (e) {
        error.value = errorMessage(e, 'Failed to load user');
    } finally {
        loading.value = false;
    }
}

function toggleRole(id) {
    const i = form.value.roles.indexOf(id);
    if (i >= 0) form.value.roles.splice(i, 1);
    else form.value.roles.push(id);
}

async function save() {
    error.value = '';
    saving.value = true;
    try {
        const payload = { ...form.value };
        if (!isEdit.value || payload.password) {
            // password required only on create
        }
        if (!payload.password) delete payload.password;
        if (isEdit.value) {
            await api.put(`/master/users/${route.params.id}`, payload);
        } else {
            await api.post('/master/users', payload);
        }
        router.push({ name: 'master.users' });
    } catch (e) {
        error.value = errorMessage(e, 'Failed to save user');
    } finally {
        saving.value = false;
    }
}

onMounted(async () => {
    if (!filters.loaded) await filters.load();
    try {
        const { data } = await api.get('/master/roles');
        roles.value = data.data;
    } catch (e) {
        /* roles optional */
    }
    load();
});
</script>

<template>
    <div v-if="loading" class="cmp-card cmp-card-pad text-center text-slate-400">Loading…</div>

    <div v-else class="cmp-card max-w-2xl">
        <div class="cmp-card-header">
            <div>
                <h2 class="cmp-card-title">{{ isEdit ? 'Edit User' : 'New User' }}</h2>
                <p class="cmp-card-sub">{{ isEdit ? 'Update account details and roles.' : 'Create an account with roles and organization assignment.' }}</p>
            </div>
        </div>

        <div v-if="error" class="cmp-card-alert cmp-card-alert-danger mx-5">{{ error }}</div>

        <form @submit.prevent="save" class="p-5 lg:p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="cmp-label">Name *</label>
                    <input v-model="form.name" required class="cmp-input" />
                </div>
                <div>
                    <label class="cmp-label">Email *</label>
                    <input v-model="form.email" type="email" required class="cmp-input" />
                </div>
                <div>
                    <label class="cmp-label">{{ isEdit ? 'New Password (leave blank to keep)' : 'Password *' }}</label>
                    <input v-model="form.password" type="password" :required="!isEdit" minlength="6" class="cmp-input" />
                </div>
                <div>
                    <label class="cmp-label">Job Title</label>
                    <input v-model="form.job_title" class="cmp-input" />
                </div>
                <div class="md:col-span-2">
                    <label class="cmp-label">Unit</label>
                    <select v-model="form.unit_id" class="cmp-select">
                        <option value="">None</option>
                        <optgroup v-for="f in units" :key="f.id" :label="f.name">
                            <option :value="f.id">{{ f.name }}</option>
                            <option v-for="child in f.children || []" :key="child.id" :value="child.id">— {{ child.name }}</option>
                        </optgroup>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="cmp-label">Roles</label>
                    <div class="space-y-1.5">
                        <label v-for="r in roles" :key="r.id" class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" :checked="form.roles.includes(r.id)" @change="toggleRole(r.id)" class="w-4 h-4 rounded border-slate-300" />
                            <span class="text-slate-700 font-medium">{{ r.name }}</span>
                            <span class="text-xs text-slate-400">{{ r.code }}</span>
                        </label>
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input v-model="form.is_active" type="checkbox" class="w-4 h-4 rounded border-slate-300" />
                        <span class="text-slate-700 font-medium">Active account</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-200">
                <button type="button" @click="router.push({ name: 'master.users' })" class="cmp-btn cmp-btn-secondary">Cancel</button>
                <button :disabled="saving" class="cmp-btn cmp-btn-primary">
                    {{ saving ? 'Saving…' : 'Save' }}
                </button>
            </div>
        </form>
    </div>
</template>
