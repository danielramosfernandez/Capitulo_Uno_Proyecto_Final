//?En este archivo se configura el boton del tema oscuro o claro en la pagina 
//?A continuación declaramos las dos variables tanto "boton" como "cuerpo"
  const botonModo = document.getElementById("modo");
  const body = document.body;

//?Aqui se verifica que en otras páginas se esta usando modo oscuro o claro 
//?Se guarda en "localStorage" para que en otras páginas se guarde el tema que estas utilizando
  if (localStorage.getItem("modo") === "oscuro") {
    body.classList.add("dark-mode");
    botonModo.textContent = "Claro";
  }
//?En esta sección del archivo establecemos que haciendo un solo "click"
//?Se alternara entre "modo oscuro" y "modo claro"
  botonModo.addEventListener("click", () => {
    body.classList.toggle("dark-mode");

    if (body.classList.contains("dark-mode")) {
      botonModo.textContent = "Claro";
      localStorage.setItem("modo", "oscuro");
    } else {
      botonModo.textContent = "Oscuro";
      localStorage.setItem("modo", "claro");
    }
  });

