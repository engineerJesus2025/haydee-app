// EscaneoOverlayLottie.js
import React from 'react';
import { View, StyleSheet } from 'react-native';
import LottieView from 'lottie-react-native';

export default function EscaneoOverlay({
  visible,
  width = 200,
  height = 200,
}) {
  if (!visible) return null;

  return (
    <View style={[styles.container, { width, height }]} pointerEvents="none">
      <View style={styles.tinte} />
      <LottieView
        source={require('../../assets/escaner_ia_animacion3.json')}
        autoPlay
        loop
        style={{ width, height }}
        resizeMode="cover"
        renderMode="software"
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { position: 'absolute', top: 0, left: 0, borderRadius: 12, overflow: 'hidden' },
  tinte: { ...StyleSheet.absoluteFillObject, backgroundColor: 'rgba(0,0,0,0.35)' },
});