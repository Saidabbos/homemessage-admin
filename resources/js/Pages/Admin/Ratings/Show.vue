<script setup>
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    rating: Object,
});

const deleteRating = () => {
    if (confirm('Haqiqatan ham bu bahoni o\'chirmoqchimisiz?')) {
        router.delete(route('admin.ratings.destroy', props.rating.id));
    }
};
</script>

<template>
    <div class="p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                <Link href="/admin/ratings" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Baho tafsilotlari</h1>
                    <p class="text-gray-500 text-sm">{{ rating.order?.order_number || 'Buyurtma yo\'q' }}</p>
                </div>
            </div>
            <button @click="deleteRating" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                O'chirish
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Rating Info -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Baho ma'lumotlari</h2>

                <!-- Type Badge -->
                <div class="mb-4">
                    <span 
                        class="text-sm px-3 py-1 rounded-full"
                        :class="rating.type === 'client_to_master' ? 'bg-purple-100 text-purple-700' : 'bg-green-100 text-green-700'"
                    >
                        {{ rating.type === 'client_to_master' ? 'Mijoz → Master' : 'Master → Mijoz' }}
                    </span>
                </div>

                <!-- Overall Rating -->
                <div class="text-center py-6 bg-gray-50 rounded-xl mb-4">
                    <div v-if="rating.overall_rating" class="text-5xl font-bold text-yellow-500 mb-2">
                        {{ rating.overall_rating }}
                    </div>
                    <div v-else class="text-2xl text-gray-400 mb-2">Kutilmoqda</div>
                    <div class="flex justify-center">
                        <svg v-for="i in 5" :key="i" class="w-8 h-8" :class="i <= rating.overall_rating ? 'text-yellow-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                </div>

                <!-- Detailed Ratings -->
                <div v-if="rating.overall_rating" class="space-y-3">
                    <div v-if="rating.punctuality_rating" class="flex items-center justify-between">
                        <span class="text-gray-600">Vaqtida kelish</span>
                        <div class="flex">
                            <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= rating.punctuality_rating ? 'text-yellow-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div v-if="rating.professionalism_rating" class="flex items-center justify-between">
                        <span class="text-gray-600">Professionallik</span>
                        <div class="flex">
                            <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= rating.professionalism_rating ? 'text-yellow-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div v-if="rating.cleanliness_rating" class="flex items-center justify-between">
                        <span class="text-gray-600">Tozalik</span>
                        <div class="flex">
                            <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= rating.cleanliness_rating ? 'text-yellow-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Feedback -->
                <div v-if="rating.feedback" class="mt-6 p-4 bg-blue-50 rounded-xl">
                    <h3 class="text-sm font-semibold text-blue-800 mb-2">Izoh</h3>
                    <p class="text-blue-700">"{{ rating.feedback }}"</p>
                </div>

                <!-- Timestamps -->
                <div class="mt-6 pt-4 border-t text-sm text-gray-500 space-y-1">
                    <div>Yaratilgan: {{ rating.created_at }}</div>
                    <div v-if="rating.rated_at">Baholangan: {{ rating.rated_at }}</div>
                </div>
            </div>

            <!-- Participants -->
            <div class="space-y-6">
                <!-- Master Card -->
                <div v-if="rating.master" class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Master</h2>
                    <div class="flex items-center">
                        <img v-if="rating.master.photo" :src="rating.master.photo" class="w-16 h-16 rounded-full object-cover" />
                        <div v-else class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center text-xl text-gray-500">
                            {{ rating.master.name?.charAt(0) }}
                        </div>
                        <div class="ml-4">
                            <h3 class="font-semibold text-gray-800">{{ rating.master.name }}</h3>
                            <div v-if="rating.master.rating" class="flex items-center text-sm text-gray-500">
                                <svg class="w-4 h-4 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                {{ parseFloat(rating.master.rating).toFixed(1) }} ({{ rating.master.rating_count }} ta baho)
                            </div>
                        </div>
                    </div>
                    <Link :href="`/admin/masters/${rating.master.id}`" class="block mt-4 text-center py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                        Masterni ko'rish
                    </Link>
                </div>

                <!-- Customer Card -->
                <div v-if="rating.customer" class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Mijoz</h2>
                    <div class="flex items-center">
                        <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center text-xl text-blue-600">
                            {{ rating.customer.name?.charAt(0) }}
                        </div>
                        <div class="ml-4">
                            <h3 class="font-semibold text-gray-800">{{ rating.customer.name }}</h3>
                            <p class="text-sm text-gray-500">{{ rating.customer.phone }}</p>
                        </div>
                    </div>
                    <Link :href="`/admin/customers/${rating.customer.id}`" class="block mt-4 text-center py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                        Mijozni ko'rish
                    </Link>
                </div>

                <!-- Order Card -->
                <div v-if="rating.order" class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Buyurtma</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Raqam:</span>
                            <span class="font-mono">{{ rating.order.order_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Xizmat:</span>
                            <span>{{ rating.order.service }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Sana:</span>
                            <span>{{ rating.order.booking_date }}</span>
                        </div>
                    </div>
                    <Link :href="`/admin/orders/${rating.order.id}`" class="block mt-4 text-center py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 text-sm">
                        Buyurtmani ko'rish
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
