function email_popup () {
  //alert (document.status.notify.checked);
  if (document.status.notify.checked) {
    document.status.target = "emailPreview";
    document.status.email_preview.value = 1; 
    var w = window.open('', 'emailPreview', 'width=700,height=800,resizable=yes,scrollbars=yes,left=100,top=50');
    document.status.onsubmit = function() {return w};
    document.status.submit();
    document.status.email_preview.value = ''; 
    document.status.target = "";
    return true;
  }
  alert ('Für Email Vorschau "Kunde benachrichtigen" anhaken!');
  return false;
}