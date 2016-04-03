function showTabTag(auswahl, anzahl) { 
  for (var i = 0; i < anzahl; i++) {

	if (document.getElementById) {
	  document.getElementById("tab_tag_" +i).style.display="none";	 	       	  
	  
	  document.getElementById("tab_tag_select_" +i).style.background="none";
	  document.getElementById("tab_tag_select_" +i).style.color="#aaaaaa";		  
	  
	  if (auswahl == "tab_tag_" + i) {
		document.getElementById("tab_tag_" + i).style.display="block";			
		
		document.getElementById("tab_tag_select_" +i).style.background="#d0d0d0";
		document.getElementById("tab_tag_select_" +i).style.color="#000000";
	  }        
	}	
  }
}