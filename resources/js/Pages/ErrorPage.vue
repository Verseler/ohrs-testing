<script setup>
import BackLink from "@/Components/BackLink.vue";
import { computed } from "vue";

const props = defineProps({ status: [Number, String] });

const title = computed(() => {
    return {
        400: "400: Bad Request",
        401: "401: Unauthorized",
        402: "402: Payment Required",
        403: "403: Forbidden",
        404: "404: Page Not Found",
        405: "405: Method Not Allowed",
        408: "408: Request Timeout",
        413: "413: Payload Too Large",
        415: "415: Unsupported Media Type",
        422: "422: Unprocessable Entity",
        429: "429: Too Many Requests",
        500: "500: Server Error",
        502: "502: Bad Gateway",
        503: "503: Service Unavailable",
        504: "504: Gateway Timeout",
    }[props.status] || `Error ${props.status}`;
});

const description = computed(() => {
    return {
        400: "Sorry, there was a problem with your request.",
        401: "Sorry, you need to log in to access this page.",
        402: "Sorry, payment is required to complete this action.",
        403: "Sorry, you are forbidden from accessing this page.",
        404: "Sorry, the page you are looking for could not be found.",
        405: "Sorry, this action is not allowed.",
        408: "Sorry, the request took too long to complete.",
        413: "Sorry, the data you sent is too large.",
        415: "Sorry, we don't support that file format.",
        422: "Sorry, we couldn't process your information.",
        429: "Sorry, you're making too many requests. Please slow down.",
        500: "Whoops, something went wrong on our servers.",
        502: "Sorry, we're having trouble connecting to upstream services.",
        503: "Sorry, we are doing some maintenance. Please check back soon.",
        504: "Sorry, one of our services is responding too slowly.",
    }[props.status] || "Sorry, something went wrong. Please try again later.";
});
</script>

<template>
    <main
        class="grid relative place-items-center px-6 py-24 min-h-screen bg-white sm:py-32 lg:px-8"
    >
        <div class="mb-20 text-center">
            <h1
                class="text-5xl font-semibold tracking-tight text-gray-900 text-balance sm:text-7xl"
            >
                {{ title }}
            </h1>
            <p
                class="mt-6 text-lg font-medium text-gray-500 text-pretty sm:text-xl/8"
            >
                {{ description }}
            </p>
            <div class="flex gap-x-6 justify-center items-center mt-10">
                <a
                    href="/"
                    class="px-3.5 py-2.5 text-sm font-semibold text-white rounded-md bg-primary-600 shadow-xs hover:bg-primary-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600"
                    >Go back home</a
                >
            </div>
        </div>

        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 1440 320"
            class="absolute bottom-0"
        >
            <path
                fill="#10b981"
                fill-opacity="1"
                d="M0,288L48,272C96,256,192,224,288,197.3C384,171,480,149,576,165.3C672,181,768,235,864,250.7C960,267,1056,245,1152,250.7C1248,256,1344,288,1392,304L1440,320L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"
            ></path>
        </svg>
    </main>
</template>
