export const ITEM_TYPES = [
  { value: 'medical_consumable', label: 'Medical Consumable', labelAr: 'مستهلكات طبية' },
  { value: 'medicine', label: 'Medicine', labelAr: 'دواء' },
  { value: 'medical_device', label: 'Medical Device', labelAr: 'جهاز طبي' },
  { value: 'medical_equipment', label: 'Medical Equipment', labelAr: 'معدات طبية' },
  { value: 'narcotic_controlled', label: 'Narcotic / Controlled', labelAr: 'مواد مخدرة / مراقبة' },
] as const

export const WAREHOUSE_TYPES = [
  { value: 'standard', label: 'Standard', labelAr: 'عادي' },
  { value: 'cold_storage', label: 'Cold Storage', labelAr: 'تبريد' },
  { value: 'quarantine', label: 'Quarantine', labelAr: 'حجر' },
  { value: 'hazmat', label: 'Hazardous Materials', labelAr: 'مواد خطرة' },
  { value: 'controlled', label: 'Controlled', labelAr: 'مراقب' },
] as const

export const ZONE_TYPES = [
  { value: 'receiving', label: 'Receiving', labelAr: 'استلام' },
  { value: 'storage', label: 'Storage', labelAr: 'تخزين' },
  { value: 'picking', label: 'Picking', labelAr: 'التقاط' },
  { value: 'shipping', label: 'Shipping', labelAr: 'شحن' },
  { value: 'quarantine', label: 'Quarantine', labelAr: 'حجر' },
  { value: 'controlled', label: 'Controlled', labelAr: 'مراقب' },
] as const

export const BARCODE_TYPES = [
  'EAN13', 'EAN8', 'UPC', 'CODE128', 'CODE39', 'QR', 'DATAMATRIX', 'GS1_128', 'GTIN',
] as const

export const PER_PAGE_OPTIONS = [10, 25, 50, 100] as const
