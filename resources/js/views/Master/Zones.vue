<script setup>
import { ref, onMounted } from "vue";
import api from "../../api";
import Badge from "../../components/Badge.vue";
import Pagination from "../../components/Pagination.vue";
import { errorMessage } from "../../utils/format";

const items = ref({ data: [], meta: {} });
const loading = ref(true);
const params = ref({ page: 1, search: "" });

async function load() {
  loading.value = true;
  try {
    const { data } = await api.get("/master/zones", { params: params.value });
    items.value = data;
  } finally {
    loading.value = false;
  }
}

function del(z) {
  if (!confirm(`Delete zone "${z.name}"?`)) return;
  api.delete(`/master/zones/${z.id}`).then(load).catch((e) => alert(errorMessage(e)));
}

onMounted(load);
</script>

<template>
  <div class="cmp-card">
    <div class="cmp-card-header">
      <div>
        <h2 class="cmp-card-title">Zones</h2>
        <p class="cmp-card-sub">Geographical zones grouping your monitoring locations.</p>
      </div>
      <div class="ml-auto flex items-center gap-2">
        <input v-model="params.search" placeholder="Searchâ€¦" class="cmp-input w-52" />
        <RouterLink :to="{ name: 'master.zones.create' }" class="cmp-btn cmp-btn-primary cmp-btn-sm">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M12 4v16m8-8H4" /></svg>
          New Zone
        </RouterLink>
      </div>
    </div>
    <div class="overflow-x-auto">
      <table class="cmp-table">
        <thead>
          <tr>
            <th>Code</th>
            <th>Name</th>
            <th>Type</th>
            <th>Children</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="z in items.data" :key="z.id">
            <td class="font-mono text-xs font-semibold text-slate-500">{{ z.code }}</td>
            <td class="font-semibold text-slate-800">{{ z.name }}</td>
            <td><Badge label="Function" color="bg-indigo-100 text-indigo-700" /></td>
            <td class="text-slate-600 tabular-nums">{{ z.children_count }}</td>
            <td>
              <div class="cmp-actions">
                <RouterLink :to="{ name: 'master.zones.edit', params: { id: z.id } }" class="cmp-btn cmp-btn-secondary cmp-btn-sm">Edit</RouterLink>
                <button @click="del(z)" class="cmp-btn cmp-btn-ghost cmp-btn-sm text-red-500 hover:bg-red-50 hover:text-red-700">Delete</button>
              </div>
            </td>
          </tr>
          <tr v-if="!items.data.length"><td colspan="5" class="cmp-empty">No zones found</td></tr>
        </tbody>
      </table>
    </div>
    <Pagination :page="items.current_page || 1" :last-page="items.last_page || 1" :total="items.total || 0" @page="params.page = $event" />
  </div>
</template>
