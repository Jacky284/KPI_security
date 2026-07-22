"use client";

import { LogOut } from "lucide-react";
import { router } from "@inertiajs/react";

import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { SidebarMenu, SidebarMenuButton, SidebarMenuItem, useSidebar } from "@/components/ui/sidebar";
import { getInitials } from "@/lib/utils";

export function NavUser({
  user,
}: {
  readonly user: {
    readonly name: string;
    readonly email: string;
    readonly avatar: string;
  };
}) {
  const { isMobile } = useSidebar();

  return (
    <SidebarMenu>
      <SidebarMenuItem>
        <SidebarMenuButton
          size="lg"
          className="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground cursor-default"
        >
          <Avatar className="h-8 w-8 rounded-lg grayscale">
            <AvatarImage src={user.avatar || undefined} alt={user.name} />
            <AvatarFallback className="rounded-lg">{getInitials(user.name)}</AvatarFallback>
          </Avatar>
          <div className="grid flex-1 text-left text-sm leading-tight">
            <span className="truncate font-medium">{user.name}</span>
            <span className="truncate text-muted-foreground text-xs">{user.email}</span>
          </div>
          <div
            onClick={(e) => { e.preventDefault(); router.post('/logout'); }}
            className="ml-auto p-2 rounded-md text-muted-foreground hover:bg-destructive/10 hover:text-destructive transition-colors cursor-pointer"
            title="Log out"
          >
            <LogOut className="size-4" />
          </div>
        </SidebarMenuButton>
      </SidebarMenuItem>
    </SidebarMenu>
  );
}
