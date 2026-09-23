<script setup>
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { ref } from 'vue';
import { errorMessage } from '../utils/format';

const router = useRouter();
const route = useRoute();
const auth = useAuthStore();

const form = ref({ email: '', password: '' });
const showPassword = ref(false);
const loading = ref(false);
const error = ref('');

const demoAccounts = [
    { email: 'admin@cmp.local', role: 'Admin' },
    { email: 'manager@cmp.local', role: 'Manager' },
    { email: 'editor@cmp.local', role: 'Editor' },
    { email: 'viewer@cmp.local', role: 'Viewer' },
];

const features = [
    { icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', title: 'Unified planning & realization', desc: 'Track annual planning targets across all units.' },
    { icon: 'M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', title: 'Risk in a glance', desc: 'Matrix-based risk scoring with mitigation tracking.' },
    { icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', title: 'Compliance assurance', desc: 'Monitor regulatory and internal compliance status.' },
];

async function submit() {
    error.value = '';
    loading.value = true;
    try {
        await auth.login(form.value.email, form.value.password);
        router.push(route.query.redirect || '/');
    } catch (e) {
        error.value = errorMessage(e, 'Login failed');
    } finally {
        loading.value = false;
    }
}

function fill(email) {
    form.value.email = email;
    form.value.password = 'password';
}
</script>

<template>
    <div class="min-h-screen grid lg:grid-cols-2 bg-white">
        <!-- Brand panel -->
        <div class="hidden lg:flex flex-col relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-900 to-indigo-950 text-white p-12">
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-brand-600/30 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-0 w-[28rem] h-[28rem] bg-brand-500/10 rounded-full blur-3xl"></div>

            <div class="relative flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white font-extrabold text-2xl shadow-xl shadow-brand-900/50">C</div>
                <div>
                    <div class="text-xl font-extrabold tracking-tight">CMP</div>
                    <div class="text-sm text-slate-400">Centralized Monitoring Platform</div>
                </div>
            </div>

            <div class="relative mt-auto space-y-8 pb-12">
                <div>
                    <h1 class="text-3xl font-extrabold leading-tight tracking-tight">
                        Corporate performance,<br />
                        <span class="text-brand-400">one dashboard.</span>
                    </h1>
                    <p class="mt-3 text-slate-300 max-w-md text-sm leading-relaxed">
                        Monitor planning, risk management, ongoing business, HSSE committee and compliance — consolidated in a single, secured platform.
                    </p>
                </div>

                <div class="space-y-4">
                    <div v-for="f in features" :key="f.title" class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-brand-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" :d="f.icon" /></svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold">{{ f.title }}</div>
                            <div class="text-xs text-slate-400">{{ f.desc }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <p class="relative text-[11px] text-slate-500">Laravel · Vue.js · MySQL · REST API · RBAC</p>
        </div>

        <!-- Form panel -->
        <div class="flex flex-col items-center justify-center p-6 lg:p-12 bg-slate-50">
            <div class="w-full max-w-md">
                <div class="lg:hidden flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white font-extrabold text-lg">C</div>
                    <div>
                        <div class="font-extrabold text-slate-900 tracking-tight">CMP</div>
                        <div class="text-xs text-slate-500">Centralized Monitoring Platform</div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-xl shadow-slate-200/60 p-8">
                    <h1 class="text-xl font-bold text-slate-900">Sign in to your account</h1>
                    <p class="text-sm text-slate-500 mt-1 mb-6">Enter your corporate credentials to continue.</p>

                    <div v-if="error" class="mb-5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 flex items-start gap-2.5">
                        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>{{ error }}</span>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="cmp-label">Email address</label>
                            <input v-model="form.email" type="email" required autocomplete="email" placeholder="you@company.com" class="cmp-input py-2.5" />
                        </div>
                        <div>
                            <label class="cmp-label">Password</label>
                            <div class="relative">
                                <input v-model="form.password" :type="showPassword ? 'text' : 'password'" required autocomplete="current-password" placeholder="••••••••" class="cmp-input py-2.5 pr-11" />
                                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                                    <svg v-if="!showPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                </button>
                            </div>
                        </div>
                        <button :disabled="loading" class="cmp-btn cmp-btn-primary w-full py-2.5">
                            <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            {{ loading ? 'Signing in…' : 'Sign In' }}
                        </button>
                    </form>

                    <div class="mt-7 pt-6 border-t border-slate-100">
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-2.5">Demo accounts <span class="font-medium normal-case text-slate-400">(password: password)</span></p>
                        <div class="flex flex-wrap gap-1.5">
                            <button v-for="d in demoAccounts" :key="d.email" @click="fill(d.email)"
                                class="text-xs px-2.5 py-1 rounded-full border border-slate-200 text-slate-600 hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 transition-colors font-medium">
                                {{ d.role }}: {{ d.email }}
                            </button>
                        </div>
                    </div>
                </div>

                <p class="text-center text-xs text-slate-400 mt-6">© {{ new Date().getFullYear() }} CMP · Centralized Monitoring Platform</p>
            </div>
        </div>
    </div>
</template>