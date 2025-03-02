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

