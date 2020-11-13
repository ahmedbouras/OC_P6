// Creation des éléments
var div = $('<div></div>').attr({
    class: 'card-body p-0',
});
var form = $('<form></form>').attr({
    class: 'd-flex flex-column',
    method: 'post',
});
var input = $('<input>');
var btn = $('<button></button>').attr(
    'type', 'submit'
);

// Placement des éléments dans le bon ordre
$(div).append(form);
$(form).append(input);
$(form).append(btn);

// Evenement pour les images associés à un Trick
$('.edit-image').click(function() {
    let idImg = $(this).attr('data-img');
    let idTrick = $(this).attr('data-trick');

    $(form).attr({
        action: `/image/update/${idImg}/trick/${idTrick}`,
        enctype: 'multipart/form-data',
    });
    $(input).attr({
        type: 'file',
        name: 'newImg',
    })
    $(btn).text("Modifier l'image");

    $(`.card[data-img=${idImg}]`).append(div);
})

// Evenement pour les vidéos associés à un Trick
$('.edit-video').click(function() {
    let idVideo = $(this).attr('data-video');
    let idTrick = $(this).attr('data-trick');
    
    $(form).attr(
        'action', `/video/update/${idVideo}/trick/${idTrick}`
    );
    $(input).attr({
        type: 'text',
        name: 'newUrl',
    })
    $(btn).text("Modifier la video");

    $(`.card[data-video=${idVideo}]`).append(div);
})