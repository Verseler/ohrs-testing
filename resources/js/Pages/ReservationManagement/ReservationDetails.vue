<script setup lang="ts">
import { ReservationStatus, Reservation } from '@/Pages/ReservationManagement/reservations.types'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import type { Office } from '@/Pages/OfficeManagement/office.types'
import { formatCurrency, formatDateString } from '@/lib/utils'
import { Badge, BadgeVariants } from '@/Components/ui/badge'
import { CalendarCheck, CreditCard, History, Home, Pencil } from 'lucide-vue-next'
import { Separator } from '@/Components/ui/separator'
import PageHeader from '@/Components/PageHeader.vue'
import { Button } from '@/Components/ui/button'
import { Label } from '@/Components/ui/label'
import { Head, Link } from '@inertiajs/vue3'
import {
Card,
CardContent,
CardHeader,
CardTitle
} from '@/Components/ui/card'
import { capitalize } from 'vue'
import { Bed, Room } from '@/Pages/RoomManagement/room.types'
import GenderBadge from '@/Components/GenderBadge.vue'
import { 
    Breadcrumb, 
    BreadcrumbItem, 
    BreadcrumbLink, 
    BreadcrumbList, 
    BreadcrumbSeparator, 
    BreadcrumbPage 
} from '@/Components/ui/breadcrumb'
import BackLink from '@/Components/BackLink.vue'

type ReservationDetailsProps = {
    reservation: Omit<Reservation, 'guest_office_id' | 'host_office_id'> & {
    guest_office: Office;
    host_office: Office;
    beds: (Bed & {room: Room})[];
}
}

const { reservation } = defineProps<ReservationDetailsProps>();

  const getSeverity = (status: ReservationStatus): BadgeVariants["severity"] => {
    switch (status) {
      case 'pending':
        return 'warning'
      case 'checked_in':
        return 'success'
      case 'checked_out':
        return 'secondary'
      case 'canceled':
        return 'danger'
      default:
        return 'secondary'
    }
  }
  </script>


<template>
    <Head title="Reservation Details" />

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
                        <BreadcrumbLink :href="route('reservation.list')">
                            Reservation Management
                        </BreadcrumbLink>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbPage>Reservation Details</BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>

            <BackLink :href="route('reservation.list')" />
        </div>
        
        <PageHeader>
            <template #icon><CalendarCheck /></template>
            <template #title>Reservation Details</template>
        </PageHeader>

        <div class="max-w-4xl">
            <div class="flex flex-col gap-6">
                <!-- Header with status badge -->
                <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                    <div>
                        <p class="font-bold">CODE: <span class="text-lg font-normal">{{ reservation.reservation_code }}</span></p>
                    </div>
                    <Badge :severity="getSeverity(reservation.status)">
                        {{  capitalize(reservation.status) }}
                    </Badge>

                    <div class="ml-auto space-x-2">
                        <Link :href="route('reservation.paymentHistory', reservation.id)">
                            <Button class="bg-yellow-500 hover:bg-yellow-600">
                                <History class="mr-1" />
                                Payment History
                            </Button>
                        </Link>
                        
                        <Link v-if="reservation.remaining_balance > 0" :href="route('reservation.paymentForm', reservation.id)">
                            <Button>
                                <CreditCard class="mr-1" />
                                Pay Balance
                            </Button>
                        </Link>
                        
                        <Link :href="route('reservation.editForm', reservation.id)">
                            <Button class="bg-blue-500 hover:bg-blue-600">
                                <Pencil class="mr-1" />
                                Edit
                            </Button>
                        </Link>
                    </div>
                </div>

                <!-- Main content -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Reservation details card -->
                <Card>
                    <CardHeader>
                    <CardTitle>Reservation Information</CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                        <Label class="text-neutral-700">Check-in Date</Label>
                        <p class="font-medium">{{ formatDateString(reservation.check_in_date) }}</p>
                        </div>
                        <div>
                        <Label class="text-neutral-700">Check-out Date</Label>
                        <p class="font-medium">{{ formatDateString(reservation.check_out_date) }}</p>
                        </div>
                    </div>

                    <Separator />

                    <div class="grid gap-2">
                        <Label class="text-neutral-700">Payment Details</Label>
                        <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Billing</p>
                            <p class="font-medium">₱{{ formatCurrency(reservation.total_billing) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Remaining Balance</p>
                            <p 
                            class="font-medium"
                            :class="{
                                'text-red-500': reservation.remaining_balance > 0,
                                'text-green-500': reservation.remaining_balance <= 0
                            }">₱{{ formatCurrency(reservation.remaining_balance) }}</p>
                        </div>
                        </div>
                    </div>

                    <Separator />

                    <div class="grid gap-2">
                        <Label class="text-neutral-700">Reserved Bed</Label>
                        <template  v-if="reservation.beds.length > 0">
                            <div v-for="bed in reservation.beds" class="inline-flex text-sm gap-x-2">
                                <p>{{ bed.room.name }}</p>
                                <p>{{ bed.name }}</p>
                                <GenderBadge class="ml-auto" :gender="bed.room.eligible_gender" />
                            </div>
                        </template>
                        <p v-else class="text-sm text-muted-foreground">No bed information available</p>
                    </div>
                    </CardContent>
                </Card>

                <!-- Guest information card -->
                <Card>
                    <CardHeader>
                    <CardTitle>Guest Information</CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-4">
                    <div>
                        <Label class="text-neutral-700">Full Name</Label>
                        <p class="font-medium">
                        {{ reservation.first_name }}
                        {{ reservation.middle_initial ? reservation.middle_initial + '.' : '' }}
                        {{ reservation.last_name }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                        <Label class="text-neutral-700">Phone</Label>
                        <p class="font-medium">{{ reservation.phone }}</p>
                        </div>
                        <div>
                        <Label class="text-neutral-700">Email</Label>
                        <p class="font-medium">{{ reservation.email || '-' }}</p>
                        </div>
                    </div>

                    <Separator />

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                        <Label class="text-neutral-700">Guest Office</Label>
                        <p class="font-medium">{{ reservation?.guest_office?.name || '-' }}</p>
                        </div>
                        <div>
                        <Label class="text-neutral-700">Hostel</Label>
                        <p class="font-medium">{{ reservation?.host_office?.name || '-' }}</p>
                        </div>
                    </div>

                    <div>
                        <Label class="text-neutral-700">Purpose of Stay</Label>
                        <p class="font-medium">{{ reservation.purpose_of_stay || '-' }}</p>
                    </div>
                    </CardContent>
                </Card>
                </div>

                <!-- Employee ID section -->
                <Card>
                <CardHeader>
                    <CardTitle>Employee Identification</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-lg">{{ reservation.employee_identification || '-' }}</p>
                </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
  </template>

