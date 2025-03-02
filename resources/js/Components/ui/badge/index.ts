import { cva, type VariantProps } from 'class-variance-authority'

export { default as Badge } from './Badge.vue'

export const badgeVariants = cva(
  'inline-flex items-center rounded-md border border-neutral-200 px-2.5 shadow-sm py-1 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-neutral-950 focus:ring-offset-2 dark:border-neutral-800 dark:focus:ring-neutral-300',
  {
    variants: {
      variant: {
        default:
          'border-transparent bg-neutral-900 text-neutral-50 dark:bg-neutral-50 dark:text-neutral-900',
        secondary:
          'border-transparent bg-neutral-100 text-neutral-900  dark:bg-neutral-800 dark:text-neutral-50',
        destructive:
          'border-transparent bg-red-500 text-neutral-50 dark:bg-red-900 dark:text-neutral-50',
        outline: 'text-neutral-950 dark:text-neutral-50',
      },
      severity: {
        success:
            'border-green-500 bg-green-50 text-green-500',
        warning:
            'border-yellow-500 bg-yellow-50 text-yellow-500',
        danger:
            'border-red-500 bg-red-50 text-red-500',
        info:
            'border-blue-500 bg-blue-50 text-blue-500',
        secondary:
            'border-neutral-300 bg-neutral-100 text-neutral-500',
      },
    },
    defaultVariants: {
      variant: 'default',
      severity: 'secondary'
    },
  },
)

export type BadgeVariants = VariantProps<typeof badgeVariants>
