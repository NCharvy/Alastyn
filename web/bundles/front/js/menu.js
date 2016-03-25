function getState(val) 
{
	alert('ok');
	$.ajax
	(
		{
			type: "POST",
			dataType: "json",
			url: "/recherche/recherche_region",
			data:"[{\"country_id\":"+val+"}]",
			success: function(data)
			{
				while(document.getElementById("MenuDeroulantRegion") != null)
				{
					document.getElementById("state-list").removeChild(document.getElementById("MenuDeroulantRegion"));
				}
				
				for (i = 0; i <= data.data[2]; i++)
				{
					$("#state-list").append("<option id=\"MenuDeroulantRegion\" value=\""+ data.data[1][i]+"\" >"+ data.data[0][i]+"</option>");
				}
			}

		);
	}

function loadRegion(id){
	document.location = "/region/" + id;
}
