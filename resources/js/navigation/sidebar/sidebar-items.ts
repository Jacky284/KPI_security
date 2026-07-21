import {
  Banknote,
  Calendar,
  ChartBar,
  CheckSquare,
  Fingerprint,
  Forklift,
  Gauge,
  GraduationCap,
  Kanban,
  LayoutDashboard,
  ListTodo,
  Lock,
  type LucideIcon,
  Mail,
  MessageSquare,
  ReceiptText,
  Server,
  ShoppingBag,
  SquareArrowUpRight,
  Users,
} from "lucide-react";

export type NavBadge = "new" | "soon";

export interface NavSubItem {
  id: string;
  title: string;
  url: string;
  icon?: LucideIcon;
  badge?: NavBadge;
  disabled?: boolean;
  newTab?: boolean;
}

interface NavItemBase {
  id: string;
  title: string;
  icon?: LucideIcon;
  badge?: NavBadge;
  disabled?: boolean;
  newTab?: boolean;
}

export interface NavMainLinkItem extends NavItemBase {
  url: string;
  subItems?: never;
}

export interface NavMainParentItem extends NavItemBase {
  subItems: NavSubItem[];
}

export type NavMainItem = NavMainLinkItem | NavMainParentItem;

export interface NavGroup {
  id: number;
  label?: string;
  items: NavMainItem[];
}

export const sidebarItems: NavGroup[] = [
  {
    id: 1,
    label: "",
    items: [
      {
        id: "default",
        title: "Default Dashboard",
        url: "/",
        icon: LayoutDashboard,
      },
    ],
  },
  {
    id: 2,
    label: "Manajemen & Operasional",
    items: [
      {
        id: "anggota",
        title: "Manajemen Anggota",
        url: "/anggota",
        icon: Users,
      },
      {
        id: "jadwal-manage",
        title: "Manajemen Jadwal",
        url: "/jadwal/manage",
        icon: Calendar,
      },
      {
        id: "input-pelanggaran",
        title: "Input Pelanggaran",
        url: "/pelanggaran",
        icon: ListTodo,
      },
      {
        id: "daftar-pelanggaran",
        title: "Daftar Pelanggaran",
        url: "/pelanggaran/daftar",
        icon: CheckSquare,
      },
    ],
  },
  {
    id: 3,
    label: "Laporan & Evaluasi",
    items: [
      {
        id: "laporan-mingguan",
        title: "Laporan Mingguan",
        url: "/laporan/mingguan",
        icon: ReceiptText,
      },
      {
        id: "laporan-bulanan",
        title: "Laporan Bulanan",
        url: "/laporan/bulanan",
        icon: ReceiptText,
      },
    ],
  },
];
