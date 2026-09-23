import { defineStore } from 'pinia';
import api from '../api';

export const useFilterStore = defineStore('filters', {
    state: () => ({
        options: {
            years: [],
            quarters: [1, 2, 3, 4],
            units: [],
            zones: [],
            sites: [],
            users: [],
            levels: [1, 2, 3],
        },
        loaded: false,
        selected: {
            year: new Date().getFullYear(),
            quarter: '',
            unit_id: '',
            zone_id: '',
            site_id: '',
        },
    }),
    getters: {
        queryParams(state) {
            const params = {};
            if (state.selected.year) params.year = state.selected.year;
            if (state.selected.quarter) params.quarter = state.selected.quarter;
            if (state.selected.unit_id) params.unit_id = state.selected.unit_id;
            if (state.selected.zone_id) params.zone_id = state.selected.zone_id;
            if (state.selected.site_id) params.site_id = state.selected.site_id;

            return params;
        },
    },
    actions: {
        async load() {
            if (this.loaded) return;
            try {
                const { data } = await api.get('/auth/filters');
                this.options = data;
                if (!this.options.years.includes(this.selected.year)) {
                    this.selected.year = this.options.years[0] || new Date().getFullYear();
                }
                this.loaded = true;
            } catch (e) {
                // ignore
            }
        },
        setSelected(partial) {
            this.selected = { ...this.selected, ...partial };
            if (partial.zone_id) this.selected.site_id = '';
        },
        reset() {
            this.options = { years: [], quarters: [1, 2, 3, 4], units: [], zones: [], sites: [], users: [], levels: [1, 2, 3] };
            this.selected = { year: new Date().getFullYear(), quarter: '', unit_id: '', zone_id: '', site_id: '' };
            this.loaded = false;
        },
    },
});