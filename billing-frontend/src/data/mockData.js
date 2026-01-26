export const vendors = [
  {
    id: 1,
    name: 'ABC Corp',
    email: 'billing@abccorp.com',
    phone: '+63 912 345 6789',
  },
  {
    id: 2,
    name: 'XYZ Solutions',
    email: 'finance@xyz.com',
    phone: '+63 923 456 7890',
  },
  {
    id: 3,
    name: 'Delta Services',
    email: 'accounts@delta.com',
    phone: '+63 934 567 8901',
  },
]

export const invoices = [
  {
    id: 1,
    number: 'INV-001',
    vendorId: 1,
    amount: 15000,
    status: 'Paid',
    date: '2026-01-10',
  },
  {
    id: 2,
    number: 'INV-002',
    vendorId: 2,
    amount: 8200,
    status: 'Pending',
    date: '2026-01-12',
  },
  {
    id: 3,
    number: 'INV-003',
    vendorId: 3,
    amount: 12350,
    status: 'Overdue',
    date: '2026-01-15',
  },
]
