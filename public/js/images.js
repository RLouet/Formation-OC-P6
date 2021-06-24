 function readFile(file, onLoadCallback) {
        const reader = new FileReader();
        reader.onload = onLoadCallback;
        reader.readAsDataURL(file);
    }

    function centerImagePreview(image, $preview, targetWidth, targetHeight) {
        readFile(image, function (e) {
            const img = new Image();
            img.src = e.target.result;
            img.onload = function () {
                const w = this.width;
                const h = this.height;
                const ratio = w/h;
                const targetRatio = targetWidth/targetHeight;
                const isHigher = ratio < targetRatio;
                $("img", $preview).removeClass("w-100");
                if (isHigher) {
                    $("img", $preview).css("width", "100%");
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

    function initImagePreview($input, targetWidth, targetHeight, maxSize) {
        let $img = $("img", $(".image-input-preview", $input.closest(".image-input-container")));
        $img.data("old", $img.attr("src"));
        $input.change(function (e) {
            const $container = $(this).closest(".image-input-container");
            const $preview = $(".image-input-preview", $container);
            const oldVal = $("img", $preview).data("old")?$("img", $preview).data("old"):"/imgs/no-image.png";
            const $previewLoader = $(".img-prev-ol", $container);
            e.preventDefault();


            $preview.removeClass("border-danger");
            $(".invalid-feedback", $container).removeClass("d-block");

            // If is an image : check and change
            if (e.target.files.length > 0) {
                $previewLoader.removeClass("d-none");
                const image = e.target.files[0];
                const size = image.size / 1024 / 1024;

                // check Mime type
                if (!checkMimeType(image, "image/png|image/gif|image/jpeg")) {
                    $preview.addClass("border-danger");
                    $(".img-alert .form-error-message", $container).data("to-display", 1);
                    $(".img-alert .form-error-message", $container).html("Les formats supportés sont png, jpeg et gif.");
                    $(".img-alert", $container).addClass("d-block");
                    $(this).val("");
                    $previewLoader.addClass("d-none");
                    return true;
                }

                //CheckSize
                if (size > maxSize) {
                    $preview.addClass("border-danger");
                    $(".img-alert .form-error-message", $container).data("to-display", true);
                    $(".img-alert .form-error-message", $container).html("L'image est trop volumineuse (Maxi : " + maxSize + " Mo) !");
                    $(".img-alert", $container).addClass("d-block");
                    $(this).val("");
                    $previewLoader.addClass("d-none");
                    return false;
                }

                centerImagePreview(image, $preview, targetWidth, targetHeight);
                const src = URL.createObjectURL(image);
                //alert(src);
                $("img", $preview).attr("src", src);
                //$(this).data("old", $(this).val());
                $previewLoader.addClass("d-none");
                return true;
            }
            //centerImagePreview(oldVal, $preview, targetWidth, targetHeight);

            $("img", $preview).css("width", "100%");
            $("img", $preview).css("margin", "0");
            if (oldVal === "/imgs/no-image.png") {
                if (!$(".img-alert .form-error-message", $container).data("to-display") === true) {
                    $(".img-alert .form-error-message", $container).html("Merci de choisir une image valide ou de supprimer cette image.");
                }
                $(".img-alert .form-error-message", $container).data("to-display", false);
                $(".img-alert", $container).addClass("d-block");
            }
            $("img", $preview).attr("src", oldVal);
            return false;
        });
    }

    function updateImagesPreviews($imageItems, itemWidth, targetRatio) {
        $imageItems.each(function(){
            const ratio = $("img", $(this))[0].width / $("img", $(this))[0].height;
            if (ratio < targetRatio) {
                $("img", $(this)).css("margin", "-" + ((itemWidth / ratio) - (itemWidth / targetRatio)) * 0.5 + "px 0");
                return;
            }
            $("img", $(this)).css("margin", "0 -" + (((itemWidth / targetRatio) * ratio) - itemWidth) * 0.5 + "px");

        });
    }


