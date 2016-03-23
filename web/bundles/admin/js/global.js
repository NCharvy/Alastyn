
function statRegion(pays) {
    $.ajax({
        type: "POST",
        url: '/api/recherche_good_region_json',
        data: '{"idpays":' + pays + '}',
        async: true,
        dataType: "json",
        success: function (response) {
            var data_flows = [];
            var data_wines = [];
            if(response.data.length > 0){
                data_flows = response.data;
            }
            var chart_region_flows = c3.generate({
                bindto: '#chart_region_flows',
                data: {
                    columns: data_flows,
                    type : 'pie'
                },
                pie: {
                    label: {
                        format: function (value, ratio, id) {
                            return d3.format('')(value);
                        }
                    }
                }
            });
            if(response.wines.length > 0){
                data_wines = response.wines;
            }
            var chart_region_wines = c3.generate({
                bindto: '#chart_region_wines',
                data: {
                    columns: data_wines,
                    type : 'pie'
                },
                pie: {
                    label: {
                        format: function (value, ratio, id) {
                            return d3.format('')(value);
                        }
                    }
                }
            });
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            console.log(XMLHttpRequest + " " + textStatus + " " + errorThrown);
        },
    });
}
