import { useState, useCallback } from 'react';

/**
 * Hook para manejar estados booleanos (true/false)
 */
export default function useToggle(valorInicial = false) {
  const [estado, setEstado] = useState(valorInicial);

  // Usamos useCallback para que la función no se re-cree en cada render
  // ayuda a optimizar el rendimiento de componentes nativos hijos.
  const toggle = useCallback(() => {
    setEstado((prev) => !prev);
  }, []);

  const hacerTrue = useCallback(() => setEstado(true), []);
  const hacerFalse = useCallback(() => setEstado(false), []);

  return [estado, toggle, hacerTrue, hacerFalse];
}