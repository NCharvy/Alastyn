$(document).ready(function() {
    var $div = $('#feed');
    $('#btn-test').on('click', function(e){
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: '/getfeed',
            data: {ref:$("#input-test").val()},
            async: true,
            dataType: "json",
            success: function(response)
        {
            console.log(response);
            $('.truc').remove();
            response.forEach(function(element){
                var $p = $('<p></p>').attr('class', 'truc');
                $p.html(element.title);
                $div.append($p);
                console.log(element.title);
            });
        },
        error: function(XMLHttpRequest, textStatus, errorThrown)
        {
            console.log(XMLHttpRequest + " " + textStatus + " " + errorThrown);
        },
    });
    })
});