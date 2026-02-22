// ayuda/FormatoFechas.js
const FormatoFechas = {
    /**
     * Formatea una fecha según el formato especificado.
     */
    formatear(fecha, formato) {
        if (!fecha) return '';

        const date = (fecha instanceof Date) ? fecha : new Date(fecha.replace(' ', 'T'));
        if (isNaN(date.getTime())) return '';

        const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        const mesesCompletos = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        const dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        const diasCompletos = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

        const anio = date.getFullYear();
        const mes = date.getMonth() + 1;
        const dia = date.getDate();
        let horas = date.getHours();
        const minutos = date.getMinutes();
        const segundos = date.getSeconds();
        const ampm = horas >= 12 ? 'PM' : 'AM';
        const horas12 = horas % 12 || 12;

        return formato.replace(/YYYY|YY|MMMM|MMM|MM|M|DD|D|dddd|ddd|HH|H|hh|h|mm|ss|A|a/g, (token) => {
            switch (token) {
                case 'YYYY': return anio;
                case 'YY': return anio.toString().slice(-2);
                case 'MMMM': return mesesCompletos[mes - 1];
                case 'MMM': return meses[mes - 1];
                case 'MM': return mes.toString().padStart(2, '0');
                case 'M': return mes;
                case 'DD': return dia.toString().padStart(2, '0');
                case 'D': return dia;
                case 'dddd': return diasCompletos[date.getDay()];
                case 'ddd': return dias[date.getDay()];
                case 'HH': return horas.toString().padStart(2, '0');
                case 'H': return horas;
                case 'hh': return horas12.toString().padStart(2, '0');
                case 'h': return horas12;
                case 'mm': return minutos.toString().padStart(2, '0');
                case 'ss': return segundos.toString().padStart(2, '0');
                case 'A': return ampm;
                case 'a': return ampm.toLowerCase();
                default: return token;
            }
        });
    },

    /**
     * Obtiene el nombre completo del mes a partir del número.
     * @param {number} mes - Número del mes (1-12)
     * @returns {string}
     */
    nombreMes(mes) {
        const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        return meses[mes - 1] || '';
    },

    /**
     * Formato rápido DD-MM-YYYY
     */
    formatoDMA(fecha) {
        return FormatoFechas.formatear(fecha, 'DD-MM-YYYY');
    },

    /**
     * Formato rápido DD-MM-YYYY HH:mm
     */
    formatoFechaHora(fecha) {
        return FormatoFechas.formatear(fecha, 'DD-MM-YYYY HH:mm');
    },

    cambiarFormatoFecha(fechaStr) {
        // 1. Limpiamos: Separamos por "/"
        const partes = fechaStr.split('/');

        // Si no hay 3 partes, la fecha está mal escrita
        if (partes.length !== 3) return "Formato inválido";

        // 2. Procesamos cada parte para que tenga 2 dígitos (excepto el año)
        const dia  = partes[0].padStart(2, '0');
        const mes  = partes[1].padStart(2, '0');
        const anio = partes[2]; // El año ya suele tener 4 dígitos

        // 3. Retornamos en orden YYYY-MM-DD
        return `${anio}-${mes}-${dia}`;
    },
    aLocal(fechaMySQL) {
        if (!fechaMySQL) return '';
        const partes = fechaMySQL.split('-');
        if (partes.length !== 3) return fechaMySQL;
        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    },  

    /**
     * Formato de último acceso (texto relativo)
     */
    formatoUltimoAcceso(fecha) {
        if (!fecha) return 'Nunca';
        const ahora = new Date();
        const fechaAcceso = new Date(fecha.replace(' ', 'T'));
        if (isNaN(fechaAcceso.getTime())) return '';

        const diffMs = ahora - fechaAcceso;
        const diffDias = Math.floor(diffMs / (1000 * 60 * 60 * 24));
        const horaFormateada = FormatoFechas.formatear(fechaAcceso, 'h:mm a');

        if (diffDias === 0) return `Hoy a las ${horaFormateada}`;
        if (diffDias === 1) return `Ayer a las ${horaFormateada}`;
        if (diffDias <= 7) {
            const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            return `El ${diasSemana[fechaAcceso.getDay()]} a las ${horaFormateada}`;
        }
        return FormatoFechas.formatear(fechaAcceso, 'DD/MM/YYYY') + ` a las ${horaFormateada}`;
    }
};

// Hacerlo global (si no se usa módulos)
window.FormatoFechas = FormatoFechas;