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
    const d = typeof date === 'string' ? new Date(date) : date;

    if (isNaN(d.getTime())) {
        console.error("Invalid date input");
        return "";
    }

    return d.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      })
}

export function formatDateTimeString(date: string | Date): string {
    const d = typeof date === 'string' ? new Date(date) : date;

    if (isNaN(d.getTime())) {
        console.error("Invalid date input");
        return "";
    }

    return d.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
}


export function formatYear(date: Date | string | undefined): string | null {
    if (!date) return null;

    const jsDate = new Date(date);

    if (isNaN(jsDate.getTime())) {
        console.error("Invalid date input");
        return null;
    }

    const year = jsDate.getFullYear();
    return `${year}`;
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


export function formatCurrency(amount: number): string {
    return amount.toLocaleString('en-PH', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    })
  }


export function yesterdayDate() {
    return new Date(Date.now() - 1000 * 60 * 60 * 24);
}

export function tomorrowDate() {
   return new Date(+new Date() + 86400000);
}


//remove element in an array using index
export function removeItem<T>(arr: Array<T>, index: number): Array<T> {
    if (index > -1) {
      arr.splice(index, 1);
    }
    return arr;
  }

  export function isObjectEmpty<T>(obj: T): boolean {
    if (typeof obj !== 'object' || obj === null) {
        return false; // Not an object, or is null
    }

    if (Array.isArray(obj)) {
        return obj.length === 0; // Check for empty array
    }

    return Object.keys(obj).length === 0;
}
