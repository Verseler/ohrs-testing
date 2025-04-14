<script setup lang="ts">
import { Mail } from "lucide-vue-next";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/Components/ui/card";
import { Alert, AlertDescription } from "@/Components/ui/alert";
import PinInput from "@/Components/ui/pin-input/PinInput.vue";
import { useForm } from "@inertiajs/vue3";
import {
    PinInputGroup,
    PinInputInput,
    PinInputSeparator,
} from "@/Components/ui/pin-input";
import GuestLayout from "@/Layouts/GuestLayout.vue";

type OtpConfirmationProps = {
    action: "edit" | "cancel" | "rebook";
    reservation_id: number;
    email: string;
};

const { action, reservation_id, email } = defineProps<OtpConfirmationProps>();

const form = useForm({
    token: ["", "", "", "", "", ""],
    reservation_id: reservation_id,
});

//verify token code
function handleComplete() {
    const tokenString = form.token.join("");

    switch (action) {
        case "edit":
            form.get(
                route("reservation.verifyEdit", {
                    reservation_id: reservation_id,
                    token: tokenString,
                })
            );
            break;
        case "cancel":
            form.get(
                route("reservation.verifyCancel", {
                    reservation_id: reservation_id,
                    token: tokenString,
                })
            );
            break;
        case "rebook":
            form.get(
                route("reservation.verifyRebook", {
                    reservation_id: reservation_id,
                    token: tokenString,
                })
            );
            break;
    }
}
</script>

<template>
    <GuestLayout>
        <div class="grid p-4 mt-40 place-content-center">
            <Card class="relative w-full max-w-md py-6">
                <CardHeader class="space-y-1 text-center">
                    <Mail class="w-6 h-6 mx-auto" />
                    <CardTitle class="text-2xl">Email Verification</CardTitle>
                    <CardDescription>
                        We've sent a verification code to
                        <span class="text-primary-700">{{ email }}</span
                        >. If you don't see the verification code in your inbox,
                        please check your spam or junk folder.
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <div class="text-sm text-center">
                                Enter the 6-digit code sent to your email
                            </div>

                            <div class="flex justify-center gap-2">
                                <PinInput
                                    id="pin-input"
                                    v-model="form.token"
                                    placeholder="O"
                                    @complete="handleComplete"
                                >
                                    <PinInputGroup class="gap-1">
                                        <template
                                            v-for="(id, index) in 6"
                                            :key="id"
                                        >
                                            <PinInputInput
                                                class="border rounded-md"
                                                :index="index"
                                            />
                                            <template v-if="index !== 5">
                                                <PinInputSeparator />
                                            </template>
                                        </template>
                                    </PinInputGroup>
                                </PinInput>
                            </div>

                            <Alert
                                v-if="form.errors.token"
                                variant="destructive"
                                class="mt-4"
                            >
                                <AlertDescription>{{
                                    form.errors.token
                                }}</AlertDescription>
                            </Alert>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </GuestLayout>
</template>
