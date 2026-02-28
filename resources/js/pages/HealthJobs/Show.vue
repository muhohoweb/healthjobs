<script setup lang="ts">
import { Head, Link, Form } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useAuth } from '@/utils/auth';
import { User } from '@/types';

const user = useAuth();
const { job } = defineProps<{ job: any }>();

const formatJobType = (type: string): string => {
  if (!type) return 'Not specified';
  return type.split('-').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
};

function canShowInterest(user: User, job: any): boolean {
  if (user.id === job.user_id) return false;
  const alreadyInterested = user.jobInterests.some(
      (interest: any) => interest.health_job_id === job.id
  );
  return !alreadyInterested;
}

const formatSalary = (salary: number): string => {
  if (!salary || salary === 0) return 'Not specified';
  return new Intl.NumberFormat('en-US').format(salary);
};

const formatDate = (date: string): string => {
  if (!date) return 'Not specified';
  return new Date(date).toLocaleDateString('en-KE', {
    year: 'numeric', month: 'long', day: 'numeric'
  });
};
</script>

<template>
  <Head :title="job.title" />
  <AppLayout>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Flash Message -->
        <div v-if="$page.props.flash.flashMessage" class="mb-6 rounded-md border border-red-300 bg-red-50 p-4 text-red-800 shadow-sm dark:border-red-700 dark:bg-red-900 dark:text-red-200">
          <div class="flex items-center">
            <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.662 1.732-3L13.732 4c-.77-1.338-2.694-1.338-3.464 0L3.34 16c-.77 1.338.192 3 1.732 3z" />
            </svg>
            <span class="ml-2 font-medium">{{ $page.props.flash.flashMessage }}</span>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">

          <!-- Header -->
          <div class="bg-blue-600 dark:bg-blue-700 text-white p-6">
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <h1 class="text-2xl font-bold mb-1">{{ job.title }}</h1>
                <p v-if="job.organization" class="text-blue-100 text-sm mb-1">🏢 {{ job.organization }}</p>
                <p class="text-blue-200 text-sm">📍 {{ job.location ?? 'Location not specified' }}</p>
              </div>
              <div class="flex flex-col items-end gap-2 ml-4">
                                <span class="inline-flex items-center rounded-full bg-white/20 px-3 py-1 text-sm font-medium">
                                    {{ formatJobType(job.job_type) }}
                                </span>
                <span v-if="job.contract_duration" class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs">
                                    📄 {{ job.contract_duration }}
                                </span>
              </div>
            </div>
          </div>

          <!-- Quick Info Bar -->
          <div class="grid grid-cols-2 md:grid-cols-4 border-b border-gray-100 dark:border-gray-700">
            <div class="p-4 text-center border-r border-gray-100 dark:border-gray-700">
              <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Salary</p>
              <p class="text-sm font-semibold text-green-600 dark:text-green-400">
                                <span v-if="job.salary_min && job.salary_max">
                                    Ksh {{ formatSalary(job.salary_min) }} – {{ formatSalary(job.salary_max) }}
                                </span>
                <span v-else-if="job.salary">{{ job.salary }}</span>
                <span v-else class="text-gray-400">Not specified</span>
              </p>
            </div>
            <div class="p-4 text-center border-r border-gray-100 dark:border-gray-700">
              <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Deadline</p>
              <p class="text-sm font-semibold text-red-500 dark:text-red-400">
                {{ job.deadline ? formatDate(job.deadline) : 'Open' }}
              </p>
            </div>
            <div class="p-4 text-center border-r border-gray-100 dark:border-gray-700">
              <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Experience</p>
              <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                {{ job.experience_level ?? 'Not specified' }}
              </p>
            </div>
            <div class="p-4 text-center">
              <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Category</p>
              <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                {{ job.cadre ?? 'General' }}
              </p>
            </div>
          </div>

          <!-- Content -->
          <div class="p-6 space-y-8">

            <!-- Description -->
            <div v-if="job.description">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                <span class="w-1 h-5 bg-blue-600 rounded-full inline-block"></span>
                Job Description
              </h2>
              <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 text-sm leading-relaxed">
                <span v-html="job.description"></span>
              </div>
            </div>

            <!-- Responsibilities -->
            <div v-if="job.responsibilities && job.responsibilities.length">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                <span class="w-1 h-5 bg-blue-600 rounded-full inline-block"></span>
                Key Responsibilities
              </h2>
              <ul class="space-y-2">
                <li v-for="item in job.responsibilities" :key="item" class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                  <svg class="h-4 w-4 mt-0.5 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                  {{ item }}
                </li>
              </ul>
            </div>

            <!-- Requirements -->
            <div v-if="job.requirements && job.requirements.length">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                <span class="w-1 h-5 bg-green-500 rounded-full inline-block"></span>
                Requirements
              </h2>
              <ul class="space-y-2">
                <li v-for="req in job.requirements" :key="req" class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                  <svg class="h-4 w-4 mt-0.5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                  </svg>
                  {{ req }}
                </li>
              </ul>
            </div>

            <!-- Qualifications -->
            <div v-if="job.qualifications && job.qualifications.length">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                <span class="w-1 h-5 bg-purple-500 rounded-full inline-block"></span>
                Qualifications
              </h2>
              <ul class="space-y-2">
                <li v-for="qual in job.qualifications" :key="qual" class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                  <svg class="h-4 w-4 mt-0.5 text-purple-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                  </svg>
                  {{ qual }}
                </li>
              </ul>
            </div>

            <!-- How to Apply -->
            <div v-if="job.how_to_apply || job.contact_phone || job.contact_email || job.contact_address" class="rounded-lg bg-blue-50 dark:bg-blue-900/20 p-5 border border-blue-100 dark:border-blue-800">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                  <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                </svg>
                How to Apply
              </h2>
              <div class="space-y-3 text-sm">
                <p v-if="job.how_to_apply" class="text-gray-700 dark:text-gray-300">
                  {{ job.how_to_apply }}
                </p>
                <div v-if="job.contact_phone" class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                  <svg class="h-4 w-4 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                  </svg>
                  <a :href="`tel:${job.contact_phone}`" class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                    {{ job.contact_phone }}
                  </a>
                </div>
                <div v-if="job.contact_email" class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                  <svg class="h-4 w-4 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                  </svg>
                  <a :href="`mailto:${job.contact_email}`" class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                    {{ job.contact_email }}
                  </a>
                </div>
                <div v-if="job.contact_address" class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                  <svg class="h-4 w-4 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                  </svg>
                  {{ job.contact_address }}
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4 pt-2">
              <Form v-if="canShowInterest(user, job)" :action="route('health-jobs.interested')" method="post">
                <button type="submit" class="bg-blue-600 dark:bg-blue-700 text-white px-6 py-3 rounded-md hover:bg-blue-700 dark:hover:bg-blue-600 font-medium cursor-pointer transition-colors">
                  Interested
                </button>
                <input type="hidden" name="job" :value="job.uuid"/>
              </Form>

              <Link
                  :href="route('health-jobs.index')"
                  class="border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 px-6 py-3 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 font-medium text-center transition-colors"
              >
                ← Back to Jobs
              </Link>
            </div>

            <!-- Meta -->
            <div class="border-t border-gray-100 dark:border-gray-700 pt-4 text-xs text-gray-400 dark:text-gray-500">
              <span v-if="job.user">Posted by {{ job.user.email }}</span>
              <span v-if="job.created_at"> · {{ formatDate(job.created_at) }}</span>
            </div>

          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>