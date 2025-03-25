<script setup lang="ts">
import PageHeader from "@/Components/PageHeader.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {
    CalendarClockIcon,
    CalendarIcon,
    ChartColumnIncreasing,
    CreditCardIcon,
    Home,
    UsersIcon,
} from "lucide-vue-next";
import { Head, useForm } from "@inertiajs/vue3";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";
import StatsCard from "@/Components/Analytics/StatsCard.vue";
import { BarChart } from "@/Components/ui/chart-bar";
import YearPicker from "@/Components/YearPicker.vue";
import { formatYear, getMonthYear } from "@/lib/utils";
import { onMounted, watch } from "vue";
import type { MonthlyRevenue } from "@/Pages/Admin/Dashboard/dashboard.types";

type DashboardProps = {
    pendingReservationsCount: number;
    totalReservationsCount: number;
    totalGuestsCount: number;
    totalRevenue: number;
    monthlyRevenue: MonthlyRevenue[];
};

const {
    pendingReservationsCount,
    totalGuestsCount,
    totalReservationsCount,
    totalRevenue,
    monthlyRevenue,
} = defineProps<DashboardProps>();

const form = useForm({
    selected_date: new Date(),
    monthly_revenue_year: new Date(),
});

watch(
    [() => form.selected_date, () => form.monthly_revenue_year],
    updateDashboardData
);

onMounted(() => updateDashboardData());

function updateDashboardData() {
    form.get(route("dashboard"), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
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

        <PageHeader>
            <template #icon><ChartColumnIncreasing /></template>
            <template #title>Dashboard</template>
        </PageHeader>

        <!-- Main Content -->
        <p
            class="px-4 py-1.5 mb-2 mr-2 text-xl font-bold border rounded-lg text-primary-700 border-primary-500 max-w-max"
        >
            {{ getMonthYear(new Date()) }}
        </p>

        <section id="stats" class="grid grid-cols-4 gap-3">
            <StatsCard>
                <template #title>Pending Reservations</template>
                <template #value>
                    <span
                        :class="{
                            'text-red-500': pendingReservationsCount > 0,
                        }"
                    >
                        {{ pendingReservationsCount }}
                    </span>
                </template>
                <template #icon><CalendarClockIcon /></template>
            </StatsCard>
            <StatsCard>
                <template #title>Total Reservations</template>
                <template #value>{{ totalReservationsCount }}</template>
                <template #icon><CalendarIcon /></template>
            </StatsCard>

            <StatsCard>
                <template #title>Total Guests</template>
                <template #value>{{ totalGuestsCount }}</template>
                <template #icon><UsersIcon /></template>
            </StatsCard>
            <StatsCard>
                <template #title>Revenue</template>
                <template #value>
                    <span
                        :class="{
                            'text-red-500': totalRevenue <= 0,
                        }"
                    >
                        ₱ {{ totalRevenue }}
                    </span></template
                >
                <template #icon><CreditCardIcon /></template>
            </StatsCard>
        </section>

        <section
            id="monthly-revenue"
            class="max-w-full px-5 py-3 mt-4 border rounded-lg"
        >
            <div class="flex items-center justify-between mt-2 mb-4 gap-x-2">
                <p class="text-lg">
                    Monthly Revenue for
                    <span class="font-bold">
                        {{ formatYear(form.monthly_revenue_year) }}
                    </span>
                </p>
                <YearPicker v-model="form.monthly_revenue_year" />
            </div>

            <BarChart
                class="w-full"
                :data="monthlyRevenue"
                index="name"
                :categories="['revenue']"
                :y-formatter="
                    (tick, i) => {
                        return typeof tick === 'number'
                            ? `₱ ${new Intl.NumberFormat('us')
                                  .format(tick)
                                  .toString()}`
                            : '';
                    }
                "
            />
        </section>
    </AuthenticatedLayout>
</template>
