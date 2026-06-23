import forge from 'node-forge';
import * as Crypto from 'expo-crypto';
import * as SecureStore from 'expo-secure-store';

// Para errores estructurados
export class CryptoError extends Error {
  constructor(codigo, mensaje) {
    super(mensaje);
    this.name = "CryptoError";
    this.codigo = codigo; 
  }
}

// REGLAS Y CONSTANTES CRIPTOGRÁFICAS
const CONFIG = {
  AES_KEY_SIZE_BYTES: 32, // Requerido para AES-256
  AES_IV_SIZE_BYTES: 12,  // 12 bytes (96 bits) es el estándar para AES-GCM
  SECURE_STORE_KEY: 'dispositivo_id_seguro'
};

let _llavePublicaRSA = null;
let _claveAESSesion = null;
let _dispositivoIdCache = null;

export const criptografiaMovil = {
  
  getDispositivoId: async () => {
    if (!_dispositivoIdCache) {
      let idGuardado = await SecureStore.getItemAsync(CONFIG.SECURE_STORE_KEY);
      
      if (!idGuardado) {
        idGuardado = Crypto.randomUUID();
        await SecureStore.setItemAsync(CONFIG.SECURE_STORE_KEY, idGuardado);
      }
      _dispositivoIdCache = idGuardado;
    }
    return _dispositivoIdCache;
  },

  getClaveAESSesion: () => _claveAESSesion,

  setLlavePublica: (pem) => {
    if (!pem) throw new CryptoError('PEM_INVALIDO', "Certificado PEM inválido");
    _llavePublicaRSA = forge.pki.publicKeyFromPem(pem);
  },

  preComputarAES: () => {
    if (!_llavePublicaRSA) throw new CryptoError('RSA_FALTANTE', "Llave pública RSA no establecida");

    // Generamos 32 bytes (256 bits) para seguridad militar
    const nuevaClaveAES = forge.random.getBytesSync(CONFIG.AES_KEY_SIZE_BYTES);
    
    // RSA-OAEP con SHA-256
    const claveAESCifradaConRSA = _llavePublicaRSA.encrypt(nuevaClaveAES, 'RSA-OAEP', {
      md: forge.md.sha256.create(),
      mgf1: {
        md: forge.md.sha256.create()
      }
    });
    
    _claveAESSesion = nuevaClaveAES;
    return forge.util.encode64(claveAESCifradaConRSA);
  },

  cifrarPayload: (objetoJSON) => {
    if (!_claveAESSesion) throw new CryptoError('AES_FALTANTE', "No hay clave de sesión establecida");

    // Convertimos el JSON a string
    const jsonString = JSON.stringify(objetoJSON);
    
    // codificación nativa
    const utf8String = forge.util.encodeUtf8(jsonString); 
    
    //Vector de inicialización Único por cada petición
    const iv = forge.random.getBytesSync(CONFIG.AES_IV_SIZE_BYTES);
    
    const cipher = forge.cipher.createCipher('AES-GCM', _claveAESSesion);
    cipher.start({ iv: iv });
    
    // Inyectamos la cadena ya codificada
    cipher.update(forge.util.createBuffer(utf8String));
    cipher.finish();

    return {
      payload: forge.util.encode64(cipher.output.getBytes()),
      iv: forge.util.encode64(iv),
      tag: forge.util.encode64(cipher.mode.tag.getBytes())
    };
  },

  descifrarPayload: (payloadB64, ivB64, tagB64) => {
    if (!_claveAESSesion) throw new CryptoError('AES_FALTANTE', "No hay clave de sesión establecida");

    const decipher = forge.cipher.createDecipher('AES-GCM', _claveAESSesion);
    
    decipher.start({
      iv: forge.util.decode64(ivB64),
      tag: forge.util.decode64(tagB64)
    });
    
    decipher.update(forge.util.createBuffer(forge.util.decode64(payloadB64)));
    const pass = decipher.finish();

    if (!pass) {
      throw new CryptoError('TAG_INVALIDO', "Fallo de integridad: El mensaje fue interceptado/modificado");
    }

    const utf8Decoded = forge.util.decodeUtf8(decipher.output.getBytes());
    
    return JSON.parse(utf8Decoded);
  },

  limpiarClaves: () => {
    _claveAESSesion = null;
    console.log("Seguridad: Clave AES eliminada de la memoria volátil.");
  }
};