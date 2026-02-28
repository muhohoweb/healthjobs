<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Edit, Trash2, Plus } from 'lucide-vue-next';
import {
  Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import {
  Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle,
} from '@/components/ui/dialog';
import {
  AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent,
  AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from 'vue-sonner';

const props = defineProps<{
  packages: Array<any>;
  billingCycles: Array<{ value: string; label: string }>;
}>();

const formatCycle = (cycle: string): string => {
  const found = props.billingCycles.find(c => c.value === cycle);
  return found?.label ?? cycle;
};

// ─── Add ────────────────────────────────────────────────────────
const isAddOpen = ref(false);
const addForm = useForm({
  name:          '',
  description:   '',
  price:         '',
  billing_cycle: '',
  is_active:     true,
  features:      [] as string[],
  sort_order:    0,
});
const newAddFeature = ref('');

const openAdd  = () => { addForm.reset(); addForm.clearErrors(); isAddOpen.value = true; };
const closeAdd = () => { isAddOpen.value = false; addForm.reset(); addForm.clearErrors(); };

const addFeature = () => {
  if (newAddFeature.value.trim()) {
    addForm.features.push(newAddFeature.value.trim());
    newAddFeature.value = '';
  }
};
const removeAddFeature = (i: number) => addForm.features.splice(i, 1);

const handleAddSubmit = () => {
  addForm.post(route('admin.packages.store'), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Package created successfully.');
      closeAdd();
    },
    onError: () => toast.error('Please check the form for errors.'),
  });
};

// ─── Edit ───────────────────────────────────────────────────────
const isEditOpen     = ref(false);
const editingPackage = ref<any>(null);
const editForm = useForm({
  name:          '',
  description:   '',
  price:         '',
  billing_cycle: '',
  is_active:     true,
  features:      [] as string[],
  sort_order:    0,
});
const newEditFeature = ref('');

const openEdit = (pkg: any) => {
  editingPackage.value   = pkg;
  editForm.name          = pkg.name;
  editForm.description   = pkg.description ?? '';
  editForm.price         = pkg.price;
  editForm.billing_cycle = pkg.billing_cycle;
  editForm.is_active     = pkg.is_active;
  editForm.features      = pkg.features ?? [];
  editForm.sort_order    = pkg.sort_order ?? 0;
  editForm.clearErrors();
  isEditOpen.value = true;
};
const closeEdit = () => { isEditOpen.value = false; editingPackage.value = null; editForm.reset(); };

const addEditFeature = () => {
  if (newEditFeature.value.trim()) {
    editForm.features.push(newEditFeature.value.trim());
    newEditFeature.value = '';
  }
};
const removeEditFeature = (i: number) => editForm.features.splice(i, 1);

const handleEditSubmit = () => {
  editForm.put(route('admin.packages.update', editingPackage.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Package updated successfully.');
      closeEdit();
    },
    onError: () => toast.error('Please check the form for errors.'),
  });
};

// ─── Delete ─────────────────────────────────────────────────────
const isDeleteOpen     = ref(false);
const deletingPackage  = ref<any>(null);

const openDelete  = (pkg: any) => { deletingPackage.value = pkg; isDeleteOpen.value = true; };
const closeDelete = () => { isDeleteOpen.value = false; deletingPackage.value = null; };

const handleDeleteConfirm = () => {
  if (!deletingPackage.value) return;
  router.delete(route('admin.packages.destroy', deletingPackage.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Package deleted.');
      closeDelete();
    },
    onError: () => toast.error('Failed to delete package.'),
  });
};
</script>

<template>
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
      <div class="relative min-h-[100vh] flex-1 rounded-xl border border-gray-200 bg-white md:min-h-min dark:border-gray-700 dark:bg-gray-800">
        <div class="p-6">
          <div class="mb-4 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Packages</h2>
            <Button @click="openAdd">
              <Plus class="mr-2 h-4 w-4"/> New Package
            </Button>
          </div>

          <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>#</TableHead>
                  <TableHead>Name</TableHead>
                  <TableHead>Price</TableHead>
                  <TableHead>Billing Cycle</TableHead>
                  <TableHead>Features</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead class="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="packages.length === 0">
                  <TableCell colspan="7" class="h-24 text-center text-gray-500 dark:text-gray-400">
                    No packages yet. Create your first one.
                  </TableCell>
                </TableRow>
                <TableRow v-for="(pkg, index) in packages" :key="pkg.id">
                  <TableCell class="font-medium">{{ index + 1 }}</TableCell>
                  <TableCell>
                    <div class="font-medium text-gray-900 dark:text-white">{{ pkg.name }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ pkg.description }}</div>
                  </TableCell>
                  <TableCell class="font-medium text-green-600 dark:text-green-400">
                    KES {{ Number(pkg.price).toLocaleString() }}
                  </TableCell>
                  <TableCell>
                                        <span class="inline-flex items-center rounded-full bg-blue-50 dark:bg-blue-900/30 px-2.5 py-1 text-xs font-medium text-blue-700 dark:text-blue-300">
                                            {{ formatCycle(pkg.billing_cycle) }}
                                        </span>
                  </TableCell>
                  <TableCell>
                    <span class="text-xs text-gray-500">{{ (pkg.features ?? []).length }} features</span>
                  </TableCell>
                  <TableCell>
                                        <span
                                            :class="pkg.is_active
                                                ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                                : 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300'"
                                            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium"
                                        >
                                            {{ pkg.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                  </TableCell>
                  <TableCell class="text-right">
                    <div class="flex items-center justify-end gap-2">
                      <button @click="openEdit(pkg)"
                              class="inline-flex items-center gap-1 rounded-md bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-200 dark:hover:bg-blue-800">
                        <Edit class="h-4 w-4"/> Edit
                      </button>
                      <button @click="openDelete(pkg)"
                              class="inline-flex items-center gap-1 rounded-md bg-red-100 px-3 py-1.5 text-sm font-medium text-red-700 hover:bg-red-200 dark:bg-red-900 dark:text-red-200 dark:hover:bg-red-800">
                        <Trash2 class="h-4 w-4"/> Delete
                      </button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </div>
      </div>
    </div>

    <!-- ─── Add Dialog ──────────────────────────────────────── -->
    <Dialog v-model:open="isAddOpen">
      <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-[600px]"
                     @interact-outside="(e) => e.preventDefault()">
        <DialogHeader>
          <DialogTitle>New Package</DialogTitle>
          <DialogDescription>Fill in the package details below.</DialogDescription>
        </DialogHeader>

        <form @submit.prevent="handleAddSubmit">
          <div class="grid gap-4 py-4">

            <div class="grid grid-cols-2 gap-4">
              <div class="grid gap-2">
                <Label>Package Name</Label>
                <Input v-model="addForm.name" placeholder="e.g. Basic, Pro" required/>
                <p v-if="addForm.errors.name" class="text-xs text-red-500">{{ addForm.errors.name }}</p>
              </div>
              <div class="grid gap-2">
                <Label>Billing Cycle</Label>
                <select v-model="addForm.billing_cycle" required
                        class="rounded-md border border-input bg-background px-3 py-2 text-sm">
                  <option value="">Select cycle</option>
                  <option v-for="cycle in billingCycles" :key="cycle.value" :value="cycle.value">
                    {{ cycle.label }}
                  </option>
                </select>
                <p v-if="addForm.errors.billing_cycle" class="text-xs text-red-500">{{ addForm.errors.billing_cycle }}</p>
              </div>
            </div>

            <div class="grid gap-2">
              <Label>Description</Label>
              <textarea v-model="addForm.description" rows="2"
                        class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                        placeholder="Brief description"/>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="grid gap-2">
                <Label>Price (KES)</Label>
                <Input v-model="addForm.price" type="number" min="0" step="0.01" placeholder="0.00" required/>
                <p v-if="addForm.errors.price" class="text-xs text-red-500">{{ addForm.errors.price }}</p>
              </div>
              <div class="grid gap-2">
                <Label>Sort Order</Label>
                <Input v-model="addForm.sort_order" type="number" min="0"/>
              </div>
            </div>

            <!-- Features -->
            <div class="grid gap-2">
              <Label>Features</Label>
              <div class="flex gap-2">
                <Input v-model="newAddFeature" placeholder="e.g. Up to 10 job postings"
                       @keydown.enter.prevent="addFeature"/>
                <Button type="button" variant="outline" @click="addFeature">Add</Button>
              </div>
              <ul class="space-y-1 mt-1">
                <li v-for="(f, i) in addForm.features" :key="i"
                    class="flex items-center justify-between rounded-md bg-gray-50 dark:bg-gray-700 px-3 py-1.5 text-sm">
                  <span>✓ {{ f }}</span>
                  <button type="button" @click="removeAddFeature(i)" class="text-red-400 hover:text-red-600 text-xs">Remove</button>
                </li>
              </ul>
            </div>

            <div class="flex items-center gap-2">
              <input v-model="addForm.is_active" type="checkbox" id="add_active" class="w-4 h-4 text-blue-600 rounded"/>
              <Label for="add_active">Active</Label>
            </div>
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" @click="closeAdd" :disabled="addForm.processing">Cancel</Button>
            <Button type="submit" :disabled="addForm.processing">
              {{ addForm.processing ? 'Saving...' : 'Create Package' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- ─── Edit Dialog ─────────────────────────────────────── -->
    <Dialog v-model:open="isEditOpen">
      <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-[600px]"
                     @interact-outside="(e) => e.preventDefault()">
        <DialogHeader>
          <DialogTitle>Edit Package</DialogTitle>
          <DialogDescription>Update the package details below.</DialogDescription>
        </DialogHeader>

        <form @submit.prevent="handleEditSubmit">
          <div class="grid gap-4 py-4">

            <div class="grid grid-cols-2 gap-4">
              <div class="grid gap-2">
                <Label>Package Name</Label>
                <Input v-model="editForm.name" required/>
                <p v-if="editForm.errors.name" class="text-xs text-red-500">{{ editForm.errors.name }}</p>
              </div>
              <div class="grid gap-2">
                <Label>Billing Cycle</Label>
                <select v-model="editForm.billing_cycle" required
                        class="rounded-md border border-input bg-background px-3 py-2 text-sm">
                  <option v-for="cycle in billingCycles" :key="cycle.value" :value="cycle.value">
                    {{ cycle.label }}
                  </option>
                </select>
                <p v-if="editForm.errors.billing_cycle" class="text-xs text-red-500">{{ editForm.errors.billing_cycle }}</p>
              </div>
            </div>

            <div class="grid gap-2">
              <Label>Description</Label>
              <textarea v-model="editForm.description" rows="2"
                        class="rounded-md border border-input bg-background px-3 py-2 text-sm"/>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="grid gap-2">
                <Label>Price (KES)</Label>
                <Input v-model="editForm.price" type="number" min="0" step="0.01" required/>
                <p v-if="editForm.errors.price" class="text-xs text-red-500">{{ editForm.errors.price }}</p>
              </div>
              <div class="grid gap-2">
                <Label>Sort Order</Label>
                <Input v-model="editForm.sort_order" type="number" min="0"/>
              </div>
            </div>

            <div class="grid gap-2">
              <Label>Features</Label>
              <div class="flex gap-2">
                <Input v-model="newEditFeature" placeholder="Add a feature"
                       @keydown.enter.prevent="addEditFeature"/>
                <Button type="button" variant="outline" @click="addEditFeature">Add</Button>
              </div>
              <ul class="space-y-1 mt-1">
                <li v-for="(f, i) in editForm.features" :key="i"
                    class="flex items-center justify-between rounded-md bg-gray-50 dark:bg-gray-700 px-3 py-1.5 text-sm">
                  <span>✓ {{ f }}</span>
                  <button type="button" @click="removeEditFeature(i)" class="text-red-400 hover:text-red-600 text-xs">Remove</button>
                </li>
              </ul>
            </div>

            <div class="flex items-center gap-2">
              <input v-model="editForm.is_active" type="checkbox" id="edit_active" class="w-4 h-4 text-blue-600 rounded"/>
              <Label for="edit_active">Active</Label>
            </div>
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" @click="closeEdit" :disabled="editForm.processing">Cancel</Button>
            <Button type="submit" :disabled="editForm.processing">
              {{ editForm.processing ? 'Saving...' : 'Update Package' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- ─── Delete Dialog ───────────────────────────────────── -->
    <AlertDialog v-model:open="isDeleteOpen">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Delete Package?</AlertDialogTitle>
          <AlertDialogDescription>
            This will permanently delete
            <span v-if="deletingPackage" class="font-semibold">{{ deletingPackage.name }}</span>.
            This action cannot be undone.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="closeDelete">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="handleDeleteConfirm" class="bg-red-600 hover:bg-red-700">
            Delete
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </AppLayout>
</template>