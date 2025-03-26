import jsPDF from 'jspdf';
import { nextTick } from 'vue';
import html2canvas from 'html2canvas';

export function printReport() {
    const reportsHeading = document.getElementById('reportHeading');
    const reportsTable = document.getElementById("reports");

    if (!reportsTable || !reportsHeading) {
        console.error("Table element not found");
        return;
    }

    reportsHeading.style.display = 'flex';

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
                    @page { margin-top: 0}
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
                ${reportsHeading.outerHTML}
                ${reportsTable.outerHTML}
            </body>
        </html>
    `);


    printWindow.document.close();
    printWindow.focus();
    reportsHeading.style.display = 'none';
    printWindow.print();
    printWindow.close();
}


export async function downloadReport() {
    const reportsTable = document.getElementById("reports");
    if (!reportsTable) {
        console.error("Table element not found");
        return;
    }

    await nextTick();

    // Add a small delay to ensure the table is fully rendered
    setTimeout(async () => {
        try {
            const canvas = await html2canvas(reportsTable, {
                scale: 4, // Increase scale for better quality
            });
            const imgData = canvas.toDataURL("image/png");
            const pdf = new jsPDF("p", "mm", "a4");
            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();
            const margin = 4; // Add padding/margin
            const imgWidth = pageWidth - margin * 2;
            const imgHeight = (canvas.height * imgWidth) / canvas.width;

            let position = margin;
            if (imgHeight > pageHeight - margin * 2) {
                let remainingHeight = imgHeight;
                while (remainingHeight > 0) {
                    pdf.addImage(
                        imgData,
                        "PNG",
                        margin,
                        position,
                        imgWidth,
                        Math.min(imgHeight, pageHeight - margin * 2)
                    );
                    remainingHeight -= pageHeight - margin * 2;
                    position = margin; // Reset position for new page
                    if (remainingHeight > 0) pdf.addPage();
                }
            } else {
                pdf.addImage(
                    imgData,
                    "PNG",
                    margin,
                    position,
                    imgWidth,
                    imgHeight
                );
            }

            pdf.save(`report_${new Date().toISOString()}.pdf`);
        } catch (error) {
            console.error("Failed to generate PDF:", error);
        }
    }, 100);
}
