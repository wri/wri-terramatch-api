# Carga de Registros de Inversiones desde CSV

Este directorio contiene los archivos CSV necesarios para cargar registros en las tablas de Investments e Investment splits.

## Archivos CSV Disponibles

### 1. `Investments.csv`
Contiene 3 registros de inversiones con los siguientes campos:
- **UUID**: UUID único de la inversión (ya proporcionado)
- **projectUuid**: ID del proyecto en la base de datos
- **investmentDate**: Fecha de la inversión (formato: 1/4/2025)
- **type**: Tipo de inversión (ej: Debt)

### 2. `Investment splits.csv`
Contiene 3 registros de investment splits que se vinculan con las inversiones:
- **uuid**: UUID de referencia que vincula con el archivo de inversiones
- **investmentUuid**: Campo vacío (se usa el uuid como referencia)
- **funder**: Nombre del financiador (ej: ted-audacious)
- **amount**: Monto de la inversión (formato: $85,000.00)

## Comando para Cargar los Datos

### Comando Principal
```bash
php artisan one-off:load-investment-records-from-csv {archivo_inversiones} {archivo_splits} [--dry-run]
```

### Ejemplo con tus archivos:
```bash
# Vista previa (dry-run) - RECOMENDADO PRIMERO
php artisan one-off:load-investment-records-from-csv imports/Investments.csv imports/Investment\ splits.csv --dry-run

# Carga real de datos
php artisan one-off:load-investment-records-from-csv imports/Investments.csv imports/Investment\ splits.csv
```

## Cómo Funciona el Comando

### 1. **Procesamiento de Investments**
- Lee el archivo `Investments.csv`
- Valida que los proyectos existan en la base de datos
- Crea registros en la tabla `investments` usando los UUIDs proporcionados
- Mapea los UUIDs para vincular con los splits

### 2. **Procesamiento de Investment Splits**
- Lee el archivo `Investment splits.csv`
- Usa el campo `uuid` como referencia para vincular con las inversiones
- Crea registros en la tabla `investment_splits`
- Genera nuevos UUIDs únicos para cada split

### 3. **Vinculación de Datos**
- El UUID en `Investment splits.csv` se usa para encontrar la inversión correspondiente
- Se crea la relación entre `investment_id` y `investment_splits.investment_id`

## Estructura de los Datos

### Tabla `investments`
- `uuid`: UUID proporcionado en el CSV (ej: 617601e0-9839-49fd-b48e-6c07404e7140)
- `project_id`: ID del proyecto (ej: 313)
- `investment_date`: Fecha parseada (ej: 2025-01-04)
- `type`: Tipo de inversión (ej: Debt)

### Tabla `investment_splits`
- `uuid`: UUID único generado automáticamente
- `investment_id`: ID de la inversión (foreign key)
- `funder`: Nombre del financiador (ej: ted-audacious)
- `amount`: Monto sin símbolos de moneda (ej: 85000.00)

## Características del Comando

### **Manejo de Formatos**
- **Delimitador**: Usa punto y coma (;) como separador
- **Fechas**: Parsea múltiples formatos (1/4/2025, 01/04/2025, etc.)
- **Montos**: Elimina símbolos de moneda ($) y comas (,)
- **Encoding**: Maneja BOM y problemas de codificación

### **Validaciones**
- Verifica que los proyectos existan antes de crear inversiones
- Valida el formato de fechas
- Verifica que los montos sean numéricos
- Asegura la integridad referencial entre tablas

### **Manejo de Errores**
- Muestra advertencias específicas para cada fila
- Continúa procesando otras filas si hay errores
- Proporciona información detallada de debugging

## Modo Dry-Run

**IMPORTANTE**: Siempre ejecuta primero con `--dry-run` para:
- Ver qué registros se crearían
- Identificar posibles problemas
- Verificar que los datos se lean correctamente

```bash
php artisan one-off:load-investment-records-from-csv imports/Investments.csv imports/Investment\ splits.csv --dry-run
```

## Ejemplo de Salida del Comando

```
Starting to load investment records from CSV files...
Processing investments from: imports/Investments.csv
Headers found: UUID, projectUuid, investmentDate, type
Would create investment: UUID=617601e0-9839-49fd-b48e-6c07404e7140, Project=313, Date=2025-01-04, Type=Debt
Would create investment: UUID=c462918b-47f7-4ed5-99e0-7fec6e342036, Project=389, Date=2025-02-06, Type=Debt
Would create investment: UUID=fd1c50d3-1bd9-454a-bd81-8493b9ab2e84, Project=474, Date=2025-01-07, Type=Debt
Investments processed: 3, skipped: 0

Processing investment splits from: imports/Investment splits.csv
Headers found: uuid, investmentUuid, funder, amount
Would create investment split: Investment UUID=617601e0-9839-49fd-b48e-6c07404e7140, Funder=ted-audacious, Amount=85000
Would create investment split: Investment UUID=c462918b-47f7-4ed5-99e0-7fec6e342036, Funder=ted-audacious, Amount=104957.45
Would create investment split: Investment UUID=fd1c50d3-1bd9-454a-bd81-8493b9ab2e84, Funder=ted-audacious, Amount=115277
Investment splits processed: 3, skipped: 0

Dry run completed successfully. No data was persisted.
```

## Solución de Problemas

### Error: "Project ID X not found"
- Verifica que los IDs 313, 389, y 474 existan en la tabla `v2_projects`
- Asegúrate de que sean números enteros válidos

### Error: "Invalid date format"
- El comando maneja automáticamente fechas en formato 1/4/2025
- Si hay problemas, verifica que las fechas estén en formato reconocible

### Error: "Invalid amount format"
- El comando elimina automáticamente símbolos $ y comas
- Asegúrate de que los montos sean números válidos

### Error: "Investment UUID X not found"
- Verifica que los UUIDs en ambos archivos coincidan
- Asegúrate de que no haya espacios extra o caracteres ocultos

## Notas Importantes

1. **UUIDs de Investments**: Se usan los proporcionados en el CSV
2. **UUIDs de Splits**: Se generan automáticamente nuevos UUIDs únicos
3. **Transacciones**: Todas las operaciones se ejecutan dentro de una transacción
4. **Validaciones**: Se valida la integridad de los datos antes de crear registros
5. **Delimitador**: El comando está configurado para usar punto y coma (;) como separador

## Personalización

Si necesitas modificar el comportamiento del comando:
1. Edita el archivo `app/Console/Commands/OneOff/LoadInvestmentRecordsFromCsvCommand.php`
2. Ajusta el delimitador en `fgetcsv($handle, 1000, ';')`
3. Modifica las validaciones en `validateRow()`
4. Personaliza el parsing de fechas en `parseDate()`
5. Ajusta el parsing de montos en `parseAmount()`
