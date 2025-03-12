 
<script setup lang="ts">
import { 
Card, 
CardContent
} from '@/Components/ui/card'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import BackLink from '@/Components/BackLink.vue'
import * as htmlToImage from "html-to-image";
import { Download } from 'lucide-vue-next';
import { Button } from '@/Components/ui/button';
import { onMounted } from 'vue';
import { toast } from 'vue-sonner';

type PaymentReceiptProps = {
    'hostel': string,
    'paymentReceipt': string,
    'dateIssued': string | Date,
    'bookBy': string,
    'contact': number,
    'reservationCode': string;
    'totalAmountPaid': number,
    'totalBilling': number,
    'previousBalance': number,
    'latestBalance': number 
}


const { 
    hostel, 
    paymentReceipt, 
    dateIssued, 
    bookBy, 
    contact, 
    reservationCode,
    totalAmountPaid, 
    totalBilling, 
    previousBalance, 
    latestBalance 
} = defineProps<PaymentReceiptProps>();



function downloadReceipt() {
    htmlToImage
        .toJpeg(document.getElementById("receipt") as HTMLElement, {
            quality: 2,
        })
        .then(function (dataUrl) {
            var link = document.createElement("a");
            link.download = `${paymentReceipt}-HRS-R10.jpeg`;
            link.href = dataUrl;
            link.click();
        });
}

onMounted(() => {
    downloadReceipt();

    //remind guest to download confirmation if ever they have not downloaded it yet
    toast.info("Remember to download your receipt before leaving.",
            {
                style: {
                    background: "#3b82f6",
                    color: "white",
                },
                position: "top-center",
            }
    );
});
</script>


<template>
    <div class="relative p-6 mx-auto bg-neutral-50">
      <BackLink class="absolute top-4 right-4" :href="route('reservation.list')" />
      
      <div class="max-w-2xl mx-auto">
        <Card id="receipt" class="border rounded-none">
            <CardContent class="w-full p-8">
            <!-- Receipt Header -->
            <div class="mb-8 text-center">
                <ApplicationLogo class="size-20" />
                <h1 class="text-2xl font-bold">{{ hostel }} Hostel</h1>
                <p>{{ hostel }}</p>
            </div>
            
            <!-- Receipt Title -->
            <div class="mb-8 text-center">
                <h2 class="text-xl font-bold tracking-wide uppercase">Official Receipt</h2>
                <div class="inline-block px-4 py-1 mt-2 border border-dashed border-muted-foreground">
                <p class="text-sm font-medium">Receipt No: {{ paymentReceipt }}</p>
                </div>
            </div>
            
            <!-- Receipt Details -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                <p class="text-sm text-muted-foreground">Date Issued:</p>
                <p class="font-medium">{{ dateIssued }}</p>
                </div>
                <div>
                <p class="text-sm text-muted-foreground">Reservation Code:</p>
                <p class="font-medium">{{ reservationCode }}</p>
                </div>
            </div>
            
            <!-- Guest Information -->
            <div class="p-4 mb-6 rounded-md bg-muted">
                <h3 class="mb-2 font-medium">Guest Information</h3>
                <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-muted-foreground">Name:</p>
                    <p class="font-medium">
                    {{ bookBy }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Contact:</p>
                    <p class="font-medium">{{ contact }}</p>
                </div>
                </div>
            </div>
            
            <!-- Payment Details -->
            <div class="mb-6">
                <h3 class="mb-3 font-medium">Payment Details</h3>
                <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b">
                    <th class="py-2 text-left">Description</th>
                    <th class="py-2 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <tr>
                    <td class="py-3">
                        <div class="font-medium">Total Billing</div>
                    </td>
                    <td class="py-3 font-normal text-right">₱{{ totalBilling }}</td>
                    </tr>
                    
                    <tr>
                    <td class="py-3">
                        <div class="font-medium">Previous Balance</div>
                    </td>
                    <td class="py-3 font-normal text-right">₱{{ previousBalance }}</td>
                    </tr>
                    
                    <tr>
                    <td class="py-3">
                        <div class="font-medium">Total Amount Paid</div>
                    </td>
                    <td class="py-3 font-normal text-right">₱{{ totalAmountPaid }}</td>
                    </tr>
                    
                    <tr class="text-lg border-t">
                    <td class="py-3 font-bold text-right">Remaining Balance:</td>
                    <td class="py-3 text-base font-bold text-right">₱{{ latestBalance }}</td>
                    </tr>
                </tbody>
                </table>
            </div>
            
            <!-- Footer -->
            <div class="pt-4 text-sm text-center border-t text-muted-foreground">
                <p class="mb-1">Thank you for your payment!</p>
                <p>This is an official receipt. Please keep this for your records.</p>
                <p class="mt-2 text-xs">
                All electronic payments are subject to clearing and verification.
                </p>
            </div>
            </CardContent>
        </Card>

        <Button size="lg" @click="downloadReceipt" class="w-full mt-2">
            <Download /> Download Receipt
        </Button>
      </div>
    </div>
  </template>
 