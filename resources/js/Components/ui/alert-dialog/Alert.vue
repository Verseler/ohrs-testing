<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from "@/Components/ui/alert-dialog";
import { Severity } from "@/types";
import { cn } from "@/lib/utils";

type AlertProps = {
    title: string;
    description?: string | null;
    cancelLabel?: string;
    confirmLabel?: string;
    onConfirm: () => void;
    severity?: Severity;
};

const {
    title,
    description,
    cancelLabel = "Cancel",
    confirmLabel = "Confirm",
    severity = "success",
    onConfirm,
} = defineProps<AlertProps>();

const model = defineModel();
</script>

<template>
    <AlertDialog v-model="model">
        <AlertDialogContent class='max-w-md'>
            <AlertDialogHeader>
                <AlertDialogTitle>{{ title }}</AlertDialogTitle>
                <AlertDialogDescription v-if="description">
                    {{ description }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>{{ cancelLabel }}</AlertDialogCancel>
                <AlertDialogAction
                    @click="onConfirm"
                    :class="
                        cn(
                            severity === 'danger' &&
                                'bg-red-500 hover:bg-red-600 text-white',
                            severity === 'success' &&
                                'bg-green-600 hover:bg-green-700 text-white'
                        )
                    "
                >
                    {{ confirmLabel }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
