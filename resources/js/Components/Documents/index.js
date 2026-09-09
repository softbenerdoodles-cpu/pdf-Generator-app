import DocumentEditor from '../Generator/DocumentEditor';
import InvoiceEditor from './InvoiceEditor';
import QuoteEditor from './QuoteEditor';
import ReceiptEditor from './ReceiptEditor';
import BillingEditor from './BillingEditor';

/** Document types with a hand-arranged editor. The rest use the schema-driven one. */
const EDITORS = {
    invoice: InvoiceEditor,
    quote: QuoteEditor,
    estimate: BillingEditor,
    receipt: ReceiptEditor,
};

export function editorFor(templateKey) {
    return EDITORS[templateKey] ?? DocumentEditor;
}
