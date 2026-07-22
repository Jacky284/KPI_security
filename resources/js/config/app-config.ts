import packageJson from "../../../package.json";

const currentYear = new Date().getFullYear();
const appVersion = (packageJson as unknown as { version: string }).version;

export const APP_CONFIG = {
  name: "KPI Security",
  version: appVersion,
  copyright: `© ${currentYear}, KPI Security.`,
  meta: {
    title: "KPI Security - Modern Next.js Dashboard Starter Template",
    description:
      "KPI Security is a modern security solution that provides comprehensive protection for your digital assets. Our platform offers advanced threat detection, real-time monitoring, and robust security features to safeguard your data and ensure compliance with industry standards.",
  },
};
