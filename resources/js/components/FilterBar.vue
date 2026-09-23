<script setup>
import { useFilterStore } from '../stores/filters';

const filters = useFilterStore();

const years = () => filters.options.years;
const functions = () => filters.options.units || [];
</script>

<template>
    <div class="cmp-card bg-white/80 backdrop-blur">
        <div class="flex flex-wrap items-end gap-3 px-4 py-3.5">
            <div class="hidden lg:flex items-center gap-2 pr-2 self-stretch">
                <div class="w-9 h-9 rounded-lg bg-brand-50 border border-brand-100 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                </div>
                <span class="text-sm font-bold text-slate-700">Filters</span>
            </div>

            <label class="min-w-[110px]">
                <span class="cmp-label">Year</span>
                <select v-model="filters.selected.year" class="cmp-select">
                    <option v-for="y in years()" :key="y" :value="y">{{ y }}</option>
                </select>
            </label>

            <label class="min-w-[110px]">
                <span class="cmp-label">Quarter</span>
                <select v-model="filters.selected.quarter" class="cmp-select">
                    <option value="">All</option>
                    <option v-for="q in filters.options.quarters" :key="q" :value="q">Q{{ q }}</option>
                </select>
            </label>

            <label class="min-w-[190px] flex-1 lg:flex-none">
                <span class="cmp-label">Function / Department</span>
                <select v-model="filters.selected.unit_id" class="cmp-select">
                    <option value="">All</option>
                    <optgroup v-for="f in functions()" :key="f.id" :label="f.name">
                        <option :value="f.id">{{ f.name }}</option>
                        <option v-for="child in f.children || []" :key="child.id" :value="child.id">— {{ child.name }}</option>
                    </optgroup>
                </select>
            </label>

            <label class="min-w-[140px]">
                <span class="cmp-label">Zone</span>
                <select v-model="filters.selected.zone_id" class="cmp-select">
                    <option value="">All</option>
                    <option v-for="z in filters.options.zones" :key="z.id" :value="z.id">{{ z.name }}</option>
                </select>
            </label>

            <label class="min-w-[150px]">
                <span class="cmp-label">Site</span>
                <select v-model="filters.selected.site_id" class="cmp-select">
                    <option value="">All</option>
                    <option v-for="s in filters.options.sites" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
            </label>

            <label class="min-w-[120px] ml-auto">
                <span class="cmp-label">&nbsp;</span>
                <button
                    type="button"
                    @click="filters.setSelected({ year: filters.selected.year, quarter: '', unit_id: '', zone_id: '', site_id: '' })"
                    class="cmp-btn cmp-btn-secondary w-full"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    Reset
                </button>
            </label>
        </div>
    </div>
</template>