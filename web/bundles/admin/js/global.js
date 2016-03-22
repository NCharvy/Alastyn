function loadPage(numpage){
    $.ajax({
        type: "POST",
        url: '/api/flows',
        data: {page:numpage},
        async: true,
        dataType: "json",
        success: function(response)
        {
            //console.log(response);
            console.log("mes couilles");
        },
        error: function(XMLHttpRequest, textStatus, errorThrown)
        {
            console.log(XMLHttpRequest + " " + textStatus + " " + errorThrown);
        },
    });
}