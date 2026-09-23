export function formatCurrency(value, currency = 'IDR') {
    const n = Number(value || 0);

    if (currency === 'IDR') {
        return 'Rp ' + Math.round(n).toLocaleString('id-ID');
    }

    return n.toLocaleString('en-US', { style: 'currency', currency });
}

export function formatNumber(value) {
    return Number(value || 0).toLocaleString('id-ID');
}

export function formatDate(value) {
    if (!value) return '—';
    const d = new Date(value);

    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

export function formatDateTime(value) {
    if (!value) return '—';

    return new Date(value).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

export function today() {
    return new Date().toISOString().slice(0, 10);
}

export const PLAN_STATUS = {
    plan: { label: 'Plan', color: 'bg-blue-100 text-blue-700' },
    done: { label: 'Done', color: 'bg-emerald-100 text-emerald-700' },
    cancelled: { label: 'Cancelled', color: 'bg-slate-200 text-slate-600' },
};

export const RISK_STATUS = {
    identified: { label: 'Identified', color: 'bg-slate-100 text-slate-700' },
    assessed: { label: 'Assessed', color: 'bg-cyan-100 text-cyan-700' },
    mitigated: { label: 'Mitigated', color: 'bg-amber-100 text-amber-700' },
    monitored: { label: 'Monitored', color: 'bg-blue-100 text-blue-700' },
    closed: { label: 'Closed', color: 'bg-emerald-100 text-emerald-700' },
};

export const RISK_LEVEL = {
    critical: { label: 'Critical', color: 'bg-red-100 text-red-700' },
    high: { label: 'High', color: 'bg-orange-100 text-orange-700' },
    medium: { label: 'Medium', color: 'bg-amber-100 text-amber-700' },
    low: { label: 'Low', color: 'bg-emerald-100 text-emerald-700' },
};

export const RISK_CATEGORY = {
    operational: { label: 'Operational', color: 'bg-slate-100 text-slate-700' },
    strategic: { label: 'Strategic', color: 'bg-violet-100 text-violet-700' },
    financial: { label: 'Financial', color: 'bg-emerald-100 text-emerald-700' },
    compliance: { label: 'Compliance', color: 'bg-blue-100 text-blue-700' },
    environmental: { label: 'Environmental', color: 'bg-teal-100 text-teal-700' },
};

export const CONTRACT_STATUS = {
    active: { label: 'Active', color: 'bg-emerald-100 text-emerald-700' },
    expiring: { label: 'Expiring', color: 'bg-amber-100 text-amber-700' },
    expired: { label: 'Expired', color: 'bg-red-100 text-red-700' },
    completed: { label: 'Completed', color: 'bg-blue-100 text-blue-700' },
    terminated: { label: 'Terminated', color: 'bg-slate-200 text-slate-600' },
};

export const CONTRACT_TYPE = {
    ilj: { label: 'ILJ', color: 'bg-indigo-100 text-indigo-700' },
    maintenance: { label: 'Maintenance', color: 'bg-cyan-100 text-cyan-700' },
    general: { label: 'General', color: 'bg-slate-100 text-slate-700' },
};

export const MEETING_STATUS = {
    scheduled: { label: 'Scheduled', color: 'bg-blue-100 text-blue-700' },
    done: { label: 'Done', color: 'bg-emerald-100 text-emerald-700' },
    missed: { label: 'Missed', color: 'bg-red-100 text-red-700' },
};

export const COMPLIANCE_STATUS = {
    compliant: { label: 'Compliant', color: 'bg-emerald-100 text-emerald-700' },
    partial: { label: 'Partial', color: 'bg-amber-100 text-amber-700' },
    non_compliant: { label: 'Non Compliant', color: 'bg-red-100 text-red-700' },
    in_progress: { label: 'In Progress', color: 'bg-blue-100 text-blue-700' },
    na: { label: 'N/A', color: 'bg-slate-200 text-slate-600' },
};

export const COMPLIANCE_CATEGORY = {
    legal: { label: 'Legal', color: 'bg-violet-100 text-violet-700' },
    regulatory: { label: 'Regulatory', color: 'bg-blue-100 text-blue-700' },
    internal: { label: 'Internal', color: 'bg-slate-100 text-slate-700' },
    audit: { label: 'Audit', color: 'bg-amber-100 text-amber-700' },
};

export const TASK_STATUS = {
    pending: { label: 'Pending', color: 'bg-slate-200 text-slate-600' },
    in_progress: { label: 'In Progress', color: 'bg-blue-100 text-blue-700' },
    done: { label: 'Done', color: 'bg-emerald-100 text-emerald-700' },
};

export const MILESTONE_STATUS = {
    pending: { label: 'Pending', color: 'bg-slate-200 text-slate-600' },
    reached: { label: 'Reached', color: 'bg-emerald-100 text-emerald-700' },
    missed: { label: 'Missed', color: 'bg-red-100 text-red-700' },
};

export function assetUrl(path) {
    if (!path) return null;

    return '/storage/' + path.replace(/^public\//, '');
}

export function errorMessage(err, fallback = 'Something went wrong') {
    if (err?.response?.data?.message) return err.response.data.message;
    if (err?.response?.data?.errors) {
        const first = Object.values(err.response.data.errors)[0];

        return Array.isArray(first) ? first[0] : fallback;
    }

    return fallback;
}

export function errorMessage400(err, fallback = 'Something went wrong') {
    const r = err?.response?.data;

    if (r?.message && typeof r.message === 'string') return r.message;

    if (r?.errors) {
        const vals = Object.values(r.errors);
        const first = vals[0];

        return Array.isArray(first) ? first[0] : String(first ?? '');
    }

    return errorMessage(err, fallback);
}