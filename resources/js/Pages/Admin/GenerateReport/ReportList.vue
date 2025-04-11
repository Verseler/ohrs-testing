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

type ReportType = 'revenue' | 'collectable';

type Report = {
    date: string;
    orNumber: string;
    bookedBy: string;
    numberOfGuests: number;
    numberOfDays: number;
    amount: number;
};

type GenerateReportProps = {
    revenueReport: Report[];
    revenueTotalAmount: number;
    collectableReport: Pick<Report, 'bookedBy' | 'numberOfGuests' | 'numberOfDays' | 'amount'>[];
    collectableTotalAmount: number;
};

const { revenueReport, collectableReport, revenueTotalAmount, collectableTotalAmount } = defineProps<GenerateReportProps>();

const form = useForm({
    selected_revenue_date: new Date(),
    selected_collectable_date: new Date(),
});

function changeDate() {
    form.get(route("report.list", { revenue_date: form.selected_revenue_date, collectable_date: form.selected_collectable_date }), {
        preserveScroll: true,
        preserveState: true,
    });
}

watch(() => form.selected_revenue_date, changeDate);

watch(() => form.selected_collectable_date, changeDate);

function downloadReport(type: ReportType) {
    if (!form.selected_revenue_date) {
        return;
    }

    const url = `/reports/download/${formatDate(form.selected_revenue_date)}/${type}`;
    window.open(url, "_blank");
}

function printReport(type: ReportType) {
    if (!form.selected_revenue_date) {
        return;
    }

    const url = `/reports/print/${formatDate(form.selected_revenue_date)}/${type}`;
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

        <!-- Revenue Table -->
        <div>
            <div class="flex items-center justify-end mb-2 gap-x-2">
                <h1 class="text-2xl font-bold mr-auto ps-2">Revenue</h1>
                <MonthPicker v-model="form.selected_revenue_date" />
                <Button @click="downloadReport('revenue')" class="md:min-w-28">
                    <Download /><span class="hidden md:block">Download</span>
                </Button>
                <Button
                    @click="printReport('revenue')"
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
                            <TableHead> OR Date </TableHead>
                            <TableHead> OR Number </TableHead>
                            <TableHead> Booked By </TableHead>
                            <TableHead> Number of Guests </TableHead>
                            <TableHead> Number of Days </TableHead>
                            <TableHead> Amount </TableHead>
                        </TableRowHeader>
                    </TableHeader>

                    <TableBody>
                        <template v-if="revenueReport && revenueReport.length > 0">
                            <TableRow v-for="report in revenueReport" :key="report.date">
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

                        <TableRow class="bg-neutral-50">
                            <TableCell colspan="5" class="text-right">
                                Total Amount:
                            </TableCell>
                            <TableCell class="font-bold">
                                {{ formatCurrency(revenueTotalAmount) }}
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </TableContainer>
        </div>


        <!-- Collectables Table -->
        <div class="mt-20">
            <div class="flex items-center justify-end mb-2 gap-x-2">
                <h1 class="text-2xl font-bold mr-auto ps-2">Collectables</h1>
                <MonthPicker v-model="form.selected_collectable_date" />
                <Button @click="downloadReport('collectable')" class="md:min-w-28">
                    <Download /><span class="hidden md:block">Download</span>
                </Button>
                <Button
                    @click="printReport('collectable')"
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
                            <TableHead> Booked By </TableHead>
                            <TableHead> Number of Guests </TableHead>
                            <TableHead> Number of Days </TableHead>
                            <TableHead> Amount </TableHead>
                        </TableRowHeader>
                    </TableHeader>

                    <TableBody>
                        <template v-if="collectableReport && collectableReport.length > 0">
                            <!-- TODO: key? -->
                            <TableRow v-for="report in collectableReport" :key="report.bookedBy">
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
                                    colspan="4"
                                    class="py-10 italic text-center text-neutral-500"
                                >
                                    No records found.
                                </TableCell>
                            </TableRow>
                        </template>

                        <TableRow class="bg-neutral-50">
                            <TableCell colspan="3" class="text-right">
                                Total Amount:
                            </TableCell>
                            <TableCell class="font-bold">
                                {{ formatCurrency(collectableTotalAmount) }}
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </TableContainer>
        </div>
    </AuthenticatedLayout>
</template>
