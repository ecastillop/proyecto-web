/**
 * ==============================================================================
 * HEADER: GLOSARIO DE MÉTODOS Y PALABRAS CLAVE DEL ARCHIVO
 * ==============================================================================
 * - addEventListener(): Escucha y reacciona a eventos específicos (como clics o carga) en un elemento HTML o el documento.
 * - DOMContentLoaded: Evento que se dispara cuando el documento HTML inicial ha sido completamente cargado y parseado por el navegador.
 * - getElementById(): Busca y devuelve una referencia exacta a un único elemento del DOM utilizando su atributo ID.
 * - querySelectorAll(): Busca y devuelve una lista estática (NodeList) de todos los elementos del DOM que coincidan con un selector CSS.
 * - forEach(): Método iterativo que ejecuta una función proporcionada una vez por cada elemento presente en una lista o arreglo.
 * - querySelector(): Busca y devuelve únicamente el primer elemento del DOM que coincida con un selector CSS específico.
 * - dataset: Propiedad que permite leer o manipular los atributos personalizados de datos (data-*) definidos en una etiqueta HTML.
 * - textContent: Propiedad que permite obtener o reemplazar el texto puro contenido dentro de un elemento, ignorando etiquetas HTML internas.
 * ==============================================================================
 */

// addEventListener() y DOMContentLoaded: Pone el código en espera hasta que el navegador haya construido todo el árbol de elementos HTML
document.addEventListener("DOMContentLoaded", function () {
  // getElementById(): Atrapa en memoria las referencias a los modales y campos específicos buscando sus IDs en la página
  const modalEliminar = document.getElementById("modalEliminarProducto");
  const formEliminar = document.getElementById("formEliminarProducto");
  const nombreProducto = document.getElementById("nombreProductoEliminar");

  // querySelectorAll(): Selecciona de golpe todos los botones de la tabla que contengan el atributo personalizado de borrado
  // forEach(): Inicia un bucle para procesar y configurar cada uno de esos botones de eliminación de forma individual
  document.querySelectorAll("[data-eliminar-id]").forEach((boton) => {
    // addEventListener(): Le indica a este botón en particular que debe estar atento a un "click" del usuario
    boton.addEventListener("click", function () {
      if (!modalEliminar || !formEliminar || !nombreProducto) return;

      // querySelector(): Busca dentro de la estructura del formulario el input oculto que se enviará al servidor PHP
      // dataset: Extrae automáticamente el número de ID guardado en el atributo 'data-eliminar-id' del botón clickeado (this)
      formEliminar.querySelector('[name="id_producto"]').value = this.dataset.eliminarId;
      
      // textContent: Inyecta el nombre del producto directamente en el texto del modal de confirmación por seguridad visual
      nombreProducto.textContent = this.dataset.eliminarNombre || "este producto";
      
      bootstrap.Modal.getOrCreateInstance(modalEliminar).show();
    });
  });

  const toastExito = document.getElementById("toastExito");
  if (toastExito) {
    bootstrap.Toast.getOrCreateInstance(toastExito).show();
  }
});