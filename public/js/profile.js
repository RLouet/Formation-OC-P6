$(document).ready(function() {

    function readFile(file, onLoadCallback) {
        const reader = new FileReader();
        reader.onload = onLoadCallback;
        reader.readAsDataURL(file);
    }

    function centerImagePreview(image, $preview) {
        readFile(image, function (e) {
            const img = new Image();
            img.src = e.target.result;
            img.onload = function () {
                const w = this.width;
                const h = this.height;
                const isPortrait = w < h;
                const ratio = w/h;
                $("img", $preview).removeClass("w-100").removeClass("h-100")
                if (isPortrait) {
                    $("img", $preview).addClass("w-100");
                    $("img", $preview).css("margin", "calc(50% - " + $preview.width() / ratio * 0.5 + "px) auto");
                    return;
                }
                $("img", $preview).addClass("h-100");
                $("img", $preview).css("margin", "auto calc(50% - " + $preview.width() * ratio * 0.5 + "px)");
            };
        });
    }

    function initImagePreview($input) {
        $input.data("old", "");
        $input.change(function(e) {
            e.preventDefault();
            const oldVal = $(this).data("old");

            let $container = $(this).closest(".image-input-container");
            let $preview = $(".image-input-preview", $container);
            let $previewLoader = $(".img-prev-ol", $container);

            $preview.removeClass("border-danger");
            $(".invalid-feedback", $container).removeClass("d-block");

            // If is an image : check and change
            if (e.target.files.length > 0) {
                $previewLoader.show();
                const image = e.target.files[0];
                console.log(image.type);

                // check Mime type
                if (window.FileReader && window.Blob) {
                    if(!image.type.match("image/png|image/gif|image/jpeg"))
                    {
                        $preview.addClass("border-danger");
                        $(".img-alert .form-error-message", $container).html("Les formats supportés sont png, jpeg et gif.");
                        $(".img-alert", $container).addClass("d-block");
                        $(this).val(oldVal);
                        $previewLoader.hide();
                        return true;
                    }
                }

                centerImagePreview(image, $preview);
                const src = URL.createObjectURL(image);
                $("img", $preview).attr("src", src);
                $(this).data("old", $(this).val());
                $previewLoader.hide();
                return true;
            }

            $(this).val(oldVal);
            return true;
        });
    }
    initImagePreview($("#profile_avatar"));
});