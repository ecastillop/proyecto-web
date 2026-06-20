/**
 * ==============================================================================
 * HEADER: GLOSARIO DE MÉTODOS Y PALABRAS CLAVE DEL ARCHIVO
 * ==============================================================================
 * - document.getElementById(): Busca y devuelve una referencia a un elemento del DOM usando su atributo ID.
 * - insertAdjacentHTML(): Analiza una cadena de texto como HTML y la inserta en una posición específica del DOM.
 * - Promise: Objeto que representa la terminación o el fracaso eventual de una operación asíncrona.
 * - addEventListener(): Escucha y reacciona a eventos específicos (como clics o envíos) en un elemento HTML.
 * - async: Define una función asíncrona que maneja operaciones en segundo plano y permite el uso de await.
 * - await: Pausa la ejecución de la función asíncrona hasta que la Promesa (ej. fetch) se resuelva.
 * - fetch(): Realiza peticiones HTTP asíncronas para enviar o recibir datos de un servidor (API).
 * - .json(): Método que extrae y convierte el cuerpo de una respuesta HTTP en un objeto JavaScript estructurado.
 * - localStorage: Guarda datos en el navegador del usuario que persisten incluso al recargar o cerrar la página.
 * - JSON.parse() / JSON.stringify(): Convierte texto JSON a objeto JavaScript, o viceversa.
 * - map(), filter(), find(), forEach(): Métodos nativos para transformar, filtrar, buscar o recorrer arreglos.
 * ==============================================================================
 */

/**
 * Logica frontend de Mundo Tech.
 * Conecta HTML con la API PHP via fetch/async-await y gestiona catalogo, carrito, asesor y contacto.
 */
// Arreglo global de productos cargado desde la API PHP o respaldo local
let productos = [];

// Ruta base de los endpoints del backend
const API_BASE = "php/api/";

/**
 * Inserta modales Bootstrap reutilizables si aun no existen en la pagina.
 */
function inicializarModales() {
  // document.getElementById(): Verifica si el modal ya existe en el DOM para no duplicarlo
  if (document.getElementById("modalMensaje")) return;

  // insertAdjacentHTML(): Inyecta dinámicamente todo el bloque HTML de los modales al final de la página
  document.body.insertAdjacentHTML("beforeend", `
    <div class="modal fade modal-mundo-tech" id="modalMensaje" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header" id="modalMensajeEncabezado">
            <h5 class="modal-title" id="modalMensajeTitulo"></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body" id="modalMensajeCuerpo"></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primario-modal" data-bs-dismiss="modal">Entendido</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade modal-mundo-tech" id="modalConfirmar" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header modal-header-advertencia">
            <h5 class="modal-title" id="modalConfirmarTitulo"></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body" id="modalConfirmarCuerpo"></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secundario-modal" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primario-modal" id="modalConfirmarAceptar">Confirmar</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade modal-mundo-tech" id="modalCheckout" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirmar pedido</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <form id="formCheckout">
            <div class="modal-body">
              <p class="texto-modal-intro">Completa tus datos para registrar el pedido en Mundo Tech.</p>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label" for="checkoutNombre">Nombre completo</label>
                  <input class="form-control" id="checkoutNombre" name="nombre" type="text" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="checkoutCorreo">Correo electronico</label>
                  <input class="form-control" id="checkoutCorreo" name="correo" type="email" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="checkoutTelefono">Telefono (opcional)</label>
                  <input class="form-control" id="checkoutTelefono" name="telefono" type="tel">
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="checkoutDireccion">Direccion (opcional)</label>
                  <input class="form-control" id="checkoutDireccion" name="direccion" type="text">
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secundario-modal" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primario-modal">Registrar pedido</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  `);
}

/**
 * Muestra un modal informativo (exito, error o advertencia).
 * Uso: reemplaza alert() en checkout, contacto y validaciones del carrito.
 */
function mostrarMensaje(tipo, titulo, mensaje) {
  const modalEl = document.getElementById("modalMensaje");
  const encabezado = document.getElementById("modalMensajeEncabezado");
  if (!modalEl || !encabezado) return;

  document.getElementById("modalMensajeTitulo").textContent = titulo;
  document.getElementById("modalMensajeCuerpo").textContent = mensaje;
  encabezado.className = "modal-header modal-header-" + tipo;

  bootstrap.Modal.getOrCreateInstance(modalEl).show();
}

/**
 * Pide confirmacion al usuario y devuelve true/false.
 * Uso: await mostrarConfirmacion(...) antes de vaciar el carrito.
 */
function mostrarConfirmacion(titulo, mensaje) {
  // Promise: Retorna una promesa que envolverá el proceso asíncrono de esperar el clic del usuario
  return new Promise((resolve) => {
    const modalEl = document.getElementById("modalConfirmar");
    const botonAceptar = document.getElementById("modalConfirmarAceptar");
    if (!modalEl || !botonAceptar) {
      resolve(false);
      return;
    }

    document.getElementById("modalConfirmarTitulo").textContent = titulo;
    document.getElementById("modalConfirmarCuerpo").textContent = mensaje;

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    const limpiar = () => {
      botonAceptar.removeEventListener("click", alAceptar);
      modalEl.removeEventListener("hidden.bs.modal", alCancelar);
    };

    const alAceptar = () => {
      limpiar();
      modal.hide();
      resolve(true);
    };

    const alCancelar = () => {
      limpiar();
      resolve(false);
    };

    // addEventListener(): Se queda escuchando permanentemente el clic en el botón de aceptar
    botonAceptar.addEventListener("click", alAceptar);
    modalEl.addEventListener("hidden.bs.modal", alCancelar);
    modal.show();
  });
}

/**
 * Abre el modal de checkout y devuelve los datos del cliente.
 * Uso: const datos = await mostrarCheckout(); cancelar rechaza la promesa.
 */
function mostrarCheckout() {
  // Promise: Crea la estructura para esperar que el usuario envíe el formulario
  return new Promise((resolve, reject) => {
    const modalEl = document.getElementById("modalCheckout");
    const formulario = document.getElementById("formCheckout");
    if (!modalEl || !formulario) {
      reject(null);
      return;
    }

    formulario.reset();
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    let confirmado = false;

    const alEnviar = (evento) => {
      evento.preventDefault();
      confirmado = true;
      limpiar();
      modal.hide();
      resolve({
        nombre_cliente: document.getElementById("checkoutNombre").value.trim(),
        correo_cliente: document.getElementById("checkoutCorreo").value.trim(),
        telefono: document.getElementById("checkoutTelefono").value.trim(),
        direccion: document.getElementById("checkoutDireccion").value.trim()
      });
    };

    const alCancelar = () => {
      limpiar();
      if (!confirmado) {
        reject(null);
      }
    };

    const limpiar = () => {
      formulario.removeEventListener("submit", alEnviar);
      modalEl.removeEventListener("hidden.bs.modal", alCancelar);
    };

    // addEventListener(): Intercepta el evento de envío (submit) del formulario
    formulario.addEventListener("submit", alEnviar);
    modalEl.addEventListener("hidden.bs.modal", alCancelar);
    modal.show();
  });
}

// addEventListener(): Espera a que todo el HTML esté completamente cargado antes de arrancar la lógica
document.addEventListener("DOMContentLoaded", async function() {
  // Punto de entrada: se ejecuta cuando el HTML termino de cargar
  inicializarModales();

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

  const CLAVE_CARRITO = "mundo-tech-cart";
  // JSON.parse() y localStorage: Extrae los datos guardados en el navegador y los convierte en un array real
  let carrito = JSON.parse(localStorage.getItem(CLAVE_CARRITO)) || [];

  /**
   * Obtiene el catalogo desde PHP al cargar index.html.
   * fetch + await: envia GET y espera respuesta; si falla, usa products.js.
   */
  // async: Permite que esta función no congele la página mientras espera la red
  async function cargarProductosDesdeApi() {
    try {
      // GET al endpoint listar; no bloquea la pagina gracias a async/await
      // fetch() y await: Lanza la petición al backend y espera a que el servidor entregue los datos
      const respuesta = await fetch(API_BASE + "productos.php?accion=listar");
      
      // .json() y await: Desempaqueta la respuesta de texto en un array JSON procesable
      const datos = await respuesta.json();

      if (datos.exito && Array.isArray(datos.productos)) {
        productos = datos.productos;
        return;
      }
    } catch (error) {
      console.warn("No se pudo conectar a la API, usando datos locales.", error);
    }

    // Respaldo: usa el arreglo definido en products.js si existe
    if (typeof window.productosLocal !== "undefined") {
      productos = window.productosLocal;
    }
  }

  /** Formatea montos en soles peruanos para la interfaz. */
  function formatearMoneda(valor) {
    return new Intl.NumberFormat("es-PE", {
      style: "currency",
      currency: "PEN",
      minimumFractionDigits: 2
    }).format(valor);
  }

  /** Extrae categorias unicas del catalogo cargado. */
  function obtenerCategorias() {
    // map(): Itera sobre todos los productos extrayendo únicamente el string de su 'categoria'
    return [...new Set(productos.map((producto) => producto.categoria))].sort();
  }

  /** Llena el select de filtros en index.html. Solo corre si existe #filtroCategoria. */
  function cargarCategorias() {
    if (!filtroCategoria) return;

    // forEach(): Bucle que toma cada categoría única y construye su etiqueta HTML <option>
    obtenerCategorias().forEach((categoria) => {
      const opcion = document.createElement("option");
      opcion.value = categoria;
      opcion.textContent = categoria;
      filtroCategoria.appendChild(opcion);
    });
  }

  /** Filtra en memoria por categoria, nivel y texto (solo catalogo, no asesor). */
  function obtenerProductosFiltrados() {
    if (!filtroCategoria || !filtroNivel || !filtroBusqueda) return [];

    const categoria = filtroCategoria.value;
    const nivel = filtroNivel.value;
    const texto = filtroBusqueda.value.trim().toLowerCase();

    // filter(): Barre el catálogo completo y devuelve un nuevo array solo con los productos que pasan la validación
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

  /** Pinta la grilla del catalogo clonando la plantilla HTML de cada producto. */
  function renderizarProductos() {
    if (!grillaProductos || !plantillaProducto) return;

    const lista = obtenerProductosFiltrados();
    grillaProductos.innerHTML = "";

    if (!lista.length) {
      grillaProductos.innerHTML = `
        <article class="tarjeta-info tarjeta-vacia">
          <h3>No encontramos resultados</h3>
          <p>Prueba con otra categoria, otro nivel o una busqueda mas amplia.</p>
        </article>
      `;
      return;
    }

    // forEach(): Recorre la lista filtrada inyectando los datos en las tarjetas HTML
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
      
      // addEventListener(): Asigna al botón de la tarjeta la orden de agregar el ID específico al carrito
      botonAgregar.addEventListener("click", () => agregarAlCarrito(producto.id));

      grillaProductos.appendChild(fragmento);
    });
  }

  /** Persiste el carrito en localStorage del navegador. */
  function guardarCarrito() {
    // JSON.stringify() y localStorage: Convierte el array del carrito a texto y lo guarda permanentemente en el navegador
    localStorage.setItem(CLAVE_CARRITO, JSON.stringify(carrito));
  }

  /** Suma cantidad si el producto ya estaba en el carrito y abre el panel lateral. */
  function agregarAlCarrito(idProducto) {
    // find(): Busca si en el array de carrito ya existe un producto con el mismo ID ingresado
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

  /** Elimina un producto del carrito por id. */
  function quitarDelCarrito(idProducto) {
    // filter(): Crea un nuevo array excluyendo aquel producto que coincida con el ID que se quiere eliminar
    carrito = carrito.filter((item) => item.id !== idProducto);
    guardarCarrito();
    renderizarCarrito();
  }

  /** Vacia el carrito en memoria y en localStorage. */
  function limpiarCarrito() {
    carrito = [];
    guardarCarrito();
    renderizarCarrito();
  }

  /** Recalcula total, unidades y lista visible del panel carrito. */
  function renderizarCarrito() {
    if (!itemsCarrito || !totalCarrito || !cantidadCarrito || !contadorCarrito) return;

    itemsCarrito.innerHTML = "";

    if (!carrito.length) {
      itemsCarrito.innerHTML = '<p class="texto-carrito-vacio">Todavia no agregaste productos.</p>';
      totalCarrito.textContent = formatearMoneda(0);
      cantidadCarrito.textContent = "0";
      contadorCarrito.textContent = "0";
      return;
    }

    let total = 0;
    let cantidad = 0;

    // forEach(): Recorre el carrito iterando los cálculos matemáticos del recibo
    carrito.forEach((item) => {
      // find(): Cruza la información del carrito con el catálogo original para obtener precios y nombres reales
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
          <small>${producto.categoria} - ${producto.nivel}</small>
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

  /** Muestra el panel lateral del carrito con overlay. */
  function abrirPanelCarrito() {
    if (!panelCarrito || !overlay) return;
    panelCarrito.classList.add("activo");
    panelCarrito.setAttribute("aria-hidden", "false");
    overlay.hidden = false;
  }

  /** Oculta el panel lateral del carrito. */
  function cerrarPanelCarrito() {
    if (!panelCarrito || !overlay) return;
    panelCarrito.classList.remove("activo");
    panelCarrito.setAttribute("aria-hidden", "true");
    overlay.hidden = true;
  }

  /**
   * Registra el pedido en MySQL via POST JSON.
   * Flujo: modal checkout -> fetch pedidos.php -> modal de confirmacion.
   */
  // async: Declara función asíncrona porque el checkout y la base de datos toman tiempo en responder
  async function procesarCompra() {
    if (!carrito.length) {
      mostrarMensaje("advertencia", "Carrito vacio", "Tu carrito todavia esta vacio. Agrega productos antes de confirmar el pedido.");
      return;
    }

    let datosCliente;

    try {
      // await: Pausa el proceso de compra hasta que el usuario llene sus datos en el modal
      datosCliente = await mostrarCheckout();
    } catch (error) {
      return;
    }

    if (!datosCliente) return;

    const pedido = {
      ...datosCliente,
      items: carrito
    };

    try {
      // POST con JSON: el backend valida stock y guarda pedido + detalle
      // fetch() y await: Lanza los datos de compra al PHP por POST y aguarda la confirmación
      const respuesta = await fetch(API_BASE + "pedidos.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        // JSON.stringify(): Convierte el objeto de pedido a texto JSON crudo para enviarlo por HTTP
        body: JSON.stringify(pedido)
      });

      // .json() y await: Desencripta la respuesta HTTP validando si la compra fue exitosa
      const datos = await respuesta.json();

      if (datos.exito) {
        mostrarMensaje(
          "exito",
          "Pedido registrado",
          "Pedido #" + datos.id_pedido + " registrado correctamente. Total: " + formatearMoneda(datos.total)
        );
        limpiarCarrito();
        cerrarPanelCarrito();
      } else {
        mostrarMensaje("error", "No se pudo registrar el pedido", datos.mensaje);
      }
    } catch (error) {
      mostrarMensaje("error", "Error de conexion", "No se pudo conectar al servidor. Verifica que XAMPP este activo.");
    }
  }

  /** Traduce el presupuesto a perfil Básico, Intermedio o Avanzado (solo UI). */
  function obtenerNivelPorPresupuesto(presupuesto) {
    if (presupuesto <= 1800) return "Básico";
    if (presupuesto <= 3500) return "Intermedio";
    return "Avanzado";
  }

  /** Lee los tres campos del formulario del asesor. */
  function obtenerCriteriosAsesor() {
    const presupuesto = Number(document.getElementById("entradaPresupuesto")?.value);
    const objetivo = document.getElementById("entradaObjetivo")?.value || "";
    const preferencia = document.getElementById("entradaPreferencia")?.value || "";

    return { presupuesto, objetivo, preferencia };
  }

  /** Valida minimo de datos antes de llamar al endpoint recomendar. */
  function criteriosAsesorCompletos(criterios) {
    return criterios.presupuesto >= 600 && criterios.objetivo && criterios.preferencia;
  }

  /** Agrega al array global productos devueltos por el asesor (para el carrito). */
  function sincronizarProductosRecomendados(lista) {
    // forEach(): Introduce las nuevas sugerencias del backend al array principal de productos si no existen
    lista.forEach((producto) => {
      // find(): Evalúa que el producto recomendado no esté ya duplicado en la memoria
      const existente = productos.find((item) => item.id === producto.id);
      if (!existente) {
        productos.push(producto);
      }
    });
  }

  /** Renderiza HTML de sugerencias en #panelResultado de asesor.html. */
  function renderizarResultadoRecomendacion(seleccion, nivel, presupuesto, objetivo, preferencia) {
    if (!panelResultado) return;

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
      "Basico": "La seleccion prioriza equipos funcionales y de buena relacion costo-beneficio.",
      "Básico": "La seleccion prioriza equipos funcionales y de buena relacion costo-beneficio.",
      "Intermedio": "Aqui conviene apuntar a una compra equilibrada, con mejor margen para trabajo y uso continuo.",
      "Avanzado": "La prioridad pasa por rendimiento, mejor respuesta general y mayor proyeccion a futuro."
    };

    const preferenciasTexto = {
      "Precio": "gastar menos",
      "Equilibrio": "mantener equilibrio",
      "Rendimiento": "obtener mas rendimiento",
      "Portabilidad": "ganar movilidad"
    };

    // map(): Convierte el array de productos sugeridos directamente en un bloque iterado de cadenas HTML
    panelResultado.innerHTML = `
      <div class="resultado-contenido">
        <p class="etiqueta">Sugerencia</p>
        <span class="insignia-nivel">Perfil ${nivel}</span>
        <h3>Compra sugerida para ${objetivo.toLowerCase()}</h3>
        <p>${mensajes[nivel] || mensajes["Básico"]} En tu caso, la prioridad es <strong>${preferenciasTexto[preferencia]}</strong> con un tope de <strong>${formatearMoneda(presupuesto)}</strong>.</p>
        <ul class="lista-sugerencias">
          ${seleccion.map((producto) => `
            <li>
              <strong>${producto.nombre}</strong>
              <span>${producto.categoria} - ${producto.nivel}</span>
              <p>${producto.descripcion}</p>
              <strong>${formatearMoneda(producto.precio)}</strong>
              <button class="boton boton-primario boton-agregar" type="button" data-id="${producto.id}">Agregar</button>
            </li>
          `).join("")}
        </ul>
      </div>
    `;

    // forEach(): Asocia un evento a todos los botones 'Agregar' recién inyectados en la sugerencia
    panelResultado.querySelectorAll(".boton-agregar").forEach((boton) => {
      boton.addEventListener("click", function() {
        agregarAlCarrito(Number(this.getAttribute("data-id")));
      });
    });
  }

  /**
   * Consulta recomendaciones en PHP al pulsar "Ver recomendacion".
   * GET productos.php?accion=recomendar con presupuesto, objetivo y preferencia.
   */
  // async: Prepara el formulario del asesor para la asincronía del algoritmo de búsqueda
  async function generarRecomendacion() {
    if (!panelResultado) return;

    const criterios = obtenerCriteriosAsesor();
    if (!criteriosAsesorCompletos(criterios)) {
      mostrarMensaje("advertencia", "Datos incompletos", "Completa presupuesto, uso principal y prioridad antes de ver la recomendacion.");
      return;
    }

    panelResultado.innerHTML = `
      <div class="estado-vacio">
        <p class="etiqueta">Consultando</p>
        <h3>Buscando opciones en la base de datos...</h3>
      </div>
    `;

    const params = new URLSearchParams({
      accion: "recomendar",
      presupuesto: String(criterios.presupuesto),
      objetivo: criterios.objetivo,
      preferencia: criterios.preferencia
    });

    try {
      // Consulta filtrada en servidor; PHP ejecuta JOINs sobre objetivos y preferencias
      // fetch() y await: Envía la URL construida al backend y espera a que PHP procese su algoritmo SQL
      const respuesta = await fetch(API_BASE + "productos.php?" + params.toString());
      
      // .json() y await: Desglosa los resultados encontrados devueltos por el backend
      const datos = await respuesta.json();

      if (!datos.exito) {
        panelResultado.innerHTML = `
          <div class="estado-vacio">
            <p class="etiqueta">Sugerencia</p>
            <h3>No se pudo generar la recomendacion.</h3>
            <p>${datos.mensaje}</p>
          </div>
        `;
        return;
      }

      const seleccion = Array.isArray(datos.productos) ? datos.productos : [];
      sincronizarProductosRecomendados(seleccion);
      renderizarResultadoRecomendacion(
        seleccion,
        datos.nivel || obtenerNivelPorPresupuesto(criterios.presupuesto),
        criterios.presupuesto,
        criterios.objetivo,
        criterios.preferencia
      );
    } catch (error) {
      panelResultado.innerHTML = `
        <div class="estado-vacio">
          <p class="etiqueta">Sugerencia</p>
          <h3>Error de conexion</h3>
          <p>No se pudo consultar la base de datos. Verifica que XAMPP este activo.</p>
        </div>
      `;
    }
  }

  /**
   * Envia contacto.html al backend y muestra modal de respuesta.
   * POST contacto.php con JSON { nombre, correo, tipo_consulta, mensaje }.
   */
  // async: Maneja el envío del formulario de manera no bloqueante
  async function enviarFormularioContacto(evento) {
    evento.preventDefault();
    if (!mensajeFormulario || !formContacto) return;

    const payload = {
      nombre: document.getElementById("nombreContacto").value.trim(),
      correo: document.getElementById("correoContacto").value.trim(),
      tipo_consulta: document.getElementById("tipoConsulta").value,
      mensaje: document.getElementById("mensajeContacto").value.trim()
    };

    try {
      // Guarda el mensaje en tabla contactos via API REST
      // fetch() y await: Inicia la comunicación con contacto.php y paraliza la función esperando confirmación
      const respuesta = await fetch(API_BASE + "contacto.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        // JSON.stringify(): Empaqueta los datos del formulario de contacto para el motor PHP
        body: JSON.stringify(payload)
      });

      // .json() y await: Procesa el código 200 OK del backend
      const datos = await respuesta.json();

      if (datos.exito) {
        mostrarMensaje("exito", "Mensaje enviado", payload.nombre + ", " + datos.mensaje);
        formContacto.reset();
      } else {
        mostrarMensaje("error", "No se pudo enviar", datos.mensaje);
      }
    } catch (error) {
      mostrarMensaje("error", "Error de conexion", "No se pudo enviar el mensaje. Verifica que el servidor local este activo.");
    }
  }

  /** Enlaza filtros, carrito, asesor y contacto segun elementos presentes en la pagina. */
  function registrarEventos() {
    // addEventListener(): Se acopla a las etiquetas select para detonar renderizarProductos() ante cambios
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
    
    // addEventListener() y async/await: Asigna un evento asíncrono atado a la promesa del modal de confirmación
    vaciarCarrito?.addEventListener("click", async () => {
      if (!carrito.length) {
        mostrarMensaje("advertencia", "Carrito vacio", "No hay productos para quitar del carrito.");
        return;
      }

      const confirmado = await mostrarConfirmacion(
        "Vaciar carrito",
        "Se eliminaran todos los productos del carrito. Esta accion no se puede deshacer."
      );

      if (confirmado) {
        limpiarCarrito();
      }
    });
    procesarPedido?.addEventListener("click", procesarCompra);

    formContacto?.addEventListener("submit", enviarFormularioContacto);
  }

  // Inicio: carga datos, pinta catalogo/carrito y activa eventos
  // await: Obliga al navegador a esperar que se cargue la base de datos antes de pintar los filtros y UI
  await cargarProductosDesdeApi();
  cargarCategorias();
  renderizarProductos();
  renderizarCarrito();
  registrarEventos();
});