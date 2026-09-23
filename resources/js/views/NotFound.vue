<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();
</script>

<template>
    <div class="flex flex-col items-center justify-center min-h-full pt-16">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white font-extrabold text-xl">404</div>
        <div class="mt-5 text-3xl font-extrabold text-slate-800 tracking-tight">Page not found</div>
        <p class="mt-2 text-sm text-slate-500">The page you are looking for does not exist or has been moved.</p>
        <button @click="router.push('/')" class="cmp-btn cmp-btn-primary mt-6">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10" /></svg>
            Back to Dashboard
        </button>
    </div>
</template>