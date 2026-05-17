<?php
require_once 'classes.php';
$db = new db_class();

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if (!$action) {
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
}

switch ($action) {

    // SECTORS ANALYTICS
    case "GetBudgetOverview":
        $userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;
        $fiscal_year_id = $_GET['fiscal_year_id'] ?? $_POST['fiscal_year_id'] ?? null;

        if (!$userId) {
            echo json_encode([
                "success" => false,
                "message" => "Missing user ID"
            ]);
            exit;
        }
        $overview = $db->getOfficeBudgetOverviewByUser($userId, $fiscal_year_id);
        if (!$overview) {
            echo json_encode([
                'success' => false,
                'message' => 'No data found'
            ]);
            exit;
        }
        echo json_encode([
            'success' => true,
            'data' => $overview
        ]);
        exit;

    case "GetUserPPMPItems":
        $userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;
        $fiscal_year_id = $_GET['fiscal_year_id'] ?? $_POST['fiscal_year_id'] ?? null;

        if (!$userId) {
            echo json_encode([
                "success" => false,
                "message" => "Missing user ID"
            ]);
            exit;
        }

        $result = $db->getPPMPItemsByUser($userId, $fiscal_year_id);
        $data = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

        echo json_encode([
            "success" => true,
            "data" => $data
        ]);
        exit;
    // END SECTORS ANALYTICS


    // PROCUREMENT HEAD/ BAC Secretariat Head Analytics
    case "GetAllPPMPRecordsChart":
        $fiscal_year_id = $_GET['fiscal_year_id'] ?? null;
        $ppmpRecords = $db->getAllPPMPRecords(null, $fiscal_year_id);

        $data = [];

        if ($ppmpRecords && $ppmpRecords->num_rows > 0) {
            while ($row = $ppmpRecords->fetch_assoc()) {
                $data[] = [
                    "office_name" => $row['office_name'],
                    "fiscal_year" => $row['fiscal_year'],
                    "total_amount" => (float) $row['total_amount']
                ];
            }
        }

        echo json_encode([
            "success" => true,
            "data" => $data
        ]);
        exit;

    case "GetFinalizedAPPItemsChart":
        $fiscal_year_id = $_GET['fiscal_year_id'] ?? null;

        $items = $db->getFinalizedAPPItems($fiscal_year_id);

        $data = [];

        if ($items && $items->num_rows > 0) {
            while ($row = $items->fetch_assoc()) {
                $data[] = [
                    "item_name" => $row['item_name'],
                    "total_cost" => (float) $row['total_cost']
                ];
            }
        }

        echo json_encode([
            "success" => true,
            "data" => $data
        ]);
        exit;
    // END PROCUREMENT HEAD/ BAC Secretariat Head Analytics

    // case "GetAnnualBudgetChart":
    //     $annualBudgets = $db->getAnnualBudgets();
    //     $data = [];
    //     if ($annualBudgets && $annualBudgets->num_rows > 0) {
    //         while ($row = $annualBudgets->fetch_assoc()) {
    //             $allocatedData = $db->getTotalAllocatedAmountByFiscalYear($row['fiscal_year_id']);
    //             $totalAllocated = $allocatedData['total_allocated'] ?? 0.00;
    //             $totalAnnualBudget = $row['total_budget_amount'];
    //             $unallocatedBudget = $totalAnnualBudget - $totalAllocated;

    //             $data[] = [
    //                 "year" => $row['fiscal_year'],
    //                 "annual" => (float) $totalAnnualBudget,
    //                 "allocated" => (float) $totalAllocated,
    //                 "unallocated" => (float) $unallocatedBudget
    //             ];
    //         }
    //     }
    //     echo json_encode([
    //         "success" => true,
    //         "data" => $data
    //     ]);
    //     exit;
    case "GetAnnualBudgetChart":
        $currentFY = $db->getCurrentFiscalYear();

        $data = [];

        if ($currentFY) {
            $annualBudget = $db->getAnnualBudgetByFiscalYear($currentFY['fiscal_year_id']);

            if ($annualBudget) {
                $allocatedData = $db->getTotalAllocatedAmountByFiscalYear($currentFY['fiscal_year_id']);
                $totalAllocated = $allocatedData['total_allocated'] ?? 0.00;

                $totalAnnualBudget = $annualBudget['total_budget_amount'];
                $unallocatedBudget = $totalAnnualBudget - $totalAllocated;

                $data[] = [
                    "year" => $currentFY['year'],
                    "annual" => (float) $totalAnnualBudget,
                    "allocated" => (float) $totalAllocated,
                    "unallocated" => (float) $unallocatedBudget
                ];
            }
        }

        echo json_encode([
            "success" => true,
            "data" => $data
        ]);
        exit;

    default:
        echo json_encode(["success" => false, "error" => "Invalid action."]);
        break;
}


