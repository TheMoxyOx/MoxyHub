var SelectObject, lblSelect, txtSelect;
var SelectFixedX  = -1;
var SelectFixedY  = -1;
var SelectStartAt = 1;
var bSelectLoaded = false;
var bSelectShow   = false;
var SelectColours  = new Array(
'#00CCFF', '#FFFF66', '#FF9200', '#FF4040',
'#FFC756', '#84CD53', '#669966', '#E3D9FF',
'#B23232', '#EAF5A2', '#99CC99', '#666699',
'#AFA4CD', '#5B7F96', '#CDDD61', '#FF7474',
'#F2DA00', '#E52828', '#666666', '#708686',
'#66513C', '#F2ACCD', '#EB3F91', '#4C61F9',
'#1DA0CF', '#5B340D', '#C0E835', '#6B802A',
'#EB1D52', '#8E82AF', '#999999', '#707070');

document.observe("dom:loaded", function() {
	// no more document.write for you! muahahahah
	var selector_html = "<div onclick='bSelectShow=true' id='ColourSelector' style='z-index: +999; position: absolute; visibility: hidden;'>\n"
		+ "<table width='100'>\n"
		+ "<tr>\n"
		+ "	<td width='100%'>\n"
		+ '	<table width="100" height="100" border="0" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF">'
		+ '	<tr align="left" valign="top">';

	for (i = 1; i < SelectColours.length + 1; i++)
	{
		selector_html += '	  <td width="25" height="25" bgcolor="' + SelectColours[i-1]  + '"><img src="images/common/s.gif" width="25" height="25" border="0" onClick="setColour(\'' + SelectColours[i-1] +'\')"></td>';
		if ((i % 4) == 0)
		{
			selector_html += '	</tr>';
			selector_html += '	<tr align="left" valign="top">';
		}
	}

	selector_html += '	</tr>'
		+ '	</table>'
		+ "  </td>\n"
		+ "</tr>"
		+ "</table>"
		+ "</div>";

	$$('body')[0].insert(selector_html);
});

function setColour(colour)
{
	SelectorHide();
	lblSelect.style.background = colour;
	txtSelect.value = colour;
}

function SelectorHide()
{
	SelectObject.visibility = "hidden";
	showElement( 'SELECT' );
	showElement( 'APPLET' );
}

function SelectorShow(ctl, ctl2)
{
	var posLeft = -4;
	var posTop = -4;
	lblSelect = ctl;
	txtSelect = ctl2;
	ColourRegisterEvents();
	if (bSelectLoaded)
	{
		if ( SelectObject.visibility == "hidden" )
		{
			aTag = ctl;
			do
			{
				aTag = aTag.offsetParent;
				posLeft += aTag.offsetLeft;
				posTop += aTag.offsetTop;
			}
			while (aTag.tagName != 'BODY');

//			SelectObject.left = (SelectFixedX == -1) ? ctl.offsetLeft + posLeft : SelectFixedX;
//			SelectObject.top = (SelectFixedY ==- 1) ? ctl.offsetTop + posTop + ctl.offsetHeight + 2 : SelectFixedY;
			SelectObject.left = (ctl.offsetLeft + posLeft + ctl.offsetWidth + 2 - 0) + 'px'
			SelectObject.top = (ctl.offsetTop + posTop - 0) + 'px'
			SelectObject.visibility= (dom||ie) ? "visible" : "show";

			hideElement( 'SELECT', document.getElementById("ColourSelector") );
			hideElement( 'APPLET', document.getElementById("ColourSelector") );

			bSelectShow = true;
		}
	}
	else
	{
		ColourSelectorInit();
		SelectorShow(ctl, ctl2);
	}
}

function ColourSelectorInit()
{
	if (!ns4)
	{
		SelectObject = (dom) ? document.getElementById("ColourSelector").style : ie ? document.all.ColourSelector : document.ColourSelector
		SelectorHide()
		bSelectLoaded = true
	}
}

function ColourRegisterEvents()
{
	document.onkeypress = function HideSelector_Trap1 ()
	{
		if (event.keyCode == 27)
		{
			SelectorHide();
		}
	}

	document.onclick = function HideSelector_Trap1()
	{
		if (!bSelectShow)
		{
			SelectorHide();
		}
		bSelectShow = false
	}
}
