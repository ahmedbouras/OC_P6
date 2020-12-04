$("#load-more").click(function () { 
    var offset = Number($("#offset").val());
    var total = Number($("#total").val());
    var trick = Number($("#trick").val());

    var itemToDisplay = 4;
    offset += itemToDisplay;

    if (offset <= total) {
        $("#offset").val(offset);
        $.ajax({
            type: "post",
            url: "/more-comment",
            data: {offset:offset, trick:trick},
            success: function (response) {
                $(".comment-row:last").after(response).show();

                var rowno = offset + itemToDisplay;
                if (rowno > total) {
                    $("#load-more").text("Voir moins");
                }
            }
        });
    } else {
        $(`.comment-row:nth-child(${itemToDisplay})`).nextAll(".comment-row").remove();
        $("#offset").val(0);
        $("#load-more").text("Voir plus");
    }
});

$(".see-medias").click(function() { 
    $(".bloc-img-vids").css("display", 'initial');
    $(".bloc-see-medias").css("display", "none");    
});