export type Notification = {
    id: string;
    type: string;
    notifiable_type: string;
    notifiable_id: number;
    data: NotificationData;
    read_at: string;
    created_at: string;
    updated_at: string;
}

export type NotificationData = {
    message: string;
    link: string;
}

