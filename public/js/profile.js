/*global initImagePreview, updateImagesPreviews*/

$(document).ready(function() {
    initImagePreview($("#profile_avatar"), 256, 256, 5);

    const targetRatio = 256/256;
    function updateAvatarPreview() {
        const $imageItem = $("label[for='profile_avatar'].avatar-preview");
        const itemWidth = $imageItem.first().width();
        updateImagesPreviews($imageItem, itemWidth, targetRatio);
    }
    window.onresize = updateAvatarPreview;
});