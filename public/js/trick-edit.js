$(document).ready(function() {
    $("#trick_name").keyup(function () {
        $(".trick-name").text($(this).val());
    });

    const $addVideoModal = $("#videoFormModal");

    $addVideoModal.on('show.bs.modal', function (e) {
        $("#videoName", $(this)).removeClass('is-invalid');
        $("#videoName", $(this)).val("");
        $(".errors", $(this)).addClass("d-none");
        $(".errors div", $(this)).html("");
        let action = $(e.relatedTarget).data('action');
        switch (action) {
            case 'add':
                $("button[type=submit], .modal-title .action", $(this)).html("Ajouter");
                break;
            case 'edit':
                $("button[type=submit], .modal-title .action", $(this)).html("Modifier");
                $("button[type=submit]", $(this)).data("index", $(e.relatedTarget).data('index'));
                break;
        }
        $("button[type=submit]", $(this)).data("action", action);
    })

    const $addVideoForm = $('form', $addVideoModal);
    const $mediasContainer = $("#mediasList .medias-container");
    $(".video-prototype", $mediasContainer).data("index", $mediasContainer.find(".video").length);

    $addVideoForm.submit(function (e){
        e.preventDefault();
        let action = $("button[type=submit]", $(this)).data("action");
        //alert('sub');
        let value = $("#videoName", $(this)).val();
        if (value.match(/^[a-z0-9_-]{7,15}$/i)) {
            switch (action) {
                case 'add':
                    addVideo(value);
                    break;
                case 'edit':
                    editVideo(value, $("button[type=submit]", $(this)).data("index"));
                    break;
            }
            $addVideoModal.modal('hide');
            return true;
        }
        $("#videoName", $(this)).addClass('is-invalid');
        $(".errors", $(this)).removeClass("d-none");
        $(".errors div", $(this)).html("Le nom de la video n'est pas valide. ( entre 7 et 15 lettres, chiffres, - et _ )");
        return false;
    })

    function addVideo(value) {
        let prototype = $(".video-prototype", $mediasContainer).data("prototype");
        let index = $(".video-prototype", $mediasContainer).data("index");
        let newForm = prototype;
        newForm = newForm.replace(/__name__/g, index);
        $(".video-prototype", $mediasContainer).data("index", index + 1);
        let newVideoItem = $('<div class="col-md-2 px-1 video video-' + index + '">\n' +
            '                    <div class="embed-responsive embed-responsive-4by3">\n' +
            '                        <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/' + value + '?rel=0" allowfullscreen></iframe>\n' +
            '                    </div>\n' +
            '                    <div class="medias-management-btns text-right mt-2">\n' +
            '                        <span class="p-1 border border-dark rounded">\n' +
            '                           <a href="#" title="Modifier la vidéo" class="edit-btn px-2 border-right border-dark" data-toggle="modal" data-target="#videoFormModal" data-action="edit" data-index="' + index + '"><em class="far fa-edit"></em></a>\n' +
            '                           <a href="#" title="Supprimer la vidéo" class="delete-btn px-2" data-toggle="modal" data-target="#videoDeleteModal" data-index="' + index + '"><em class="far fa-trash-alt"></em></a>\n' +
            '                        </span>\n' +
            '                    </div>\n' +
            '                 </div>').append(newForm);
        $("input", newVideoItem).val(value);
        $mediasContainer.append(newVideoItem);
    }

    function editVideo(value, item) {
        const videoItem = $(".video-" + item, $mediasContainer);
        $("input", videoItem).val(value);
        $("iframe", videoItem).attr("src", "https://www.youtube.com/embed/" + value + "?rel=0");
    }

    const $deleteVideoModal = $("#videoDeleteModal");

    $deleteVideoModal.on('show.bs.modal', function (e) {
        $("button.delete-btn", $(this)).data("index", $(e.relatedTarget).data('index'));
    })
    $("button.delete-btn", $deleteVideoModal).on("click", function (){
        $(".video-" + $(this).data("index"), $mediasContainer).remove();
        $deleteVideoModal.modal("hide");
    })
});