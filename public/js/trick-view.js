/*global TrickImgPath, trickImages, generatePaginationItem, showFlashMessage*/

$(document).ready(function() {

    $("#mediasList").on("show.bs.collapse", function () {
        location.hash = "#trickMedias";
    });
    $("#mediasList").on("hide.bs.collapse", function () {
        location.hash = "";
    });
    $("#mediasList").on("shown.bs.collapse", function () {
        $(".trick-medias .view-medias").html("<em class=\"fas fa-arrow-up\"></em> Masquer les médias <em class=\"fas fa-arrow-up\"></em>").removeClass("btn-primary").addClass("btn-secondary");
    });
    $("#mediasList").on("hidden.bs.collapse", function () {
        $(".trick-medias .view-medias").html("<em class=\"fas fa-arrow-down\"></em> Voir les médias <em class=\"fas fa-arrow-down\"></em>").addClass("btn-primary").removeClass("btn-secondary");
    });

    $("#imageModal").on("show.bs.modal", function (event) {
        const button = $(event.relatedTarget);
        const recipient = button.data("index");
        const modal = $(this);
        const currentImage = trickImages[recipient];
        modal.find(".modal-body img").attr("src", TrickImgPath + currentImage );
        modal.find(".modal-body .nav.next").data("index", recipient + 2 > trickImages.length ? 0 : recipient + 1 );
        modal.find(".modal-body .nav.prev").data("index", recipient - 1 < 0 ? trickImages.length - 1 : recipient - 1 );
    });

    $("#imageModal .modal-body .nav").on("click", function (event) {
        const recipient = $(this).data("index");
        const currentImage = trickImages[recipient];
        $("#imageModal .modal-body img").attr("src", TrickImgPath + currentImage);
        $("#imageModal .modal-body .nav.next").data("index", recipient + 2 > trickImages.length ? 0 : recipient + 1 );
        $("#imageModal .modal-body .nav.prev").data("index", recipient - 1 < 0 ? trickImages.length - 1 : recipient - 1 );
    });

    $("#commentForm").on("submit", function (e){
        e.preventDefault();
        const $button = $("button", $(this));
        const $textarea = $("textarea", $(this));
        const $errors = $(".invalid-feedback", $(this));
        $errors.removeClass("d-block");
        $textarea.removeClass("is-invalid");
        $("span", $errors).remove();

        $button.prop("disabled", true);
        $button.addClass("disabled");
        $button.html("<span class=\"spinner-border spinner-border-sm\" role=\"status\" aria-hidden=\"true\"></span> Envoi...");
        $.ajax({
            url: $(this).attr("action"),
            method: $(this).attr("method"),
            data: $(this).serialize(),
            dataType: "json",
            success(data) {
                if (data.success) {
                    $("#MessageItemsContainer").prepend(generatePaginationItem("message", data.message, data.userRoles));
                    $textarea.val("");
                    showFlashMessage("primary", "Ton commentaire a bien été ajouté.");
                    $button.removeClass("disabled");
                    $button.prop("disabled", false);
                    $button.html("Valider");
                    return;
                }
                if (data.error) {
                    showFlashMessage("danger", data.error);
                }
                if (data.formErrors && data.formErrors.length > 0) {
                    showFlashMessage("danger", "Ton commentaire n'est pas valide.");
                    for (const error of data.formErrors) {
                        let $error = $("<span class=\"d-block\"><span class=\"form-error-icon badge badge-danger text-uppercase\">Erreur</span><span class=\"form-error-message\">" + error + "</span></span>");
                        $errors.append($error);
                    }
                    $errors.addClass("d-block");
                    $textarea.addClass("is-invalid");
                }

                $button.removeClass("disabled");
                $button.prop("disabled", false);
                $button.html("Valider");
            },
            error(e) {
                showFlashMessage("danger", "Une erreur s'est produite.");
                $button.removeClass("disabled");
                $button.prop("disabled", false);
                $button.html("Valider");
            }
        });
    });

    const $messageDeleteModal = $("#messageDeleteModal");

    $messageDeleteModal.on("show.bs.modal", function (event) {
        const $button = $(event.relatedTarget);
        const $modalButton = $("#deleteCommentBtn", $(this));
        $modalButton.removeClass("disabled");
        $modalButton.prop("disabled", false);
        $modalButton.data("id", $button.data("id"))
    });

    $("#deleteCommentBtn").on("click", function (e){
        const $button = $(this);
        const $messageItem = $("#MessageItemsContainer .trick-message-" + $button.data("id"));

        $button.prop("disabled", true);
        $button.addClass("disabled");
        $.ajax({
            url: "/profile/ajax/deletecomment",
            method: "POST",
            data: {
                id: $button.data("id"),
                token: $button.data("token")
            },
            dataType: "json",
            success(data) {
                if (data.success) {
                    $messageItem.remove();
                    showFlashMessage("primary", "Le commentaire a bien été supprimé.");
                    $messageDeleteModal.modal("hide");
                    return;
                }
                showFlashMessage("danger", data.error);
                $messageDeleteModal.modal("hide");
            },
            error(e) {
                showFlashMessage("danger", "Une erreur s'est produite.");
                $messageDeleteModal.modal("hide");
            }
        });
    });
});