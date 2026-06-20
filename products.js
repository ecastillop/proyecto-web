const productosLocal = [
  {
    id: 1,
    nombre: "Notebook Pulse 14",
    categoria: "Laptops",
    nivel: "Básico",
    precio: 1699,
    objetivo: ["Oficina", "Estudio", "Movilidad"],
    preferencia: ["Precio", "Portabilidad", "Equilibrio"],
    descripcion: "Ryzen 5, 8 GB RAM y SSD de 512 GB. Buena base para clases, oficina y uso diario.",
    imagen: "assets/categoria-pulse14.webp"
  },
  {
    id: 2,
    nombre: "Notebook Frame 15",
    categoria: "Laptops",
    nivel: "Intermedio",
    precio: 2699,
    objetivo: ["Oficina", "Estudio", "Diseño"],
    preferencia: ["Equilibrio", "Portabilidad", "Rendimiento"],
    descripcion: "Core i5, 16 GB RAM y SSD de 512 GB. Pensada para multitarea, trabajo y diseño ligero.",
    imagen: "assets/categoria-frame15.webp"
  },
  {
    id: 3,
    nombre: "Notebook Creator 16",
    categoria: "Laptops",
    nivel: "Avanzado",
    precio: 4199,
    objetivo: ["Diseño", "Gaming"],
    preferencia: ["Rendimiento"],
    descripcion: "Ryzen 7, 16 GB RAM y gráfica dedicada. Ideal para edición, render y gaming exigente.",
    imagen: "assets/categoria-creator16.webp"
  },
  {
    id: 4,
    nombre: "PC WorkStation A5",
    categoria: "PC",
    nivel: "Intermedio",
    precio: 2899,
    objetivo: ["Oficina", "Estudio"],
    preferencia: ["Equilibrio", "Rendimiento"],
    descripcion: "Torre con Ryzen 5, 16 GB RAM y SSD NVMe. Rinde muy bien para trabajo continuo y multitarea.",
    imagen: "assets/categoria-a5.webp"
  },
  {
    id: 5,
    nombre: "PC Studio RTX",
    categoria: "PC",
    nivel: "Avanzado",
    precio: 4899,
    objetivo: ["Diseño", "Gaming"],
    preferencia: ["Rendimiento"],
    descripcion: "Equipo con gráfica dedicada, 32 GB RAM y almacenamiento rápido para producción y gaming.",
    imagen: "assets/categoria-rtx.webp"
  },
  {
    id: 6,
    nombre: "Celular Orbit 128",
    categoria: "Celulares",
    nivel: "Básico",
    precio: 899,
    objetivo: ["Movilidad", "Estudio"],
    preferencia: ["Precio", "Portabilidad"],
    descripcion: "Pantalla amplia, batería duradera y 128 GB de almacenamiento para uso diario sin complicaciones.",
    imagen: "assets/categoria-celular.webp"
  },
  {
    id: 7,
    nombre: "Celular Orbit Pro 256",
    categoria: "Celulares",
    nivel: "Intermedio",
    precio: 1599,
    objetivo: ["Movilidad", "Estudio", "Oficina"],
    preferencia: ["Equilibrio", "Portabilidad"],
    descripcion: "Más memoria, mejor cámara y mejor respuesta general para productividad, contenido y movilidad.",
    imagen: "assets/categoria-celular.webp"
  },
  {
    id: 8,
    nombre: "Monitor View 24 IPS",
    categoria: "Monitores",
    nivel: "Básico",
    precio: 599,
    objetivo: ["Oficina", "Estudio"],
    preferencia: ["Precio", "Equilibrio"],
    descripcion: "Panel IPS Full HD de 24 pulgadas para escritorio, clases, oficina y consumo multimedia.",
    imagen: "assets/categoria-monitor.webp"
  },
  {
    id: 9,
    nombre: "Monitor View 27 QHD",
    categoria: "Monitores",
    nivel: "Intermedio",
    precio: 1299,
    objetivo: ["Diseño", "Oficina", "Gaming"],
    preferencia: ["Equilibrio", "Rendimiento"],
    descripcion: "Más área de trabajo y mejor definición para edición, productividad y entretenimiento.",
    imagen: "assets/categoria-monitor.webp"
  },
  {
    id: 10,
    nombre: "SSD NVMe 500 GB",
    categoria: "Componentes",
    nivel: "Básico",
    precio: 229,
    objetivo: ["Oficina", "Estudio", "Gaming"],
    preferencia: ["Precio", "Equilibrio"],
    descripcion: "Una mejora simple y efectiva para acelerar el inicio del sistema y la carga de programas.",
    imagen: "assets/categoria-componente.webp"
  },
  {
    id: 11,
    nombre: "SSD NVMe 1 TB",
    categoria: "Componentes",
    nivel: "Intermedio",
    precio: 419,
    objetivo: ["Oficina", "Diseño", "Gaming"],
    preferencia: ["Equilibrio", "Rendimiento"],
    descripcion: "Más capacidad y mejor margen para proyectos, juegos, archivos pesados y trabajo continuo.",
    imagen: "assets/categoria-componente.webp"
  },
  {
    id: 12,
    nombre: "RAM DDR4 16 GB Kit",
    categoria: "Componentes",
    nivel: "Intermedio",
    precio: 329,
    objetivo: ["Oficina", "Diseño", "Gaming"],
    preferencia: ["Equilibrio", "Rendimiento"],
    descripcion: "Kit 2x8 GB para mejorar multitarea y dar mayor soltura a programas de uso intensivo.",
    imagen: "assets/categoria-ramm.webp"
  },
  {
    id: 13,
    nombre: "GPU Orbit 12 GB",
    categoria: "Componentes",
    nivel: "Avanzado",
    precio: 2799,
    objetivo: ["Gaming", "Diseño"],
    preferencia: ["Rendimiento"],
    descripcion: "Gráfica dedicada para render, creación de contenido y juegos con mayor exigencia gráfica.",
    imagen: "assets/categoria-componente.webp"
  },
  {
    id: 14,
    nombre: "Teclado Slim Pro",
    categoria: "Accesorios",
    nivel: "Intermedio",
    precio: 249,
    objetivo: ["Oficina", "Gaming", "Estudio"],
    preferencia: ["Equilibrio", "Portabilidad"],
    descripcion: "Perfil delgado, buena respuesta al tacto y estética limpia para escritorio de trabajo.",
    imagen: "assets/categoria-teclado.webp"
  },
  {
    id: 15,
    nombre: "Mouse Ergo Wireless",
    categoria: "Accesorios",
    nivel: "Básico",
    precio: 119,
    objetivo: ["Oficina", "Estudio", "Movilidad"],
    preferencia: ["Precio", "Portabilidad"],
    descripcion: "Mouse inalámbrico cómodo, práctico y fácil de llevar para trabajo y clases.",
    imagen: "assets/categoria-mouse.webp"
  },
  {
    id: 16,
    nombre: "Combo Gamer Shadow",
    categoria: "Accesorios",
    nivel: "Intermedio",
    precio: 499,
    objetivo: ["Gaming"],
    preferencia: ["Equilibrio", "Rendimiento"],
    descripcion: "Set con teclado, mouse y audífonos para completar una base gamer sin comprar por separado.",
    imagen: "assets/categoria-combo.webp"
  }
];
