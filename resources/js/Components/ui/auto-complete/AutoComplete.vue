<script setup lang="ts" generic="T">
import { ref, computed, onMounted, onBeforeUnmount } from "vue";

interface Props<T> {
    items: T[];
    placeholder?: string;
    invalid?: boolean;
}

const props = withDefaults(defineProps<Props<T>>(), {
    placeholder: "",
    invalid: false,
});

const model = defineModel<T>();

const emit = defineEmits<{
    (event: "item-selected", value: T): void;
}>();

const selectedIndex = ref(-1);
const showSuggestions = ref(false);
const container = ref<HTMLElement | null>(null);

const filteredItems = computed(() => {
    if (!model.value) return [];
    const searchTerm = model.value.toString().toLowerCase();
    return props.items.filter((item) =>
        String(item).toLowerCase().includes(searchTerm)
    );
});

const handleInput = () => {
    showSuggestions.value = true;
    selectedIndex.value = -1;
};

const handleArrowDown = () => {
    if (selectedIndex.value < filteredItems.value.length - 1) {
        selectedIndex.value++;
    }
};

const handleArrowUp = () => {
    if (selectedIndex.value > 0) {
        selectedIndex.value--;
    }
};

const selectItem = (item?: T) => {
    const selectedItem = item || filteredItems.value[selectedIndex.value];
    if (selectedItem) {
        model.value = selectedItem;
        emit("item-selected", selectedItem);
    }
    closeSuggestions();
};

const closeSuggestions = () => {
    showSuggestions.value = false;
    selectedIndex.value = -1;
};

const handleClickOutside = (event: MouseEvent) => {
    if (container.value && !container.value.contains(event.target as Node)) {
        closeSuggestions();
    }
};

onMounted(() => window.addEventListener("click", handleClickOutside));
onBeforeUnmount(() => window.removeEventListener("click", handleClickOutside));
</script>

<template>
    <div ref="container" class="relative w-full">
        <input
            type="text"
            v-model="model"
            :placeholder="placeholder"
            @input="handleInput"
            @keydown.down="handleArrowDown"
            @keydown.up="handleArrowUp"
            @keydown.esc="closeSuggestions"
            class="w-full h-12 bg-transparent rounded-md focus:ring-primary-800"
            :class="{
                'border-red-500 focus:border-red-500': invalid,
                'border-primary-800 focus:border-primary-500': !invalid,
            }"
        />

        <ul
            v-if="showSuggestions && filteredItems.length > 0"
            class="overflow-y-auto absolute bottom-full z-20 mb-1 w-full max-h-52 bg-white rounded border"
        >
            <li
                v-for="(item, index) in filteredItems"
                :key="String(item)"
                @click="selectItem(item)"
                :class="{ selected: index === selectedIndex }"
                class="px-4 py-2 cursor-pointer hover:bg-gray-100"
                tabindex='0'
            >
                {{ item }}
            </li>
        </ul>
    </div>
</template>
