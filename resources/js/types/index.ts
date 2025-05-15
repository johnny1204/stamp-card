export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export interface Child {
    id: number;
    name: string;
    birth_date?: string;
    age?: number | null;
    age_group?: string;
    target_stamps: number;
    avatar_path?: string;
    created_at: string;
    updated_at: string;
    // 統計情報
    todayStampsCount?: number;
    thisMonthStampsCount?: number;
    totalStampsCount?: number;
}

export interface Pokemon {
    id: number;
    name: string;
    type1?: string;
    type2?: string;
    genus?: string;
    is_legendary: boolean;
    is_mythical: boolean;
    image_url?: string;
    cry_url?: string;
}

export interface StampType {
    id: number;
    name: string;
    icon: string;
    color: string;
    description?: string;
}

export interface Stamp {
    id: number;
    child_id: number;
    stamp_type_id: number;
    pokemon_id: number;
    stamped_at: string;
    comment?: string;
    image_path?: string;
    child?: Child;
    pokemon?: Pokemon;
    stamp_type?: StampType;
}

export interface PageProps {
    auth?: {
        user?: User;
    };
    flash?: {
        success?: string;
        error?: string;
        newStamp?: {
            pokemon?: Pokemon;
            child?: Pick<Child, 'name'>;
        };
    };
    errors?: Record<string, string>;
}

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: Array<{
        url?: string;
        label: string;
        active: boolean;
    }>;
    next_page_url?: string;
    path: string;
    per_page: number;
    prev_page_url?: string;
    to: number;
    total: number;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    flash?: {
        success?: string;
        error?: string;
        newStamp?: {
            stamp: StampWithRelations;
            pokemon: Pokemon;
            celebration_message: string;
        };
    };
    children?: Child[];
};