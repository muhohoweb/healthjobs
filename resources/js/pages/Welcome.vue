<script setup lang="ts">
import { ref,computed  } from 'vue';

const props = defineProps<{
  featuredJobs: Array<any>;
  jobStats: any;
}>();

const currentIndex = ref(0);

const formatDeadline = (date: string): string => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-KE', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
};

const visibleJobs = computed(() => {
  return props.featuredJobs.slice(currentIndex.value, currentIndex.value + 3);
});

const canGoLeft = computed(() => currentIndex.value > 0);
const canGoRight = computed(() => currentIndex.value + 3 < props.featuredJobs.length);

const goLeft = () => { if (canGoLeft.value) currentIndex.value--; };
const goRight = () => { if (canGoRight.value) currentIndex.value++; };

const truncate = (text: string, max = 100): string => {
  if (!text) return '';
  const plain = text.replace(/<[^>]*>/g, '');
  return plain.length <= max ? plain : plain.substring(0, max) + '...';
};

const mobileMenuOpen = ref(false);

const features = [
    {
        icon: '🇰🇪',
        title: 'Kenya-First Job Matching',
        description:
            'Smart matching prioritises openings across counties—Nairobi, Nakuru, Mombasa, Kisumu, Eldoret—based on your specialty and preferred shifts.',
    },
    {
        icon: '🛡️',
        title: 'License Status: Active ✅',
        description:
            'We only surface clinicians whose professional registration has been confirmed active with the relevant Kenyan councils.',
    },
    {
        icon: '🏥',
        title: 'Verified Facilities',
        description:
            'Partner facilities are vetted and reputable; roles are posted by authorised HR leads from hospitals and clinics in Kenya.',
    },
    {
        icon: '📊',
        title: 'Career & Compliance Tracker',
        description:
            'Track applications, interviews, and expiring documents—KRA PIN, CPD, licenses—so you stay job-ready.',
    },
];

const navigateToLogin = (): void => {
    window.location.href = '/login';
};

const navigateToRegister = (): void => {
    window.location.href = '/register';
};

const closeMobileMenu = (): void => {
    mobileMenuOpen.value = false;
};
</script>

<template>
<!--  <Head title="MediCareers Kenya – Legit Medical Jobs" />-->
    <!-- Kenyan ribbon (subtle flag accent) -->
    <div class="fixed top-0 left-0 right-0 z-[60] h-1">
        <div class="h-full grid grid-cols-12">
            <div class="col-span-3 bg-black"></div>
            <div class="col-span-3 bg-white"></div>
            <div class="col-span-3 bg-red-600"></div>
            <div class="col-span-3 bg-green-600"></div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="fixed w-full z-50 bg-white/95 backdrop-blur-lg border-b border-gray-100 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-2 sm:space-x-3">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center shadow-lg bg-gradient-to-br from-emerald-600 via-red-600 to-black">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path
                                d="M9 2l4 2 3-1 1 3 3 2-2 3 2 3-3 2-1 3-3-1-4 2-1-3-4-2 2-3-2-3 4-2z"
                            />
                        </svg>
                    </div>
                    <span class="font-extrabold text-sm sm:text-base lg:text-xl bg-gradient-to-r from-emerald-700 via-red-600 to-black bg-clip-text text-transparent">
                        MediCareers Kenya
                    </span>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#jobs" class="text-gray-700 hover:text-emerald-700 transition-colors font-medium">Jobs</a>
                    <a href="#about" class="text-gray-700 hover:text-emerald-700 transition-colors font-medium">About</a>
                    <a href="#contact" class="text-gray-700 hover:text-emerald-700 transition-colors font-medium">Contact</a>
                </div>

                <!-- Desktop Actions -->
                <div class="hidden md:flex items-center space-x-4">
                    <button @click="navigateToLogin" class="text-gray-700 hover:text-emerald-700 active:text-emerald-800 transition-colors font-medium px-3 py-2">
                        Login
                    </button>
                    <button
                        @click="navigateToRegister"
                        class="bg-gradient-to-r from-emerald-600 to-green-600 text-white px-6 py-2.5 rounded-full hover:shadow-lg active:shadow-md transform hover:scale-105 active:scale-95 transition-all duration-200 font-medium"
                    >
                        Get Started
                    </button>
                </div>

                <!-- Mobile Actions -->
                <div class="flex items-center space-x-2 md:hidden">
                    <button @click="navigateToLogin" class="text-gray-700 hover:text-emerald-700 active:text-emerald-800 transition-colors font-medium px-2 py-1 text-sm">
                        Login
                    </button>
                    <button
                        @click="navigateToRegister"
                        class="bg-gradient-to-r from-emerald-600 to-green-700 text-white px-3 py-1 sm:px-4 sm:py-2 rounded-full hover:shadow-lg active:shadow-md transform hover:scale-105 active:scale-95 transition-all duration-200 font-medium text-xs sm:text-sm"
                    >
                        Get Started
                    </button>
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 rounded-lg hover:bg-gray-100 active:bg-gray-200 transition-colors touch-manipulation">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Overlay -->
        <div
            v-if="mobileMenuOpen"
            class="md:hidden fixed inset-0 top-16 z-40 bg-black/50 backdrop-blur-sm"
            @click="closeMobileMenu"
        ></div>

        <!-- Mobile Menu -->
        <div
            :class="[
                'md:hidden absolute top-full left-0 right-0 bg-white border-b border-gray-200 shadow-lg transition-all duration-300 ease-in-out',
                mobileMenuOpen ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-2 pointer-events-none'
            ]"
        >
            <div class="px-4 py-6 space-y-4">
                <a href="#jobs" @click="closeMobileMenu" class="block text-gray-700 hover:text-emerald-700 font-medium py-2 transition-colors">
                    🏥 Browse Jobs
                </a>
                <a href="#about" @click="closeMobileMenu" class="block text-gray-700 hover:text-emerald-700 font-medium py-2 transition-colors">
                    ℹ️ About Us
                </a>
                <a href="#contact" @click="closeMobileMenu" class="block text-gray-700 hover:text-emerald-700 font-medium py-2 transition-colors">
                    📞 Contact
                </a>
                <div class="pt-4 border-t border-gray-100">
                    <p class="text-sm text-gray-500 mb-3">🇰🇪 Trusted by 25,000+ Kenyan Medics</p>
                    <div class="flex flex-col space-y-2">
                        <button
                            @click="navigateToRegister(); closeMobileMenu()"
                            class="bg-gradient-to-r from-emerald-600 to-green-700 text-white px-6 py-3 rounded-full font-semibold text-center"
                        >
                            Create Free Profile
                        </button>
                        <a
                            href="https://wa.me/254712419949"
                            target="_blank"
                            rel="noopener"
                            @click="closeMobileMenu"
                            class="flex items-center justify-center space-x-2 bg-green-500 text-white px-6 py-3 rounded-full font-semibold"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M20.52 3.48A11.91 11.91 0 0012 0C5.37 0 0 5.37 0 12c0 2.1.54 4.15 1.56 5.96L0 24l6.31-1.63A11.94 11.94 0 0012 24c6.63 0 12-5.37 12-12 0-3.19-1.24-6.19-3.48-8.52zM12 22a9.94 9.94 0 01-5.1-1.39l-.36-.21-3.74.96.99-3.64-.24-.38A9.94 9.94 0 012 12c0-5.52 4.48-10 10-10s10 4.48 10 10-4.48 10-10 10zm5.28-7.17c-.29-.14-1.71-.84-1.98-.93-.27-.1-.47-.14-.67.14s-.77.93-.94 1.12c-.17.19-.35.21-.64.07-.29-.14-1.22-.45-2.32-1.43-.86-.77-1.44-1.71-1.61-2-.17-.29-.02-.45.13-.59.14-.14.29-.35.43-.52.14-.17.19-.29.29-.48.1-.19.05-.36-.02-.5-.07-.14-.67-1.61-.92-2.21-.24-.58-.49-.5-.67-.51h-.57c-.19 0-.5.07-.76.36-.26.29-1 1-1 2.45s1.03 2.84 1.17 3.04c.14.19 2.03 3.09 4.92 4.33.69.3 1.22.48 1.64.61.69.22 1.31.19 1.8.12.55-.08 1.71-.7 1.95-1.38.24-.67.24-1.24.17-1.38-.07-.14-.26-.22-.55-.36z"
                                />
                            </svg>
                            <span>WhatsApp Support</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-20 sm:pt-24 pb-16 sm:pb-28 bg-gradient-to-br from-emerald-50 via-white to-green-50 overflow-hidden">
        <!-- Decorative circles in Kenyan palette -->
        <div class="absolute top-24 left-4 sm:left-10 w-12 h-12 sm:w-16 sm:h-16 bg-emerald-200/40 rounded-full"></div>
        <div class="absolute top-32 right-8 sm:right-20 w-10 h-10 sm:w-14 sm:h-14 bg-red-200/40 rounded-full"></div>
        <div class="absolute bottom-40 left-8 sm:left-20 w-8 h-8 sm:w-12 sm:h-12 bg-black/10 rounded-full"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center min-h-[60vh] lg:min-h-[70vh]">
                <!-- Left Content -->
                <div class="space-y-6 sm:space-y-8 text-center lg:text-left">
                    <div class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2 bg-emerald-100 rounded-full text-emerald-800 text-xs sm:text-sm font-medium">
                        🇰🇪 Karibu! Trusted by 25,000+ Kenyan Medics
                    </div>

                    <h1 class="text-3xl sm:text-5xl md:text-6xl lg:text-7xl font-black leading-tight">
                        Get Legit!!
                        <span class="bg-gradient-to-r from-emerald-700 via-red-600 to-black bg-clip-text text-transparent block">
                            Medical Jobs in Kenya
                        </span>
                    </h1>

                    <p class="text-base sm:text-lg md:text-xl text-gray-700 leading-relaxed max-w-xl mx-auto lg:mx-0">
                        We bridge the gap between healthcare institutions,
                        pharmaceutical companies, and research organizations by connecting
                        them with qualified professionals to address the increasing demand for skilled personnel in these fields. <span class="font-semibold text-emerald-700">confirmed active</span>.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center lg:justify-start">
                        <button
                            @click="navigateToRegister"
                            class="bg-gradient-to-r from-emerald-600 to-green-700 text-white px-6 py-3 sm:px-8 sm:py-4 rounded-full text-base sm:text-lg font-semibold hover:shadow-2xl transform hover:scale-105 transition-all duration-300 flex items-center justify-center space-x-2"
                        >
                            <span>Find Roles Near You</span>
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </button>
                        <a
                            href="#jobs"
                            class="border-2 border-gray-300 text-gray-700 px-6 py-3 sm:px-8 sm:py-4 rounded-full text-base sm:text-lg font-semibold hover:border-emerald-600 hover:text-emerald-700 transition-all duration-300 flex items-center justify-center space-x-2"
                        >
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1M12 6v4"></path>
                            </svg>
                            <span>Browse Jobs</span>
                        </a>
                    </div>


                </div>

                <!-- Right Content - Hero Images -->
                <div class="relative mt-8 lg:mt-0">
                    <div class="relative z-10">
                        <div class="relative group">
                            <div class="absolute -inset-1 bg-gradient-to-r from-emerald-600 to-red-600 rounded-2xl blur opacity-30 group-hover:opacity-50 transition duration-1000"></div>
                            <div class="relative bg-white rounded-2xl overflow-hidden shadow-2xl">
                                <img
                                    src="https://guardianhospitalmeru.com/wp-content/uploads/2023/06/WhatsApp-Image-2023-09-11-at-11.10.43-1.jpeg"
                                    alt="Kenyan healthcare professionals"
                                    class="w-full h-64 sm:h-80 lg:h-96 object-cover"
                                />
                                <div class="absolute inset-0 bg-gradient-to-t from-black/25 to-transparent"></div>
                            </div>
                        </div>

                        <!-- Floating cards - responsive positioning -->
                        <div class="absolute -top-4 -left-4 sm:-top-6 sm:-left-6 bg-white rounded-xl p-3 sm:p-4 shadow-lg">
                            <div class="flex items-center space-x-2 sm:space-x-3">
                                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-emerald-50 rounded-full grid place-items-center">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-700" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2a10 10 0 1010 10A10.011 10.011 0 0012 2zm-1 15l-4-4 1.414-1.414L11 13.172l5.293-5.293L18.707 9z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-xs sm:text-sm font-semibold text-gray-900">Job Match in Kenya</div>
                                    <div class="text-xs text-gray-500">96% compatible</div>
                                </div>
                            </div>
                        </div>

                        <div class="absolute -bottom-3 -right-3 sm:-bottom-4 sm:-right-4 bg-white rounded-xl p-3 sm:p-4 shadow-lg">
                            <div class="flex items-center space-x-2 sm:space-x-3">
                                <img
                                    src="https://www.internationalinsurance.com/wp-content/uploads/2020/05/health-insurance-in-kenya-350x233.avif"
                                    alt="Kenyan medic"
                                    class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover"
                                />
                                <div>
                                    <div class="text-xs sm:text-sm font-semibold text-gray-900">Dr. Tom Odhiambo</div>
                                    <div class="text-xs text-gray-500">Hired in Nairobi 🥳</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wave Bottom -->
        <div class="absolute bottom-0 left-0 w-full overflow-hidden leading-none">
            <svg class="relative block w-full h-12 sm:h-24" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path
                    d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z"
                    opacity=".25"
                    fill="#ffffff"
                ></path>
                <path
                    d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z"
                    opacity=".5"
                    fill="#ffffff"
                ></path>
                <path
                    d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z"
                    fill="#ffffff"
                ></path>
            </svg>
        </div>
    </section>

    <!-- New Segment: Need a Nurse for Professional Health Care -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 bg-gradient-to-r from-green-400 to-blue-500 text-white rounded-xl shadow-xl">
        <div class="text-center">
            <h2 class="text-3xl sm:text-4xl font-semibold leading-tight mb-4">
                Looking for a Professional Nurse?
            </h2>
            <p class="text-lg sm:text-xl mb-6 max-w-3xl mx-auto text-gray-100">
                Whether it's for routine care or specialized services, we have a pool of verified and highly trained nurses available to provide professional healthcare tailored to your needs.
            </p>
            <button
                @click="navigateToRegister"
                class="bg-white text-emerald-700 px-8 py-4 rounded-full font-semibold text-lg shadow-md hover:bg-emerald-100 hover:scale-105 transition-transform duration-300"
            >
                Connect with a Nurse Today
            </button>
        </div>
    </div>


    <!-- Hello / Mission Section -->
    <section id="about" class="relative bg-white py-16 sm:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 sm:mb-14">
                <div
                    class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2 bg-gradient-to-r from-emerald-100 to-red-100 rounded-full text-emerald-800 text-xs sm:text-sm font-medium mb-6"
                >
                    👋 Karibu, Shujaa wa Afya
                </div>
                <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6">
                    Empowering Medics
                    <span class="bg-gradient-to-r from-emerald-700 via-red-600 to-black bg-clip-text text-transparent block">
                        Elevating Patient Care
                    </span>
                </h2>
                <p class="text-lg sm:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                    Join the community of healthcare professionals in Kenya. We connect you with genuine opportunities, verified employers, and a system that helps you maintain the validity of your license
                </p>
            </div>

            <!-- Three culture/impact cards -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 mb-12">
                <div class="group transform hover:scale-105 transition-all duration-500">
                    <div class="bg-white rounded-2xl overflow-hidden shadow-xl">
                        <img
                            src="https://ichef.bbci.co.uk/ace/ws/640/cpsprodpb/30FE/production/_124724521_asterguardiansresavedwhitebackground.jpg.webp"
                            alt="Clinician with patient in Kenya"
                            class="w-full h-48 sm:h-56 lg:h-64 object-cover"
                        />
                        <div class="p-4 sm:p-6">
                            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-2">Compassion and Integrity</h3>
                            <p class="text-gray-600">Relationships that build trust and healing.</p>
                        </div>
                    </div>
                </div>

                <div class="group transform hover:scale-105 transition-all duration-500">
                    <div class="bg-white rounded-2xl overflow-hidden shadow-xl">
                        <img
                            src="https://jessekayhospital.com/wp-content/uploads/elementor/thumbs/Consultation_11zon-qwq0ex9lorndpw6rbe20x6kgnzvcfvv060tafenyq0.jpg"
                            alt="Healthcare teamwork Kenya"
                            class="w-full h-48 sm:h-56 lg:h-64 object-cover"
                        />
                        <div class="p-4 sm:p-6">
                            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-2">Team Work</h3>
                            <p class="text-gray-600">Delivering excellence through teamwork.</p>
                        </div>
                    </div>
                </div>

                <div class="group transform hover:scale-105 transition-all duration-500 sm:col-span-2 lg:col-span-1">
                    <div class="bg-white rounded-2xl overflow-hidden shadow-xl">
                        <img
                            src="https://www.internationalinsurance.com/wp-content/uploads/2020/05/health-insurance-in-kenya-scaled.jpg"
                            alt="Pediatric care Kenya"
                            class="w-full h-48 sm:h-56 lg:h-64 object-cover"
                        />
                        <div class="p-4 sm:p-6">
                            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-2">A Better Tomorrow</h3>
                            <p class="text-gray-600">Nurturing future generations through service.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <button
                    @click="navigateToRegister"
                    class="bg-gradient-to-r from-emerald-600 to-green-700 text-white px-8 py-3 sm:px-10 sm:py-4 rounded-full text-base sm:text-lg font-semibold hover:shadow-2xl transform hover:scale-105 transition-all duration-300"
                >
                    Get started today
                </button>
            </div>
        </div>
    </section>

    <!-- Jobs Showcase Section -->
    <section id="jobs" class="py-16 sm:py-24 bg-white relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 sm:mb-16">
                <div
                    class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2 bg-gradient-to-r from-emerald-100 to-green-100 rounded-full text-emerald-700 text-xs sm:text-sm font-medium mb-6"
                >
                    🏥 Nationwide Healthcare Openings
                </div>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-6">
                    Discover
                    <span class="bg-gradient-to-r from-emerald-700 via-red-600 to-black bg-clip-text text-transparent">Trusted Medical Careers</span>
                </h2>
                <p class="text-base sm:text-lg md:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                    Explore opportunities with accredited hospitals and clinics across Kenya. Transparent salary ranges in KES.
                    Build your professional profile to unlock full details and apply seamlessly in one step.
                </p>
            </div>

          <!-- Carousel Navigation -->
          <div class="flex items-center justify-between mb-6">
            <button
                @click="goLeft"
                :disabled="!canGoLeft"
                :class="[
            'flex items-center justify-center w-10 h-10 rounded-full border-2 transition-all duration-200',
            canGoLeft
                ? 'border-emerald-600 text-emerald-600 hover:bg-emerald-600 hover:text-white'
                : 'border-gray-200 text-gray-300 cursor-not-allowed'
        ]"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
              </svg>
            </button>

            <span class="text-sm text-gray-500">
        Showing {{ currentIndex + 1 }}–{{ Math.min(currentIndex + 3, featuredJobs.length) }} of {{ featuredJobs.length }} recent jobs
    </span>

            <button
                @click="goRight"
                :disabled="!canGoRight"
                :class="[
            'flex items-center justify-center w-10 h-10 rounded-full border-2 transition-all duration-200',
            canGoRight
                ? 'border-emerald-600 text-emerald-600 hover:bg-emerald-600 hover:text-white'
                : 'border-gray-200 text-gray-300 cursor-not-allowed'
        ]"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </button>
          </div>

          <!-- Job Cards -->
          <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 mb-8 sm:mb-12">
            <div
                v-for="job in visibleJobs"
                :key="job.id"
                class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-emerald-500/20"
            >
              <div class="relative p-4 sm:p-6 pb-3 sm:pb-4">
                <div class="flex items-start justify-between mb-4">
                  <div class="flex-1 pr-4">
                    <h3 class="text-lg sm:text-xl font-bold leading-tight text-gray-900 group-hover:text-emerald-700 mb-2">
                      {{ job.title }}
                    </h3>
                    <div class="flex items-center space-x-2 mb-2 text-xs text-gray-500">
                      <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                      </svg>
                      <span>{{ job.location ?? 'Kenya' }}</span>
                    </div>
                  </div>
                  <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-1 sm:px-3 sm:py-1.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200/50 capitalize">
                    {{ job.job_type ?? 'Full-time' }}
                </span>
                </div>

                <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                  {{ truncate(job.description, 120) }}
                </p>
              </div>

              <div class="flex-1 px-4 sm:px-6">
                <div class="mt-3 sm:mt-4 rounded-lg border border-emerald-100 bg-emerald-50/40 p-3 sm:p-4">
                  <h4 class="mb-2 text-xs sm:text-sm font-semibold text-emerald-800">Key Requirements</h4>
                  <ul class="space-y-1">
                    <li v-for="q in (job.qualifications ?? []).slice(0, 2)" :key="q" class="text-xs text-emerald-800">
                      {{ q }}
                    </li>
                    <li class="text-xs font-medium text-emerald-700">Sign up to view all</li>
                  </ul>
                </div>
              </div>

              <div class="mt-auto border-t border-gray-50 p-4 sm:p-6 pt-3 sm:pt-4">
                <div class="flex items-center justify-between">
                  <div class="space-y-1">
                    <div class="text-xs sm:text-sm font-semibold text-gray-600">
                        <span v-if="job.salary_min && job.salary_max">
                            KES {{ Number(job.salary_min).toLocaleString() }} – {{ Number(job.salary_max).toLocaleString() }}
                        </span>
                      <span v-else class="text-gray-400">KES ••••</span>
                    </div>
                    <div class="text-xs text-gray-500">Posted {{ job.created_at }}</div>
                  </div>
                  <button
                      @click="navigateToRegister"
                      class="inline-flex items-center space-x-2 rounded-xl bg-emerald-600 px-3 py-2 sm:px-5 sm:py-2.5 text-xs sm:text-sm font-semibold text-white shadow-lg hover:bg-emerald-700"
                  >
                    <span>Unlock Details</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Empty state -->
            <div v-if="featuredJobs.length === 0" class="col-span-3 text-center py-16 text-gray-400">
              <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
              <p class="text-sm">No new jobs in the last 5 days. Check back soon!</p>
            </div>
          </div>

            <!-- View All Jobs CTA -->
            <div class="text-center">
                <div class="inline-flex items-center px-4 py-2 sm:px-6 sm:py-3 bg-gradient-to-r from-gray-100 to-gray-200 rounded-full text-gray-600 text-xs sm:text-sm font-medium mb-6">
                    📈 New Kenyan jobs posted daily • 10,000+ active positions
                </div>
                <div class="space-y-4">
                    <button
                        @click="navigateToRegister"
                        class="bg-gradient-to-r from-emerald-600 to-green-700 text-white px-8 py-3 sm:px-10 sm:py-4 rounded-full text-base sm:text-lg font-semibold hover:shadow-2xl transform hover:scale-105 transition-all duration-300 mr-0 sm:mr-4 block sm:inline-block"
                    >
                        View All Roles
                    </button>
                    <p class="text-gray-500 text-xs sm:text-sm max-w-md mx-auto">
                        Create a free account to access full job descriptions, visible facility names, and one-click applications.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 sm:py-24 bg-slate-50 relative">
        <!-- Wave Top -->
        <div class="absolute top-0 left-0 w-full overflow-hidden leading-none rotate-180">
            <svg class="relative block w-full h-12 sm:h-24" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path
                    d="M985.66,92.83C906.67,72,823.78,31,743.84,14.19c-82.26-17.34-168.06-16.33-250.45.39-57.84,11.73-114,31.07-172,41.86A600.21,600.21,0,0,1,0,27.35V120H1200V95.8C1132.19,118.92,1055.71,111.31,985.66,92.83Z"
                    fill="#f8fafc"
                ></path>
            </svg>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 sm:pt-16">
            <div class="text-center mb-12 sm:mb-16">
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-6">
                    Why Choose
                    <span class="bg-gradient-to-r from-emerald-700 via-red-600 to-black bg-clip-text text-transparent">MediCareers Kenya?</span>
                </h2>
                <p class="text-lg sm:text-xl text-gray-600 max-w-3xl mx-auto">
                    Technology built for Kenyan medics—grounded in trust, verification, and county-wide opportunity.
                </p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
                <div
                    v-for="feature in features"
                    :key="feature.title"
                    class="bg-gradient-to-br from-white to-gray-50 p-6 sm:p-8 rounded-2xl border border-gray-100 hover:shadow-xl hover:border-emerald-200 transition-all duration-300"
                >
                    <div class="text-3xl sm:text-4xl mb-4">{{ feature.icon }}</div>
                    <h3 class="text-lg sm:text-xl font-bold mb-3 text-gray-900">{{ feature.title }}</h3>
                    <p class="text-sm sm:text-base text-gray-600 leading-relaxed">{{ feature.description }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section id="contact" class="py-16 sm:py-24 bg-gradient-to-r from-emerald-700 via-red-600 to-black relative overflow-hidden">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8 relative z-10">
            <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold text-white mb-6 leading-tight">
                Ready to build your career in Kenya's health sector?
            </h2>

            <p class="text-lg sm:text-xl text-emerald-100 mb-8 sm:mb-12 leading-relaxed">
                Join thousands of Kenyan medics placed in legitimate roles. Profiles with confirmed active licenses are highlighted to employers.
            </p>

            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center items-center">
                <button
                    @click="navigateToRegister"
                    class="bg-white text-emerald-700 px-8 py-3 sm:px-10 sm:py-4 rounded-full text-base sm:text-lg font-bold hover:shadow-2xl transform hover:scale-105 transition-all duration-300 flex items-center space-x-2 w-full sm:w-auto justify-center"
                >
                    <span>Create Free Profile</span>
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </button>

                <a
                    href="https://wa.me/254712419949"
                    target="_blank"
                    rel="noopener"
                    class="flex items-center justify-center space-x-2 bg-green-500 text-white px-6 py-3 sm:px-8 sm:py-4 rounded-full text-base sm:text-lg font-semibold hover:bg-green-600 transition-all duration-300 w-full sm:w-auto"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M20.52 3.48A11.91 11.91 0 0012 0C5.37 0 0 5.37 0 12c0 2.1.54 4.15 1.56 5.96L0 24l6.31-1.63A11.94 11.94 0 0012 24c6.63 0 12-5.37 12-12 0-3.19-1.24-6.19-3.48-8.52zM12 22a9.94 9.94 0 01-5.1-1.39l-.36-.21-3.74.96.99-3.64-.24-.38A9.94 9.94 0 012 12c0-5.52 4.48-10 10-10s10 4.48 10 10-4.48 10-10 10zm5.28-7.17c-.29-.14-1.71-.84-1.98-.93-.27-.1-.47-.14-.67.14s-.77.93-.94 1.12c-.17.19-.35.21-.64.07-.29-.14-1.22-.45-2.32-1.43-.86-.77-1.44-1.71-1.61-2-.17-.29-.02-.45.13-.59.14-.14.29-.35.43-.52.14-.17.19-.29.29-.48.1-.19.05-.36-.02-.5-.07-.14-.67-1.61-.92-2.21-.24-.58-.49-.5-.67-.51h-.57c-.19 0-.5.07-.76.36-.26.29-1 1-1 2.45s1.03 2.84 1.17 3.04c.14.19 2.03 3.09 4.92 4.33.69.3 1.22.48 1.64.61.69.22 1.31.19 1.8.12.55-.08 1.71-.7 1.95-1.38.24-.67.24-1.24.17-1.38-.07-.14-.26-.22-.55-.36z"
                        />
                    </svg>
                    <span>Chat on WhatsApp</span>
                </a>
            </div>

            <!-- Legal note -->
            <p class="mt-4 sm:mt-6 text-xs text-emerald-100/80 max-w-2xl mx-auto">
                Note: "License confirmed active" refers to a successful status check with the relevant Kenyan professional council at the time of verification.
            </p>
        </div>
    </section>
</template>
