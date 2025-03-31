<template>
    <div
        class="p-4 pb-5 bg-white border rounded-lg shadow-sm border-neutral-200"
    >
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-sm text-gray-500">
                    <slot name="title" />
                </p>
                <p class="text-2xl font-semibold">
                    <slot name="value" />
                </p>
            </div>
            <div
                class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-50 text-primary-500"
            >
                <slot name="icon" />
            </div>
        </div>
    </div>
</template>
