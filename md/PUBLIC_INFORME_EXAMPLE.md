# Ejemplo de Uso - Formulario Público de Informes

## URLs de Ejemplo

Según las congregaciones existentes en el sistema (usando códigos):

### Congregación "Administración"
```
http://tu-dominio.com/informe/ADMIN
```

### Congregación "Lo Cañas"
```
http://tu-dominio.com/informe/LC
```

### Congregación "Los Copihues"
```
http://tu-dominio.com/informe/LCP
```

**Importante:** Las URLs ahora usan el código de la congregación en lugar del ID. Si el código no existe, se redirige a la página de inicio.

## Flujo de Uso

### 1. Acceder a la URL
El usuario accede a la URL correspondiente a su congregación.

### 2. Completar el Formulario

#### Paso 1: Seleccionar Grupo
- Se muestra una lista de grupos de la congregación
- Al seleccionar un grupo, se cargan automáticamente los usuarios

#### Paso 2: Seleccionar Usuario
- Aparecen solo los usuarios activos del grupo seleccionado
- Cada usuario puede enviar un solo informe por período

#### Paso 3: Seleccionar Período
- Mes actual: [Mes Actual] [Año Actual]
- Mes anterior: [Mes Anterior] [Año Anterior]

#### Paso 4: Indicar Participación
- **Si NO participó:** Solo completar comentarios (opcional) y enviar
- **Si SÍ participó:** Continuar con los siguientes campos

#### Paso 5: Seleccionar Servicio (si participó)
- Opciones disponibles: Publicador, Precursor Regular, Precursor Especial, etc.
- Campo obligatorio si marcó que participó

#### Paso 6: Ingresar Estudios (si participó)
- Cantidad de estudios bíblicos realizados
- Valor entre 0 y 50

#### Paso 7: Ingresar Horas (solo para ciertos servicios)
- **Se habilita automáticamente para:**
  - Precursor Regular (ID: 1)
  - Precursor Especial (ID: 3)
- Valor entre 1 y 100
- Campo obligatorio para estos servicios

#### Paso 8: Agregar Comentarios (opcional)
- Campo de texto libre
- Máximo 1000 caracteres
- Contador de caracteres en tiempo real

### 3. Enviar Informe
- Click en botón "Enviar Informe"
- El sistema valida todos los datos
- Muestra mensaje de éxito o errores

## Validaciones del Sistema

### Validaciones Automáticas

✅ **Campos obligatorios:**
- Grupo
- Usuario
- Período
- Servicio (si marcó participación)
- Horas (si servicio es ID 1 o 3)

✅ **Validaciones de lógica:**
- Usuario debe pertenecer al grupo seleccionado
- Grupo debe pertenecer a la congregación de la URL
- No puede existir otro informe del mismo usuario en el mismo período

✅ **Validaciones de formato:**
- Estudios: número entre 0-50
- Horas: número entre 1-100
- Comentarios: máximo 1000 caracteres

### Mensajes de Error

El sistema muestra mensajes claros:
- ❌ "Ya existe un informe para este usuario en el período seleccionado"
- ❌ "Debe seleccionar un servicio cuando participó en la actividad"
- ❌ "Debe ingresar las horas de servicio para este tipo de servicio"
- ❌ "El usuario no pertenece al grupo seleccionado"

### Mensajes de Éxito

- ✅ "¡Informe enviado exitosamente! Gracias por su colaboración."

## Escenarios de Uso

### Escenario 1: Usuario sin participación
1. Selecciona grupo y usuario
2. Selecciona período
3. NO marca "Participé en actividades"
4. Agrega comentario (opcional): "Estuve enfermo este mes"
5. Click "Enviar Informe"
6. ✅ Informe guardado con participa=0

### Escenario 2: Publicador regular
1. Selecciona grupo y usuario
2. Selecciona período
3. ✅ Marca "Participé en actividades"
4. Selecciona servicio: "Publicador"
5. Ingresa estudios: 2
6. (Campo horas permanece deshabilitado)
7. Click "Enviar Informe"
8. ✅ Informe guardado con estudios=2, horas=null

### Escenario 3: Precursor Regular
1. Selecciona grupo y usuario
2. Selecciona período
3. ✅ Marca "Participé en actividades"
4. Selecciona servicio: "Precursor Regular" (ID: 1)
5. Ingresa estudios: 5
6. Campo horas se habilita automáticamente
7. Ingresa horas: 70
8. Agrega comentario (opcional)
9. Click "Enviar Informe"
10. ✅ Informe guardado con estudios=5, horas=70

### Escenario 4: Intento de duplicado
1. Usuario intenta enviar informe del mismo período nuevamente
2. Sistema valida y muestra:
3. ❌ "Ya existe un informe para este usuario en el período seleccionado"
4. El usuario debe contactar al administrador para modificar el informe existente

## Características Especiales

### Interfaz Responsive
- ✅ Funciona en computadoras de escritorio
- ✅ Funciona en tablets
- ✅ Funciona en teléfonos móviles

### Feedback Visual
- Campos se iluminan al recibir foco
- Animaciones suaves al mostrar/ocultar campos
- Contador de caracteres para comentarios
- Spinner de carga al enviar

### Accesibilidad
- Navegable con teclado (Tab)
- Indicadores visuales claros
- Mensajes de error descriptivos
- Iconos intuitivos

## Soporte

Para cualquier problema o pregunta sobre el formulario:
1. Verificar que la URL contenga el ID correcto de la congregación
2. Asegurarse de que el grupo y usuario estén activos en el sistema
3. Revisar que no exista un informe previo del mismo período
4. Contactar al administrador del sistema si persisten los problemas
