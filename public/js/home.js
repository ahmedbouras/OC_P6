$("#load-more").click(function () { 
    var offset = Number($("#offset").val());
    var total = Number($("#total").val());

    var itemToDisplay = 10;
    offset += itemToDisplay;

    if (offset <= total) {
        $("#offset").val(offset);
        $.ajax({
            type: "post",
            url: "/more-trick",
            data: {offset:offset},
            success: function (response) {
                $(".card:last").after(response).show();

                var rowno = offset + itemToDisplay;
                if (rowno > total) {
                    $("#load-more").text("Voir moins");
                }
            }
        });
    } else {
        $(`.card:nth-child(${itemToDisplay})`).nextAll(".card").remove();
        $("#offset").val(0);
        $("#load-more").text("Voir plus");
    }
});

$(".delete-this-trick").click(function() { 
    let id = $(this).attr("data-id");
    let choice = confirm("Souhaitez vous supprimer ce trick ?");
    
    if (choice) {
        $(location).attr('href', `/suppression/trick/${id}`);
    }
    e.preventDefault();
});