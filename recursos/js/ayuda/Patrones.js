/**
 * Patrones.js
 * Propósito: Centralizar las expresiones regulares para validaciones en todo el sistema Haydee.
 * Uso: 
 * - Validaciones completas (keyup / blur / submit)
 * - Bloqueo de teclas en tiempo real (keypress)
 */
const Patrones = {

    // ============================================================
    // 1. VALIDADORES GLOBALES Y TEXTOS GENÉRICOS
    // ============================================================
    correo: /^[a-zA-Z0-9._+-]{3,35}@([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/,
    contrasena: /^[A-Za-z0-9_.+*$#%&@-]{5,100}$/,
    
    // Nombres y textos (Solo letras y espacios)
    nombrePersona: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,40}$/,  // 3 a 40 caracteres
    textoBreve: /^.{3,}$/,                            // Mínimo 3 caracteres (Cualquier tipo)
    textoCorto: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,30}$/,     // 3 a 30 caracteres
    textoMedio: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ() \s]{3,50}$/,  // 3 a 50 caracteres (Permite paréntesis)
    textoLargo: /^.{10,}$/,                           // Mínimo 10 caracteres (Cualquier tipo)
    textoModulo: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ _\s]{3,30}$/,
    digitos: /^\d+$/,                                 // Solo números enteros (útil para IDs)

    // ============================================================
    // 2. FINANZAS, PAGOS Y BANCOS
    // ============================================================
    monto: /^\d{1,12}([.,]\d{1,2})?$/,                // Hasta 12 enteros y 2 decimales
    porcentaje: /^\d{1,2}(\.\d{0,2})?$/,              // Ejemplo: 12 o 12.50
    referenciaBancaria: /^[0-9A-Za-z]{4,20}$/,        // 4 a 20 caracteres alfanuméricos
    codigoBanco: /^\d{4}$/,                           // Exactamente 4 dígitos
    numeroCuenta: /^\d{18,30}$/,                      // 18 a 30 dígitos numéricos
    
    // ============================================================
    // 3. IDENTIFICACIÓN, CONTACTO Y UBICACIÓN
    // ============================================================
    tipoDocumento: /^[VEJG]$/,                        // Iniciales de documentos (V, E, J, G)
    cedula: /^[0-9]{7,8}$/,                           // 7 a 8 dígitos numéricos
    rif: /^[0-9]{7,9}$/,                              // 7 a 9 dígitos numéricos
    telefono: /^\d{11}$/,                             // Exactamente 11 dígitos numéricos
    direccion: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9,.\-#°\s]{3,100}$/, // Permite caracteres de dirección física

    // ============================================================
    // 4. MÓDULOS ESPECÍFICOS (Condominio, Cartelera, Caja, etc.)
    // ============================================================
    
    // Condominio / Apartamentos
    nroApartamento: /^[0-9-]{1,3}$/,                  // 1 a 3 caracteres (Números y guiones)
    
    // Cartelera Virtual y Solicitudes
    prioridad: /^[1-3]$/,                             // Valores numéricos 1, 2 o 3
    tituloCartelera: /^[A-Za-zÁÉÍÓÚáéíóúñÑ0-9.,;()'\"!?¡¿%°\-\s]{3,100}$/,
    descripcionCartelera: /^[A-Za-zÁÉÍÓÚáéíóúñÑ0-9.,;()'\"!?¡¿%°\-\s]{3,200}$/,
    
    // Gastos, Presupuestos y Caja Chica
    nombreDetalle: /^[A-Za-záéíóúñÑ \s]{4,50}$/,      // Nombres de ítems de presupuesto
    conceptoCaja: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s]{3,100}$/,
    descripcionCaja: /^[a-zA-Z0-9 áéíóúÁÉÍÓÚñÑ\s-]{0,100}$/,
    descripcion: /^[A-Za-z0-9áéíóúñÁÉÍÓÚÑ\s]{3,60}$/, 
    observacion: /^[A-Za-z0-9ñ.,\s]{0,50}$/,          // Opcional (Permite estar vacío)
    observacionExtendida: /^[a-zA-Z0-9\sáéíóúñÁÉÍÓÚÑ.,-]{3,60}$/,
    
    // Roles y Permisos
    accionPermiso: /^[A-Za-z_]{3,50}$/,               // Solo letras y guión bajo (Ej: GESTIONAR_USUARIOS)
    
    // Fechas y Años Fiscales
    diaMes: /^([1-9]|[12]\d|3[01])$/,                 // Días válidos del 1 al 31
    anio: /^\d{4}$/,                                  // Año de 4 dígitos (Ej: 2024)
    mesAnio: /^\d{1,2}-\d{4}$/,                       // Formato MM-YYYY o M-YYYY
    estadoAnio: /^[A-Za-z]{3,15}$/,                   // Estado del año fiscal (Abierta, Cerrada)


    // ============================================================
    // 5. RESTRICCIONES EN TIEMPO REAL (Para el evento 'keypress')
    // ============================================================
    
    // Teclas puras y alfanuméricas
    teclasLetras: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s()]+$/,
    teclasAlfanumerico: /^[A-Za-z0-9áéíóúñÁÉÍÓÚÑ\s]$/i,
    teclasObservacion: /^[A-Za-z0-9ñ.,\s]$/,
    teclasDireccion: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9,.\-#°\s]$/i,
    
    // Teclas numéricas y financieras
    teclasNumeros: /^[0-9]$/,
    teclasMonto: /^[0-9.,]$/,
    teclasPorcentaje: /^[\d.]$/,
    
    // Teclas específicas
    teclasCorreo: /^[A-Za-z0-9_+.@\b]*$/,
    teclasContrasenaExtendida: /^[A-Za-z0-9_.+*$#%&@-]$/,
    teclasCartelera: /^[A-Za-zÁÉÍÓÚáéíóúñÑ0-9.,;()'\"!?¡¿%°\-\s]*$/,
    teclasApartamento: /^[0-9-]$/,
    teclasAccion: /^[A-Za-z_]$/
};