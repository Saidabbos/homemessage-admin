<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/UI/Pagination.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    ratings: Object,
    stats: Object,
    masters: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const type = ref(props.filters?.type || '');
const status = ref(props.filters?.status || '');
const masterId = ref(props.filters?.master_id || '');
const rating = ref(props.filters?.rating || '');

const applyFilters = () => {
    router.get(route('admin.ratings.index'), {
        search: search.value || undefined,
        type: type.value || undefined,
        status: status.value || undefined,
        master_id: masterId.value || undefined,
        rating: rating.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    search.value = '';
    type.value = '';
    status.value = '';
    masterId.value = '';
    rating.value = '';
    router.get(route('admin.ratings.index'));
};

let searchTimeout;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});

watch([type, status, masterId, rating], applyFilters);

const formatRating = (val) => val ? parseFloat(val).toFixed(1) : '-';

const deleteRating = (id) => {
    if (confirm('Haqiqatan ham bu bahoni o\'chirmoqchimisiz?')) {
        router.delete(route('admin.ratings.destroy', id));
    }
};
</script>

<template>
    <div class="p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Baholar</h1>
                <p class="text-gray-500 text-sm">Barcha baholarni ko'rish va boshqarish</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-500">
                <div class="text-2xl font-bold text-blue-600">{{ stats?.total || 0 }}</div>
                <div class="text-sm text-gray-500">Jami</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
                <div class="text-2xl font-bold text-green-600">{{ stats?.rated || 0 }}</div>
                <div class="text-sm text-gray-500">Baholangan</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-orange-500">
                <div class="text-2xl font-bold text-orange-600">{{ stats?.pending || 0 }}</div>
                <div class="text-sm text-gray-500">Kutilmoqda</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-yellow-500">
                <div class="text-2xl font-bold text-yellow-600">{{ formatRating(stats?.avg_master) }}</div>
                <div class="text-sm text-gray-500">Master o'rtacha</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-purple-500">
                <div class="text-2xl font-bold text-purple-600">{{ formatRating(stats?.avg_client) }}</div>
                <div class="text-sm text-gray-500">Mijoz o'rtacha</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
                <div>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Qidirish..." 
                        class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                    />
                </div>
                <div>
                    <select v-model="type" class="w-full px-3 py-2 border rounded-lg text-sm">
                        <option value="">Barcha turlar</option>
                        <option value="client_to_master">Mijoz → Master</option>
                        <option value="master_to_client">Master → Mijoz</option>
                    </select>
                </div>
                <div>
                    <select v-model="status" class="w-full px-3 py-2 border rounded-lg text-sm">
                        <option value="">Barcha holatlar</option>
                        <option value="rated">Baholangan</option>
                        <option value="pending">Kutilmoqda</option>
                    </select>
                </div>
                <div>
                    <select v-model="masterId" class="w-full px-3 py-2 border rounded-lg text-sm">
                        <option value="">Barcha masterlar</option>
                        <option v-for="m in masters" :key="m.id" :value="m.id">{{ m.name }}</option>
                    </select>
                </div>
                <div>
                    <select v-model="rating" class="w-full px-3 py-2 border rounded-lg text-sm">
                        <option value="">Barcha baholar</option>
                        <option value="5">5 ⭐</option>
                        <option value="4">4 ⭐</option>
                        <option value="3">3 ⭐</option>
                        <option value="2">2 ⭐</option>
                        <option value="1">1 ⭐</option>
                    </select>
                </div>
                <div>
                    <button @click="resetFilters" class="w-full px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">
                        Tozalash
                    </button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Buyurtma</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Turi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Master</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Mijoz</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Baho</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Izoh</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Sana</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Amallar</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="r in ratings.data" :key="r.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <Link v-if="r.order_id" :href="`/admin/orders/${r.order_id}`" class="text-blue-600 hover:underline font-mono text-sm">
                                {{ r.order_number }}
                            </Link>
                            <span v-else class="text-gray-400">-</span>
                        </td>
                        <td class="px-4 py-3">
                            <span 
                                class="text-xs px-2 py-1 rounded-full"
                                :class="r.type === 'client_to_master' ? 'bg-purple-100 text-purple-700' : 'bg-green-100 text-green-700'"
                            >
                                {{ r.type === 'client_to_master' ? 'Mijoz → Master' : 'Master → Mijoz' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center">
                                <img v-if="r.master_photo" :src="r.master_photo" class="w-8 h-8 rounded-full mr-2 object-cover" />
                                <div v-else class="w-8 h-8 rounded-full mr-2 bg-gray-200 flex items-center justify-center text-xs">
                                    {{ r.master_name?.charAt(0) }}
                                </div>
                                <span class="text-sm">{{ r.master_name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ r.customer_name || '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <div v-if="r.is_rated" class="flex items-center justify-center">
                                <span class="text-lg font-bold text-yellow-500 mr-1">{{ r.overall_rating }}</span>
                                <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </div>
                            <span v-else class="text-xs px-2 py-1 bg-orange-100 text-orange-600 rounded-full">Kutilmoqda</span>
                        </td>
                        <td class="px-4 py-3">
                            <p v-if="r.feedback" class="text-sm text-gray-600 truncate max-w-xs" :title="r.feedback">
                                "{{ r.feedback }}"
                            </p>
                            <span v-else class="text-gray-400">-</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ r.rated_at || r.created_at }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <Link :href="route('admin.ratings.show', r.id)" class="text-blue-500 hover:text-blue-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </Link>
                                <button @click="deleteRating(r.id)" class="text-red-500 hover:text-red-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!ratings.data?.length">
                        <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                            Baholar topilmadi
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="ratings.data?.length" class="px-4 py-3 border-t bg-gray-50">
                <Pagination :links="ratings.links" />
            </div>
        </div>
    </div>
</template>
