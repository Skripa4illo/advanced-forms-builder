(function (blocks, element, blockEditor, components) {
    var el = element.createElement;
    var InspectorControls = blockEditor.InspectorControls;
    var TextControl = components.TextControl;
    var PanelBody = components.PanelBody;

    blocks.registerBlockType('afb/form-block', {
        title: 'AFB Advanced Form', // Название блока в меню
        icon: 'feedback',           // Иконка (стандартный dashicon)
        category: 'widgets',        // Категория блока
        attributes: {
            formId: {
                type: 'number',
                default: 1
            }
        },

        // Что отображается в самом редакторе Gutenberg
        edit: function (props) {
            var attributes = props.attributes;

            return [
                // 1. Настройки в правом сайдбаре
                el(InspectorControls, { key: 'setting' },
                    el(PanelBody, { title: 'Настройки формы', initialOpen: true },
                        el(TextControl, {
                            label: 'ID Формы',
                            type: 'number',
                            value: attributes.formId,
                            onChange: function (newId) {
                                props.setAttributes({ formId: parseInt(newId, 10) || 1 });
                            }
                        })
                    )
                ),
                // 2. Визуальное отображение блока в центре экрана
                el('div', { 
                    key: 'display',
                    style: { 
                        padding: '20px', 
                        background: '#f0f3f5', 
                        border: '2px dashed #0073aa',
                        textAlign: 'center',
                        borderRadius: '4px'
                    } 
                },
                    el('strong', null, 'Advanced Forms Builder — Блок формы'),
                    el('p', { style: { margin: '5px 0 0', color: '#666' } }, 'Выводится форма с ID: ' + attributes.formId)
                )
            ];
        },

        // На фронтенд мы ничего не сохраняем в БД, так как рендерим динамически через PHP
        save: function () {
            return null; 
        }
    });
})(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components
);