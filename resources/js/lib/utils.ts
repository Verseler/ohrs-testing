import type { Updater } from '@tanstack/vue-table'
import type { Ref } from 'vue'
import { type ClassValue, clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export function valueUpdater<T extends Updater<any>>(updaterOrValue: T, ref: Ref) {
  ref.value
    = typeof updaterOrValue === 'function'
      ? updaterOrValue(ref.value)
      : updaterOrValue
}

//Convert the first letter of given string to capital
export function capitalized(value: string) {
    return value.charAt(0).toUpperCase() + value.slice(1);
}

//Add a delay before executing a callback
export function debounce<T extends (...args: any[]) => void>(func: T, delay: number = 500) {
    let timer: number;
    return (...args: Parameters<T>) => {
        clearTimeout(timer);
        timer = setTimeout(() => func(...args), delay);
    };
}


export function formatDate(date: Date | string | undefined) {
    if (!date) return null;

    const jsDate = new Date(date);
    const year = jsDate.getFullYear();
    const month = String(jsDate.getMonth() + 1).padStart(2, '0');
    const day = String(jsDate.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

export function formatDateString(date: string | Date): string {
    const d = new Date(date);

    if (isNaN(d.getTime())) {
        console.error("Invalid date input");
        return "";
    }

    const options: Intl.DateTimeFormatOptions = { month: "long", day: "2-digit", year: "numeric" };
    return d.toLocaleDateString("en-US", options).replace(",", ""); // Remove comma
}


export function getDaysDifference(startDate: string | Date, endDate: string | Date): number {
    const start = new Date(startDate);
    const end = new Date(endDate);

    if (isNaN(start.getTime()) || isNaN(end.getTime())) {
        console.error("Invalid date input");
        return 0;
    }

    const differenceInTime = end.getTime() - start.getTime();
    return Math.floor(differenceInTime / (1000 * 60 * 60 * 24)); // Convert milliseconds to days
}


