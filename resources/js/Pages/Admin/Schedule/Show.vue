<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps({
  master: Object,
  startDate: String,
  calendar: Array,
  slots: Array,
});

const selectedSlot = ref(null);
const selectedDate = ref(null);
const blockReason = ref('');
const showBlockModal = ref(false);

const navigateWeek = (direction) => {
  const current = new Date(props.startDate);
  current.setDate(current.getDate() + (direction * 7));
  router.get(route('admin.schedule.show', props.master.id), {
    start_date: current.toISOString().split('T')[0],
  }, {
    preserveState: true,
  });
};

const getStatusClass = (status) => {
  const classes = {
    'FREE': 'bg-[#d4edda] text-[#155724] border-[#c3e6cb]',
    'PENDING': 'bg-[#fff3cd] text-[#856404] border-[#ffeeba]',
    'RESERVED': 'bg-[#cce5ff] text-[#004085] border-[#b8daff]',
    'BLOCKED': 'bg-[#f8d7da] text-[#721c24] border-[#f5c6cb]',
  };
  return classes[status] || 'bg-gray-100 text-gray-600';
};

const openBlockModal = (slot, date) => {
  selectedSlot.value = slot;
  selectedDate.value = date;
  blockReason.value = '';
  showBlockModal.value = true;
};

const blockSlot = () => {
  router.post(route('admin.schedule.block-slot', props.master.id), {
    slot_id: selectedSlot.value.id,
    date: selectedDate.value,
    reason: blockReason.value,
  }, {
    preserveState: true,
    onSuccess: () => {
      showBlockModal.value = false;
      selectedSlot.value = null;
      selectedDate.value = null;
    },
  });
};

const unblockSlot = (slot) => {
  if (confirm(t('schedule.confirmUnblock'))) {
    router.post(route('admin.schedule.unblock-slot', props.master.id), {
      booking_id: slot.booking_id,
    }, {
      preserveState: true,
    });
  }
};

const blockDay = (date) => {
  const reason = prompt(t('schedule.blockReasonPrompt'));
  if (reason !== null) {
    router.post(route('admin.schedule.block-day', props.master.id), {
      date: date,
      reason: reason,
    }, {
      preserveState: true,
    });
  }
};

const unblockDay = (date) => {
  if (confirm(t('schedule.confirmUnblockDay'))) {
    router.post(route('admin.schedule.unblock-day', props.master.id), {
      date: date,
    }, {
      preserveState: true,
    });
  }
};
</script>

<template>
  <div>
    <!-- Content Header -->
    <div class="mb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-[#1f2d3d]">{{ t('schedule.calendar') }}</h1>
          <p class="text-sm text-[#6c757d] mt-1">{{ master.first_name }} {{ master.last_name }}</p>
        </div>
        <nav class="mt-2 sm:mt-0">
          <ol class="flex items-center text-sm">
            <li><Link href="/admin/dashboard" class="text-[#007bff]">{{ t('common.home') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li><Link :href="route('admin.schedule.index')" class="text-[#007bff]">{{ t('schedule.title') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li class="text-[#6c757d]">{{ master.first_name }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Week Navigation -->
    <div class="bg-white rounded shadow-sm mb-4">
      <div class="px-4 py-3 flex items-center justify-between">
        <button
          @click="navigateWeek(-1)"
          class="flex items-center px-3 py-2 text-sm text-[#007bff] hover:bg-[#e7f3ff] rounded transition"
        >
          <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          {{ t('schedule.prevWeek') }}
        </button>
        <span class="font-semibold text-[#1f2d3d]">
          {{ calendar[0]?.date }} — {{ calendar[6]?.date }}
        </span>
        <button
          @click="navigateWeek(1)"
          class="flex items-center px-3 py-2 text-sm text-[#007bff] hover:bg-[#e7f3ff] rounded transition"
        >
          {{ t('schedule.nextWeek') }}
          <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Legend -->
    <div class="bg-white rounded shadow-sm mb-4 p-4">
      <div class="flex flex-wrap gap-4 text-sm">
        <div class="flex items-center">
          <span class="w-4 h-4 rounded bg-[#d4edda] border border-[#c3e6cb] mr-2"></span>
          {{ t('schedule.statusFree') }}
        </div>
        <div class="flex items-center">
          <span class="w-4 h-4 rounded bg-[#fff3cd] border border-[#ffeeba] mr-2"></span>
          {{ t('schedule.statusPending') }}
        </div>
        <div class="flex items-center">
          <span class="w-4 h-4 rounded bg-[#cce5ff] border border-[#b8daff] mr-2"></span>
          {{ t('schedule.statusReserved') }}
        </div>
        <div class="flex items-center">
          <span class="w-4 h-4 rounded bg-[#f8d7da] border border-[#f5c6cb] mr-2"></span>
          {{ t('schedule.statusBlocked') }}
        </div>
      </div>
    </div>

    <!-- Calendar Grid -->
    <div class="bg-white rounded shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-[#f8f9fa] border-b border-gray-200">
            <tr>
              <th class="px-4 py-3 text-left font-semibold text-[#6c757d] w-20">{{ t('schedule.time') }}</th>
              <th
                v-for="day in calendar"
                :key="day.date"
                class="px-2 py-3 text-center font-semibold min-w-[120px]"
                :class="day.is_today ? 'bg-[#e7f3ff] text-[#007bff]' : 'text-[#6c757d]'"
              >
                <div>{{ day.day_short }}</div>
                <div class="text-lg">{{ day.day_number }}</div>
                <div class="mt-1">
                  <button
                    @click="blockDay(day.date)"
                    class="text-xs text-[#dc3545] hover:underline mr-2"
                  >{{ t('schedule.blockAll') }}</button>
                  <button
                    @click="unblockDay(day.date)"
                    class="text-xs text-[#28a745] hover:underline"
                  >{{ t('schedule.unblockAll') }}</button>
                </div>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="(slot, slotIndex) in slots" :key="slot.id" class="hover:bg-[#f8f9fa]">
              <td class="px-4 py-2 font-medium text-[#1f2d3d] whitespace-nowrap">
                {{ slot.start_time?.substring(0, 5) }} - {{ slot.end_time?.substring(0, 5) }}
              </td>
              <td
                v-for="day in calendar"
                :key="day.date + '-' + slot.id"
                class="px-2 py-2 text-center"
              >
                <div
                  class="px-2 py-1 rounded border text-xs cursor-pointer"
                  :class="getStatusClass(day.slots[slotIndex]?.status || 'FREE')"
                  @click="day.slots[slotIndex]?.status === 'FREE' ? openBlockModal(slot, day.date) : (day.slots[slotIndex]?.status === 'BLOCKED' ? unblockSlot(day.slots[slotIndex]) : null)"
                >
                  <span v-if="day.slots[slotIndex]?.status === 'FREE'">{{ t('schedule.free') }}</span>
                  <span v-else-if="day.slots[slotIndex]?.status === 'PENDING'">{{ t('schedule.pending') }}</span>
                  <span v-else-if="day.slots[slotIndex]?.status === 'RESERVED'">{{ t('schedule.reserved') }}</span>
                  <span v-else-if="day.slots[slotIndex]?.status === 'BLOCKED'">
                    {{ t('schedule.blocked') }}
                    <span v-if="day.slots[slotIndex]?.block_reason" class="block text-[10px] truncate max-w-[80px]">
                      ({{ day.slots[slotIndex].block_reason }})
                    </span>
                  </span>
                  <span v-else>{{ t('schedule.free') }}</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Block Modal -->
    <div v-if="showBlockModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="px-4 py-3 border-b border-gray-200">
          <h3 class="font-semibold text-[#1f2d3d]">{{ t('schedule.blockSlot') }}</h3>
        </div>
        <div class="p-4">
          <p class="text-sm text-[#6c757d] mb-4">
            {{ selectedSlot?.start_time?.substring(0, 5) }} - {{ selectedSlot?.end_time?.substring(0, 5) }}, {{ selectedDate }}
          </p>
          <div>
            <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
              {{ t('schedule.blockReason') }}
            </label>
            <input
              type="text"
              v-model="blockReason"
              :placeholder="t('schedule.blockReasonPlaceholder')"
              class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
            />
          </div>
        </div>
        <div class="px-4 py-3 border-t border-gray-200 flex justify-end gap-3">
          <button
            @click="showBlockModal = false"
            class="px-4 py-2 text-[#6c757d] hover:text-[#1f2d3d] text-sm font-medium transition"
          >
            {{ t('common.cancel') }}
          </button>
          <button
            @click="blockSlot"
            class="px-4 py-2 bg-[#dc3545] text-white text-sm font-medium rounded hover:bg-[#c82333] transition"
          >
            {{ t('schedule.block') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
