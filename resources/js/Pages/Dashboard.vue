<script setup lang="ts">
import PageHeader from "@/Components/PageHeader.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ChartColumnIncreasing, Home } from "lucide-vue-next";
import { Head } from "@inertiajs/vue3";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="flex justify-between min-h-12">
            <Breadcrumb>
                <BreadcrumbList>
                    <BreadcrumbItem>
                        <BreadcrumbLink :href="route('dashboard')">
                            <Home class="size-4" />
                        </BreadcrumbLink>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbPage>Dashboard</BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>
        </div>

        <PageHeader>
            <template #icon><ChartColumnIncreasing /></template>
            <template #title>Dashboard</template>
        </PageHeader>
    </AuthenticatedLayout>
</template>
