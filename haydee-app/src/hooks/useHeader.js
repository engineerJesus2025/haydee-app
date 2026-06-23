import { useState } from 'react';

export default function useHeader() {
  const [isMenuVisible, setIsMenuVisible] = useState(false);

  const toggleMenu = () => setIsMenuVisible((prev) => !prev);
  const closeMenu = () => setIsMenuVisible(false);

  return { isMenuVisible, toggleMenu, closeMenu };
}