/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */
(function($) {
    'use strict';
    class Installer {
        form = null;
        steps = {};
        firstStep = null;
        currentStep = null;
        submitButton = null;
        messagesElement = null;
        currentMessageElement = null;
        constructor(form) {
            this.form = form;
            this.steps = form.data('steps');
            this.firstStep = form.data('first-step');
            this.messagesElement = form.find('[data-progress-block]');
            this.submitButton = form.find('button[type="submit"]');
        }
        start() {
            this.currentStep = this.firstStep;
            this.messagesElement.html('');
            this.submitButton.append('<i class="fa-solid fa-spinner fa-spin ms-1"></i>');
            this.submitButton.attr('disabled', true);
            this.send();
        };
        beforeSend() {
            this.currentMessageElement = $('<li>');
            this.currentMessageElement.data('step', this.currentStep);
            this.currentMessageElement.text(`${this.steps[this.currentStep]}:`);
            this.currentMessageElement.append('<span class="ms-1"><i class="fa-solid fa-spinner fa-spin"></i></span>');
            this.messagesElement.append(this.currentMessageElement);
        };
        send() {
            this.beforeSend();
            $.ajax({
                type: 'POST',
                url: UrlManager.createUrl('/install/default/install', { step: this.currentStep }),
                data: this.form.serialize(),
                showNoty: false,
            }).then((response) => {
                if (!response) {
                    this.setError('Something wrong');
                    return;
                }
                if (response.error) {
                    this.setError(response.error);
                    return;
                }
                this.stepSuccess();
                if (response.done) {
                    this.done();
                } else {
                    this.currentStep = response.nextStep;
                    this.send();
                }
            });
        };
        stepSuccess() {
            this.currentMessageElement.find('span')
                .removeClass('text-danger')
                .addClass('text-success')
                .html('<i class="fa-solid fa-check"></i>');
        };
        setError(error) {
            this.currentMessageElement.find('span').addClass('text-danger').text(error);
        };
        done() {
            $('[data-install-success]').show();
            this.submitButton.hide();
        };
    }

    $.fn.extend({
        installForm() {
            return $(this).each(() => {
                const $this = $(this);
                $(document).on('ajaxBeforeSend', $this, (event, jqXHR, settings)  => {
                    settings.showNoty = false;
                });
                const installer = new Installer($this);
                $this.on('beforeSubmit', (event) => {
                    event.preventDefault();
                    installer.start();
                    return false;
                });
            });
        }
    });
    $('#install-form').installForm();
})(jQuery);
