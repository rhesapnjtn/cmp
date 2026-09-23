import { defineStore } from 'pinia';
import api from '../api';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('cmp_token') || '',
        user: JSON.parse(localStorage.getItem('cmp_user') || 'null'),
    }),
    getters: {
        isAuthenticated: (s) => !!s.token,
        permissions: (s) => s.user?.permissions || [],
        roleCodes: (s) => (s.user?.roles || []).map((r) => r.code),
        isAdmin: (s) => s.user?.roles?.some((r) => r.code === 'admin') || false,
    },
    actions: {
        setAuth(token, user) {
            this.token = token;
            this.user = user;
            localStorage.setItem('cmp_token', token);
            localStorage.setItem('cmp_user', JSON.stringify(user));
        },
        async login(email, password) {
            const { data } = await api.post('/auth/login', { email, password });
            this.setAuth(data.token, data.user);

            return data;
        },
        async fetchMe() {
            const { data } = await api.get('/auth/me');
            this.user = data.user;
            localStorage.setItem('cmp_user', JSON.stringify(data.user));

            return data.user;
        },
        async logout() {
            try {
                await api.post('/auth/logout');
            } catch (e) {
                // ignore
            }
            this.token = '';
            this.user = null;
            localStorage.removeItem('cmp_token');
            localStorage.removeItem('cmp_user');
        },
        can(code) {
            if (this.isAdmin) return true;

            return this.permissions.includes(code);
        },
    },
});