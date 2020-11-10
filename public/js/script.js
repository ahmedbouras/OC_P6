$('.hidden-form').hide();

$('.edit-video').click(function() {
    let idVideo = $(this).attr('data-id');
    $('.hidden-form').hide();
    $(`.hidden-form[data-id=${idVideo}]`).show();
})