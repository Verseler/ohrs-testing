<script setup lang="ts">
import {
    CalendarClockIcon,
    ChartColumnIncreasing,
    CalendarX2,
    Wallet,
} from "lucide-vue-next";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import NotificationLinkButton from "@/Components/NotificationLinkButton.vue";
import type { MonthlyRevenue } from "@/Pages/Admin/Dashboard/dashboard.types";
import StatsCard from "@/Components/Analytics/StatsCard.vue";
import { formatCurrency, formatYear, getMonthYear } from "@/lib/utils";
import YearPicker from "@/Components/YearPicker.vue";
import { BarChart } from "@/Components/ui/chart-bar";
import PageHeader from "@/Components/PageHeader.vue";
import { Message } from "@/Components/ui/message";
import { onMounted, watch } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import { usePoll } from "@inertiajs/vue3";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import { data } from "@/Pages/Admin/Dashboard/data";

usePoll(5000);

type DashboardProps = {
    pendingReservationsCount: number;
    unpaidReservationsCount: number;
    overdueCheckinCount: number;
    overdueCheckoutCount: number;
    monthlyRevenues: MonthlyRevenue[];
    runningRevenue: number;
    runningCollectables: number;
};

const {
    pendingReservationsCount,
    unpaidReservationsCount,
    overdueCheckinCount,
    overdueCheckoutCount,
    monthlyRevenues,
    runningRevenue,
    runningCollectables,
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
        <div class="flex items-center justify-between">
            <Breadcrumbs :items="data.breadcrumbs" />
            <NotificationLinkButton />
        </div>
        <PageHeader>
            <template #icon><ChartColumnIncreasing /></template>
            <template #title>Dashboard</template>
        </PageHeader>

        <!-- Main Content -->
        <Message
            severity="primary"
            class="px-4 py-1.5 mb-2 mr-2 text-xl font-bold rounded-lg max-w-max"
        >
            {{ getMonthYear(new Date()) }}
        </Message>

        <section id="stats" class="grid grid-cols-2 gap-3 md:grid-cols-4">
            <StatsCard>
                <template #title>Pending Reservations</template>
                <template #value>{{ pendingReservationsCount }}</template>
                <template #icon><CalendarClockIcon /></template>
            </StatsCard>
            <StatsCard>
                <template #title>Unpaid Reservations</template>
                <template #value>{{ unpaidReservationsCount }}</template>
                <template #icon><Wallet /></template>
            </StatsCard>
            <StatsCard>
                <template #title>Overdue Check in</template>
                <template #value>{{ overdueCheckinCount }}</template>
                <template #icon><CalendarX2 /></template>
            </StatsCard>
            <StatsCard>
                <template #title>Overdue Check out</template>
                <template #value>{{ overdueCheckoutCount }}</template>
                <template #icon><CalendarX2 /></template>
            </StatsCard>
        </section>

        <section
            id="monthly-revenue"
            class="max-w-full px-5 py-3 mt-4 border rounded-lg"
        >
            <div class="flex items-center text-lg justify-between mt-2 mb-4 gap-x-2">
                <p>
                    Monthly Revenue for
                    <span class="font-bold">
                        {{ formatYear(form.monthly_revenue_year) }}
                    </span>
                </p>

                <p>Running Revenue:
                    <span class="font-bold text-primary-500">
                        {{ formatCurrency(runningRevenue) }}
                    </span>
                </p>

                <p>Running Collectables:
                    <span class="font-bold text-red-500">
                        {{ formatCurrency(runningCollectables) }}
                    </span>
                </p>

                <YearPicker v-model="form.monthly_revenue_year" />
            </div>

            <div class="relative">
                <div
                    style="writing-mode: vertical-rl; transform: rotate(180deg)"
                    class="absolute inset-y-0 flex justify-center my-auto -left-3"
                >
                    <p class='text-sm font-medium tracking-wide uppercase text-neutral-600'>In &nbsp; Philippine &nbsp; Peso</p>
                </div>

                <BarChart
                    class="w-full pl-5"
                    :data="monthlyRevenues"
                    index="name"
                    :categories="['revenue', 'collectables']"
                    :y-formatter="
                        (tick, i) => {
                            return typeof tick === 'number'
                                ? `${new Intl.NumberFormat('us')
                                      .format(tick)
                                      .toString()}`
                                : '';
                        }
                    "
                />
            </div>
        </section>
    </AuthenticatedLayout>
</template>
