/*global initImagePreview, updateImagesPreviews*/

$(document).ready(function() {
    const $heroChoiceModal = $("#heroChoiceModal");
    const $mediasContainer = $("#mediasList .medias-container");

    initImagePreview($(".image input"));

    const targetRatio = 1280/1024;
    function updateHeroImagesPreviews() {
        const $imageItems = $(".new-image-item", $heroChoiceModal);
        const itemWidth = $(".img-container", $imageItems).first().width();
        updateImagesPreviews($imageItems, itemWidth, targetRatio);
    }
    function updateTrickImagesPreviews() {
        const $imageItems = $("div[class^='new-image-'], div[class*=' new-image-']", $mediasContainer);
        const itemWidth = $(".image-input-preview", $imageItems).first().width();
        updateImagesPreviews($imageItems, itemWidth, targetRatio);
        updateHeroImagesPreviews();
    }
    window.onresize = updateTrickImagesPreviews;

    $("#mediasList").on("shown.bs.collapse", function () {
        updateTrickImagesPreviews();
    });

    $("#trick_name").keyup(function () {
        $(".trick-name").text($(this).val());
    });

    const $addVideoModal = $("#videoFormModal");

    $addVideoModal.on("show.bs.modal", function (e) {
        $("#videoName", $(this)).removeClass("is-invalid");
        $("#videoName", $(this)).val("");
        $(".errors", $(this)).addClass("d-none");
        $(".errors div", $(this)).html("");
        let action = $(e.relatedTarget).data("action");
        switch (action) {
            case "add":
                $("button[type=submit], .modal-title .action", $(this)).html("Ajouter");
                break;
            case "edit":
                $("button[type=submit], .modal-title .action", $(this)).html("Modifier");
                $("button[type=submit]", $(this)).data("index", $(e.relatedTarget).data("index"));
                break;
        }
        $("button[type=submit]", $(this)).data("action", action);
    });

    const $addVideoForm = $("form", $addVideoModal);
    $(".video-prototype", $mediasContainer).data("index", $mediasContainer.find(".video").length);

    function addVideo(value) {
        let prototype = $(".video-prototype", $mediasContainer).data("prototype");
        let index = $(".video-prototype", $mediasContainer).data("index");
        let newForm = prototype;
        newForm = newForm.replace(/__name__/g, index);
        $(".video-prototype", $mediasContainer).data("index", index + 1);
        let newVideoItem = $("<div class=\"col-6 col-md-2 px-1 pb-2 video video-" + index + "\">\n" +
            "                    <div class=\"embed-responsive embed-responsive-4by3\">\n" +
            "                        <iframe class=\"embed-responsive-item\" src=\"https://www.youtube.com/embed/" + value + "?rel=0\" allowfullscreen></iframe>\n" +
            "                    </div>\n" +
            "                    <div class=\"medias-management-btns text-right mt-2\">\n" +
            "                        <span class=\"p-1 border border-dark rounded\">\n" +
            "                           <a href=\"#\" title=\"Modifier la vidéo\" class=\"edit-btn px-2 border-right border-dark\" data-toggle=\"modal\" data-target=\"#videoFormModal\" data-action=\"edit\" data-index=\"" + index + "\"><em class=\"far fa-edit\"></em></a>\n" +
            "                           <a href=\"#\" title=\"Supprimer la vidéo\" class=\"delete-btn px-2\" data-toggle=\"modal\" data-target=\"#mediaDeleteModal\" data-item=\".video-" + index + "\" data-type=\"la video\"><em class=\"far fa-trash-alt\"></em></a>\n" +
            "                        </span>\n" +
            "                    </div>\n" +
            "                 </div>").append(newForm);
        $("input", newVideoItem).val(value);
        $mediasContainer.append(newVideoItem);
    }

    function editVideo(value, item) {
        const videoItem = $(".video-" + item, $mediasContainer);
        $("input", videoItem).val(value);
        $("iframe", videoItem).attr("src", "https://www.youtube.com/embed/" + value + "?rel=0");
    }

    $addVideoForm.submit(function (e){
        e.preventDefault();
        let action = $("button[type=submit]", $(this)).data("action");
        let value = $("#videoName", $(this)).val();
        if (value.match(/^[a-z0-9_-]{7,15}$/i)) {
            switch (action) {
                case "add":
                    addVideo(value);
                    break;
                case "edit":
                    editVideo(value, $("button[type=submit]", $(this)).data("index"));
                    break;
            }
            $addVideoModal.modal("hide");
            return true;
        }
        $("#videoName", $(this)).addClass("is-invalid");
        $(".errors", $(this)).removeClass("d-none");
        $(".errors div", $(this)).html("Le nom de la video n'est pas valide. ( entre 7 et 15 lettres, chiffres, - et _ )");
        return false;
    });

    const $deleteMediaModal = $("#mediaDeleteModal");

    $deleteMediaModal.on("show.bs.modal", function (e) {
        $("span.type", $(this)).html($(e.relatedTarget).data("type"));
        $("button.delete-btn", $(this)).data("item", $(e.relatedTarget).data("item"));
    });

    $("button.delete-btn", $deleteMediaModal).on("click", function (){
        $($(this).data("item"), $mediasContainer).remove();
        $deleteMediaModal.modal("hide");
    });

    $(".new-image-prototype", $mediasContainer).data("index", $mediasContainer.find("div[class^='new-image-'], div[class*=' new-image-']").length);

    $(".add-image").on("click", function(e){
        const prototype = $(".new-image-prototype", $mediasContainer).data("prototype");
        const index = $(".new-image-prototype", $mediasContainer).data("index");
        const $newImageItem = $(prototype.replace(/__name__/g, index));
        $(".video-prototype", $mediasContainer).before($newImageItem);
        $(".new-image-prototype", $mediasContainer).data("index", index + 1);
        $("label.edit-btn", $newImageItem).click();
        initImagePreview($("input", $newImageItem), 1280, 1024, 5);
    });

    $heroChoiceModal.on("show.bs.modal", function (e) {
        const $imagesList = $(".trick-images-list", $(this));
        $imagesList.html("");
        let imageCount = 0;
        $("div[class^='new-image-'], div[class*=' new-image-']", $mediasContainer).each(function(){
            if ($("input[type='file']", $(this)).val()) {
                imageCount++;
                const $image = $(".img-input-preview",$(this));
                let $imageItem = $("<div class=\"col-6 col-md-3 p-1 new-image-item\"><div class=\"overflow-hidden img-container w-100\">" + $image.clone()[0].outerHTML + "<div class=\"heroChoiceFieldContainer\"><label for=\"heroChoice" + imageCount + "\"><input type=\"radio\" id=\"heroChoice" + imageCount + "\" name=\"hero_choice\" value=\"new-" + $(this).data("index") + "\"></label></div></div></div>");
                $imagesList.append($imageItem);
            }
        });
        if (!imageCount) {
            $imagesList.html("<p>Il n'y a pas d'image disponible.</p>");
        }
    });
    $heroChoiceModal.on("shown.bs.modal", function (e) {
        updateHeroImagesPreviews();
    });

    const $heroChoiceForm = $("form", $heroChoiceModal);

    $heroChoiceForm.submit(function (e){
        e.preventDefault();
        const choice = $("input[name='hero_choice']:checked", $(this)).length > 0?$("input[name='hero_choice']:checked", $(this)).val():null;
        if (choice) {
            const re = /^(?<type>(new|old))-(?<index>(\d){1,4})$/gi;
            let found = choice.matchAll(re);
            found = Array.from(found);
            //alert("Type = " + found[0].groups['type'] + " // Index = " + found[0].groups['index'] + " // " + found[0][0]);
            $("form[name='trick'] input#trick_hero").val(found[0][0]);
            const imgSrc = $(".image." + found[0].groups["type"] + "-image-" + found[0].groups["index"] + " .img-input-preview").attr("src");
            $("header .trick-hero").css("background-image", "url('" + imgSrc + "')");
            $heroChoiceModal.modal("hide");
        }

        const string = "new-5797";
    });
});