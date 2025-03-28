<script setup lang="ts">
import { Home } from "lucide-vue-next";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";

type Item = {
    label: string;
    href: string | null;
};

type BreadcrumbsProps = {
    items: Item[];
};

const { items } = defineProps<BreadcrumbsProps>();
</script>

<template>
    <Breadcrumb>
        <BreadcrumbList>
            <BreadcrumbItem>
                <BreadcrumbLink :href="route('dashboard')">
                    <Home class="size-4" />
                </BreadcrumbLink>
            </BreadcrumbItem>
            <template v-for="item in items" :key="item.label">
                <BreadcrumbSeparator />
                <BreadcrumbItem>
                    <BreadcrumbLink v-if="item.href" :href="item.href">
                        <Home class="size-4" />
                    </BreadcrumbLink>
                    <BreadcrumbPage v-else>{{ item.label }}</BreadcrumbPage>
                </BreadcrumbItem>
            </template>
        </BreadcrumbList>
    </Breadcrumb>
</template>
