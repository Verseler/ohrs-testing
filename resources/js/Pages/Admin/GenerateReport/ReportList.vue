<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { Download, Printer } from "lucide-vue-next";
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
import ReportHeading from "@/Pages/Admin/GenerateReport/Partials/ReportHeading.vue";
import { formatCurrency } from "@/lib/utils";
import { SidebarTrigger } from "@/Components/ui/sidebar";

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
    form.get(route("reports", { date: form.selected_date }), {
        preserveScroll: true,
        preserveState: true,
    });
}

watch(() => form.selected_date, changeDate);
</script>

<template>
    <Head title="Generate Report" />

    <AuthenticatedLayout>
        <div class="flex justify-end mb-20 gap-x-2">
            <SidebarTrigger class="mr-auto size-9" />
            <MonthPicker v-model="form.selected_date" />
            <Button @click="downloadReport" class="md:min-w-28">
                <Download /><span class="hidden md:block">Download</span>
            </Button>
            <Button
                @click="printReport"
                class="text-white bg-neutral-800 md:min-w-28 hover:bg-neutral-700 hover:text-white"
            >
                <Printer class="mr-1" /><span class="hidden md:block"
                    >Print</span
                >
            </Button>
        </div>
        <div class="max-w-[300mm] mx-auto">
            <div id="reports" class="font-[sans-serif]">
                <ReportHeading />

                <Table class="w-full border-collapse">
                    <TableHeader>
                        <TableRow>
                            <TableHead> Date </TableHead>
                            <TableHead> OR Number </TableHead>
                            <TableHead> Booked By </TableHead>
                            <TableHead> Number of Guests </TableHead>
                            <TableHead> Number of Days </TableHead>
                            <TableHead> Amount </TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <template v-if="reports && reports.length > 0">
                            <TableRow
                                v-for="report in reports"
                                :key="report.date"
                            >
                                <TableCell style="height: 3rem">
                                    {{ report.date }}
                                </TableCell>
                                <TableCell style="height: 3rem">
                                    {{ report.orNumber }}
                                </TableCell>
                                <TableCell style="height: 3rem">
                                    {{ report.bookedBy }}
                                </TableCell>
                                <TableCell style="height: 3rem">
                                    {{ report.numberOfDays }}
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
                                    colspan="6"
                                    class="py-10 italic text-center text-neutral-500"
                                >
                                    No records found.
                                </TableCell>
                            </TableRow>
                        </template>

                        <TableRow>
                            <TableCell colspan="5" class="text-right">
                                Total Amount:
                            </TableCell>
                            <TableCell class="font-bold">
                                {{ formatCurrency(totalAmount) }}
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
th {
    font-weight: bold;
}

th,
td {
    border: 1px solid black;
    padding: 8px;
    text-align: left;
    color: black;
}
</style>
