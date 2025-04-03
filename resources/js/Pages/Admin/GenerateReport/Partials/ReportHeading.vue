<script setup lang="ts">
import { formatDateTimeString, getMonthYear } from "@/lib/utils";
</script>

<template>
    <header
        :style="{
            textAlign: 'center',
            lineHeight: '1',
            display: 'flex',
            flexDirection: 'column',
            gap: '5px',
            marginBottom: '12px',
        }"
    >
        <!-- Upper side -->
        <div
            :style="{
                display: 'flex',
                alignItems: 'center',
                gap: '5px',
            }"
        >
            <!-- Image Left Side -->
            <img
                src="/images/denr10Logo.png"
                :style="{
                    objectFit: 'contain',
                    width: '100px',
                    height: '100px',
                }"
            />
            <!-- Center DENR Info -->
            <div
                :style="{
                    flex: 1,
                    display: 'flex',
                    flexDirection: 'column',
                    gap: '5px',
                }"
            >
                <div>Republic of the Philippines</div>
                <div>DEPARTMENT OF ENVIRONMENT AND NATURAL RESOURCES</div>
                <h3 :style="{ fontSize: '16px', margin: 0 }">
                    Regional Office
                </h3>
                <div>Brgy. Puntod, Cagayan de Oro City</div>
            </div>
            <!-- Space Right Side to balance the content of flex -->
            <div :style="{ width: '100px', height: '100px' }" />
        </div>

        <!-- Divider -->
        <hr
            :style="{
                width: '100%',
                marginTop: '10px',
                marginBottom: 0,
            }"
        />

        <!-- LowerSide side -->
        <div :style="{ display: 'flex', flexDirection: 'column', gap: '5px' }">
            <h1 :style="{ fontSize: '20px', margin: 0 }">
               ONLINE HOSTEL RESERVATION SYSTEM
            </h1>
            <div>Monthly Report for {{ getMonthYear(new Date()) }}</div>
            <div>Generated on: {{ formatDateTimeString(new Date()) }}</div>
        </div>
    </header>
</template>

<style scoped>
header {
    text-align: center;
    padding-bottom: 5px;
}
h1 {
    font-size: 1.5rem !important;
    font-weight: bolder;
}
h3 {
    font-weight: bold;
}
</style>
