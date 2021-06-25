const $LOGIN_MODAL = $("#loginModal");

function removeHashGet(value) {
    const uri = window.location.toString();
    if (uri.indexOf(value) > 0) {
        const CLEAN_URI = uri.substring(
            0,
            uri.indexOf(value)
        );

        window.history.replaceState(
            {},
            document.title,
            CLEAN_URI
        );
    }
}

if (location.hash !== "#login") {
    removeHashGet("?target=");
}
if (location.hash === "#login") {
    removeHashGet("#");
    $LOGIN_MODAL.modal("show");
}
if (location.hash === "") {
    removeHashGet("#");
}
if (location.hash === "#top") {
    removeHashGet("#");
}
window.onhashchange = function(){
    if (location.hash === "") {
        removeHashGet("#");
    }
    if (location.hash === "#top") {
        removeHashGet("#");
    }
};

$LOGIN_MODAL.on("hide.bs.modal", function(e){
    removeHashGet("?target=");
    if (location.hash === "login") {
        removeHashGet("#");
    }
});

const $deleteTrickModal = $("#trickDeleteModal");
const $deleteTrickButton = $("button.delete-btn", $deleteTrickModal);

$deleteTrickModal.on('show.bs.modal', function (e) {
    const $button = $(e.relatedTarget);
    $deleteTrickButton.removeClass("disabled");
    $deleteTrickButton.prop('disabled', false);
    $(".trick-name", $(this)).text($button.data("name"));
    $deleteTrickButton.data("trick-id", $button.data("id"));
});

$deleteTrickButton.click(function (e) {
    //alert($(this).data("token") + " // " + $(this).data("trick-id"));
    $(this).addClass("disabled");
    $(this).prop('disabled', true);
    let id = $(this).data("trick-id");
    let token = $(this).data("token");
    $.ajax({
        url: window.location.origin + "/profile/ajax/deletetrick",
        method: "POST",
        data: {
            token,
            id
        },
        dataType: "json",
        success(data) {
            if (data.success) {
                $("#tricksList article.trick-item.trick-" + id).remove();
                showFlashMessage("primary", "Le trick a bien été supprimé.");
                $deleteTrickModal.modal('hide');
                return;
            }
            showFlashMessage("danger", data.error);
            $deleteTrickModal.modal('hide');

        },
        error(e) {
            showFlashMessage("danger", "Une erreur s'est produite.");
            $deleteTrickModal.modal('hide');
        }
    });
})

