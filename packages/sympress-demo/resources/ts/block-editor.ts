import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { PanelBody, RangeControl, TextControl } from '@wordpress/components';
import { createElement, Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

type NotesAttributes = {
    limit?: number;
    topic?: string;
};

type EditProps = {
    attributes: NotesAttributes;
    setAttributes: (attributes: Partial<NotesAttributes>) => void;
};

registerBlockType<NotesAttributes>('sympress-demo/notes', {
    title: __('SymPress Notes', 'sympress-demo'),
    edit: ({ attributes, setAttributes }: EditProps) => {
        const limit = typeof attributes.limit === 'number' ? attributes.limit : 5;
        const topic = typeof attributes.topic === 'string' ? attributes.topic : '';
        const blockProps = useBlockProps({
            className: 'sympress-demo-block-preview',
        });

        return createElement(
            Fragment,
            null,
            createElement(
                InspectorControls,
                null,
                createElement(
                    PanelBody,
                    {
                        title: __('Notes query', 'sympress-demo'),
                        initialOpen: true,
                    },
                    createElement(RangeControl, {
                        label: __('Limit', 'sympress-demo'),
                        min: 1,
                        max: 20,
                        value: limit,
                        onChange: (value: number | undefined): void => {
                            setAttributes({ limit: typeof value === 'number' ? value : 5 });
                        },
                    }),
                    createElement(TextControl, {
                        label: __('Topic slug', 'sympress-demo'),
                        value: topic,
                        onChange: (value: string): void => {
                            setAttributes({ topic: value });
                        },
                    }),
                ),
            ),
            createElement(
                'div',
                blockProps,
                createElement(ServerSideRender, {
                    block: 'sympress-demo/notes',
                    attributes: {
                        limit,
                        topic,
                    },
                }),
            ),
        );
    },
    save: () => null,
});
