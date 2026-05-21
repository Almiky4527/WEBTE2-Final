<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    FileCode2,
    FolderGit2,
    Move3d,
    ScrollText,
    Terminal,
    Waves,
} from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    { title: 'nav.console', href: '/console', icon: Terminal },
    { title: 'nav.pendulum', href: '/pendulum', icon: Move3d },
    { title: 'nav.ballBeam', href: '/ball-beam', icon: Waves },
    { title: 'nav.stats', href: '/stats', icon: ScrollText },
    { title: 'nav.apiDocs', href: '/api-docs', icon: FileCode2 },
];

const footerNavItems: NavItem[] = [
    {
        title: 'nav.repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'nav.documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
