document.addEventListener("DOMContentLoaded", function() {

  const grillaProductos = document.getElementById("grillaProductos");
  const plantillaProducto = document.getElementById("plantillaProducto");
  const filtroCategoria = document.getElementById("filtroCategoria");
  const filtroNivel = document.getElementById("filtroNivel");
  const filtroBusqueda = document.getElementById("filtroBusqueda");

  const formRecomendador = document.getElementById("formRecomendador");
  const panelResultado = document.getElementById("panelResultado");

  const abrirCarrito = document.getElementById("abrirCarrito");
  const cerrarCarrito = document.getElementById("cerrarCarrito");
  const panelCarrito = document.getElementById("panelCarrito");
  const overlay = document.getElementById("overlay");
  const itemsCarrito = document.getElementById("itemsCarrito");
  const totalCarrito = document.getElementById("totalCarrito");
  const cantidadCarrito = document.getElementById("cantidadCarrito");
  const contadorCarrito = document.getElementById("contadorCarrito");
  const procesarPedido = document.getElementById("procesarPedido");
  const vaciarCarrito = document.getElementById("vaciarCarrito");

  const formContacto = document.getElementById("formContacto");
  const mensajeFormulario = document.getElementById("mensajeFormulario");

  const CLAVE_CARRITO = "mundo-tech-cart";
  let carrito = JSON.parse(localStorage.getItem(CLAVE_CARRITO)) || [];

  function formatearMoneda(valor) {
    return new Intl.NumberFormat("es-PE", {
      style: "currency",
      currency: "PEN",
      minimumFractionDigits: 2
    }).format(valor);
  }

  function obtenerCategorias() {
    return [...new Set(productos.map((producto) => producto.categoria))].sort();
  }

  function cargarCategorias() {
    if (!filtroCategoria) return;
    
    obtenerCategorias().forEach((categoria) => {
      const opcion = document.createElement("option");
      opcion.value = categoria;
      opcion.textContent = categoria;
      filtroCategoria.appendChild(opcion);
    });
  }

  function obtenerProductosFiltrados() {
    if (!filtroCategoria || !filtroNivel || !filtroBusqueda) return [];

    const categoria = filtroCategoria.value;
    const nivel = filtroNivel.value;
    const texto = filtroBusqueda.value.trim().toLowerCase();

    return productos.filter((producto) => {
      const coincideCategoria = categoria === "all" || producto.categoria === categoria;
      const coincideNivel = nivel === "all" || producto.nivel === nivel;
      const coincideTexto =
        producto.nombre.toLowerCase().includes(texto) ||
        producto.descripcion.toLowerCase().includes(texto) ||
        producto.categoria.toLowerCase().includes(texto);

      return coincideCategoria && coincideNivel && coincideTexto;
    });
  }

  function renderizarProductos() {
    if (!grillaProductos || !plantillaProducto) return;

    const lista = obtenerProductosFiltrados();
    grillaProductos.innerHTML = "";

    if (!lista.length) {
      grillaProductos.innerHTML = `
        <article class="tarjeta-info tarjeta-vacia">
          <h3>No encontramos resultados</h3>
          <p>Prueba con otra categoría, otro nivel o una búsqueda más amplia.</p>
        </article>
      `;
      return;
    }

    lista.forEach((producto) => {
      const fragmento = plantillaProducto.content.cloneNode(true);
      const imagen = fragmento.querySelector(".producto-foto");
      imagen.src = producto.imagen;
      imagen.alt = producto.nombre;

      fragmento.querySelector(".producto-categoria").textContent = producto.categoria;
      fragmento.querySelector(".producto-nivel").textContent = producto.nivel;
      fragmento.querySelector(".producto-nombre").textContent = producto.nombre;
      fragmento.querySelector(".producto-descripcion").textContent = producto.descripcion;
      fragmento.querySelector(".producto-precio").textContent = formatearMoneda(producto.precio);

      const botonAgregar = fragmento.querySelector(".boton-agregar");
      botonAgregar.addEventListener("click", () => agregarAlCarrito(producto.id));

      grillaProductos.appendChild(fragmento);
    });
  }

  function guardarCarrito() {
    localStorage.setItem(CLAVE_CARRITO, JSON.stringify(carrito));
  }

  function agregarAlCarrito(idProducto) {
    const itemExistente = carrito.find((item) => item.id === idProducto);

    if (itemExistente) {
      itemExistente.cantidad += 1;
    } else {
      carrito.push({ id: idProducto, cantidad: 1 });
    }

    guardarCarrito();
    renderizarCarrito();
    abrirPanelCarrito();
  }

  function quitarDelCarrito(idProducto) {
    carrito = carrito.filter((item) => item.id !== idProducto);
    guardarCarrito();
    renderizarCarrito();
  }

  function limpiarCarrito() {
    carrito = [];
    guardarCarrito();
    renderizarCarrito();
  }

  function renderizarCarrito() {
    if (!itemsCarrito || !totalCarrito || !cantidadCarrito || !contadorCarrito) return;

    itemsCarrito.innerHTML = "";

    if (!carrito.length) {
      itemsCarrito.innerHTML = '<p class="texto-carrito-vacio">Todavía no agregaste productos.</p>';
      totalCarrito.textContent = formatearMoneda(0);
      cantidadCarrito.textContent = "0";
      contadorCarrito.textContent = "0";
      return;
    }

    let total = 0;
    let cantidad = 0;

    carrito.forEach((item) => {
      const producto = productos.find((entrada) => entrada.id === item.id);
      if (!producto) return;

      const subtotal = producto.precio * item.cantidad;
      total += subtotal;
      cantidad += item.cantidad;

      const articulo = document.createElement("article");
      articulo.className = "item-carrito";
      articulo.innerHTML = `
        <div>
          <h3>${producto.nombre}</h3>
          <small>${producto.categoria} · ${producto.nivel}</small>
          <p>${item.cantidad} x ${formatearMoneda(producto.precio)}</p>
        </div>
        <div class="item-carrito-acciones">
          <strong>${formatearMoneda(subtotal)}</strong>
          <button type="button" class="boton-enlace">Quitar</button>
        </div>
      `;

      articulo.querySelector(".boton-enlace").addEventListener("click", () => quitarDelCarrito(producto.id));
      itemsCarrito.appendChild(articulo);
    });

    totalCarrito.textContent = formatearMoneda(total);
    cantidadCarrito.textContent = String(cantidad);
    contadorCarrito.textContent = String(cantidad);
  }

  function abrirPanelCarrito() {
    if (!panelCarrito || !overlay) return;
    panelCarrito.classList.add("activo");
    panelCarrito.setAttribute("aria-hidden", "false");
    overlay.hidden = false;
  }

  function cerrarPanelCarrito() {
    if (!panelCarrito || !overlay) return;
    panelCarrito.classList.remove("activo");
    panelCarrito.setAttribute("aria-hidden", "true");
    overlay.hidden = true;
  }

  function procesarCompra() {
    if (!carrito.length) {
      alert("Tu carrito todavía está vacío.");
      return;
    }
    alert("Tu pedido fue registrado correctamente.");
    limpiarCarrito();
    cerrarPanelCarrito();
  }

  function obtenerNivelPorPresupuesto(presupuesto) {
    if (presupuesto <= 1800) return "Básico";
    if (presupuesto <= 3500) return "Intermedio";
    return "Avanzado";
  }

  function generarRecomendacion() {
    if (!panelResultado) return;

    const presupuesto = Number(document.getElementById("entradaPresupuesto").value);
    const objetivo = document.getElementById("entradaObjetivo").value;
    const preferencia = document.getElementById("entradaPreferencia").value;

    const nivel = obtenerNivelPorPresupuesto(presupuesto);

    let opciones = productos.filter((producto) => {
      const cumpleNivel = producto.nivel === nivel || (nivel === "Avanzado" && producto.nivel === "Intermedio");
      const cumpleObjetivo = producto.objetivo.includes(objetivo);
      const cumplePreferencia = producto.preferencia.includes(preferencia) || preferencia === "Equilibrio";
      const cumplePresupuesto = producto.precio <= presupuesto;

      return cumpleNivel && cumpleObjetivo && cumplePreferencia && cumplePresupuesto;
    });

    if (preferencia === "Precio") {
      opciones.sort((a, b) => a.precio - b.precio);
    } else {
      opciones.sort((a, b) => b.precio - a.precio);
    }

    const seleccion = opciones.slice(0, 4);

    if (!seleccion.length) {
      panelResultado.innerHTML = `
        <div class="estado-vacio">
          <p class="etiqueta">Sugerencia</p>
          <h3>No encontramos una coincidencia exacta.</h3>
          <p>Prueba ampliando el presupuesto o cambiando la prioridad de compra.</p>
        </div>
      `;
      return;
    }

    const mensajes = {
      "Básico": "La selección prioriza equipos funcionales y de buena relación costo-beneficio.",
      "Intermedio": "Aquí conviene apuntar a una compra equilibrada, con mejor margen para trabajo y uso continuo.",
      "Avanzado": "La prioridad pasa por rendimiento, mejor respuesta general y mayor proyección a futuro."
    };

    const preferenciasTexto = {
      "Precio": "gastar menos",
      "Equilibrio": "mantener equilibrio",
      "Rendimiento": "obtener más rendimiento",
      "Portabilidad": "ganar movilidad"
    };

    panelResultado.innerHTML = `
      <div class="resultado-contenido">
        <p class="etiqueta">Sugerencia</p>
        <span class="insignia-nivel">Perfil ${nivel}</span>
        <h3>Compra sugerida para ${objetivo.toLowerCase()}</h3>
        <p>${mensajes[nivel]} En tu caso, la prioridad es <strong>${preferenciasTexto[preferencia]}</strong> con un tope de <strong>${formatearMoneda(presupuesto)}</strong>.</p>
        <ul class="lista-sugerencias">
          ${seleccion.map((producto) => `
            <li>
              <strong>${producto.nombre}</strong>
              <span>${producto.categoria} · ${producto.nivel}</span>
              <p>${producto.descripcion}</p>
              <strong>${formatearMoneda(producto.precio)}</strong>
              <button class="boton boton-primario boton-agregar" type="button" data-id="${producto.id}">Agregar</button>
            </li>
          `).join("")}
        </ul>
      </div>
    `;
    const botonesNuevos = panelResultado.querySelectorAll(".boton-agregar");
  
    botonesNuevos.forEach((boton) => {
      boton.addEventListener("click", function() {
        const idProducto = this.getAttribute("data-id");         
        agregarAlCarrito(Number(idProducto)); 
      });
    });
  }

  function enviarFormularioContacto(evento) {
    evento.preventDefault();
    if (!mensajeFormulario || !formContacto) return;

    const nombre = document.getElementById("nombreContacto").value.trim();
    const tipo = document.getElementById("tipoConsulta").value;

    mensajeFormulario.textContent = `${nombre}, recibimos tu solicitud sobre ${tipo.toLowerCase()}. Te responderemos pronto.`;
    formContacto.reset();
  }

  function registrarEventos() {
    filtroCategoria?.addEventListener("change", renderizarProductos);
    filtroNivel?.addEventListener("change", renderizarProductos);
    filtroBusqueda?.addEventListener("input", renderizarProductos);

    formRecomendador?.addEventListener("submit", (evento) => {
      evento.preventDefault();
      generarRecomendacion();
    });

    abrirCarrito?.addEventListener("click", abrirPanelCarrito);
    cerrarCarrito?.addEventListener("click", cerrarPanelCarrito);
    overlay?.addEventListener("click", cerrarPanelCarrito);
    vaciarCarrito?.addEventListener("click", limpiarCarrito);
    procesarPedido?.addEventListener("click", procesarCompra);

    formContacto?.addEventListener("submit", enviarFormularioContacto);
  }

  cargarCategorias();
  renderizarProductos();
  renderizarCarrito();
  registrarEventos();
});