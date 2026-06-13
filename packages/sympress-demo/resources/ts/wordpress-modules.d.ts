declare module '@wordpress/block-editor' {
    export const InspectorControls: unknown;
    export function useBlockProps(props?: Record<string, unknown>): Record<string, unknown>;
}

declare module '@wordpress/blocks' {
    export function registerBlockType<TAttributes = Record<string, unknown>>(
        name: string,
        settings: Record<string, unknown>,
    ): void;
}

declare module '@wordpress/components' {
    export const PanelBody: unknown;
    export const RangeControl: unknown;
    export const TextControl: unknown;
}

declare module '@wordpress/element' {
    export const Fragment: unknown;
    export function createElement(
        type: unknown,
        props?: Record<string, unknown> | null,
        ...children: unknown[]
    ): unknown;
}

declare module '@wordpress/i18n' {
    export function __(text: string, domain?: string): string;
}

declare module '@wordpress/server-side-render' {
    const ServerSideRender: unknown;

    export default ServerSideRender;
}
