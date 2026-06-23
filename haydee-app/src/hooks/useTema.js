import { useSelector } from 'react-redux'

export const useTema = () => {
  const modoOscuro = useSelector(state => state.tema.modoOscuro)

  const colores = modoOscuro
    ? {
        background: '#2A2A4D',
        text: '#ffffff',
        navigation: '#313190', 
        card: '#1e1e1e',
        border: '#333333',
        inputBackground: '#2d2d2d',
        textTitle: '#E1E1F7',
        backgroundTabla: '#1B1B29',
        backgroundBotones: '#313190',
        textPlaceholder: 'rgba(230, 230, 246, 0.71)',
        primario: '#4facfe'
      }
    : {
        background: '#F4F7F6',
        text: '#2c3e50',
        navigation: '#3939a9', 
        card: '#f8f8f8',
        border: '#e0e0e0',
        inputBackground: '#ffffff',
        textTitle: '#495057',
        backgroundTabla: '#fff',
        backgroundBotones: '#007BFF',
        textPlaceholder: '#999',
        primario: '#007BFF'
      }

  return { modoOscuro, colores }
}