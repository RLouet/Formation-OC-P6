/*global TrickImgPath, trickImages*/

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
});