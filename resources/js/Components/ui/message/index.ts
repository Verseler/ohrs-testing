import { cva, type VariantProps } from 'class-variance-authority';

export { default as Message } from './Message.vue';

export const messageVariants = cva(
    'p-2 border rounded-md',
  {
    variants: {
      severity: {
        default: 'border-primary-400 text-primary-500 bg-primary-50',
        success:
          'border-green-400 text-green-500 bg-green-50',
        info:
           'border-blue-400 text-blue-500 bg-blue-50',
        danger:
          'border-red-400 text-red-500 bg-red-50',
        secondary:
          'border-neutral-400 text-neutral-500 bg-neutral-50',

      }
    },
    defaultVariants: {
        severity: 'default',
    },
  },
);


export type MessageVariants = VariantProps<typeof messageVariants>;
