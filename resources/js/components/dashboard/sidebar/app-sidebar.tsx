"use client";

import { useEffect } from "react";
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
  useSidebar,
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

  const { setOpenMobile } = useSidebar();
  const page = usePage();
  const { auth } = page.props as any;
  const url = page.url;
  const user = auth?.user;
  const userRole = user?.role;

  useEffect(() => {
    setOpenMobile(false);
  }, [url, setOpenMobile]);

  const itemsToRender = userRole === "Admin" ? [...adminItems, ...sidebarItems] : sidebarItems;
  const filteredItems = itemsToRender.map(group => {
    return {
      ...group,
      items: group.items.filter(item => {
        if (item.id === "jadwal-manage") {
          return ["Admin", "Chief"].includes(userRole);
        }
        if (item.id === "input-pelanggaran") {
          return ["Admin", "Chief", "Danru"].includes(userRole);
        }
        if (item.id === "catatan-harian") {
          return ["Admin", "Chief", "Danru"].includes(userRole);
        }
        if (item.id === "anggota") {
          return ["Admin", "Chief", "Danru"].includes(userRole);
        }
        if (item.id === "laporan-bulanan") {
          return ["Admin", "Chief"].includes(userRole);
        }
        return true;
      })
    };
  });

  const loggedInUser = user ? {
    id_user: user.id_user,
    name: user.nama_lengkap,
    email: user.role + (user.regu ? ` - ${user.regu}` : ''),
    avatar: '',
  } : {
    id_user: 0,
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
            <SidebarMenuButton size="lg" asChild className="h-12 px-2">
              <Link prefetch={false} href="/" className="flex items-center gap-2.5">
                <div className="flex items-center justify-center shrink-0 w-9 h-9">
                  <img src="/images/logo-app.png" alt="Logo" className="w-full h-full object-contain" />
                </div>
                <span className="font-bold text-base tracking-tight">KPI Security</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarHeader>
      <SidebarContent>
        <NavMain items={filteredItems} />
      </SidebarContent>
      <SidebarFooter>
        <NavUser user={loggedInUser} />
      </SidebarFooter>
    </Sidebar>
  );
}
