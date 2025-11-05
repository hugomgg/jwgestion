# Sistema de Ingreso Público de Informes

## Descripción

Se ha implementado un sistema de formulario público para que los usuarios puedan ingresar sus informes mensuales sin necesidad de autenticación.

## URL de Acceso

La URL pública tiene el siguiente formato:

```
/informe/{congregacion_codigo}
```

**Ejemplo:**
- Para la congregación con código "LC": `http://tu-dominio.com/informe/LC`
- Para la congregación con código "ADMIN": `http://tu-dominio.com/informe/ADMIN`

**Nota:** Si el código no existe, se redirige automáticamente a la página de inicio con un mensaje de error.

## Características del Formulario

### Campos del Formulario

1. **Grupo** (obligatorio)
   - Lista desplegable con los grupos de la congregación especificada
   - Solo muestra grupos activos (estado=1)
   - Campo: `informes.grupo_id`

2. **Usuario** (obligatorio)
   - Se carga dinámicamente según el grupo seleccionado
   - Solo muestra usuarios activos del grupo seleccionado
   - Campo: `informes.user_id`

3. **Período** (obligatorio)
   - Lista con dos opciones:
     - Mes actual (año-mes)
     - Mes anterior (año-mes)
   - Campos: `informes.anio` y `informes.mes`

4. **Actividad** (checkbox)
   - Indica si participó en actividades del ministerio
   - Habilita/deshabilita otros campos según su estado
   - Campo: `informes.participa`

5. **Servicio**
   - Deshabilitado por defecto
   - Se habilita cuando se marca "Actividad"
   - Campo: `informes.servicio_id`

6. **Estudios**
   - Deshabilitado por defecto
   - Se habilita cuando se marca "Actividad"
   - Rango: 0-50
   - Campo: `informes.cantidad_estudios`

7. **Horas**
   - Deshabilitado por defecto
   - Se habilita solo cuando:
     - Se marca "Actividad" Y
     - Se selecciona un servicio con ID 1 o 3 (Precursor Regular o Especial)
   - Rango: 1-100
   - Campo: `informes.horas`

8. **Comentarios**
   - Campo de texto opcional
   - Máximo 1000 caracteres
   - Contador de caracteres en tiempo real
   - Campo: `informes.comentario`

### Validaciones

El formulario valida:
- ✅ No permite duplicados (misma congregación, usuario, año y mes)
- ✅ El usuario debe pertenecer al grupo seleccionado
- ✅ El grupo debe pertenecer a la congregación de la URL
- ✅ Servicio obligatorio si marcó "Actividad"
- ✅ Horas obligatorias para servicios ID 1 o 3 (si marcó "Actividad")
- ✅ Todos los campos tienen validación de formato y rango

### Comportamiento Dinámico

- **Al seleccionar Grupo:** Carga automáticamente los usuarios de ese grupo
- **Al marcar Actividad:** Habilita campos de Servicio y Estudios
- **Al desmarcar Actividad:** Deshabilita y limpia Servicio, Estudios y Horas
- **Al seleccionar Servicio:** Habilita campo Horas solo si es ID 1 o 3

## Diseño del Formulario

- ✨ Diseño moderno y responsive
- 🎨 Panel dividido: información a la izquierda, formulario a la derecha
- 📱 Adaptable a dispositivos móviles
- ♿ Accesible con teclado
- 🎯 Animaciones suaves y feedback visual
- ⚡ Validación en tiempo real

## Archivos Creados

### Backend
1. **Controlador:** `app/Http/Controllers/PublicInformeController.php`
   - Métodos: `show()`, `store()`, `getUsersByGrupo()`

### Frontend
2. **Vista:** `resources/views/public/informe.blade.php`
   - HTML completo del formulario

3. **JavaScript:** `public/js/public-informe.js`
   - Lógica de interacción y AJAX

4. **CSS:** `public/css/public-informe.css`
   - Estilos modernos y responsivos

### Rutas
5. **Rutas públicas en:** `routes/web.php`
   ```php
   Route::get('/informe/{congregacion_id}', [PublicInformeController::class, 'show']);
   Route::post('/informe/{congregacion_id}', [PublicInformeController::class, 'store']);
   Route::get('/informe/{congregacion_id}/usuarios-por-grupo', [PublicInformeController::class, 'getUsersByGrupo']);
   ```

## Uso

1. Comparte la URL con el ID de la congregación a los usuarios
2. Los usuarios completan el formulario
3. El sistema valida y guarda el informe
4. Muestra confirmación de éxito o mensajes de error según corresponda

## Notas Técnicas

- No requiere autenticación (ruta pública)
- El campo `informes.congregacion_id` se obtiene automáticamente de la URL
- Los campos de auditoría (`creador_id`, `modificador_id`) se llenan con el `user_id` del informe
- Todos los informes creados tienen `estado=1` (activo) por defecto

## Seguridad

- Validación de datos en backend
- Protección CSRF con token
- Verificación de existencia de congregación
- Validación de relaciones entre grupo, usuario y congregación
- Prevención de duplicados
