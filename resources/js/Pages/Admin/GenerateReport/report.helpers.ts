import { formatDate } from '@/lib/utils';
import jsPDF from 'jspdf';


export function printReport() {
    const reportsTable = document.getElementById("reports");

    if (!reportsTable) {
        console.error("Table element not found");
        return;
    }

    const printWindow = window.open("", "_blank");
    if (!printWindow) {
        console.error("Failed to open print window");
        return;
    }

    printWindow.document.write(`
        <html>
            <head>
                <title>Print Report</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 0; padding: 10px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid black; padding: 8px; text-align: left; }

                    @media print {
                        table { page-break-inside: auto; }
                        tr { page-break-inside: avoid; page-break-after: auto; }
                    }
                </style>
            </head>
            <body>
                ${reportsTable.outerHTML}
            </body>
        </html>
    `);

    //add a delay so that the image will load first before printing
    setTimeout(() => {
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    }, 10)
}

export function downloadReport() {
    const doc = new jsPDF({
        orientation: 'portrait',
        unit: 'mm',
        format: 'a4'
    });
    const reportsTable = document.getElementById('reports');

    if (!reportsTable) {
        console.error("Elements not found");
        return;
    }

    const htmlContent = `
        <!DOCTYPE html>
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 0;
                        padding: 10px;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        page-break-inside: auto !important;
                    }
                    th, td {
                        border: 1px solid black;
                        padding: 8px;
                        text-align: left;
                        font-size: 10px; /* Adjust font size to fit content */
                    }
                    tr {
                        page-break-inside: auto !important;
                        page-break-after: auto !important;
                    }

                    @media print {
                        tr {
                            page-break-inside: avoid !important;
                            page-break-after: auto !important;
                        }
                        td, th {
                            padding: 4px !important;
                            font-size: 8px !important;
                        }
                    }
                </style>
            </head>
            <body>
                ${reportsTable.outerHTML}
            </body>
        </html>
    `;

    doc.html(htmlContent, {
        callback: (file) => {
            file.save(`hrs-report-${formatDate(new Date)}.pdf`);
        },
        margin: [10, 10, 10, 10],
        autoPaging: 'text',
        width: 210,
        windowWidth: 800,
        html2canvas: {
            allowTaint: true,
            letterRendering: true,
            logging: false,
            scale: 0.237, // Adjust the scale to fit content
        },
    });
}

