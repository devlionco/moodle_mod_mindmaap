define([
    "jquery",
    "core/modal_factory",
    "core/modal_events",
], function ($, ModalFactory, ModalEvents) {
    "use strict";

    return {
        init: function (url, title) {
            $("#page-mod-mindmaap-view #mindmaapopen").on("click", function () {
                ModalFactory.create({
                    type: ModalFactory.types.SAVE_CANCEL,
                    title: title,
                    body: '<embed class="mindmaap-embed" src=' + url + ' />',
                    large: true
                }).then(function (modal) {
                    var root = modal.getRoot();
                    root.on(ModalEvents.save, function () {
                    });
                    modal.show();
                });

            });
        }
    };
});
