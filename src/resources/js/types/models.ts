export type ItemType =
  | 'medical_consumable'
  | 'medicine'
  | 'medical_device'
  | 'medical_equipment'
  | 'narcotic_controlled'

export type TransactionType =
  | 'receiving'
  | 'putaway'
  | 'issue'
  | 'transfer'
  | 'return'
  | 'adjustment'
  | 'count'
  | 'disposal'
  | 'reservation'
  | 'release'
  | 'quarantine'
  | 'unquarantine'

export type EpcStatus =
  | 'unassigned'
  | 'active'
  | 'in_use'
  | 'decommissioned'
  | 'lost'
  | 'damaged'
  | 'returned'

export type AssetStatus =
  | 'available'
  | 'assigned'
  | 'in_transit'
  | 'in_maintenance'
  | 'in_calibration'
  | 'quarantined'
  | 'damaged'
  | 'lost'
  | 'retired'
  | 'decommissioned'

export interface User {
  id: number
  company_id: number | null
  branch_id: number | null
  name: string
  name_ar: string | null
  email: string
  phone: string | null
  avatar: string | null
  locale: string
  theme: string
  is_active: boolean
  roles: Role[]
  permissions: string[]
}

export interface Role {
  id: number
  name: string
  company_id: number | null
  permissions: Permission[]
}

export interface Permission {
  id: number
  name: string
}

export interface Company {
  id: number
  name: string
  name_ar: string | null
  code: string
  logo: string | null
  is_active: boolean
}

export interface Branch {
  id: number
  company_id: number
  name: string
  name_ar: string | null
  code: string
  is_active: boolean
}

export interface Warehouse {
  id: number
  company_id: number
  branch_id: number
  name: string
  name_ar: string | null
  code: string
  type: string
  is_active: boolean
}

export interface Zone {
  id: number
  warehouse_id: number
  name: string
  code: string
  type: string
}

export interface Item {
  id: number
  company_id: number
  item_code: string
  name: string
  name_ar: string | null
  item_type: ItemType
  category_id: number
  primary_uom_id: number
  batch_controlled: boolean
  expiry_controlled: boolean
  serial_controlled: boolean
  rfid_enabled: boolean
  is_active: boolean
  status: string
  category?: ItemCategory
  primary_uom?: ItemUom
}

export interface ItemCategory {
  id: number
  name: string
  name_ar: string | null
  code: string
  parent_id: number | null
  children?: ItemCategory[]
}

export interface ItemUom {
  id: number
  name: string
  name_ar: string | null
  abbreviation: string
  type: string
}

export interface Batch {
  id: number
  item_id: number
  batch_number: string
  manufacturing_date: string | null
  expiry_date: string | null
  status: string
}

export interface Stock {
  id: number
  item_id: number
  warehouse_id: number
  batch_id: number | null
  quantity_on_hand: number
  quantity_reserved: number
  quantity_available: number
  quantity_in_transit: number
  quantity_quarantined: number
  item?: Item
  warehouse?: Warehouse
  batch?: Batch
}

export interface Epc {
  id: number
  epc_code: string
  item_id: number
  status: EpcStatus
  last_read_at: string | null
  item?: Item
}

export interface Asset {
  id: number
  asset_code: string
  item_id: number
  name: string
  name_ar: string | null
  status: AssetStatus
  serial_number: string | null
  item?: Item
}

export interface InventoryTransaction {
  id: number
  transaction_number: string
  transaction_type: TransactionType
  transaction_date: string
  item_id: number
  quantity: number
  created_at: string
  item?: Item
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    from: number
    last_page: number
    per_page: number
    to: number
    total: number
  }
  links: {
    first: string
    last: string
    prev: string | null
    next: string | null
  }
}
