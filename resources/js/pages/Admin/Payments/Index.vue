<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import {
  Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';

const props = defineProps<{
  payments: {
    data: Array<any>;
    links: Array<any>;
    total: number;
    from: number;
    to: number;
  };
}>();

const formatDate = (date: string): string => {
  if (!date) return '—';
  return new Date(date).toLocaleDateString('en-KE', {
    day: 'numeric', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  });
};
</script>

<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto px-4 py-8">

      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">M-Pesa Payments</h1>
        <span class="text-sm text-gray-500 dark:text-gray-400">{{ payments.total }} total</span>
      </div>

      <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>#</TableHead>
              <TableHead>User</TableHead>
              <TableHead>Package</TableHead>
              <TableHead>Amount</TableHead>
              <TableHead>Receipt</TableHead>
              <TableHead>Phone</TableHead>
              <TableHead>Subscription</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Date</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="payments.data.length === 0">
              <TableCell colspan="9" class="h-24 text-center text-gray-400">
                No payments yet.
              </TableCell>
            </TableRow>
            <TableRow v-for="(payment, index) in payments.data" :key="payment.id">
              <TableCell>{{ payments.from + index }}</TableCell>
              <TableCell>
                <div class="font-medium text-sm text-gray-900 dark:text-white">
                  {{ payment.user?.name }}
                </div>
                <div class="text-xs text-gray-500">{{ payment.user?.email }}</div>
              </TableCell>
              <TableCell>
                                <span class="inline-flex items-center rounded-full bg-blue-50 dark:bg-blue-900/30 px-2.5 py-1 text-xs font-medium text-blue-700 dark:text-blue-300">
                                    {{ payment.package?.name }}
                                </span>
              </TableCell>
              <TableCell class="font-semibold text-green-600 dark:text-green-400">
                KES {{ Number(payment.amount).toLocaleString() }}
              </TableCell>
              <TableCell class="text-xs font-mono text-gray-700 dark:text-gray-300">
                {{ payment.mpesa_receipt_number ?? '—' }}
              </TableCell>
              <TableCell class="text-sm text-gray-600 dark:text-gray-400">
                {{ payment.phone_number ?? '—' }}
              </TableCell>
              <TableCell>
                                <span v-if="payment.subscription"
                                      :class="payment.subscription.status === 'active'
                                          ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                          : payment.subscription.status === 'expired'
                                          ? 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300'
                                          : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'"
                                      class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium capitalize">
                                    {{ payment.subscription.status }}
                                </span>
                <span v-else class="text-xs text-gray-400">—</span>
              </TableCell>
              <TableCell>
                                <span
                                    :class="payment.status === 'completed'
                                        ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                        : payment.status === 'failed'
                                        ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300'
                                        : 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300'"
                                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                                >
                                    {{ payment.status }}
                                </span>
              </TableCell>
              <TableCell class="text-xs text-gray-500 dark:text-gray-400">
                {{ formatDate(payment.paid_at ?? payment.created_at) }}
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <!-- Pagination -->
      <div v-if="payments.links.length > 3" class="mt-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">
          Showing {{ payments.from }}–{{ payments.to }} of {{ payments.total }}
        </p>
        <div class="flex gap-1">
          <Link
              v-for="link in payments.links"
              :key="link.label"
              :href="link.url ?? '#'"
              v-html="link.label"
              :class="[
                            'px-3 py-1.5 text-sm rounded-md border transition-colors',
                            link.active
                                ? 'bg-blue-600 text-white border-blue-600'
                                : 'border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700',
                            !link.url ? 'opacity-40 cursor-not-allowed' : ''
                        ]"
              :preserve-state="true"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>