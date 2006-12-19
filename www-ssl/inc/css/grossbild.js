  function isClass(object, className) {
    return (object.className.search('(^|\\s)' + className + '(\\s|$)') != -1);
  }
  
  var grossbild_an = 0
  
  function grossbild_over(e) {
    if(grossbild_an) return
    grossbild_an = 1
    if(!e) e = window.event;
    body = document.getElementsByTagName("body")[0]
    i = document.createElement("img")
    i.src = e.target.src;
    i.style.position = "absolute"
    /*a = ""
    for(b in e) a += b + " "
    alert(a)*/
    i.style.top = e.clientY + window.scrollY
    i.style.left = e.clientX + window.scrollX
    i.id = "mouseoverphoto"
    i.onmouseover = grossbild_over
    i.onmouseout = grossbild_out
    //i.onmousemove = grossbild_move
    body.appendChild(i);
  }
  
  function grossbild_out(e) {
    if(!grossbild_an) return
    grossbild_an = 0
    if(!e) e = window.event;
    body = document.getElementsByTagName("body")[0]
    i = document.getElementById("mouseoverphoto")
    body.removeChild(i)
  }
  
  function grossbild_move(e) {
    if(!e) e = window.event;
    i = document.getElementById("mouseoverphoto")
    i.style.top = e.clientY + window.scrollY
    i.style.left = e.clientX + window.scrollX
}
  
  function grossbild_register(objekt) {
    objekt.onmouseover = grossbild_over
    objekt.onmouseout = grossbild_out
    objekt.onmousemove = grossbild_move
  }
  
  function grossbild_registrieren() {
    if(grossbild_altonload)
      grossbild_altonload()
    
    objekte = document.getElementsByTagName("img");
    for(var i = 0; i < objekte.length; i++) {
      if(isClass(objekte[i], "photo")) {
        grossbild_register(objekte[i])
      }
    }
  }
  
  var grossbild_altonload = window.onload
  window.onload = grossbild_registrieren
