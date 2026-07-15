"use client";

import { Link, usePage } from "@inertiajs/react";
import { CircleHelp, ClipboardList, Command, Database, File, Search, Settings, Users, LayoutDashboard, ListTodo, ReceiptText } from "lucide-react";
import { useShallow } from "zustand/react/shallow";

import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from "@/components/ui/sidebar";
import { APP_CONFIG } from "@/config/app-config";
import { sidebarItems } from "@/navigation/sidebar/sidebar-items";
import { usePreferencesStore } from "@/stores/preferences/preferences-provider";

import { NavMain } from "./nav-main";
import { NavUser } from "./nav-user";

const adminItems = [
  {
    id: 1,
    label: "Admin Panel",
    items: [
      {
        id: "admin-users",
        title: "Manajemen User",
        url: "/admin/users",
        icon: Users,
      },
    ],
  },
];

export function AppSidebar({ ...props }: React.ComponentProps<typeof Sidebar>) {
  const { sidebarVariant, sidebarCollapsible, isSynced } = usePreferencesStore(
    useShallow((s) => ({
      sidebarVariant: s.values.sidebar_variant,
      sidebarCollapsible: s.values.sidebar_collapsible,
      isSynced: s.isSynced,
    })),
  );

  const { auth } = usePage().props as any;
  const user = auth?.user;
  const userRole = user?.role;

  const itemsToRender = userRole === "Admin" ? [...adminItems, ...sidebarItems] : sidebarItems;

  const loggedInUser = user ? {
    name: user.nama_lengkap,
    email: user.role + (user.regu ? ` - ${user.regu}` : ''),
    avatar: '',
  } : {
    name: 'Guest',
    email: 'Guest',
    avatar: '',
  };

  const variant = isSynced ? sidebarVariant : props.variant;
  const collapsible = isSynced ? sidebarCollapsible : props.collapsible;

  return (
    <Sidebar {...props} variant={variant} collapsible={collapsible}>
      <SidebarHeader>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton asChild>
              <Link prefetch={false} href="/">
                <Command />
                <span className="font-semibold text-base">{APP_CONFIG.name}</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarHeader>
      <SidebarContent>
        <NavMain items={itemsToRender} />
      </SidebarContent>
      <SidebarFooter>
        <NavUser user={loggedInUser} />
      </SidebarFooter>
    </Sidebar>
  );
}
