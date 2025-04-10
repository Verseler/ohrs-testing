<script setup lang="ts">
import { Button } from "@/Components/ui/button";
import { FilterX } from "lucide-vue-next";

</script>


<template>
    <Button
        variant="destructive"
        size="icon"
        class="w-20 px-2 md:w-9"
    >
        <FilterX /><span class='block md:hidden'>Clear</span>
    </Button>
</template>
