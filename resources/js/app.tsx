import './bootstrap';
import '../css/app.css';

import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

import { TooltipProvider } from '@/components/ui/tooltip';
import { Toaster } from '@/components/ui/sonner';
import { PREFERENCE_DEFAULTS } from '@/lib/preferences/preferences-config';
import { PreferencesStoreProvider } from '@/stores/preferences/preferences-provider';

const appName = import.meta.env.VITE_APP_NAME || 'Studio Admin';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.tsx`, import.meta.glob('./Pages/**/*.tsx')),
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(
            <TooltipProvider>
                <PreferencesStoreProvider initialValues={PREFERENCE_DEFAULTS}>
                    <App {...props} />
                    <Toaster />
                </PreferencesStoreProvider>
            </TooltipProvider>
        );
    },
    progress: {
        color: '#4B5563',
    },
});
