<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { Download, FileDown, Home, Printer } from "lucide-vue-next";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import MonthPicker from "@/Components/MonthPicker.vue";
import { watch } from "vue";
import { Button } from "@/Components/ui/button";
import { formatCurrency, formatDate } from "@/lib/utils";
import { SidebarTrigger } from "@/Components/ui/sidebar";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";
import PageHeader from "@/Components/PageHeader.vue";
import TableContainer from "@/Components/ui/table/TableContainer.vue";
import TableRowHeader from "@/Components/ui/table/TableRowHeader.vue";

type Report = {
    date: string;
    orNumber: string;
    bookedBy: string;
    numberOfGuests: number;
    numberOfDays: number;
    amount: number;
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
    form.get(route("report.list", { date: form.selected_date }), {
        preserveScroll: true,
        preserveState: true,
    });
}

watch(() => form.selected_date, changeDate);

function downloadReport() {
    if (!form.selected_date) {
        return;
    }

    const url = `/reports/download/${formatDate(form.selected_date)}`;
    window.open(url, "_blank");
}

function printReport() {
    if (!form.selected_date) {
        return;
    }

    const url = `/reports/print/${formatDate(form.selected_date)}`;
    const pdfWindow = window.open(url, "_blank");

    if (pdfWindow) {
        pdfWindow.addEventListener("load", () => {
            pdfWindow.print();
        });
    } else {
        alert("Please allow popups for this website");
    }
}
</script>

<template>
    <Head title="Generate Report" />

    <AuthenticatedLayout>
        <div class="flex justify-between min-h-12">
            <Breadcrumb>
                <BreadcrumbList>
                    <BreadcrumbItem>
                        <SidebarTrigger class="me-2" />
                    </BreadcrumbItem>

                    <BreadcrumbItem>
                        <BreadcrumbLink :href="route('dashboard')">
                            <Home class="size-4" />
                        </BreadcrumbLink>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbLink :href="route('office.list')">
                            Generate Report
                        </BreadcrumbLink>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>
        </div>

        <PageHeader>
            <template #icon><FileDown /></template>
            <template #title>Generate Report</template>
        </PageHeader>

        <div class="flex items-center justify-end mb-2 gap-x-2">
            <MonthPicker v-model="form.selected_date" />
            <Button @click="downloadReport" class="md:min-w-28">
                <Download /><span class="hidden md:block">Download</span>
            </Button>
            <Button
                @click="printReport"
                class="text-white bg-neutral-800 md:min-w-28 hover:bg-neutral-700 hover:text-white"
            >
                <Printer class="md:mr-1" />
                <span class="hidden md:block">Print</span>
            </Button>
        </div>

        <TableContainer>
            <Table>
                <TableHeader>
                    <TableRowHeader>
                        <TableHead> Date </TableHead>
                        <TableHead> OR Number </TableHead>
                        <TableHead> Booked By </TableHead>
                        <TableHead> Number of Guests </TableHead>
                        <TableHead> Number of Days </TableHead>
                        <TableHead> Amount </TableHead>
                    </TableRowHeader>
                </TableHeader>

                <TableBody>
                    <template v-if="reports && reports.length > 0">
                        <TableRow v-for="report in reports" :key="report.date">
                            <TableCell>
                                {{ report.date }}
                            </TableCell>
                            <TableCell>
                                {{ report.orNumber }}
                            </TableCell>
                            <TableCell>
                                {{ report.bookedBy }}
                            </TableCell>
                            <TableCell>
                                {{ report.numberOfGuests }}
                            </TableCell>
                            <TableCell>
                                {{ report.numberOfDays }}
                            </TableCell>
                            <TableCell>
                                {{ formatCurrency(report.amount) }}
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell
                                colspan="6"
                                class="py-10 italic text-center text-neutral-500"
                            >
                                No records found.
                            </TableCell>
                        </TableRow>
                    </template>

                    <TableRow class="bg-neutral-200">
                        <TableCell colspan="5" class="text-right">
                            Total Amount:
                        </TableCell>
                        <TableCell class="font-bold">
                            {{ formatCurrency(totalAmount) }}
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </TableContainer>
    </AuthenticatedLayout>
</template>
