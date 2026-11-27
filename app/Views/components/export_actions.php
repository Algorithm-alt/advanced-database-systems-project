<?php
use App\Core\View;

$exportFilename = $exportFilename ?? 'table-export.csv';
$exportCsvLabel = $exportCsvLabel ?? 'Export CSV';
$exportPrintClass = $exportPrintClass ?? 'btn-ghost';
?>
<button type="button" class="btn btn-ghost" data-export-csv
        data-export-filename="<?= View::e($exportFilename) ?>"><?= View::e($exportCsvLabel) ?></button>
<button type="button" class="btn <?= View::e($exportPrintClass) ?>" data-print
        title="Open the print dialog; choose Save as PDF to download a PDF">Print / Save as PDF</button>
