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
                const ratio = w/h;
                const targetRatio = 1280/1024;
                const isHigher = ratio < targetRatio;
                $("img", $preview).removeClass("w-100");
                if (isHigher) {
                    $("img", $preview).addClass("w-100");
                    $("img", $preview).css("margin", "-" + (($preview.width() / ratio) - ($preview.width() / targetRatio)) * 0.5 + "px 0");
                    return;
                }
                $("img", $preview).css("width", ratio / targetRatio * 100 + "%");
                $("img", $preview).css("margin", "0 -" + ((($preview.width() / targetRatio) * ratio) - $preview.width()) * 0.5 + "px");
            };
        });
    }

    function checkMimeType(file, types) {
        if (window.FileReader && window.Blob) {
            return file.type.match(types);
        }
        return true;
    }

    function initImagePreview($input) {
        $input.data("old", "");
        $input.change(function (e) {
            //alert('init');
            e.preventDefault();
            const oldVal = $(this).data("old");

            let $container = $(this).closest(".image");
            let $preview = $(".image-input-preview", $container);
            let $previewLoader = $(".img-prev-ol", $container);

            $preview.removeClass("border border-danger");
            $(".invalid-feedback", $container).removeClass("d-block");

            // If is an image : check and change
            if (e.target.files.length > 0) {
                $previewLoader.show();
                const image = e.target.files[0];
                const size = image.size / 1024 / 1024;

                // check Mime type
                if (!checkMimeType(image, "image/png|image/gif|image/jpeg")) {
                    $preview.addClass("border border-danger");
                    $(".img-alert .form-error-message", $container).html("Les formats supportés sont png, jpeg et gif.");
                    $(".img-alert", $container).addClass("d-block");
                    $(this).val(oldVal);
                    $previewLoader.hide();
                    return false;
                }

                const maxSize = 5;
                //CheckSize
                if (size > maxSize) {
                    $preview.addClass("border border-danger");
                    $(".img-alert .form-error-message", $container).html("L'image est trop volumineuse (Maxi : " + maxSize + " Mo) !");
                    $(".img-alert", $container).addClass("d-block");
                    $(this).val(oldVal);
                    $previewLoader.hide();
                    return false;
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
    initImagePreview($(".image input"));

$("form[name='trick']").submit(function(e){
    let emptyImage = false;
    $(".image", $(this)).each(function(){
        const $fileInput = $("input", $(this));
        if ($fileInput.val() === "") {
            $(".image-input-preview", $(this)).addClass("border border-danger");
            $(".img-alert .form-error-message", $(this)).html("Merci de choisir une image valide ou de supprimer cette image.");
            $(".img-alert", $(this)).addClass("d-block");
            emptyImage = true;
        }
    });

    if (emptyImage) {
        $("#mediasList").collapse("show");
        $("#mediasList")[0].scrollIntoView({behavior: "smooth", block: "end", inline: "end"});
        return false;
    }
})

