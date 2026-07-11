/**
 * Gutenberg Blocks for The Stitch Forms
 */
( function( wp ) {
    var registerBlockType = wp.blocks.registerBlockType;
    var el = wp.element.createElement;
    var ServerSideRender = wp.serverSideRender;
    var __ = wp.i18n.__;

    // Bridal Consultation Form Block
    registerBlockType( 'thestitch/bridal-form', {
        title: __( 'Bridal Consultation Form', 'thestitch-forms' ),
        icon: 'feedback', // dashicon
        category: 'widgets',
        description: __( 'Insert the Bridal Consultation Form.', 'thestitch-forms' ),
        
        edit: function( props ) {
            return el(
                'div',
                { className: 'thestitch-block-preview' },
                el( ServerSideRender, {
                    block: 'thestitch/bridal-form',
                    attributes: props.attributes,
                    // If serverSideRender doesn't find the block type, it won't render. 
                    // So we can fallback to displaying a placeholder. 
                } )
            );
        },
        save: function() {
            return null; // Rendered via PHP
        },
    } );

    // Recreate Form Block
    registerBlockType( 'thestitch/dream-outfit-form', {
        title: __( 'Recreate Form', 'thestitch-forms' ),
        icon: 'admin-appearance', // dashicon
        category: 'widgets',
        description: __( 'Insert the Multi-Step Recreate Form.', 'thestitch-forms' ),
        
        edit: function( props ) {
            return el(
                'div',
                { className: 'thestitch-block-preview' },
                el( ServerSideRender, {
                    block: 'thestitch/dream-outfit-form',
                    attributes: props.attributes
                } )
            );
        },
        save: function() {
            return null; // Rendered via PHP
        },
    } );
}( window.wp ) );