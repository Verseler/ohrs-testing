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
import { nextTick, watch } from "vue";
import { Button } from "@/Components/ui/button";
import jsPDF from "jspdf";
import html2canvas from "html2canvas";

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

async function downloadReport() {
    const reportsTable = document.getElementById("reports");
    if (!reportsTable) {
        console.error("Table element not found");
        return;
    }

    await nextTick();

    // Add a small delay to ensure the table is fully rendered
    setTimeout(async () => {
        try {
            const canvas = await html2canvas(reportsTable, {
                scale: 4, // Increase scale for better quality
            });
            const imgData = canvas.toDataURL("image/png");
            const pdf = new jsPDF("p", "mm", "a4");
            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();
            const margin = 4; // Add padding/margin
            const imgWidth = pageWidth - margin * 2;
            const imgHeight = (canvas.height * imgWidth) / canvas.width;

            let position = margin;
            if (imgHeight > pageHeight - margin * 2) {
                let remainingHeight = imgHeight;
                while (remainingHeight > 0) {
                    pdf.addImage(
                        imgData,
                        "PNG",
                        margin,
                        position,
                        imgWidth,
                        Math.min(imgHeight, pageHeight - margin * 2)
                    );
                    remainingHeight -= pageHeight - margin * 2;
                    position = margin; // Reset position for new page
                    if (remainingHeight > 0) pdf.addPage();
                }
            } else {
                pdf.addImage(
                    imgData,
                    "PNG",
                    margin,
                    position,
                    imgWidth,
                    imgHeight
                );
            }

            pdf.save(`report_${new Date().toISOString()}.pdf`);
        } catch (error) {
            console.error("Failed to generate PDF:", error);
        }
    }, 100);
}

//Print Report Image
async function printReport() {
    const reportsTable = document.getElementById("reports");
    if (!reportsTable) {
        console.error("Table element not found");
        return;
    }

    await nextTick();

    setTimeout(async () => {
        try {
            const canvas = await html2canvas(reportsTable, {
                scale: 2,
            });
            const imgData = canvas.toDataURL("image/png");

            const printWindow = window.open("", "_blank");
            if (!printWindow) {
                console.error("Failed to open print window");
                return;
            }

            printWindow.document.write(`
                <html>
                <head>
                    <title>Print Report</title>
                    <style>
                        body { text-align: center; margin: 0; padding: 0; }
                        img { width: 100%; max-width: 100%; padding: 0; margin: 0; }
                    </style>
                </head>
                <body>
                    <img src="${imgData}" onload="window.print(); window.onafterprint = () => window.close();">
                </body>
                </html>
            `);
            printWindow.document.close();
        } catch (error) {
            console.error("Failed to print report:", error);
        }
    }, 100);
}
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

        <!-- Bind the ref to the table element -->
        <Table id="reports" class="text-base border">
            <TableHeader>
                <TableRow class="bg-neutral-100">
                    <TableHead style="width: 15%; height: 3rem">
                        Date
                    </TableHead>
                    <TableHead style="width: 15%; height: 3rem">
                        OR Number
                    </TableHead>
                    <TableHead style="width: 40%; height: 3rem">
                        Particulars
                    </TableHead>
                    <TableHead style="width: 15%; height: 3rem">
                        Number of Guests
                    </TableHead>
                    <TableHead style="width: 15%; height: 3rem">
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
                            {{ report.amount }}
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
                    <TableCell
                        style="height: 3rem"
                        colspan="4"
                        class="font-semibold text-right"
                    >
                        Total Amount:
                    </TableCell>
                    <TableCell class="font-semibold">
                        {{ totalAmount }}
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
