<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useAuth } from '@/utils/auth';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import { PlusIcon } from 'lucide-vue-next';

const user = useAuth();

const props = defineProps({
    jobs: Object,
    locations: Object,
    filters: Object,
    isProfileComplete: Boolean,
});

const searchForm = reactive({
    search: props.filters.search || '',
    job_type: props.filters.job_type || '',
    time_filter: props.filters.time_filter || '',
    location: props.filters.location || '',
});

const search = (): void => {
    router.get(route('health-jobs.index'), searchForm, {
        preserveState: true,
        replace: true,
    });
};

const formatJobType = (type: string): string => {
    return type
        .split('-')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

// Helper function to format time ago
const formatTimeAgo = (dateString: string): string => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInMinutes = Math.floor((now.getTime() - date.getTime()) / (1000 * 60));

    if (diffInMinutes < 1) return 'Just now';
    if (diffInMinutes < 60) return `${diffInMinutes} min ago`;

    const diffInHours = Math.floor(diffInMinutes / 60);
    if (diffInHours < 24) return `${diffInHours} hour${diffInHours > 1 ? 's' : ''} ago`;

    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays < 7) return `${diffInDays} day${diffInDays > 1 ? 's' : ''} ago`;

    const diffInWeeks = Math.floor(diffInDays / 7);
    if (diffInWeeks < 4) return `${diffInWeeks} week${diffInWeeks > 1 ? 's' : ''} ago`;

    const diffInMonths = Math.floor(diffInDays / 30);
    if (diffInMonths < 12) return `${diffInMonths} month${diffInMonths > 1 ? 's' : ''} ago`;

    const diffInYears = Math.floor(diffInDays / 365);
    return `${diffInYears} year${diffInYears > 1 ? 's' : ''} ago`;
};

// Add this helper function to your script section
const truncateDescription = (description, maxLength = 150) => {
    if (!description) return '';

    // Remove HTML tags for character counting
    const textOnly = description.replace(/<[^>]*>/g, '');

    if (textOnly.length <= maxLength) {
        return description;
    }

    // Find the last complete word within the limit
    const truncated = textOnly.substring(0, maxLength);
    const lastSpace = truncated.lastIndexOf(' ');
    const finalText = lastSpace > 0 ? truncated.substring(0, lastSpace) : truncated;

    return finalText + '...';
};

// Pagination helper functions
const getPageRange = () => {
    const current = props.jobs.current_page;
    const last = props.jobs.last_page;
    const range = [];

    // Show max 5 page numbers
    let start = Math.max(1, current - 2);
    const end = Math.min(last, start + 4);

    // Adjust start if we're near the end
    if (end - start < 4) {
        start = Math.max(1, end - 4);
    }

    for (let i = start; i <= end; i++) {
        range.push(i);
    }

    return range;
};

const getPageUrl = (page: number) => {
    const url = new URL(window.location.href);
    url.searchParams.set('page', page.toString());
    return url.toString();
};
</script>

<template>
    <Head title="Health Job" />

    <AppLayout>
        <div class="min-h-screen bg-gray-50 py-8 dark:bg-gray-900">
            <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">

                <div v-if="!props.isProfileComplete" class="mb-6 rounded-md bg-red-100 p-4 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                    ⚠️ Kindly complete your profile to enhance the credibility of your posts.
                    <Link :href="route('profile.update')" class="ml-3 text-blue-600 underline">Complete Profile</Link>
                </div>



                <div class="mb-6 rounded-lg bg-white p-6 shadow dark:bg-gray-800 dark:shadow-gray-700/25">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Filter Jobs</h2>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-6">
<!--                        Col 1  -->
                        <div>
                            <label for="search" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                            <input
                                v-model="searchForm.search"
                                type="text"
                                id="search"
                                placeholder="Search jobs..."
                                class="w-full rounded-md border border-gray-300 bg-white px-4 py-2.5 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-400 dark:focus:ring-blue-400"
                            />
                        </div>

                        <div>
                            <label for="time_filter" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">By Time</label>
                            <select
                                v-model="searchForm.time_filter"
                                id="time_filter"
                                class="w-full rounded-md border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-400"
                            >
                                <option value="">By time</option>
                                <option value="latest">Latest</option>
                                <option value="oldest">Oldest</option>
                            </select>
                        </div>

                        <div>
                            <label for="job_type_filter" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Job Type</label>
                            <select
                                v-model="searchForm.job_type"
                                id="job_type_filter"
                                class="w-full rounded-md border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-400"
                            >
                                <option value="">All Job Types</option>
                                <option value="full-time">Full Time</option>
                                <option value="part-time">Locum</option>
                            </select>
                        </div>
                        <div>
                            <label for="location" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
                            <select
                                v-model="searchForm.location"
                                id="location"
                                class="w-full rounded-md border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-400"
                            >
                                <option value="">Select Location</option>
                                <option v-for="location in locations" :value="location.location" v-bind:key="location">
                                    {{ location.location }}
                                </option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button
                                @click="search"
                                class="mr-3 w-full cursor-pointer rounded-md bg-blue-600 px-4 py-2.5 text-white transition-colors hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-offset-gray-800"
                            >
                                Apply
                            </button>

                            <Link
                                class="w-full cursor-pointer rounded-md bg-red-400 px-4 py-2.5 text-white transition-colors hover:bg-red-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-offset-gray-800"
                                :href="route('health-jobs.index')"
                            >Clear
                            </Link>

<!--                            <div  class="mb-6 flex justify-end">-->

<!--                                -->
<!--                            </div>-->
                        </div>

                        <div>
                            <Link v-if="user.hasPermission('create-job-postings')"
                                class="flex text-center items-center rounded-md bg-blue-600 px-4 py-2.5 font-medium text-white transition hover:bg-blue-700 focus:ring-2 focus:ring-blue-400 focus:outline-none mt-6"
                                :href="route('health-jobs.create')"
                            > <PlusIcon></PlusIcon> New Job
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Job cards section with consistent blue theme -->
                <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="job in props.jobs.data"
                        :key="job.id"
                        class="group relative flex
                         flex-col overflow-hidden rounded-xl
                         border border-gray-200 bg-white
                         shadow-md transition-all duration-300
                         hover:-translate-y-1 hover:shadow-xl
                         hover:shadow-blue-500/10 dark:border-gray-900 dark:bg-gray-800"
                    >
                        <!-- Blue gradient stripe for all jobs -->
                        <div class="absolute top-0 right-0 left-0 h-1"></div>

                        <!-- Header Section -->
                        <div class="p-6 pb-4">
                            <!-- Time and License Badge Container -->
                            <div class="absolute top-4 right-4 flex items-center gap-2">
                                <!-- Time Badge -->
                                <span
                                    class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"
                                >
                                    {{ formatTimeAgo(job.created_at) }}
                                </span>
                            </div>

                            <!-- Job Title -->
                            <h3 class="mb-2 line-clamp-2 pr-20 text-lg leading-tight font-bold text-gray-900 dark:text-white">
                                {{ job.title }}
                            </h3>

                            <!-- Job Type & Location -->
                            <div class="mb-3 flex items-center justify-between">
                                <span
                                    class="inline-flex items-center rounded-md bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-300"
                                >
                                    {{ formatJobType(job.job_type) }}
                                </span>

                                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    <svg class="mr-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            fill-rule="evenodd"
                                            d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    {{ job.location }}
                                </div>
                            </div>
                        </div>

                        <!-- Content Section -->
                        <div class="flex-1 px-6 pb-4">
                            <!-- Description Preview -->
                            <div class="mb-4">
                                <div
                                    class="line-clamp-3 text-sm text-gray-600 dark:text-gray-400"
                                    v-html="truncateDescription(job.description, 150)"
                                ></div>
                            </div>

                            <!-- Key Qualifications Preview -->
                            <div v-if="job.qualifications && job.qualifications.length > 0" class="mb-4">
                                <h4 class="mb-2 text-sm font-semibold text-gray-800 dark:text-gray-200">Key Requirements:</h4>
                                <ul class="space-y-1">
                                    <li
                                        v-for="(qualification, index) in job.qualifications.slice(0, 2)"
                                        :key="index"
                                        class="flex items-start text-xs text-gray-600 dark:text-gray-400"
                                    >
                                        <div class="mt-1.5 mr-2 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-blue-500"></div>
                                        <span class="line-clamp-1">{{ qualification }}</span>
                                    </li>
                                    <li v-if="job.qualifications.length > 2" class="text-xs text-gray-500 italic dark:text-gray-400">
                                        +{{ job.qualifications.length - 2 }} more requirements
                                    </li>
                                </ul>
                            </div>
                        </div>

                      <!-- Footer Section -->
                      <div class="mt-auto border-t border-gray-100 p-6 pt-4 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-3">
                          <Link
                              :href="route('health-jobs.show', job.uuid)"
                              class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm font-semibold text-gray-700 transition-all duration-200 hover:border-blue-600 hover:bg-blue-600 hover:text-white focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-600 dark:text-gray-300 dark:hover:border-blue-600 dark:hover:bg-blue-600 dark:hover:text-white"
                          >
                            <span>View Details</span>
                            <svg class="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                          </Link>

                          <div v-if="job.user !== null && job.user && job.user.license_status !== 'active'" class="flex items-center justify-end">
            <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-100 px-2 py-1 text-xs font-medium text-amber-800 dark:border-amber-800/50 dark:bg-amber-900/30 dark:text-amber-300">
                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                User Unverified
            </span>
                          </div>
                        </div>

                        <!-- New fields from WhatsApp pipeline -->
                        <div class="space-y-1 text-xs text-gray-500 dark:text-gray-400">
                          <div v-if="job.deadline" class="flex items-center gap-1">
                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                              <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                            Deadline: {{ job.deadline }}
                          </div>
                          <div v-if="job.contact_phone" class="flex items-center gap-1">
                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                              <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                            </svg>
                            {{ job.contact_phone }}
                          </div>
                          <div v-if="job.contact_email" class="flex items-center gap-1">
                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                              <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                              <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                            {{ job.contact_email }}
                          </div>
                          <div v-if="job.contract_duration" class="flex items-center gap-1">
                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                              <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                            </svg>
                            {{ job.contract_duration }}
                          </div>
                        </div>

                        <div v-if="user.roles[0].name === 'super-admin' && job.user" class="mt-3 border-t border-gray-100 pt-3 dark:border-gray-700">
                          <small class="text-gray-500">Posted By: {{ job.user.email }}</small>
                        </div>
                      </div>

                      <div v-if="user.roles[0].name === 'super-admin' && job.user"  class="mt-auto border-t border-gray-100 p-6 pt-4 lg:grid lg:grid-cols-1 lg:grid-cols-2 dark:border-gray-700">
                        <small>Posted By : {{job.user.email}}</small>
                      </div>
                    </div>
                </div>

                <!-- Pagination Section -->
                <div v-if="props.jobs.last_page > 1" class="mt-8 flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-1 justify-between sm:hidden">
                        <!-- Mobile pagination -->
                        <Link
                            v-if="props.jobs.prev_page_url"
                            :href="props.jobs.prev_page_url"
                            class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                            preserve-state
                        >
                            Previous
                        </Link>
                        <span v-else class="relative inline-flex items-center rounded-md border border-gray-300 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400 dark:border-gray-600 dark:bg-gray-700">
                            Previous
                        </span>

                        <Link
                            v-if="props.jobs.next_page_url"
                            :href="props.jobs.next_page_url"
                            class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                            preserve-state
                        >
                            Next
                        </Link>
                        <span v-else class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400 dark:border-gray-600 dark:bg-gray-700">
                            Next
                        </span>
                    </div>

                    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Showing
                                <span class="font-medium">{{ props.jobs.from || 0 }}</span>
                                to
                                <span class="font-medium">{{ props.jobs.to || 0 }}</span>
                                of
                                <span class="font-medium">{{ props.jobs.total || 0 }}</span>
                                results
                            </p>
                        </div>
                        <div>
                            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                <!-- Previous page button -->
                                <Link
                                    v-if="props.jobs.prev_page_url"
                                    :href="props.jobs.prev_page_url"
                                    class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 dark:ring-gray-600 dark:hover:bg-gray-700"
                                    preserve-state
                                >
                                    <span class="sr-only">Previous</span>
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                                    </svg>
                                </Link>
                                <span v-else class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-300 ring-1 ring-inset ring-gray-300 dark:text-gray-600 dark:ring-gray-600">
                                    <span class="sr-only">Previous</span>
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                                    </svg>
                                </span>

                                <!-- Page numbers -->
                                <template v-for="page in getPageRange()" :key="page">
                                    <Link
                                        v-if="page === props.jobs.current_page"
                                        :href="getPageUrl(page)"
                                        class="relative z-10 inline-flex items-center bg-blue-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                                        preserve-state
                                    >
                                        {{ page }}
                                    </Link>
                                    <Link
                                        v-else
                                        :href="getPageUrl(page)"
                                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 dark:text-gray-300 dark:ring-gray-600 dark:hover:bg-gray-700"
                                        preserve-state
                                    >
                                        {{ page }}
                                    </Link>
                                </template>

                                <!-- Next page button -->
                                <Link
                                    v-if="props.jobs.next_page_url"
                                    :href="props.jobs.next_page_url"
                                    class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 dark:ring-gray-600 dark:hover:bg-gray-700"
                                    preserve-state
                                >
                                    <span class="sr-only">Next</span>
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                    </svg>
                                </Link>
                                <span v-else class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-300 ring-1 ring-inset ring-gray-300 dark:text-gray-600 dark:ring-gray-600">
                                    <span class="sr-only">Next</span>
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
