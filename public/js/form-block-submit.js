$("form.submit-block").on("submit", function(){
    const $submitBtn = $("button[type='submit']", $(this));
    $submitBtn.prop("disabled", true);
    $submitBtn.addClass("disabled");
    return true;
});

function unblockBtn($button) {
    $button.prop("disabled", false);
    $button.removeClass("disabled");
}