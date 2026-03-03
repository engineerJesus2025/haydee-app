// ayuda/FormatoFechas.js
const FormatoFechas = (function() {
    // ============================================================
    // CONSTANTES PRIVADAS (inmutables)
    // ============================================================
    const mesesAbreviados = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    const mesesCompletos = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    const diasAbreviados = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    const diasCompletos = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

    Object.freeze(mesesAbreviados);
    Object.freeze(mesesCompletos);
    Object.freeze(diasAbreviados);
    Object.freeze(diasCompletos);

    // ============================================================
    // PARSEADOR PRIVADO (convierte cualquier entrada a Date local)
    // ============================================================

    /**
     * Analiza una entrada y devuelve un objeto Date válido en hora local.
     * Soporta: Date, timestamp numérico, strings en formato ISO, MySQL, latinoamericano, etc.
     * @param {Date|string|number} entrada - Fecha en cualquier formato soportado.
     * @returns {Date|null} - Objeto Date en hora local, o null si es inválido.
     */
    function parsearFecha(entrada) {
        // Casos especiales: null, undefined, 0 (timestamp válido)
        if (entrada == null) return null;
        if (entrada instanceof Date) {
            return isNaN(entrada.getTime()) ? null : new Date(entrada);
        }

        // Si es número, asumimos timestamp UNIX en milisegundos
        if (typeof entrada === 'number' && !isNaN(entrada)) {
            const d = new Date(entrada);
            return isNaN(d.getTime()) ? null : d;
        }

        const str = String(entrada).trim();
        if (str === '') return null;

        // 1. Formato ISO completo con zona horaria (YYYY-MM-DDTHH:mm:ss.sssZ o ±hh:mm)
        if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d+)?(Z|[+-]\d{2}:\d{2})?$/i.test(str)) {
            const d = new Date(str);
            return isNaN(d.getTime()) ? null : d;
        }

        // 2. Fecha con hora separada por espacio (YYYY-MM-DD HH:mm:ss o DD/MM/YYYY HH:mm)
        let match = str.match(/^(\d{1,4})[-/](\d{1,2})[-/](\d{1,4})\s+(\d{1,2}):(\d{1,2})(?::(\d{1,2}))?$/);
        if (match) {
            const [_, parte1, parte2, parte3, hora, min, seg] = match;
            return crearFechaLocal(parte1, parte2, parte3, hora, min, seg || 0);
        }

        // 3. Solo fecha (sin hora)
        match = str.match(/^(\d{1,4})[-/](\d{1,2})[-/](\d{1,4})$/);
        if (match) {
            const [_, parte1, parte2, parte3] = match;
            return crearFechaLocal(parte1, parte2, parte3);
        }

        // 4. Último recurso: dejar que el constructor Date nativo lo intente (poco fiable, pero cubre casos raros)
        const d = new Date(str);
        return isNaN(d.getTime()) ? null : d;
    }

    /**
     * Construye un objeto Date en hora local a partir de componentes numéricos.
     * Determina automáticamente si el formato es YYYY-MM-DD o DD-MM-YYYY.
     * @param {string} comp1 - Primer componente (día o año)
     * @param {string} comp2 - Segundo componente (mes)
     * @param {string} comp3 - Tercer componente (año o día)
     * @param {number|string} [hora=0] - Hora (0-23)
     * @param {number|string} [min=0] - Minutos
     * @param {number|string} [seg=0] - Segundos
     * @returns {Date|null} - Fecha local o null si los componentes son inválidos.
     */
    function crearFechaLocal(comp1, comp2, comp3, hora = 0, min = 0, seg = 0) {
        let año, mes, dia;

        // Detectar orden: si el primer campo tiene 4 dígitos → YYYY-MM-DD
        // si el tercero tiene 4 dígitos → DD-MM-YYYY
        if (/^\d{4}$/.test(comp1)) {
            año = parseInt(comp1, 10);
            mes = parseInt(comp2, 10) - 1; // los meses van de 0 a 11
            dia = parseInt(comp3, 10);
        } else if (/^\d{4}$/.test(comp3)) {
            año = parseInt(comp3, 10);
            mes = parseInt(comp2, 10) - 1;
            dia = parseInt(comp1, 10);
        } else {
            // Asumimos formato DD/MM/YY (año de 2 dígitos al final)
            año = parseInt(comp3, 10);
            if (año < 100) año += 2000; // O usar una lógica configurable
            mes = parseInt(comp2, 10) - 1;
            dia = parseInt(comp1, 10);
        }

        // Validaciones básicas
        if (mes < 0 || mes > 11 || dia < 1 || dia > 31) return null;

        const h = parseInt(hora, 10) || 0;
        const m = parseInt(min, 10) || 0;
        const s = parseInt(seg, 10) || 0;

        return new Date(año, mes, dia, h, m, s);
    }

    // ============================================================
    // API PÚBLICA
    // ============================================================
    return {
        // --------------------------------------------------------
        // FORMATEO GENÉRICO
        // --------------------------------------------------------
        /**
         * Formatea una fecha según una máscara personalizada.
         * @param {Date|string|number} fechaEntrada - Fecha en cualquier formato soportado.
         * @param {string} patron - Patrón de formato. Tokens disponibles:
         *   YYYY, YY, MMMM, MMM, MM, M, DD, D, dddd, ddd, HH, H, hh, h, mm, ss, A, a.
         *   Los textos literales deben ir entre corchetes: [texto]
         * @returns {string} - Fecha formateada, o cadena vacía si la entrada es inválida.
         * @example
         *   FormatoFechas.formatear('2025-03-01', 'DD [de] MMMM [del] YYYY'); // "01 de Marzo del 2025"
         */
        formatear(fechaEntrada, patron) {
            const fecha = parsearFecha(fechaEntrada);
            if (!fecha) return '';

            // Extraer literales entre corchetes y reemplazarlos por marcadores seguros (@@@i@@@)
            const literales = [];
            const patronConMarcadores = patron.replace(/\[([^\]]+)]/g, (match, texto) => {
                literales.push(texto);
                return `@@@${literales.length - 1}@@@`;
            });

            const año = fecha.getFullYear();
            const mes = fecha.getMonth() + 1;
            const dia = fecha.getDate();
            const horas = fecha.getHours();
            const minutos = fecha.getMinutes();
            const segundos = fecha.getSeconds();
            const ampm = horas >= 12 ? 'PM' : 'AM';
            const horas12 = horas % 12 || 12;

            // Reemplazar tokens de fecha
            let resultado = patronConMarcadores.replace(/YYYY|YY|MMMM|MMM|MM|M|DD|D|dddd|ddd|HH|H|hh|h|mm|ss|A|a/g, (token) => {
                switch (token) {
                    case 'YYYY': return año;
                    case 'YY': return año.toString().slice(-2);
                    case 'MMMM': return mesesCompletos[mes - 1];
                    case 'MMM': return mesesAbreviados[mes - 1];
                    case 'MM': return mes.toString().padStart(2, '0');
                    case 'M': return mes;
                    case 'DD': return dia.toString().padStart(2, '0');
                    case 'D': return dia;
                    case 'dddd': return diasCompletos[fecha.getDay()];
                    case 'ddd': return diasAbreviados[fecha.getDay()];
                    case 'HH': return horas.toString().padStart(2, '0');
                    case 'H': return horas;
                    case 'hh': return horas12.toString().padStart(2, '0');
                    case 'h': return horas12;
                    case 'mm': return minutos.toString().padStart(2, '0');
                    case 'ss': return segundos.toString().padStart(2, '0');
                    case 'A': return ampm;
                    case 'a': return ampm.toLowerCase();
                    default: return token; // No debería ocurrir
                }
            });

            // Restaurar literales
            literales.forEach((texto, i) => {
                const marcador = `@@@${i}@@@`;
                resultado = resultado.split(marcador).join(texto);
            });

            return resultado;
        },

        // --------------------------------------------------------
        // FORMATEOS PREDEFINIDOS (Representan intenciones, no solo formatos)
        // --------------------------------------------------------
        /**
         * Formato corto: DD/MM/YYYY (por defecto). Permite cambiar el separador.
         * @param {Date|string|number} fechaEntrada
         * @param {string} [separador='/'] - Carácter separador (ej. '-', '.')
         * @returns {string}
         * @example
         *   FormatoFechas.formatoCorto('2025-03-01', '-'); // "01-03-2025"
         */
        formatoUsuario(fechaEntrada, separador = '/') {
            return this.formatear(fechaEntrada, `DD${separador}MM${separador}YYYY`);
        },

        /**
         * Convierte cualquier fecha soportada al formato MySQL: YYYY-MM-DD.
         * @param {Date|string|number} fechaEntrada
         * @returns {string}
         */
        formatoFechaBD(fechaEntrada) {
            return this.formatear(fechaEntrada, 'YYYY-MM-DD');
        },

        // --------------------------------------------------------
        // TIEMPO RELATIVO Y AMIGABLE (LÓGICA ESPECIAL)
        // --------------------------------------------------------
        /**
         * Devuelve una descripción amigable del tiempo transcurrido desde la fecha dada.
         * Ej: "Hace 5 minutos", "Hace 2 horas", "Hace 3 días", etc.
         * @param {Date|string|number} fechaEntrada
         * @returns {string} - Texto relativo, o cadena vacía si la fecha es inválida.
         */
        tiempoRelativo(fechaEntrada) {
            const fecha = parsearFecha(fechaEntrada);
            if (!fecha) return '';

            const ahora = new Date();
            const segundos = Math.round((ahora - fecha) / 1000);
            const minutos = Math.round(segundos / 60);
            const horas = Math.round(minutos / 60);
            const dias = Math.round(horas / 24);
            const meses = Math.round(dias / 30);
            const años = Math.round(dias / 365);

            if (segundos < 60) return 'Hace unos segundos';
            if (minutos < 60) return `Hace ${minutos} minuto${minutos !== 1 ? 's' : ''}`;
            if (horas < 24) return `Hace ${horas} hora${horas !== 1 ? 's' : ''}`;
            if (dias < 30) return `Hace ${dias} día${dias !== 1 ? 's' : ''}`;
            if (meses < 12) return `Hace ${meses} mes${meses !== 1 ? 'es' : ''}`;
            return `Hace ${años} año${años !== 1 ? 's' : ''}`;
        },

        /**
         * Formato especial para "último acceso": Hoy/Ayer/día de la semana o fecha completa.
         * @param {Date|string|number} fechaEntrada
         * @returns {string} - Ej: "Hoy a las 3:30 pm", "Ayer a las 10:15 am", "El Miércoles a las 8:00 pm", o "01/03/2025 a las 12:00 am".
         */
        formatoUltimoAcceso(fechaEntrada) {
            const fecha = parsearFecha(fechaEntrada);
            if (!fecha) return 'Nunca';

            const ahora = new Date();
            // Normalizar a medianoche para comparar solo días
            const hoy = new Date(ahora.getFullYear(), ahora.getMonth(), ahora.getDate());
            const fechaAcceso = new Date(fecha.getFullYear(), fecha.getMonth(), fecha.getDate());

            const diffDias = Math.floor((hoy - fechaAcceso) / (1000 * 60 * 60 * 24));
            const horaFormateada = this.formatear(fecha, 'h:mm a');

            if (diffDias === 0) return `Hoy a las ${horaFormateada}`;
            if (diffDias === 1) return `Ayer a las ${horaFormateada}`;
            if (diffDias > 1 && diffDias <= 7) {
                return `El ${diasCompletos[fecha.getDay()]} a las ${horaFormateada}`;
            }
            return this.formatear(fecha, 'DD/MM/YYYY') + ` a las ${horaFormateada}`;
        },

        // --------------------------------------------------------
        // OPERACIONES CON FECHAS
        // --------------------------------------------------------
        /**
         * Suma (o resta, si el valor es negativo) días a una fecha.
         * @param {Date|string|number} fechaEntrada - Fecha base.
         * @param {number} dias - Número de días a agregar (puede ser negativo).
         * @returns {Date|null} - Nueva fecha resultante, o null si la entrada es inválida.
         */
        sumarDias(fechaEntrada, dias) {
            const fecha = parsearFecha(fechaEntrada);
            if (!fecha) return null;
            fecha.setDate(fecha.getDate() + dias);
            return fecha;
        },

        // --------------------------------------------------------
        // COMPARACIONES
        // --------------------------------------------------------
        /**
         * Compara si dos fechas corresponden al mismo día (ignorando hora).
         * @param {Date|string|number} fecha1
         * @param {Date|string|number} fecha2
         * @returns {boolean} - true si son el mismo día, mes y año.
         */
        esMismoDia(fecha1, fecha2) {
            const f1 = parsearFecha(fecha1);
            const f2 = parsearFecha(fecha2);
            if (!f1 || !f2) return false;
            return f1.getFullYear() === f2.getFullYear() &&
                   f1.getMonth() === f2.getMonth() &&
                   f1.getDate() === f2.getDate();
        },

        /**
         * Retorna el nombre completo del mes (ej. "Marzo") dado su número (1-12).
         * @param {number} mesNumero - Número del mes (1 = Enero, 12 = Diciembre).
         * @returns {string}
         */
        nombreMes(mesNumero) {
            return mesesCompletos[mesNumero - 1] || '';
        },
        // --------------------------------------------------------
        // VALIDACIÓN Y UTILIDADES EXTRA
        // --------------------------------------------------------
        
        /**
         * Verifica si una entrada puede convertirse en una fecha real.
         */
        esValida(entrada) {
            return parsearFecha(entrada) !== null;
        },

        /**
         * Calcula la diferencia numérica en días entre dos fechas.
         * Útil para cálculos lógicos (moras, vencimientos).
         */
        diferenciaEnDias(fechaInicio, fechaFin) {
            const f1 = parsearFecha(fechaInicio);
            const f2 = parsearFecha(fechaFin);
            if (!f1 || !f2) return 0;

            const inicio = new Date(f1.getFullYear(), f1.getMonth(), f1.getDate());
            const fin = new Date(f2.getFullYear(), f2.getMonth(), f2.getDate());
            return Math.floor((fin - inicio) / (1000 * 60 * 60 * 24));
        },

        /**
         * Devuelve el objeto Date configurado al inicio o fin del día.
         * Útil para filtros de búsqueda en bases de datos.
         */
        limitesDelDia(fechaEntrada, modo = 'inicio') {
            const fecha = parsearFecha(fechaEntrada);
            if (!fecha) return null;

            if (modo === 'inicio') {
                return new Date(fecha.getFullYear(), fecha.getMonth(), fecha.getDate(), 0, 0, 0);
            } else {
                return new Date(fecha.getFullYear(), fecha.getMonth(), fecha.getDate(), 23, 59, 59);
            }
        },

        /**
         * Devuelve la edad actual basada en una fecha de nacimiento.
         */
        calcularEdad(fechaNacimiento) {
            const nacimiento = parsearFecha(fechaNacimiento);
            if (!nacimiento) return 0;
            
            const hoy = new Date();
            let edad = hoy.getFullYear() - nacimiento.getFullYear();
            const mes = hoy.getMonth() - nacimiento.getMonth();
            
            if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
                edad--;
            }
            return edad;
        }
    };
})();

// Exponer globalmente si estamos en un navegador
if (typeof window !== 'undefined') {
    window.FormatoFechas = FormatoFechas;
}