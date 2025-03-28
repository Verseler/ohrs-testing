import { cva, type VariantProps } from 'class-variance-authority';

export { default as Message } from './Message.vue';

export const messageVariants = cva(
    'border',
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
        primary:
            'text-primary-700 border-primary-500 bg-white',
      },
      size: {
        default: 'p-2 rounded-md',
        xs: 'text-xs px-1.5 py-2 rounded-sm',
        sm: 'text-sm p-2 rounded-sm',
        md: 'text-md p-3 rounded-md',
        lg: 'text-lg p-3 rounded-lg',
      },
    },
    defaultVariants: {
        severity: 'default',
        size: 'default'
    },
  },
);


export type MessageVariants = VariantProps<typeof messageVariants>;
