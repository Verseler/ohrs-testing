<script setup lang="ts">
import { Mail } from 'lucide-vue-next'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Alert, AlertDescription } from '@/Components/ui/alert'
import PinInput from '@/Components/ui/pin-input/PinInput.vue'
import { useForm } from '@inertiajs/vue3'
import { PinInputGroup, PinInputInput, PinInputSeparator } from '@/Components/ui/pin-input'
import Header from '@/Components/Header.vue'
import { computed, onMounted } from 'vue'
import { showSuccess } from '@/Composables/useFlash'

type OtpConfirmationProps = {
    action: 'edit' | 'cancel' | 'rebook';
    reservation_id: number;
}

const { action, reservation_id } = defineProps<OtpConfirmationProps>();

const form = useForm({
    token: ['', '', '', '', '', ''],
    reservation_id: reservation_id
});


onMounted(() => {
    showSuccess();
});


//verify token code
function handleComplete() {
    const tokenString = form.token.join('');

    console.log('act');
    switch(action) {
        case 'edit':
           form.get(route('reservation.verifyEdit', { reservation_id: reservation_id, token: tokenString }));
            break;
        case 'cancel':
            // router.visit(route('reservation.cancel', { id: id }));
            break;
        case 'rebook':
            // router.visit(route('reservation.rebook', { id: id }));
            break;
    }
}
</script>

<template>

<div class="w-full min-h-screen">
    <Header />

  <div class="grid place-content-center p-4 mt-40">

    <Card class="py-6 w-full max-w-md">
      <CardHeader class="space-y-1 text-center">
        <Mail class="mx-auto w-6 h-6" />
        <CardTitle class="text-2xl">Email Verification</CardTitle>
        <CardDescription>
            We've sent a verification code to verselerkerr.handuman@gmail.com
          <!-- We've sent a verification code to {{ maskedEmail }} -->
        </CardDescription>
      </CardHeader>

      <CardContent>
        <div class="space-y-4">
          <div class="space-y-2">
            <div class="text-sm text-center">
              Enter the 6-digit code sent to your email
            </div>

            <div class="flex gap-2 justify-center">
                <PinInput
                    id="pin-input"
                    v-model="form.token"
                    placeholder="O"
                    @complete="handleComplete"
                >
                <PinInputGroup class="gap-1">
                    <template v-for="(id, index) in 6" :key="id">
                    <PinInputInput
                        class="rounded-md border"
                        :index="index"
                    />
                    <template v-if="index !== 5">
                        <PinInputSeparator />
                    </template>
                    </template>
                </PinInputGroup>
            </PinInput>
            </div>

            <Alert v-if="form.errors.token" variant="destructive" class="mt-4">
              <AlertDescription>{{ form.errors.token }}</AlertDescription>
            </Alert>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</div>
</template>


