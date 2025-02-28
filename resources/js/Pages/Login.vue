<script setup lang="ts">
import Button from "@/Components/ui/button/Button.vue";
import Checkbox from "@/Components/ui/checkbox/Checkbox.vue";
import Input from "@/Components/ui/input/Input.vue";
import Label from "@/Components/ui/label/Label.vue";
import Message from "@/Components/ui/message/Message.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

function submit() {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
}
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <Message v-if="status" severity="success">{{ status }}</Message>

        <Message severity="info" class="p-4 mb-6 text-center">
            <span class="block font-bold"> Admin access only.</span>
            Unauthorized users cannot log in.
        </Message>

        <form @submit.prevent="submit" class="space-y-4">
            <div class="flex flex-col gap-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    type="email"
                    v-model="form.email"
                    :invalid="form.errors.email"
                    autocomplete="email"
                    autofocus
                />
                <Message v-if="form.errors.email" severity="danger">
                    {{ form.errors.email }}
                </Message>
            </div>

            <div class="flex flex-col gap-2">
                <Label for="password">Password</Label>
                <Input
                    id="password"
                    type="password"
                    v-model="form.password"
                    :invalid="form.errors.password"
                    autocomplete="current-password"
                    autofocus
                />

                <Message v-if="form.errors.password" severity="danger">
                    {{ form.errors.password }}
                </Message>
            </div>

            <div class="block mt-4">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />

                    <span class="text-sm text-gray-600 ms-2">Remember me</span>
                </label>
            </div>

            <div class="flex items-center justify-end">
                <Button
                    class="w-full mt-2"
                    type="submit"
                    :disabled="form.processing"
                >
                    Log in
                </Button>
            </div>
        </form>
    </GuestLayout>
</template>
