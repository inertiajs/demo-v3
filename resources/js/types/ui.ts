export type Appearance = 'light' | 'dark' | 'system';
export type ResolvedAppearance = 'light' | 'dark';

export type AppShellVariant = 'header' | 'sidebar';

export type LayerDialog = {
    title?: string;
    description?: string;
    variant?: 'modal' | 'slideover';
    size?: 'sm' | 'md' | 'lg' | 'xl';
};
