/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magetop.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magetop.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magetop
 * @package     Magetop_Osc
 * @copyright   Copyright (c) Magetop (https://www.magetop.com/)
 * @license     https://www.magetop.com/LICENSE.txt
 */

define([
    'jquery'
], function ($) {
    "use strict";

    $.widget('magetop.survey', {
        options: {
            url: ''
        },

        _create: function () {
            this.initObserve();
        },

        initObserve: function () {
            var self = this;

            $("#new-answer").blur(function () {
                self.addNewAnswer();
            });

            $('#survey').on('click', '#remove-answer', function () {
                $(this).parent().remove();
                $('#new-answer').show();
            });

            $("#submit-answers").click(function () {
                self.submitAnswers();
            });

        },

        submitAnswers: function () {
            var self = this;
            var answerChecked = [];
            $(".list-answer  input:checkbox:checked").each(function () {
                answerChecked.push($(this).parent().next().children('span').text());
            });
            if (answerChecked.length > 0) {
                $.ajax({
                    method: 'POST',
                    url: this.options.url,
                    data: {answerChecked: answerChecked}
                }).done(function (response) {
                    if (response.status == 'success') $('.survey-content').hide();
                    self.addSurveyMessage(response.status, response.message);
                });
            } else {
                self.addSurveyMessage('notice', 'You need to choose answer.')
            }
        },

        addNewAnswer: function () {
            var newAnswer = $('#new-answer').val();
            if (newAnswer.length > 0) {
                var d = new Date();
                $(  '<div class="survey-answer">' +
                        '<div class="checkbox-survey">' +
                            '<input type="checkbox" value="_' + d.getTime() + ' " checked/>' +
                        '</div>' +
                        '<div class="option-value">' +
                            '<span id="other-answer"></span>' +
                        '</div>' +
                    '<span id="remove-answer">X</span>' +
                    '</div>'
                ).insertBefore(".option-survey-new");
                $('#other-answer').text(newAnswer);
                $('#new-answer').val('').hide();
            }
        },
        addSurveyMessage: function (type, message) {
            $("#survey-message").html('<div class="' + type + ' message"><span>' + message + '</span></div>');
        }
    });

    return $.magetop.survey;
});
