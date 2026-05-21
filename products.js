const productos = [
  {
    id: 1,
    nombre: "HP",
    categoria: "Laptops",
    nivel: "Básico",
    precio: 1699,
    objetivo: ["Oficina", "Estudio", "Movilidad"],
    preferencia: ["Precio", "Portabilidad", "Equilibrio"],
    descripcion: "Ryzen 5, 8 GB RAM y SSD de 512 GB. Buena base para clases, oficina y uso diario.",
    imagen: "assets/produc1.jpeg"
  },
  {
    id: 2,
    nombre: "HP",
    categoria: "Laptops",
    nivel: "Intermedio",
    precio: 2699,
    objetivo: ["Oficina", "Estudio", "Diseño"],
    preferencia: ["Equilibrio", "Portabilidad", "Rendimiento"],
    descripcion: "Core i5, 16 GB RAM y SSD de 512 GB. Pensada para multitarea, trabajo y diseño ligero.",
    imagen: "assets/produc2.jpeg"
  },
  {
    id: 3,
    nombre: "acer nitro",
    categoria: "Laptops",
    nivel: "Avanzado",
    precio: 4199,
    objetivo: ["Diseño", "Gaming"],
    preferencia: ["Rendimiento"],
    descripcion: "Ryzen 7, 16 GB RAM y gráfica dedicada. Ideal para edición, render y gaming exigente.",
    imagen: "assets/produc3.jpeg"
  },
  {
    id: 4,
    nombre: "CPU armada",
    categoria: "PC",
    nivel: "Intermedio",
    precio: 2899,
    objetivo: ["Oficina", "Estudio"],
    preferencia: ["Equilibrio", "Rendimiento"],
    descripcion: "Torre con Ryzen 5, 16 GB RAM y SSD NVMe. Rinde muy bien para trabajo continuo y multitarea.",
    imagen: "assets/produc4.jpeg"
  },
  {
    id: 5,
    nombre: "CPU-RTX armada",
    categoria: "PC",
    nivel: "Avanzado",
    precio: 4899,
    objetivo: ["Diseño", "Gaming"],
    preferencia: ["Rendimiento"],
    descripcion: "Equipo con gráfica dedicada, 32 GB RAM y almacenamiento rápido para producción y gaming.",
    imagen: "assets/produc5.jpeg"
  },
  {
    id: 6,
    nombre: " Xiaomi Redmi 15",
    categoria: "Celulares",
    nivel: "Básico",
    precio: 899,
    objetivo: ["Movilidad", "Estudio"],
    preferencia: ["Precio", "Portabilidad"],
    descripcion: "Pantalla amplia, batería duradera y 128 GB de almacenamiento para uso diario sin complicaciones.",
    imagen: "assets/produc6.jpeg"
  },
  {
    id: 7,
    nombre: "Tecno SPARK 30C",
    categoria: "Celulares",
    nivel: "Intermedio",
    precio: 1599,
    objetivo: ["Movilidad", "Estudio", "Oficina"],
    preferencia: ["Equilibrio", "Portabilidad"],
    descripcion: "memoria de 256GB, cámara trasera 50MP, frontal 8MP y mejor respuesta general para productividad, contenido y movilidad.",
    imagen: "assets/produc7.jpeg"
  },
  {
    id: 8,
    nombre: "Monitor LG",
    categoria: "Monitores",
    nivel: "Básico",
    precio: 599,
    objetivo: ["Oficina", "Estudio"],
    preferencia: ["Precio", "Equilibrio"],
    descripcion: "Panel IPS Full HD de 24 pulgadas para escritorio, clases, oficina y consumo multimedia.",
    imagen: "assets/produc8.jpeg"
  },
  {
    id: 9,
    nombre: "ViewSonic ColorPro VP2785-4K",
    categoria: "Monitores",
    nivel: "Intermedio",
    precio: 1299,
    objetivo: ["Diseño", "Oficina", "Gaming"],
    preferencia: ["Equilibrio", "Rendimiento"],
    descripcion: "Más área de trabajo y mejor definición para edición, productividad y entretenimiento.",
    imagen: "assets/produc9.jpeg"
  },
  {
    id: 10,
    nombre: "SSD KINGSTON 500GB",
    categoria: "Componentes",
    nivel: "Básico",
    precio: 229,
    objetivo: ["Oficina", "Estudio", "Gaming"],
    preferencia: ["Precio", "Equilibrio"],
    descripcion: "Una mejora simple y efectiva para acelerar el inicio del sistema y la carga de programas.",
    imagen: "assets/produc10.jpeg"
  },
  {
    id: 11,
    nombre: "SSD KINGSTON 1TB",
    categoria: "Componentes",
    nivel: "Intermedio",
    precio: 419,
    objetivo: ["Oficina", "Diseño", "Gaming"],
    preferencia: ["Equilibrio", "Rendimiento"],
    descripcion: "Más capacidad y mejor margen para proyectos, juegos, archivos pesados y trabajo continuo.",
    imagen: "assets/produc11.jpeg"
  },
  {
    id: 12,
    nombre: "Kingston DDR4 ",
    categoria: "Componentes",
    nivel: "Intermedio",
    precio: 329,
    objetivo: ["Oficina", "Diseño", "Gaming"],
    preferencia: ["Equilibrio", "Rendimiento"],
    descripcion: "Kit 2x8 GB para mejorar multitarea y dar mayor soltura a programas de uso intensivo.",
    imagen: "assets/produc12.jpeg"
  },
  {
    id: 13,
    nombre: "GeForce RTX 2080",
    categoria: "Componentes",
    nivel: "Avanzado",
    precio: 2799,
    objetivo: ["Gaming", "Diseño"],
    preferencia: ["Rendimiento"],
    descripcion: "Gráfica dedicada para render, creación de contenido y juegos con mayor exigencia gráfica.",
    imagen: "assets/produc13.jpeg"
  },
  {
    id: 14,
    nombre: "AZIO Cascade Slim",
    categoria: "Accesorios",
    nivel: "Intermedio",
    precio: 249,
    objetivo: ["Oficina", "Gaming", "Estudio"],
    preferencia: ["Equilibrio", "Portabilidad"],
    descripcion: "Perfil delgado, buena respuesta al tacto y estética limpia para escritorio de trabajo.",
    imagen: "assets/produc14.jpeg"
  },
  {
    id: 15,
    nombre: "Logitech Ergo M575",
    categoria: "Accesorios",
    nivel: "Básico",
    precio: 119,
    objetivo: ["Oficina", "Estudio", "Movilidad"],
    preferencia: ["Precio", "Portabilidad"],
    descripcion: "Mouse inalámbrico cómodo, práctico y fácil de llevar para trabajo y clases.",
    imagen: "assets/produc15.jpeg"
  },
  {
    id: 16,
    nombre: "Combo Gamer",
    categoria: "Accesorios",
    nivel: "Intermedio",
    precio: 499,
    objetivo: ["Gaming"],
    preferencia: ["Equilibrio", "Rendimiento"],
    descripcion: "Set con teclado, mouse y audífonos para completar una base gamer sin comprar por separado.",
    imagen: "assets/produc16.jpeg"
  }
];
