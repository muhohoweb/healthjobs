<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem
} from '@/components/ui/sidebar';
import {type NavItem} from '@/types';
import {Link} from '@inertiajs/vue3';
import {
  LayoutGrid,
  Settings,
  FileText,
  Calendar1Icon,
  UsersRoundIcon,
  HouseIcon,
  ShieldAlertIcon,
  StethoscopeIcon,
  CircleUserIcon,
  PencilIcon, PackageIcon, BadgeCheckIcon,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import {computed} from 'vue';
import {useAuth} from '@/utils/auth';

const user = useAuth();

const allNavItems = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutGrid,
  },
  {
    title: 'Jobs',
    href: '/health-jobs',
    icon: StethoscopeIcon,
    requiredPermissions: ['view-job-postings'],
  },
  {
    title: 'Profiles',
    href: '/medics/profiles',
    icon: CircleUserIcon,
    requiredPermissions: ['view-job-postings'],
  },
  {
    title: 'Events',
    href: '/events',
    disabled: true,
    icon: Calendar1Icon,
    requiredPermissions: ['view-job-postings'],
  },

  {
    title: 'Feedback',
    href: '/feedback',
    disabled: true,
    icon: PencilIcon,
    requiredPermissions: ['view-job-postings'],
  },
  {
    title: 'Users',
    href: '/iam',
    icon: UsersRoundIcon,
    requiredRoles: ['super-admin'],
  },
  {
    title: 'Packages',
    href: '/admin/packages',
    icon: PackageIcon,
    requiredRoles: ['super-admin'],
  },
  {
    title: 'Payments',
    href: '/admin/payments',
    icon: PackageIcon,
    requiredRoles: ['super-admin'],
  },
  {
    title: 'Subscription',
    href: '/subscriptions',
    icon: BadgeCheckIcon,
  },
  {
    title: 'Roles',
    href: '/iam/roles',
    icon: ShieldAlertIcon,
    requiredRoles: ['super-admin'],
  },
  {
    title: 'Manage Facility',
    href: '/facilities',
    icon: HouseIcon,
    requiredRoles: ['super-admin'],
  },
  {
    title: 'Reports',
    href: '/reports',
    icon: FileText,
    requiredPermissions: ['view reports', 'generate reports'],
    requireAnyPermission: false,
  },
  {
    title: 'Settings',
    href: '/settings',
    icon: Settings,
    requiredRoles: ['admin'],
    requiredPermissions: ['manage settings'],
  },
];

const mainNavItems = computed((): NavItem[] => {
  return allNavItems.filter((item) => {
    const requirements = {
      requiredRoles: item.requiredRoles || [],
      requiredPermissions: item.requiredPermissions || [],
      requireAnyRole: item.requireAnyRole || false,
      requireAnyPermission: item.requireAnyPermission || false,
    };

    return user.canAccess(requirements);
  });
});

const footerNavItems: NavItem[] = [];
</script>

<template>
  <Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton size="lg" as-child>
            <Link :href="route('dashboard')">
              <AppLogo/>
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
      <NavMain :items="mainNavItems"/>
    </SidebarContent>

    <SidebarFooter>
      <NavFooter :items="footerNavItems"/>
      <NavUser/>
    </SidebarFooter>
  </Sidebar>
  <slot/>
</template>
