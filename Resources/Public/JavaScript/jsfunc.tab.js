/**
* contributed by www.MrKnowing.com
*
* öffnet einen Reiter (klick auf oberen Reiter (Tab).
* @param index: welcher Reiter wurde geklickt, 1 bis n.
*/
function openTab(index)
{
	var element = document.getElementsByTagName('div');
	var name = "";
	var obj;

	for (var i = 0; i < element.length; i++)
	{
		name = element[i].id;
		if (name.substr(0, 8) == 'tab_box_')
		{
			obj = document.getElementById(name);

			obj.hidden = true;

		}
		if (name.substr(0, 8) == 'tab_top_')
		{
			obj = document.getElementById(name);
			obj.setAttribute('class', 'tab_top_passive');
		}

	}

	var tab = document.getElementById("tab_box_" + index);
	tab.hidden = false;
	tab = document.getElementById("tab_top_" + index);
	tab.setAttribute('class', 'tab_top_active');

}

