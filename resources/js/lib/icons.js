import {
    Award,
    ClipboardList,
    FileCheck2,
    FileSignature,
    FileText,
    LayoutGrid,
    Mail,
    PenLine,
    Receipt,
} from 'lucide-react';

/** Maps the icon name a template declares to its Lucide component. */
const ICONS = {
    'file-text': FileText,
    'file-check': FileCheck2,
    receipt: Receipt,
    'clipboard-list': ClipboardList,
    'file-signature': FileSignature,
    'pen-line': PenLine,
    mail: Mail,
    award: Award,
};

export function iconFor(name) {
    return ICONS[name] ?? LayoutGrid;
}
