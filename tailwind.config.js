import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: ["class"],
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./resources/js/components/**/*.{ts,tsx,vue}",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
    ],

    theme: {
    	extend: {
    		fontFamily: {
    			sans: [
    				'Figtree',
                    ...defaultTheme.fontFamily.sans
                ]
    		},
    		borderRadius: {
    			lg: 'var(--radius)',
    			md: 'calc(var(--radius) - 2px)',
    			sm: 'calc(var(--radius) - 4px)'
    		},
    		colors: {
    			primary: {
    				'50': '#ecfdf5',
    				'100': '#d1fae5',
    				'200': '#a7f3d0',
    				'300': '#6ee7b7',
    				'400': '#34d399',
    				'500': '#10b981',
    				'600': '#059669',
    				'700': '#047857',
    				'800': '#065f46',
    				'900': '#064e3b',
    				'950': '#022c22'
    			},
    			surface: {
    				'0': '#ffffff',
    				'50': '#fafafa',
    				'100': '#f4f4f5',
    				'200': '#e4e4e7',
    				'300': '#d4d4d8',
    				'400': '#a1a1aa',
    				'500': '#71717a',
    				'600': '#52525b',
    				'700': '#3f3f46',
    				'800': '#27272a',
    				'900': '#18181b',
    				'950': '#09090b'
    			},
    			sidebar: {
    				DEFAULT: 'hsl(var(--sidebar-background))',
    				foreground: 'hsl(var(--sidebar-foreground))',
    				primary: 'hsl(var(--sidebar-primary))',
    				'primary-foreground': 'hsl(var(--sidebar-primary-foreground))',
    				accent: 'hsl(var(--sidebar-accent))',
    				'accent-foreground': 'hsl(var(--sidebar-accent-foreground))',
    				border: 'hsl(var(--sidebar-border))',
    				ring: 'hsl(var(--sidebar-ring))'
    			}
    		}
    	}
    },

    plugins: [forms, require("tailwindcss-animate")],
};
