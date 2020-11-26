$('.hidden-form').hide();

$('.edit-video').click(function() {
    let id = $(this).attr('data-video');
    $('.hidden-form').hide();
    $(`.hidden-form[data-video=${id}]`).show();
})

$('.edit-image').click(function() {
    let idimg = $(this).attr('data-img');
    $('.hidden-form').hide();
    $(`.hidden-form[data-img=${idimg}]`).show();
})