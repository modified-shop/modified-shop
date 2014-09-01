function showTab(auswahl, anzahl) { 
  for (var i = 0; i < anzahl; i++) {

	if (document.getElementById) {
	  document.getElementById("tab_lang_" +i).style.display="none";	 	       	  
	  
	  document.getElementById("tabselect_" +i).style.background="none";
	  document.getElementById("tabselect_" +i).style.color="#aaaaaa";		  
	  
	  if (auswahl == "tab_lang_" + i) {
		document.getElementById("tab_lang_" + i).style.display="block";			
		
		document.getElementById("tabselect_" +i).style.background="#d0d0d0";
		document.getElementById("tabselect_" +i).style.color="#000000";
	  }        
	}	
  }
}