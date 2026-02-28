<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
  Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from 'vue-sonner';

const props = defineProps<{
  packages: Array<any>;
  activeSubscription: any;
}>();

const isPayDialogOpen  = ref(false);
const selectedPackage  = ref<any>(null);

const form = useForm({
  package_id: '',
  phone:      '',
});

const openPayDialog = (pkg: any) => {
  selectedPackage.value = pkg;
  form.package_id       = pkg.id;
  form.phone            = '';
  form.clearErrors();
  isPayDialogOpen.value = true;
};

const closePayDialog = () => {
  isPayDialogOpen.value = false;
  selectedPackage.value = null;
  form.reset();
};

const handleSubscribe = () => {
  form.post(route('subscriptions.subscribe'), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('STK Push sent! Check your phone and enter your M-Pesa PIN.');
      closePayDialog();
    },
    onError: () => toast.error('Failed to initiate payment. Please try again.'),
  });
};

const cancelSubscription = (id: number) => {
  if (!confirm('Cancel your subscription?')) return;
  router.patch(route('subscriptions.cancel', id), {}, {
    onSuccess: () => toast.success('Subscription cancelled.'),
  });
};

const formatCycle = (cycle: string): string => {
  const map: Record<string, string> = {
    'weekly':    '/ week',
    'bi-weekly': '/ 2 weeks',
    'monthly':   '/ month',
    'quarterly': '/ 3 months',
    'annually':  '/ year',
  };
  return map[cycle] ?? cycle;
};

const formatDate = (date: string): string => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-KE', {
    weekday: 'short', day: 'numeric', month: 'short', year: 'numeric',
  });
};
</script>

<template>
  <AppLayout>
    <div class="max-w-5xl mx-auto px-4 py-10">

      <div class="text-center mb-10">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Choose a Plan</h1>
        <p class="text-gray-500 dark:text-gray-400">Subscribe to access health job listings across Kenya.</p>
      </div>

      <!-- Active Subscription Banner -->
      <div v-if="activeSubscription"
           class="mb-8 rounded-xl border border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-800 p-5">
        <div class="flex items-center justify-between">
          <div>
            <p class="font-semibold text-green-800 dark:text-green-300">
              ✅ Active Plan: {{ activeSubscription.package.name }}
            </p>
            <p class="text-sm text-green-600 dark:text-green-400 mt-1">
              Expires: {{ formatDate(activeSubscription.expires_at) }}
            </p>
          </div>
          <button
              @click="cancelSubscription(activeSubscription.id)"
              class="text-xs text-red-500 hover:underline"
          >
            Cancel Plan
          </button>
        </div>
      </div>

      <!-- Packages Grid -->
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
            v-for="pkg in packages"
            :key="pkg.id"
            :class="[
                        'relative flex flex-col rounded-2xl border p-6 shadow-sm transition-all duration-300 hover:shadow-lg',
                        activeSubscription?.package_id === pkg.id
                            ? 'border-green-400 bg-green-50 dark:bg-green-900/10'
                            : 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800'
                    ]"
        >
          <!-- Active badge -->
          <div v-if="activeSubscription?.package_id === pkg.id"
               class="absolute -top-3 left-1/2 -translate-x-1/2">
                        <span class="rounded-full bg-green-500 px-3 py-1 text-xs font-semibold text-white">
                            Current Plan
                        </span>
          </div>

          <div class="mb-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ pkg.name }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ pkg.description }}</p>
          </div>

          <div class="mb-6">
                        <span class="text-3xl font-extrabold text-gray-900 dark:text-white">
                            KES {{ Number(pkg.price).toLocaleString() }}
                        </span>
            <span class="text-sm text-gray-500 dark:text-gray-400 ml-1">
                            {{ formatCycle(pkg.billing_cycle) }}
                        </span>
          </div>

          <!-- Features -->
          <ul v-if="pkg.features && pkg.features.length" class="space-y-2 mb-6 flex-1">
            <li v-for="feature in pkg.features" :key="feature"
                class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
              <svg class="h-4 w-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              {{ feature }}
            </li>
          </ul>

          <Button
              @click="openPayDialog(pkg)"
              :disabled="activeSubscription?.package_id === pkg.id && activeSubscription?.status === 'active'"
              class="w-full mt-auto"
              :class="activeSubscription?.package_id === pkg.id ? 'opacity-50 cursor-not-allowed' : ''"
          >
            {{ activeSubscription?.package_id === pkg.id ? 'Current Plan' : 'Subscribe via M-Pesa' }}
          </Button>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="packages.length === 0" class="text-center py-16 text-gray-400">
        <p>No packages available yet. Check back soon.</p>
      </div>
    </div>

    <!-- Pay Dialog -->
    <Dialog v-model:open="isPayDialogOpen">
      <DialogContent class="sm:max-w-[420px]"
                     @interact-outside="(e) => e.preventDefault()">
        <DialogHeader>
          <DialogTitle>Subscribe to {{ selectedPackage?.name }}</DialogTitle>
          <DialogDescription>
            Enter your M-Pesa number to pay
            <strong>KES {{ Number(selectedPackage?.price).toLocaleString() }}</strong>
            {{ formatCycle(selectedPackage?.billing_cycle) }}.
          </DialogDescription>
        </DialogHeader>

        <form @submit.prevent="handleSubscribe">
          <div class="grid gap-4 py-4">
            <div class="grid gap-2">
              <Label>M-Pesa Phone Number</Label>
              <Input
                  v-model="form.phone"
                  type="tel"
                  placeholder="e.g. 0712345678"
                  required
              />
              <p v-if="form.errors.phone" class="text-xs text-red-500">{{ form.errors.phone }}</p>
              <p class="text-xs text-gray-500">You will receive an STK push on this number.</p>
            </div>
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" @click="closePayDialog" :disabled="form.processing">
              Cancel
            </Button>
            <Button type="submit" :disabled="form.processing" class="bg-green-600 hover:bg-green-700">
              {{ form.processing ? 'Sending STK Push...' : 'Pay with M-Pesa' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

  </AppLayout>
</template>