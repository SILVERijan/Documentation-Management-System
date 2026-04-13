export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    avatar: string | null;
    role: 'admin' | 'user';
    status: 'active' | 'inactive';
    theme_preference?: 'light' | 'dark' | 'system';
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};
