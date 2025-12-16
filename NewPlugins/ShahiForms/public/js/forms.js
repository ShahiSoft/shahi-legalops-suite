/**
 * Forms Frontend JavaScript
 *
 * @package ShahiForms
 */

(function($) {
    'use strict';

    var ShahiFormsHandler = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            $('.shahi-form').on('submit', this.handleSubmit);
        },

        handleSubmit: function(e) {
            e.preventDefault();

            var $form = $(this);
            var $submitBtn = $form.find('.shahi-form-submit');
            var $messages = $form.find('.shahi-form-messages');
            var formId = $form.data('form-id');

            // Collect form data
            var formData = {};
            $form.find('input, textarea, select').each(function() {
                var $field = $(this);
                var name = $field.attr('name');
                
                if (name) {
                    if ($field.attr('type') === 'checkbox') {
                        formData[name] = $field.is(':checked') ? '1' : '0';
                    } else {
                        formData[name] = $field.val();
                    }
                }
            });

            // Disable submit button
            $submitBtn.prop('disabled', true).text(shahiForms.submittingText || 'Submitting...');
            $messages.html('');

            // Submit via AJAX
            $.ajax({
                url: shahiForms.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shahi_forms_submit',
                    nonce: shahiForms.nonce,
                    form_id: formId,
                    form_data: formData
                },
                success: function(response) {
                    if (response.success) {
                        $messages.html('<div style="padding: 15px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">' + response.data.message + '</div>');
                        $form[0].reset();
                    } else {
                        $messages.html('<div style="padding: 15px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;">' + response.data.message + '</div>');
                    }
                },
                error: function() {
                    $messages.html('<div style="padding: 15px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;">An error occurred. Please try again.</div>');
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).text($submitBtn.data('original-text') || 'Submit');
                }
            });
        }
    };

    $(document).ready(function() {
        // Store original button text
        $('.shahi-form-submit').each(function() {
            $(this).data('original-text', $(this).text());
        });

        ShahiFormsHandler.init();
    });

})(jQuery);
