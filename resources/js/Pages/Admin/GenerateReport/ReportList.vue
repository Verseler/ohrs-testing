<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";
import { Download, FileDown, Home, Printer } from "lucide-vue-next";
import PageHeader from "@/Components/PageHeader.vue";
import {
    Table,
    TableBody,
    TableCell,
    TableFooter,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import MonthPicker from "@/Components/MonthPicker.vue";
import { watch } from "vue";
import { Button } from "@/Components/ui/button";
import {
    downloadReport,
    printReport,
} from "@/Pages/Admin/GenerateReport/report.helpers";
import ReportHeading from "./Partials/ReportHeading.vue";
import { formatCurrency } from "@/lib/utils";

type Report = {
    date: string;
    orNumber: string;
    particulars: string;
    amount: number;
    numberOfGuests: number;
};

type GenerateReportProps = {
    reports: Report[];
    totalAmount: number;
};

const { reports, totalAmount } = defineProps<GenerateReportProps>();

const form = useForm({
    selected_date: new Date(),
});

function changeDate() {
    form.get(route("reports", { date: form.selected_date }), {
        preserveScroll: true,
        preserveState: true,
        only: ["reports"],
    });
}

watch(() => form.selected_date, changeDate);
</script>

<template>
    <Head title="Generate Report" />

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
                    <BreadcrumbPage>Generate Report</BreadcrumbPage>
                </BreadcrumbItem>
            </BreadcrumbList>
        </Breadcrumb>

        <PageHeader>
            <template #icon><FileDown /></template>
            <template #title>Generate Report</template>
        </PageHeader>

        <div class="flex justify-end mb-2 gap-x-2">
            <MonthPicker v-model="form.selected_date" />
            <Button @click="downloadReport" class="min-w-28">
                <Download />Download
            </Button>
            <Button
                @click="printReport"
                class="text-white bg-neutral-800 min-w-28 hover:bg-neutral-700 hover:text-white"
            >
                <Printer class="mr-1" />Print
            </Button>
        </div>

        <!-- *NOTE: Only available for download and print -->
        <ReportHeading />

        <!-- Bind the ref to the table element -->
        <Table id="reports" class="border">
            <TableHeader>
                <TableRow class="bg-primary-500 hover:bg-primary-600">
                    <TableHead
                        class="text-white"
                        style="width: 15%; height: 3rem"
                    >
                        Date
                    </TableHead>
                    <TableHead
                        class="text-white"
                        style="width: 15%; height: 3rem"
                    >
                        OR Number
                    </TableHead>
                    <TableHead
                        class="text-white"
                        style="width: 35%; height: 3rem"
                    >
                        Particulars
                    </TableHead>
                    <TableHead
                        class="text-white"
                        style="width: 20%; height: 3rem"
                    >
                        Number of Guests
                    </TableHead>
                    <TableHead
                        class="text-white"
                        style="width: 15%; height: 3rem"
                    >
                        Amount
                    </TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <template v-if="reports && reports.length > 0">
                    <TableRow v-for="report in reports" :key="report.date">
                        <TableCell style="height: 3rem">
                            {{ report.date }}
                        </TableCell>
                        <TableCell style="height: 3rem">
                            {{ report.orNumber }}
                        </TableCell>
                        <TableCell style="height: 3rem">
                            {{ report.particulars }}
                        </TableCell>
                        <TableCell style="height: 3rem">
                            {{ report.numberOfGuests }}
                        </TableCell>
                        <TableCell style="height: 3rem">
                            {{ formatCurrency(report.amount) }}
                        </TableCell>
                    </TableRow>
                </template>
                <template v-else>
                    <TableRow>
                        <TableCell
                            colspan="5"
                            class="py-10 italic text-center text-neutral-500"
                        >
                            No records found.
                        </TableCell>
                    </TableRow>
                </template>
            </TableBody>
            <TableFooter>
                <TableRow>
                    <TableCell colspan="4" class="font-semibold text-right">
                        Total Amount:
                    </TableCell>
                    <TableCell class="font-semibold">
                        {{ formatCurrency(totalAmount) }}
                    </TableCell>
                </TableRow>
            </TableFooter>
        </Table>
    </AuthenticatedLayout>
</template>

<style scoped>
@media print {
    body * {
        visibility: hidden;
    }
    #reports,
    #reports * {
        visibility: visible;
    }
    #reports {
        position: absolute;
        left: 0;
        top: 0;
    }
}
</style>
