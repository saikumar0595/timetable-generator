<?php
/**
 * ChronoGen Unified Styles & Theme
 * Central location for all CSS, colors, and Tailwind classes
 */

// Color Theme
$theme = [
    'primary' => 'indigo',
    'secondary' => 'slate',
    'success' => 'emerald',
    'warning' => 'amber',
    'danger' => 'red',
    'info' => 'blue',
];

// Reusable Tailwind Classes
$classes = [
    'btn' => [
        'primary' => 'px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/30',
        'secondary' => 'px-4 py-2 bg-slate-200 text-slate-800 rounded-lg font-medium hover:bg-slate-300 transition-colors',
        'danger' => 'px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors',
        'outline' => 'px-4 py-2 border-2 border-indigo-600 text-indigo-600 rounded-lg font-medium hover:bg-indigo-50 transition-colors',
        'small' => 'px-3 py-1.5 text-sm bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors',
    ],
    'card' => 'bg-white rounded-2xl shadow-sm border border-slate-100 p-6',
    'card-hover' => 'bg-white rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-md transition-shadow cursor-pointer',
    'input' => 'w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all',
    'label' => 'block text-sm font-semibold text-slate-700 mb-2',
    'stat-card' => 'bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4',
    'table-header' => 'bg-slate-50 border-b border-slate-200 px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider',
    'table-cell' => 'border-b border-slate-200 px-6 py-3 text-slate-800',
];

// Icon size classes
$icons = [
    'xs' => 'w-4 h-4',
    'sm' => 'w-5 h-5',
    'md' => 'w-6 h-6',
    'lg' => 'w-8 h-8',
    'xl' => 'w-10 h-10',
];
?>

<style>
/* Global Styles */
:root {
    --primary: #4f46e5;
    --primary-dark: #4338ca;
    --secondary: #1e293b;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --light: #f1f5f9;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    @apply bg-slate-50 text-slate-800 antialiased;
}

/* Smooth Transitions */
* {
    @apply transition-colors duration-200;
}

/* Scrollbar Styling */
::-webkit-scrollbar {
    @apply w-2;
}
::-webkit-scrollbar-track {
    @apply bg-slate-100;
}
::-webkit-scrollbar-thumb {
    @apply bg-slate-400 rounded-full;
}
::-webkit-scrollbar-thumb:hover {
    @apply bg-slate-600;
}

/* Focus Styles */
:focus-visible {
    @apply outline-none ring-2 ring-indigo-500 ring-offset-2;
}

/* Loading Animation */
@keyframes spin-custom {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@keyframes pulse-custom {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.animate-spin-custom {
    animation: spin-custom 1s linear infinite;
}

.animate-pulse-custom {
    animation: pulse-custom 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Skeleton Loading */
.skeleton {
    @apply bg-slate-200 animate-pulse-custom rounded;
}

/* Glass Effect */
.glass {
    @apply bg-white/80 backdrop-blur-md border border-white/20;
}

/* Glassmorphism Dark */
.glass-dark {
    @apply bg-slate-900/80 backdrop-blur-md border border-slate-700/20;
}

/* Smooth Shadows */
.shadow-soft {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.shadow-medium {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.shadow-large {
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

/* Badge Styles */
.badge {
    @apply inline-block px-3 py-1 text-xs font-semibold rounded-full;
}

.badge-primary {
    @apply bg-indigo-100 text-indigo-800;
}

.badge-success {
    @apply bg-emerald-100 text-emerald-800;
}

.badge-warning {
    @apply bg-amber-100 text-amber-800;
}

.badge-danger {
    @apply bg-red-100 text-red-800;
}

/* Hover Effects */
.hover-lift {
    @apply hover:shadow-lg hover:scale-105 transition-all duration-200;
}

.hover-glow {
    @apply hover:shadow-lg hover:shadow-indigo-500/50;
}

/* Fade In Animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.fade-in {
    animation: fadeIn 0.3s ease-out;
}

/* Slide In Animation */
@keyframes slideInLeft {
    from { opacity: 0; transform: translateX(-20px); }
    to { opacity: 1; transform: translateX(0); }
}

.slide-in-left {
    animation: slideInLeft 0.3s ease-out;
}

/* Toast Notification */
.toast {
    @apply fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white font-medium shadow-lg z-50 fade-in;
}

.toast-success {
    @apply bg-emerald-500;
}

.toast-error {
    @apply bg-red-500;
}

.toast-warning {
    @apply bg-amber-500;
}

.toast-info {
    @apply bg-indigo-500;
}
</style>
