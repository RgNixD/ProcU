<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start();

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


require_once '../dompdf/autoload.inc.php';
require_once '../PHPSpreadsheet/autoload.php';
require_once 'db_config.php';
require_once 'classes.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');
$options->set('defaultPaperSize', 'A3');
$dompdf = new Dompdf($options);

$db = new db_class();

$operator_ID = $_SESSION['user_id'] ?? null;

$logoPath = '../assets/img/logo/system-logo.png';
$logoData = base64_encode(file_get_contents($logoPath));

function exportPDF($title, $tableContent, $fileName, $orientation = 'portrait')
{
    global $options, $logoData;

    $dompdf = new Dompdf($options);
    ob_start();
    ?>
    <html>

    <head>
        <style>
            body {
                font-family: "DejaVu Sans", Helvetica, Arial, sans-serif;
                font-size: 11px;
            }

            .header {
                text-align: center;
                margin-bottom: 1.5rem;
            }

            table {
                border-collapse: collapse;
                width: 100%;
                table-layout: fixed;
                font-size: 11px;
            }

            th,
            td {
                border: 1px solid #000;
                padding: 4px;
                vertical-align: top;
                overflow: hidden;
                word-wrap: break-word;
            }

            th {
                background-color: #e9ecef;
                font-weight: bold;
            }

            .text-end {
                text-align: right;
            }

            .text-center {
                text-align: center;
            }

            .note {
                margin-top: 5px;
                font-style: italic;
                font-size: 10px;
            }

            .padded-row {
                padding-top: 4px !important;
                padding-bottom: 4px !important;
                height: 1rem;
            }
        </style>
    </head>

    <body>
        <div class="header">
            <img src="data:image/png;base64,<?= $logoData ?>" alt="" width="90" style="margin-bottom: 5px;">
            <h3><?= htmlspecialchars($title) ?></h3>
        </div>

        <div style="overflow-x:auto;">
            <?= $tableContent ?>

            <div class="note">Note: Insert additional rows as necessary</div>

            <div style="text-align: right; margin-top: 0.5rem; line-height: 1.5;">
                <p style="margin-bottom: 0;">
                    Total Allotment Budget: _______________<br>
                    Total Remaining Balance: _______________<br>
                    Total Budget: _______________
                </p>
            </div>

            <div style="margin-top: 4rem; width: 100%; font-size: 10px;">
                <table style="width: 100%; border: none; margin-top: 20px;">
                    <tr style="border: none;">
                        <td style="width: 33%; text-align: left; border: none; padding: 0;">
                            <p style="margin: 0 50px; padding: 0;">Prepared by:</p>
                        </td>

                        <td style="width: 33%; text-align: left; border: none; padding: 0;">
                            <p style="margin: 0 50px; padding: 0;">Recommended by:</p>
                            <p style="margin: 0 50px; padding: 0;">By the Authority of the Bids and Awards Committee</p>
                        </td>

                        <td style="width: 33%; text-align: left; border: none; padding: 0;">
                            <p style="margin: 0 50px; padding: 0;">Approved by:</p>
                        </td>
                    </tr>

                    <tr style="border: none;">
                        <td style="width: 33%; text-align: left; border: none; padding-top: 60px;">
                            <p style="margin: 0 auto; border-bottom: 1px solid #000; width: 80%; display: block;">&nbsp;</p>
                        </td>

                        <td style="width: 33%; text-align: left; border: none; padding-top: 60px;">
                            <p style="margin: 0 auto; border-bottom: 1px solid #000; width: 80%; display: block;">&nbsp;</p>
                        </td>

                        <td style="width: 33%; text-align: left; border: none; padding-top: 60px;">
                            <p style="margin: 0 auto; border-bottom: 1px solid #000; width: 80%; display: block;">&nbsp;</p>
                        </td>
                    </tr>


                    <tr style="border: none;">
                        <td style="width: 33%; text-align: center; border: none; padding: 2px 0 0 0;">
                            <p style="margin: 0;">Signature over Printed Name</p>
                            <p style="margin: 0;">Position/Designation</p>
                            <p style="margin: 0; font-style: italic;">Bids and Awards Committee Secretariat</p>
                            <p style="margin: 5px 0 0 0;">Date: ________________</p>
                        </td>

                        <td style="width: 33%; text-align: center; border: none; padding: 2px 0 0 0;">
                            <p style="margin: 0;">Signature over Printed Name</p>
                            <p style="margin: 0;">Position/Designation</p>
                            <p style="margin: 0; font-style: italic;">Bids and Awards Committee Chairperson</p>
                            <p style="margin: 5px 0 0 0;">Date: ________________</p>
                        </td>

                        <td style="width: 33%; text-align: center; border: none; padding: 2px 0 0 0;">
                            <p style="margin: 0;">Signature over Printed Name</p>
                            <p style="margin: 0;">Position/Designation</p>
                            <p style="margin: 0; font-style: italic;">Head of the Procuring Entity</p>
                            <p style="margin: 5px 0 0 0;">Date: ________________</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </body>

    </html>
    <?php
    $html = ob_get_clean();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', $orientation);
    $dompdf->render();
    $dompdf->stream($fileName . "_" . date("Ymd_His") . ".pdf", ["Attachment" => true]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['export_annual_budget'])) {
    $annualBudgets = $db->getAnnualBudgets();
    if (!$annualBudgets || $annualBudgets->num_rows === 0) {
        header('Location: ../pages/norecordfound.php');
        exit;
    }

    $tableContent = '<table>
        <thead>
            <tr>
                <th>FISCAL YEAR</th>
                <th>ALLOTMENT BUDGET</th>
                <th class="text-end">BALANCE</th>
                <th>SUBMITTED BY</th>
                <th>LAST UPDATED BY</th>
                <th>DATE ADDED</th>
            </tr>
        </thead>
        <tbody>';
    while ($row = $annualBudgets->fetch_assoc()) {
        $tableContent .= '<tr>
            <td>' . htmlspecialchars($row['fiscal_year']) . '</td>
            <td class="text-end">₱' . number_format($row['total_budget_amount'], 2) . '</td>
            <td class="text-end">₱' . number_format($row['remaining_budget_amount'], 2) . '</td>
            <td>' . htmlspecialchars($row['submitted_by_name'] ?: 'N/A') . '</td>
            <td>' . htmlspecialchars($row['updated_by_name'] ?: 'N/A') . '</td>
            <td>' . date('M. d, Y', strtotime($row['date_added'])) . '</td>
        </tr>';
    }
    $tableContent .= '</tbody></table>';
    exportPDF("ANNUAL BUDGET REPORT", $tableContent, "Annual_Budget_Report");
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['export_budget_allocation'])) {
    $office_id = $_POST['office_id'] ?? null;
    $fiscal_year_id = $_POST['fiscal_year_id'] ?? null;

    $allocations = $db->getAllBudgetAllocationsForFiltering($office_id, $fiscal_year_id);
    if (!$allocations || $allocations->num_rows === 0) {
        header('Location: ../pages/norecordfound.php');
        exit;
    }

    $tableContent = '<table>
        <thead>
            <tr>
                <th>OFFICE NAME</th>
                <th>OFFICE HEAD</th>
                <th class="text-end">ALLOTMENT BUDGET</th>
                <th class="text-end">ALLOCATED</th>
                <th class="text-end">BALANCE</th>
            </tr>
        </thead>
        <tbody>';
    while ($row = $allocations->fetch_assoc()) {
        $spent = $row['allocated_amount'] - $row['remaining_amount'];
        $tableContent .= '<tr>
            <td>' . htmlspecialchars($row['office_name']) . '</td>
            <td>' . htmlspecialchars($row['head_name']) . '</td>
            <td class="text-end">₱' . number_format($row['allocated_amount'], 2) . '</td>
            <td class="text-end">₱' . number_format($spent, 2) . '</td>
            <td class="text-end">₱' . number_format($row['remaining_amount'], 2) . '</td>
        </tr>';
    }
    $tableContent .= '</tbody></table>';
    $fyYear = $db->getFiscalYearName($fiscal_year_id);

    exportPDF(
        "BUDGET ALLOCATION REPORT - FY " . htmlspecialchars($fyYear),
        $tableContent,
        "Budget_Allocation_Report_FY_" . $fyYear
    );
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['export_ppmp'])) {
    $office_id = $_POST['office_id'] ?? null;
    $ppmpRecords = $db->getAllPPMPRecordsForProcHeadForExporting(null, $office_id);

    if (!$ppmpRecords || $ppmpRecords->num_rows === 0) {
        header('Location: ../pages/norecordfound.php');
        exit;
    }

    $grandTotal = 0;

    $tableContent = '
        <style>
            .section-header {
                background-color: #dbeaf7;
                font-weight: bold;
            }
            .sub-header {
                background-color: #f1f1f1;
                font-weight: bold;
            }
            .text-center { text-align: center; }
            .text-end { text-align: right; }
            .small-text { font-size: 9px; color: #333; }
        </style>

        <table style="width:100%; border-collapse:collapse; font-size:10px;">
            <thead>
                <tr class="sub-header">
                    <th style="width:18%;">OFFICE NAME</th>
                    <th style="width:15%;">OFFICE HEAD</th>
                    <th style="width:10%;" class="text-center">FISCAL YEAR</th>
                    <th style="width:14%;" class="text-end">TOTAL AMOUNT</th>
                    <th style="width:13%;">STATUS</th>
                    <th style="width:15%;">SUBMITTED DATE</th>
                    <th style="width:15%;">PPMP CODE</th>
                </tr>
            </thead>
            <tbody>
    ';

    while ($ppmp = $ppmpRecords->fetch_assoc()) {
        $fullName = ucwords(trim(($ppmp['first_name'] ?? '') . ' ' . ($ppmp['last_name'] ?? '')));
        $totalAmountValue = (float) ($ppmp['total_amount'] ?? 0);
        $totalAmount = '₱' . number_format($totalAmountValue, 2);

        $submissionDate = !empty($ppmp['submitted_at'])
            ? date('M d, Y', strtotime($ppmp['submitted_at']))
            : (!empty($ppmp['created_at']) ? date('M d, Y', strtotime($ppmp['created_at'])) : '');

        $grandTotal += $totalAmountValue;

        $ppmpItems = $db->getPPMPItemsById($ppmp['ppmp_id']);

        $tableContent .= '
            <tr class="section-header">
                <td>' . htmlspecialchars($ppmp['office_name'] ?? '') . '</td>
                <td>' . htmlspecialchars($fullName ?: 'N/A') . '</td>
                <td class="text-center">' . htmlspecialchars($ppmp['fiscal_year'] ?? '') . '</td>
                <td class="text-end">' . $totalAmount . '</td>
                <td>' . htmlspecialchars($ppmp['status'] ?? '') . '</td>
                <td>' . htmlspecialchars($submissionDate) . '</td>
                <td>' . htmlspecialchars($ppmp['ppmp_code'] ?? '') . '</td>
            </tr>
        ';

        if ($ppmpItems && $ppmpItems->num_rows > 0) {
            $tableContent .= '
                <tr>
                    <td colspan="7" style="padding:8px 10px 12px 10px; background:#fcfcfc;">
                        <table style="width:100%; border-collapse:collapse; font-size:9px; margin-top:2px;">
                            <thead>
                                <tr class="sub-header">
                                    <th style="width:17%;">ITEM / DESCRIPTION</th>
                                    <th style="width:10%;">CATEGORY</th>
                                    <th style="width:10%;">SUB-CATEGORY</th>
                                    <th style="width:6%;" class="text-center">QTY</th>
                                    <th style="width:10%;">MODE</th>
                                    <th style="width:8%;" class="text-center">PRE-PROC</th>
                                    <th style="width:8%;" class="text-end">UNIT COST</th>
                                    <th style="width:9%;" class="text-end">TOTAL COST</th>
                                    <th style="width:9%;">SOURCE</th>
                                    <th style="width:7%;" class="text-center">START</th>
                                    <th style="width:7%;" class="text-center">END</th>
                                    <th style="width:7%;" class="text-center">DELIVERY</th>
                                    <th style="width:10%;">REMARKS</th>
                                </tr>
                            </thead>
                            <tbody>
            ';

            $officeSubtotal = 0;

            while ($item = $ppmpItems->fetch_assoc()) {
                $unitCost = (float) ($item['estimated_budget'] ?? 0);
                $itemTotal = (float) ($item['total_cost'] ?? 0);
                $officeSubtotal += $itemTotal;

                $itemDesc = '<strong>' . htmlspecialchars($item['item_description'] ?? '') . '</strong>';

                if (!empty($item['specifications'])) {
                    $itemDesc .= '<br><span class="small-text">Specs: ' . htmlspecialchars($item['specifications']) . '</span>';
                }

                $tableContent .= '
                    <tr>
                        <td>' . $itemDesc . '</td>
                        <td>' . htmlspecialchars($item['category_name'] ?? '') . '</td>
                        <td>' . htmlspecialchars($item['sub_cat_name'] ?? '') . '</td>
                        <td class="text-center">' . htmlspecialchars($item['quantity'] ?? '') . '</td>
                        <td>' . htmlspecialchars($item['mode_of_procurement'] ?? '') . '</td>
                        <td class="text-center">' . htmlspecialchars($item['pre_procurement_conference'] ?? '') . '</td>
                        <td class="text-end">₱' . number_format($unitCost, 2) . '</td>
                        <td class="text-end">₱' . number_format($itemTotal, 2) . '</td>
                        <td>' . htmlspecialchars($item['source_of_funds'] ?? '') . '</td>
                        <td class="text-center">' . htmlspecialchars($item['procurement_start_date'] ?? '') . '</td>
                        <td class="text-center">' . htmlspecialchars($item['bidding_date'] ?? '') . '</td>
                        <td class="text-center">' . htmlspecialchars($item['contract_signing_date'] ?? '') . '</td>
                        <td>' . nl2br(htmlspecialchars($item['remarks'] ?? '')) . '</td>
                    </tr>
                ';
            }

            $tableContent .= '
                            </tbody>
                            <tfoot>
                                <tr style="background-color:#f7f7f7; font-weight:bold;">
                                    <td colspan="7" class="text-end">OFFICE PPMP SUBTOTAL</td>
                                    <td class="text-end">₱' . number_format($officeSubtotal, 2) . '</td>
                                    <td colspan="5"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </td>
                </tr>
            ';
        }
    }

    $tableContent .= '
            </tbody>
            <tfoot>
                <tr style="background-color:#ddebf7; font-weight:bold;">
                    <td colspan="3" class="text-end">GRAND TOTAL</td>
                    <td class="text-end">₱' . number_format($grandTotal, 2) . '</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
    ';

    exportPDF("PPMP REPORT", $tableContent, "PPMP_Report", "landscape");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['export_app_pdf'])) {
    $fiscal_year_id = $_POST['fiscal_year_id'] ?? null;
    $finalizedApps = $db->getFinalizedApps($fiscal_year_id);

    if (!$finalizedApps || $finalizedApps->num_rows === 0) {
        header('Location: ../pages/norecordfound.php');
        exit;
    }

    $fyName = $db->getFiscalYearName($fiscal_year_id);

    $mainItems = [];
    $directItems = [];
    $cseItems = [];

    while ($app = $finalizedApps->fetch_assoc()) {
        $mode = strtolower(trim($app['proc_mode_name'] ?? ''));

        if ($mode === 'direct acquisition') {
            $directItems[] = $app;
        } elseif ($mode === 'common use supplies and equipment') {
            $cseItems[] = $app;
        } else {
            $mainItems[] = $app;
        }
    }

    $conn = $db->getConnection();

    $versionStmt = $conn->prepare("
        SELECT version_no, status
        FROM app_versions
        WHERE fiscal_year_id = ?
          AND status = 'Finalized'
        ORDER BY version_no DESC
        LIMIT 1
    ");

    $versionStmt->bind_param("i", $fiscal_year_id);
    $versionStmt->execute();

    $appVersionInfo = $versionStmt->get_result()->fetch_assoc();
    $versionStmt->close();

    $currentVersionNo = (int) ($appVersionInfo['version_no'] ?? 1);

    $isFinal = ($currentVersionNo === 1);
    $isUpdated = ($currentVersionNo > 1);

    function renderExportAppRow($app, $db)
    {
        $officeNames = $db->getOfficeNamesByIds($app['offices_involved'] ?? '');

        $officeNamesFormatted = htmlspecialchars(
            implode(', ', array_filter(array_map('trim', explode(',', $officeNames))))
        );

        $budget = '&#8369;' . number_format((float) ($app['total_cost'] ?? 0), 2);

        $generalDescription = '';

        if (!empty($app['sub_cat_name'])) {
            $generalDescription .= '<div style="font-weight:600; margin-bottom:4px;">' . htmlspecialchars($app['sub_cat_name']) . '</div>';
        }

        if (!empty($app['item_name'])) {
            $generalDescription .= '<div>' . htmlspecialchars($app['item_name']) . '</div>';
        }

        if (!empty($app['item_description'])) {
            $generalDescription .= '<div style="margin-top:4px; font-size:10px;">' . nl2br(htmlspecialchars($app['item_description'])) . '</div>';
        }

        return '
            <tr>
                <td>' . htmlspecialchars($app['category_name'] ?? '') . '</td>
                <td>' . $officeNamesFormatted . '</td>
                <td>' . $generalDescription . '</td>
                <td>' . htmlspecialchars($app['proc_mode_name'] ?? '') . '</td>
                <td class="text-center">' . htmlspecialchars($app['pre_procurement_conference'] ?? '') . '</td>
                <td>' . htmlspecialchars($app['bid_cat_name'] ?? '') . '</td>
                <td class="text-center">' . htmlspecialchars($app['procurement_start_date'] ?? '') . '</td>
                <td class="text-center">' . htmlspecialchars($app['bidding_date'] ?? '') . '</td>
                <td>' . htmlspecialchars($app['source_of_funds'] ?? '') . '</td>
                <td class="text-end">' . $budget . '</td>
                <td>' . htmlspecialchars($app['proc_strat_name'] ?? '') . '</td>
                <td>' . nl2br(htmlspecialchars($app['remarks'] ?? '')) . '</td>
            </tr>
        ';
    }

    function renderExportBlankRows($rows = 5, $cols = 12)
    {
        $html = '';

        for ($i = 0; $i < $rows; $i++) {
            $html .= '<tr>';

            for ($j = 0; $j < $cols; $j++) {
                $html .= '<td class="padded-row"></td>';
            }

            $html .= '</tr>';
        }

        return $html;
    }

    $mainTotal = 0;
    $directTotal = 0;
    $cseTotal = 0;
    $epaTotal = 0;

    $dompdf = new Dompdf();
    ob_start();
    ?>
    <html>

    <head>
        <meta charset="UTF-8">
        <style>
            body {
                font-family: "DejaVu Sans", Helvetica, Arial, sans-serif;
                font-size: 9px;
            }

            .header {
                text-align: center;
                margin-bottom: 12px;
                margin-bottom: 15px;
            }

            .status-indicators {
                text-align: center;
                margin-bottom: 12px;
            }

            .indicator {
                display: inline-block;
                margin: 0 10px;
                padding: 6px 14px;
                border: 1px solid #000;
                font-weight: bold;
            }

            .indicative {
                background-color: #e9ecef;
            }

            .final {
                background-color: #28a745;
                color: #fff;
            }

            .updated {
                background-color: #ffc107;
            }

            table {
                border-collapse: collapse;
                width: 100%;
                table-layout: fixed;
                font-size: 8.5px;
            }

            th,
            td {
                border: 1px solid #000;
                padding: 4px;
                overflow: hidden;
                word-wrap: break-word;
                vertical-align: top;
            }

            th {
                text-align: center;
            }

            .table-secondary {
                background-color: #e9ecef;
                font-weight: bold;
            }

            .padded-row {
                height: 18px;
            }

            .text-center {
                text-align: center;
            }

            .text-start {
                text-align: left;
            }

            .text-end {
                text-align: right;
            }

            .note {
                margin-top: 5px;
                font-style: italic;
                font-size: 8px;
            }
        </style>
    </head>

    <body>
        <div class="header">
            <?php
            $leftLogoPath = '../assets/img/logo/system-logo.png';
            $rightLogoPath = '../assets/img/logo/Bagong_Pilipinas_logo.png';

            $leftLogoData = base64_encode(file_get_contents($leftLogoPath));
            $rightLogoData = base64_encode(file_get_contents($rightLogoPath));
            ?>

            <div style="position:relative; width:100%; height:115px; text-align:center;">
                <img src="data:image/png;base64,<?= $leftLogoData ?>"
                    style="position:absolute; left:400px; top:0px; width:65px;">

                <img src="data:image/png;base64,<?= $rightLogoData ?>"
                    style="position:absolute; right:400px; top:0px; width:65px;">

                <div style="font-size:16px; font-weight:bold; padding-top:8px;">
                    TECHNOLOGICAL UNIVERSITY OF THE PHILIPPINES
                </div>

                <div style="font-size:10px; margin-top:3px;">
                    Ayala Blvd., Ermita, Manila
                </div>

                <div style="margin-top:26px; font-size:22px; font-weight:bold;">
                    ANNUAL PROCUREMENT PLAN FOR FY <?= htmlspecialchars($fyName); ?>
                </div>
            </div>
        </div>

        <div class="status-indicators">
            <div class="indicator indicative">INDICATIVE</div>
            <div class="indicator <?= $isFinal ? 'final' : '' ?>">FINAL</div>
            <div class="indicator <?= $isUpdated ? 'updated' : '' ?>">
                UPDATED<?= $isUpdated ? ' [Version No. ' . $currentVersionNo . ']' : '' ?>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th rowspan="2" class="table-secondary" style="width: 10%;">Project Title</th>
                    <th colspan="5" class="table-secondary">PROCUREMENT PROJECT DETAILS</th>
                    <th colspan="2" class="table-secondary">PROJECTED TIMELINE (MM/YYYY)</th>
                    <th colspan="2" class="table-secondary">FUNDING DETAILS</th>
                    <th rowspan="2" class="table-secondary" style="width: 10%;">PROCUREMENT STRATEGY OR TOOLS</th>
                    <th rowspan="2" class="table-secondary" style="width: 10%;">REMARKS<br>(Other relevant descriptions of
                        the procurement project, if applicable)</th>
                </tr>
                <tr>
                    <th class="table-secondary">End-User or Implementing Unit</th>
                    <th class="table-secondary">General Description of the Project</th>
                    <th class="table-secondary">Mode of Procurement</th>
                    <th class="table-secondary">To be covered by an Early Procurement Activity</th>
                    <th class="table-secondary">Criteria for Bid Evaluation</th>
                    <th class="table-secondary">Start of Procurement Activity</th>
                    <th class="table-secondary">End of Procurement Activity</th>
                    <th class="table-secondary">Source of Fund</th>
                    <th class="table-secondary">Estimated Budget / Approved Budget for the Contract (PhP)</th>
                </tr>

                <tr>
                    <th class="table-secondary">Column 1</th>
                    <th class="table-secondary">Column 2</th>
                    <th class="table-secondary">Column 3</th>
                    <th class="table-secondary">Column 4</th>
                    <th class="table-secondary">Column 5</th>
                    <th class="table-secondary">Column 6</th>
                    <th class="table-secondary">Column 7</th>
                    <th class="table-secondary">Column 8</th>
                    <th class="table-secondary">Column 9</th>
                    <th class="table-secondary">Column 10</th>
                    <th class="table-secondary">Column 11</th>
                    <th class="table-secondary">Column 12</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($mainItems as $app): ?>
                    <?php
                    $mainTotal += (float) ($app['total_cost'] ?? 0);
                    if (strtolower(trim($app['pre_procurement_conference'] ?? '')) === 'yes') {
                        $epaTotal += (float) ($app['total_cost'] ?? 0);
                    }
                    echo renderExportAppRow($app, $db);
                    ?>
                <?php endforeach; ?>

                <tr>
                    <th colspan="12" class="table-secondary text-start">
                        Miscellaneous Items (For Direct Acquisition only) Sec 32.2 of RA 12009
                    </th>
                </tr>

                <?php if (!empty($directItems)): ?>
                    <?php foreach ($directItems as $app): ?>
                        <?php
                        $directTotal += (float) ($app['total_cost'] ?? 0);
                        if (strtolower(trim($app['pre_procurement_conference'] ?? '')) === 'yes') {
                            $epaTotal += (float) ($app['total_cost'] ?? 0);
                        }
                        echo renderExportAppRow($app, $db);
                        ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?= renderExportBlankRows(); ?>
                <?php endif; ?>

                <tr>
                    <th colspan="12" class="table-secondary text-start">
                        Common Use Supplies and Equipment (CSE) to be purchased from PS-DBM
                        (Kindly indicate the summary/total amounts only)
                    </th>
                </tr>

                <?php if (!empty($cseItems)): ?>
                    <?php foreach ($cseItems as $app): ?>
                        <?php
                        $cseTotal += (float) ($app['total_cost'] ?? 0);
                        if (strtolower(trim($app['pre_procurement_conference'] ?? '')) === 'yes') {
                            $epaTotal += (float) ($app['total_cost'] ?? 0);
                        }
                        echo renderExportAppRow($app, $db);
                        ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?= renderExportBlankRows(); ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="note">Note: Insert additional rows as necessary</div>

        <div style="text-align: right; margin-top: 0.5rem; line-height: 1.5;">
            <p style="margin-bottom: 0;">
                Total Amount of Estimated Budget for EPA Projects: &#8369;<?= number_format($epaTotal, 2); ?><br>
                Total Amount of CSEs to be purchased from PS-DBM: &#8369;<?= number_format($cseTotal, 2); ?><br>
                Total Amount of Estimated Budget: &#8369;<?= number_format($mainTotal + $directTotal + $cseTotal, 2); ?>
            </p>
        </div>

        <div style="margin-top: 3rem; width: 100%; font-size: 9px;">
            <table style="width: 100%; border: none; margin-top: 20px;">
                <tr style="border: none;">
                    <td style="width: 33%; text-align: left; border: none;">
                        <p>Prepared by:</p>
                    </td>
                    <td style="width: 33%; text-align: left; border: none;">
                        <p>Recommended by:</p>
                        <p>By the Authority of the Bids and Awards Committee</p>
                    </td>
                    <td style="width: 33%; text-align: left; border: none;">
                        <p>Approved by:</p>
                    </td>
                </tr>

                <tr style="border: none;">
                    <td style="padding-top:60px;border:none;">
                        <div style="border-bottom:1px solid #000;"></div>
                    </td>
                    <td style="padding-top:60px;border:none;">
                        <div style="border-bottom:1px solid #000;"></div>
                    </td>
                    <td style="padding-top:60px;border:none;">
                        <div style="border-bottom:1px solid #000;"></div>
                    </td>
                </tr>

                <tr style="border:none;">
                    <td style="text-align:center;border:none;">
                        Signature over Printed Name<br>
                        Position/Designation<br>
                        <i>Bids and Awards Committee Secretariat</i><br>
                        Date: ________________
                    </td>
                    <td style="text-align:center;border:none;">
                        Signature over Printed Name<br>
                        Position/Designation<br>
                        <i>Bids and Awards Committee Chairperson</i><br>
                        Date: ________________
                    </td>
                    <td style="text-align:center;border:none;">
                        Signature over Printed Name<br>
                        Position/Designation<br>
                        <i>Head of the Procuring Entity</i><br>
                        Date: ________________
                    </td>
                </tr>
            </table>
        </div>
    </body>

    </html>
    <?php

    $html = ob_get_clean();

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A3', 'landscape');
    $dompdf->render();
    $dompdf->stream("APP_Report_" . date("Ymd_His") . ".pdf", ["Attachment" => true]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['export_app_excel'])) {
    $fiscal_year_id = $_POST['fiscal_year_id'] ?? null;
    $finalizedApps = $db->getFinalizedApps($fiscal_year_id);

    if (!$finalizedApps || $finalizedApps->num_rows === 0) {
        header('Location: ../pages/norecordfound.php');
        exit;
    }

    $fyName = $db->getFiscalYearName($fiscal_year_id);

    $mainItems = [];
    $directItems = [];
    $cseItems = [];

    while ($app = $finalizedApps->fetch_assoc()) {
        $mode = strtolower(trim($app['proc_mode_name'] ?? ''));

        if ($mode === 'direct acquisition') {
            $directItems[] = $app;
        } elseif ($mode === 'common use supplies and equipment') {
            $cseItems[] = $app;
        } else {
            $mainItems[] = $app;
        }
    }

    $conn = $db->getConnection();

    $versionStmt = $conn->prepare("
        SELECT version_no, status
        FROM app_versions
        WHERE fiscal_year_id = ?
          AND status = 'Finalized'
        ORDER BY version_no DESC
        LIMIT 1
    ");

    $versionStmt->bind_param("i", $fiscal_year_id);
    $versionStmt->execute();
    $appVersionInfo = $versionStmt->get_result()->fetch_assoc();
    $versionStmt->close();

    $currentVersionNo = (int) ($appVersionInfo['version_no'] ?? 1);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('APP');


    $leftLogoPath = realpath(__DIR__ . '/../assets/img/logo/system-logo.png');
    $rightLogoPath = realpath(__DIR__ . '/../assets/img/logo/Bagong_Pilipinas_logo.png');

    if ($leftLogoPath && file_exists($leftLogoPath)) {
        $drawingLeft = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawingLeft->setName('System Logo');
        $drawingLeft->setDescription('System Logo');
        $drawingLeft->setPath($leftLogoPath);
        $drawingLeft->setHeight(60);
        $drawingLeft->setCoordinates('D1');
        $drawingLeft->setOffsetX(5);
        $drawingLeft->setOffsetY(5);
        $drawingLeft->setWorksheet($sheet);
    }

    if ($rightLogoPath && file_exists($rightLogoPath)) {
        $drawingRight = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawingRight->setName('Bagong Pilipinas Logo');
        $drawingRight->setDescription('Bagong Pilipinas Logo');
        $drawingRight->setPath($rightLogoPath);
        $drawingRight->setHeight(60);
        $drawingRight->setCoordinates('I1');
        $drawingRight->setOffsetX(5);
        $drawingRight->setOffsetY(5);
        $drawingRight->setWorksheet($sheet);
    }


    $sheet->mergeCells('A1:L1');
    $sheet->setCellValue('A1', 'TECHNOLOGICAL UNIVERSITY OF THE PHILIPPINES');

    $sheet->mergeCells('A2:L2');
    $sheet->setCellValue('A2', 'Ayala Blvd., Ermita, Manila');

    $sheet->mergeCells('A3:L3');
    $sheet->setCellValue('A3', 'ANNUAL PROCUREMENT PLAN FOR F.Y. ' . $fyName);

    $sheet->getRowDimension(1)->setRowHeight(45);
    $sheet->getRowDimension(2)->setRowHeight(20);
    $sheet->getRowDimension(3)->setRowHeight(24);
    $sheet->getRowDimension(4)->setRowHeight(8);

    $sheet->getStyle('A1:L3')->getAlignment()
        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(9);
    $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14);


    $sheet->mergeCells('C5:D5');
    $sheet->mergeCells('F5:G5');
    $sheet->mergeCells('I5:J5');

    $sheet->setCellValue('C5', 'INDICATIVE');

    $sheet->setCellValue('F5', 'FINAL');

    $sheet->setCellValue(
        'I5',
        'UPDATED' . ($currentVersionNo > 1
            ? ' [Version No. ' . $currentVersionNo . ']'
            : '')
    );
    $sheet->getRowDimension(5)->setRowHeight(28);
    $sheet->getRowDimension(5)->setRowHeight(24);

    $sheet->getStyle('C5:J5')->getFont()
        ->setBold(true)
        ->setSize(11);

    $sheet->getStyle('C5:J5')->getAlignment()
        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

    foreach (['C5:D5', 'F5:G5', 'I5:J5'] as $range) {
        $sheet->getStyle($range)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $sheet->getStyle($range)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE9ECEF');
    }

    if ($currentVersionNo > 1) {
        $sheet->getStyle('I5:J5')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFC107');
    } else {
        $sheet->getStyle('F5:G5')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF28A745');

        $sheet->getStyle('F5:G5')->getFont()

            ->getColor()->setARGB('FFFFFFFF');
    }


    $sheet->mergeCells('A7:A9');
    $sheet->mergeCells('B7:F9');
    $sheet->mergeCells('G7:H9');
    $sheet->mergeCells('I7:J9');
    $sheet->mergeCells('K7:K9');
    $sheet->mergeCells('L7:L9');

    $sheet->setCellValue('A7', 'Project Title');
    $sheet->setCellValue('B7', 'PROCUREMENT PROJECT DETAILS');
    $sheet->setCellValue('G7', 'PROJECTED TIMELINE (MM/YYYY)');
    $sheet->setCellValue('I7', 'FUNDING DETAILS');
    $sheet->setCellValue('K7', 'PROCUREMENT STRATEGY OR TOOLS');
    $sheet->setCellValue('L7', 'REMARKS');

    $sheet->fromArray([
        'Column 1',
        'Column 2',
        'Column 3',
        'Column 4',
        'Column 5',
        'Column 6',
        'Column 7',
        'Column 8',
        'Column 9',
        'Column 10',
        'Column 11',
        'Column 12'
    ], null, 'A10');

    $sheet->getStyle("A7:L10")->getFont()->setBold(true);
    $sheet->getStyle("A7:L10")->getAlignment()
        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
        ->setWrapText(true);

    $row = 11;

    $writeAppRow = function ($app) use (&$sheet, &$row, $db) {
        $officeNames = $db->getOfficeNamesByIds($app['offices_involved'] ?? '');

        $description = trim(
            ($app['sub_cat_name'] ?? '') . "\n" .
            ($app['item_name'] ?? '') . "\n" .
            ($app['item_description'] ?? '')
        );

        $sheet->fromArray([
            $app['category_name'] ?? '',
            $officeNames,
            $description,
            $app['proc_mode_name'] ?? '',
            $app['pre_procurement_conference'] ?? '',
            $app['bid_cat_name'] ?? '',
            $app['procurement_start_date'] ?? '',
            $app['bidding_date'] ?? '',
            $app['source_of_funds'] ?? '',
            (float) ($app['total_cost'] ?? 0),
            $app['proc_strat_name'] ?? '',
            $app['remarks'] ?? '',
        ], null, "A{$row}");

        $sheet->getStyle("J{$row}")
            ->getNumberFormat()
            ->setFormatCode('"₱"#,##0.00');

        $row++;
    };

    $mainTotal = 0;
    $directTotal = 0;
    $cseTotal = 0;
    $epaTotal = 0;

    foreach ($mainItems as $app) {
        $mainTotal += (float) ($app['total_cost'] ?? 0);

        if (strtolower(trim($app['pre_procurement_conference'] ?? '')) === 'yes') {
            $epaTotal += (float) ($app['total_cost'] ?? 0);
        }
        $writeAppRow($app);
    }

    $sheet->mergeCells("A{$row}:L{$row}");
    $sheet->setCellValue("A{$row}", 'Miscellaneous Items (For Direct Acquisition only) Sec 32.2 of RA 12009');
    $sheet->getStyle("A{$row}:L{$row}")->getFont()->setBold(true);
    $sheet->getStyle("A{$row}:L{$row}")->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFE9ECEF');
    $row++;

    if (!empty($directItems)) {
        foreach ($directItems as $app) {
            $directTotal += (float) ($app['total_cost'] ?? 0);
            if (strtolower(trim($app['pre_procurement_conference'] ?? '')) === 'yes') {
                $epaTotal += (float) ($app['total_cost'] ?? 0);
            }
            $writeAppRow($app);
        }
    } else {
        for ($i = 0; $i < 5; $i++) {
            $row++;
        }
    }

    $sheet->mergeCells("A{$row}:L{$row}");
    $sheet->setCellValue("A{$row}", 'Common Use Supplies and Equipment (CSE) to be purchased from PS-DBM (Kindly indicate the summary/total amounts only)');
    $sheet->getStyle("A{$row}:L{$row}")->getFont()->setBold(true);
    $sheet->getStyle("A{$row}:L{$row}")->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFE9ECEF');
    $row++;

    if (!empty($cseItems)) {
        foreach ($cseItems as $app) {
            $cseTotal += (float) ($app['total_cost'] ?? 0);
            if (strtolower(trim($app['pre_procurement_conference'] ?? '')) === 'yes') {
                $epaTotal += (float) ($app['total_cost'] ?? 0);
            }
            $writeAppRow($app);
        }
    } else {
        for ($i = 0; $i < 5; $i++) {
            $row++;
        }
    }

    $tableEndRow = $row - 1;

    /*
    |--------------------------------------------------------------------------
    | SUMMARY
    |--------------------------------------------------------------------------
    */
    $row++;
    $noteRow = $row;

    $sheet->mergeCells("A{$row}:F{$row}");
    $sheet->setCellValue("A{$row}", 'Note: Insert additional rows as necessary');
    $sheet->getStyle("A{$row}")->getFont()->setItalic(true)->setSize(9);

    $sheet->mergeCells("I{$row}:K{$row}");
    $sheet->setCellValue("I{$row}", 'Total Amount of Estimated Budget for EPA Projects:');
    $sheet->setCellValue("L{$row}", $epaTotal);
    $sheet->getStyle("L{$row}")->getNumberFormat()->setFormatCode('"₱"#,##0.00');
    $row++;

    $sheet->mergeCells("I{$row}:K{$row}");
    $sheet->setCellValue("I{$row}", 'Total Amount of CSEs to be purchased from PS-DBM:');
    $sheet->setCellValue("L{$row}", $cseTotal);
    $sheet->getStyle("L{$row}")->getNumberFormat()->setFormatCode('"₱"#,##0.00');

    $row++;

    $sheet->mergeCells("I{$row}:K{$row}");
    $sheet->setCellValue("I{$row}", 'Total Amount of Estimated Budget:');
    $sheet->setCellValue("L{$row}", $mainTotal + $directTotal + $cseTotal);
    $sheet->getStyle("L{$row}")->getNumberFormat()->setFormatCode('"₱"#,##0.00');

    $summaryEndRow = $row;

    $sheet->getStyle("I{$noteRow}:L{$summaryEndRow}")->getFont()->setBold(true);
    $sheet->getStyle("I{$noteRow}:L{$summaryEndRow}")->getAlignment()
        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);


    $row += 5;
    $preparedRow = $row;

    $sheet->mergeCells("A{$row}:C{$row}");
    $sheet->setCellValue("A{$row}", 'Prepared by:');

    $sheet->mergeCells("E{$row}:G{$row}");
    $sheet->setCellValue("E{$row}", 'Recommended by:');

    $sheet->mergeCells("I{$row}:K{$row}");
    $sheet->setCellValue("I{$row}", 'Approved by:');

    $row++;

    $sheet->mergeCells("E{$row}:G{$row}");
    $sheet->setCellValue("E{$row}", 'By the Authority of the Bids and Awards Committee');

    $row += 4;
    $lineRow = $row;

    foreach (['A' => 'C', 'E' => 'G', 'I' => 'K'] as $start => $end) {
        $sheet->mergeCells("{$start}{$lineRow}:{$end}{$lineRow}");
        $sheet->getStyle("{$start}{$lineRow}:{$end}{$lineRow}")
            ->getBorders()->getBottom()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    }

    $row++;

    foreach ([
        'Signature over Printed Name',
        'Position/Designation',
        'Bids and Awards Committee Secretariat',
        'Date: ________________'
    ] as $text) {
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", $text);

        if ($text === 'Bids and Awards Committee Secretariat') {
            $sheet->getStyle("A{$row}")->getFont()->setItalic(true);
        }

        $row++;
    }

    $row = $lineRow + 1;

    foreach ([
        'Signature over Printed Name',
        'Position/Designation',
        'Bids and Awards Committee Chairperson',
        'Date: ________________'
    ] as $text) {
        $sheet->mergeCells("E{$row}:G{$row}");
        $sheet->setCellValue("E{$row}", $text);

        if ($text === 'Bids and Awards Committee Chairperson') {
            $sheet->getStyle("E{$row}")->getFont()->setItalic(true);
        }

        $row++;
    }

    $row = $lineRow + 1;

    foreach ([
        'Signature over Printed Name',
        'Position/Designation',
        'Head of the Procuring Entity',
        'Date: ________________'
    ] as $text) {
        $sheet->mergeCells("I{$row}:K{$row}");
        $sheet->setCellValue("I{$row}", $text);

        if ($text === 'Head of the Procuring Entity') {
            $sheet->getStyle("I{$row}")->getFont()->setItalic(true);
        }

        $row++;
    }

    $lastRow = $row;


    $sheet->getStyle("A7:L{$tableEndRow}")
        ->getBorders()
        ->getAllBorders()
        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    $sheet->getStyle("I{$noteRow}:L{$summaryEndRow}")
        ->getBorders()
        ->getAllBorders()
        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);

    $sheet->getStyle("A11:L{$tableEndRow}")
        ->getAlignment()
        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP)
        ->setWrapText(true);

    $sheet->getStyle("A{$preparedRow}:K{$lastRow}")
        ->getAlignment()
        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

    foreach (range('A', 'L') as $col) {
        $sheet->getColumnDimension($col)->setWidth(18);
    }

    $sheet->getColumnDimension('C')->setWidth(35);
    $sheet->getColumnDimension('K')->setWidth(35);
    $sheet->getColumnDimension('L')->setWidth(30);

    $sheet->getPageSetup()
        ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
        ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A3)
        ->setFitToWidth(1)
        ->setFitToHeight(0);

    $sheet->getPageMargins()->setTop(0.25);
    $sheet->getPageMargins()->setRight(0.25);
    $sheet->getPageMargins()->setLeft(0.25);
    $sheet->getPageMargins()->setBottom(0.25);

    $fileName = 'APP_Report_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $fyName) . '_' . date('Ymd_His') . '.xlsx';

    if (ob_get_length()) {
        ob_end_clean();
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"{$fileName}\"");
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_app_versions'])) {

    $fiscal_year_id = $_POST['fiscal_year_id'] ?? null;
    $versions = $db->getAppVersionsByFiscalYear($fiscal_year_id);

    if (!$versions || $versions->num_rows === 0) {
        header('Location: ../pages/norecordfound.php');
        exit;
    }
    $fyName = $db->getFiscalYearName($fiscal_year_id);
    ob_start();
    ?>

    <html>

    <head>
        <meta charset="UTF-8">
        <style>
            body {
                font-family: "DejaVu Sans", Helvetica, Arial, sans-serif;
                font-size: 10px;
                color: #222;
                margin: 18px;
            }

            .header {
                text-align: center;
                margin-bottom: 14px;
            }

            .header h2 {
                margin: 4px 0 2px 0;
                font-size: 18px;
                letter-spacing: .6px;
            }

            .subtitle {
                font-size: 12px;
                color: #444;
            }

            .version-card {
                border: 1px solid #111;
                margin-bottom: 18px;
                page-break-inside: avoid;
            }

            .version-title {
                background: #dbeaf7;
                border-bottom: 1px solid #111;
                padding: 7px 9px;
                font-weight: bold;
                font-size: 12px;
            }

            .meta {
                padding: 6px 9px;
                background: #fafafa;
                border-bottom: 1px solid #bbb;
                line-height: 1.5;
            }

            .meta span {
                display: inline-block;
                margin-right: 22px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
            }

            th,
            td {
                border: 1px solid #111;
                padding: 5px;
                vertical-align: top;
                word-wrap: break-word;
            }

            th {
                background: #f1f1f1;
                font-weight: bold;
                text-align: center;
                text-transform: uppercase;
                font-size: 9px;
            }

            .text-center {
                text-align: center;
            }

            .text-end {
                text-align: right;
            }

            .fw-bold {
                font-weight: bold;
            }

            .muted {
                color: #555;
                font-size: 9px;
            }

            .summary {
                background: #eef5fb;
                border-top: 1px solid #111;
                padding: 7px 9px;
                text-align: right;
                font-weight: bold;
                font-size: 11px;
            }

            .summary span {
                margin-left: 25px;
            }

            .page-break {
                page-break-after: auto;
            }
        </style>
    </head>

    <body>

        <div class="header">
            <?php
            $logoPath = '../assets/img/logo/system-logo.png';
            $logoData = base64_encode(file_get_contents($logoPath));
            ?>
            <img src="data:image/png;base64,<?= $logoData ?>" width="75">
            <h2>APP VERSION HISTORY REPORT</h2>
            <div class="subtitle">Fiscal Year <?= htmlspecialchars($fyName); ?></div>
        </div>

        <?php while ($version = $versions->fetch_assoc()): ?>
            <?php
            $versionNo = (int) ($version['version_no'] ?? 0);
            $referenceCode = 'APP-' . $fyName . '-V' . str_pad($versionNo, 2, '0', STR_PAD_LEFT);

            $items = $db->getAppVersionItems($version['app_version_id']);
            $totalBudget = 0;
            $totalItems = 0;
            ?>

            <div class="version-card">

                <div class="version-title">
                    Version <?= $versionNo; ?> |
                    <?= htmlspecialchars($referenceCode); ?> |
                    <?= htmlspecialchars($version['status'] ?? '-'); ?>
                </div>

                <div class="meta">
                    <span><strong>Created:</strong>
                        <?= !empty($version['created_at']) ? date('M d, Y h:i A', strtotime($version['created_at'])) : '-'; ?>
                    </span>

                    <span><strong>Finalized:</strong>
                        <?= !empty($version['finalized_at']) ? date('M d, Y h:i A', strtotime($version['finalized_at'])) : '-'; ?>
                    </span>

                    <span><strong>Notes:</strong>
                        <?= htmlspecialchars($version['notes'] ?? '-'); ?>
                    </span>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th style="width:12%;">Project Title</th>
                            <th style="width:20%;">General Description</th>
                            <th style="width:13%;">Offices</th>
                            <th style="width:10%;">Mode</th>
                            <th style="width:9%;">EPA</th>
                            <th style="width:11%;">Timeline</th>
                            <th style="width:10%;">Source</th>
                            <th style="width:7%;">Qty</th>
                            <th style="width:10%;">Budget</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($items && $items->num_rows > 0): ?>
                            <?php while ($item = $items->fetch_assoc()): ?>
                                <?php
                                $officeNames = $db->getOfficeNamesByIds($item['offices_involved']);

                                $timeline = '-';
                                if (!empty($item['procurement_start_date']) || !empty($item['bidding_date'])) {
                                    $timeline = htmlspecialchars($item['procurement_start_date'] ?? '-') .
                                        ' - ' .
                                        htmlspecialchars($item['bidding_date'] ?? '-');
                                }

                                $totalBudget += (float) ($item['total_cost'] ?? 0);
                                $totalItems++;
                                ?>

                                <tr>
                                    <td class="fw-bold">
                                        <?= htmlspecialchars($item['category_name'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($item['sub_cat_name'] ?? '-'); ?></div>
                                        <div><?= htmlspecialchars($item['item_name'] ?? '-'); ?></div>

                                        <?php if (!empty($item['item_description'])): ?>
                                            <div class="muted"><?= htmlspecialchars($item['item_description']); ?></div>
                                        <?php endif; ?>
                                    </td>

                                    <td><?= htmlspecialchars($officeNames ?: '-'); ?></td>
                                    <td><?= htmlspecialchars($item['proc_mode_name'] ?? '-'); ?></td>
                                    <td class="text-center"><?= htmlspecialchars($item['pre_procurement_conference'] ?? '-'); ?></td>
                                    <td class="text-center"><?= $timeline; ?></td>
                                    <td><?= htmlspecialchars($item['source_of_funds'] ?? '-'); ?></td>
                                    <td class="text-center"><?= number_format((float) ($item['total_quantity'] ?? 0)); ?></td>
                                    <td class="text-end">₱<?= number_format((float) ($item['total_cost'] ?? 0), 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">No APP items found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="summary">
                    <span>Total Items: <?= number_format($totalItems); ?></span>
                    <span>Total Budget: ₱<?= number_format($totalBudget, 2); ?></span>
                </div>

            </div>


        <?php endwhile; ?>

    </body>

    </html>

    <?php
    $html = ob_get_clean();

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A3', 'landscape');
    $dompdf->render();

    $dompdf->stream(
        'APP_Version_History_' . date('Ymd_His') . '.pdf',
        ['Attachment' => true]
    );

    exit;
}

header('Content-Type: application/json');
echo json_encode(['success' => false]);
exit;
?>