# Métricas generadas automáticamente

**Total de imágenes evaluadas:** 26

## 6.1 Métricas por campo (resultado combinado)

| Campo | Precisión | Recall | F1-score | Exactitud |
|---|---|---|---|---|
| `numero_referencia` | 0.792 | 0.760 | 0.776 | 0.769 |
| `monto_detectado` | 0.789 | 0.577 | 0.667 | 0.577 |
| `banco_origen` | 0.731 | 0.731 | 0.731 | 0.731 |
| `fecha_operacion` | 1.000 | 0.833 | 0.909 | 0.846 |

## 6.2 Métricas del OCR crudo (texto)

| Métrica | Valor |
|---|---|
| CER medio | 0.287 |
| WER medio | 0.460 |

## 6.3 Análisis de ablación por filtro

| Intento | Filtro | Imágenes resueltas | Tasa de éxito acumulada (%) | Confianza promedio (%) | CER medio |
|---|---|---|---|---|---|
| 1 | Ninguno (cruda) | 16 | 61.5 | 76.5 | 0.287 |
| 2 | Otsu | 0 | 61.5 | 72.7 | 0.331 |
| 3 | Adaptativo | 2 | 69.2 | 57.6 | 0.403 |
| — | **Fallo total** | 8 | 30.8 | — | — |

## 6.4 Matriz de confusión del clasificador de banco

| Real \ Predicho | 0102 | 0105 | 0108 | 0115 | 0134 | 0172 | 0175 | 0191 | Desconocido |
|---|---|---|---|---|---|---|---|---|---|
| **0102** | 11 | 0 | 1 | 0 | 0 | 0 | 0 | 0 | 0 |
| **0105** | 0 | 6 | 0 | 0 | 0 | 0 | 0 | 0 | 1 |
| **0108** | 0 | 0 | 1 | 0 | 0 | 0 | 0 | 0 | 0 |
| **0115** | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 1 |
| **0134** | 0 | 0 | 0 | 0 | 1 | 0 | 0 | 0 | 0 |
| **0172** | 0 | 0 | 1 | 0 | 0 | 0 | 0 | 0 | 0 |
| **0175** | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| **0191** | 0 | 0 | 0 | 0 | 0 | 2 | 1 | 0 | 0 |
| **Desconocido** | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 |

## 6.5 Distribución por tipo de imagen

| Tipo | Total | Intento 1 | Intento 2 | Intento 3 | Fallo |
|---|---|---|---|---|---|
| digital | 21 | 14 | 0 | 2 | 5 |
| foto | 5 | 2 | 0 | 0 | 3 |

## 7.x Datos para análisis de errores

- Casos con `confianza < 60%` pero marcados como éxito: **1**
- Casos marcados como éxito con `banco_origen` o `fecha_operacion` incorrectos: **5**
- Casos resueltos en intento 2 donde el adaptativo habría dado mejor CER: **0**

## 8. Resumen para conclusiones

- Tasa de resolución automática (sin revisión humana): **69.2%** (18/26)
- Tasa de fallo total (requiere revisión manual): **30.8%** (8/26)
- CER medio del pipeline completo: **0.287**
- WER medio del pipeline completo: **0.460**
