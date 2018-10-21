(function () {
    const editor = jQuery('#contact-form-editor');
    if (editor[0]) {
        jQuery("#contact-form-editor-tabs").append('<li id="bpmonline-settings-tab" class="ui-state-default ui-corner-top ui-state-hover"' +
            'role="tab" tabindex="-1" aria-controls="bpmonline-settings-panel" aria-labelledby="ui-id-5" ' +
            'aria-selected="false" aria-expanded="false">' +
            '<a href="#bpmonline-settings-panel" class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-5">Bpm\'online fields mapping</a>' +
            '</li>');
        jQuery('#contact-form-editor').append('<div class="contact-form-editor-panel ui-tabs-panel ui-widget-content ui-corner-bottom" id="bpmonline-settings-panel" aria-labelledby="ui-id-5" role="tabpanel" aria-hidden="true" style="display: block;">' +
            '<div class="config-error"></div><h2>Bpm\'online</h2>\n' +
            '<fieldset id="bpmonline-fieldset">\n' +
            '<legend>Retrieving data structure from Bpm\'online. Please wait it can take around 30 seconds.</legend>\n' +
            '<p class="description" id="bpmonline-spinner">' +
            '<label><span class="spinner is-active" style="float:left"></span></label>' +
            '</p>' +
            '</fieldset>\n' +
            '</div>');
    }
}());
(function () {
    const postControl = jQuery('#post_ID');
    if (postControl[0]) {
        wp.ajax.post('loadBpmonlineSettings', {id: postControl[0].value}).done(function(response){
            jQuery("#bpmonline-spinner").remove();
            jQuery('#bpmonline-fieldset').html(response);
        }).fail( function(response) {
            jQuery("#bpmonline-spinner").remove();
            jQuery('#bpmonline-fieldset').html(response);
        });
    }
}());
