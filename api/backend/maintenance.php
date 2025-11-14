<?php
/**
 * ============================================================
 * HealthRunCare — Maintenance Backend (Stable Production Version)
 * ============================================================
 * Location: /api/backend/maintenance.php
 *
 * Actions:
 *  - get_summary
 *  - get_plans
 *  - get_active
 *  - get_matured
 *  - start_maintenance
 *  - unlock
 *
 * Status lifecycle:
 *  - active → matured → unlocked
 * ============================================================
 */

header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../utilities/email_temps.php';
require_once __DIR__ . '/email.php';

session_start();

// 🔒 Require authenticated user
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}
$user_id = intval($_SESSION['user_id']);

$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?? $_POST ?? $_GET;
$action = $input['action'] ?? null;

// Database connection
try {
    $pdo = getPDO();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

/**
 * Unified JSON responder
 */
function respond($status, $message, $data = []) {
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}

function add_days($date, $days) {
    $dt = new DateTime($date);
    $dt->add(new DateInterval("P{$days}D"));
    return $dt->format('Y-m-d');
}

function now_iso() {
    return (new DateTime())->format('Y-m-d H:i:s');
}

/* ----------------------------------------------------------
   Optional: Schema Verification
   Run manually via ?schema_check=1
-----------------------------------------------------------*/
if (isset($_GET['schema_check']) && $_GET['schema_check'] == '1') {
    try {
        $required = [
            "plan_id INT NULL AFTER id",
            "maturity_date DATE NULL AFTER next_payment_date",
            "updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",
            "status ENUM('active','matured','unlocked','expired') NOT NULL DEFAULT 'active' AFTER roi_earned"
        ];

        foreach ($required as $definition) {
            $col = preg_split('/\s+/', trim($definition))[0];
            $exists = $pdo->query("SHOW COLUMNS FROM maintenance LIKE '$col'")->fetch(PDO::FETCH_ASSOC);
            if (!$exists) $pdo->exec("ALTER TABLE maintenance ADD COLUMN $definition");
        }

        respond('success', 'Maintenance table schema verified successfully.');
    } catch (Exception $e) {
        respond('error', 'Schema verification failed: ' . $e->getMessage());
    }
}

/* ----------------------------------------------------------
   Plans reference — matches UI plans in development.php
-----------------------------------------------------------*/
$plansRef = [
    1 => ['id'=>1, 'name'=>'Maintenance Support Starter Plan', 'roi_percent'=>5.5,  'duration_days'=>9 * 30,   'min'=>10000],
    2 => ['id'=>2, 'name'=>'Standard Equipment Care Plan',   'roi_percent'=>9.0,  'duration_days'=>12 * 30,  'min'=>25000],
    3 => ['id'=>3, 'name'=>'Infrastructure Development Plan', 'roi_percent'=>16.5, 'duration_days'=>24 * 30,  'min'=>50000],
    4 => ['id'=>4, 'name'=>'Premium Equipment Sustainability Plan','roi_percent'=>25.0,'duration_days'=>36 * 30,'min'=>250000],
    5 => ['id'=>5, 'name'=>'Lifetime Equipment Trust Plan', 'roi_percent'=>7.0,  'duration_days'=>365 * 100, 'min'=>1000000]
];

if (!$action) respond('error', 'No action specified.');

/* ----------------------------------------------------------
   MAIN SWITCH
-----------------------------------------------------------*/
switch ($action) {

    /* ======================
       GET SUMMARY
       ====================== */
    case 'get_summary':
        try {
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) AS total_spent, COALESCE(SUM(roi_earned),0) AS total_roi FROM maintenance WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $tot = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_spent'=>0,'total_roi'=>0];

            $activeStmt = $pdo->prepare("SELECT created_at, plan_id, maturity_date FROM maintenance WHERE user_id = ? AND status = 'active'");
            $activeStmt->execute([$user_id]);
            $rows = $activeStmt->fetchAll(PDO::FETCH_ASSOC);

            $active_count = count($rows);
            $next_maintenance = null;
            foreach ($rows as $r) {
                if (!empty($r['maturity_date'])) {
                    if ($next_maintenance === null || $r['maturity_date'] < $next_maintenance) $next_maintenance = $r['maturity_date'];
                } else if (!empty($r['created_at']) && !empty($r['plan_id']) && isset($plansRef[$r['plan_id']])) {
                    $m = add_days($r['created_at'], $plansRef[$r['plan_id']]['duration_days']);
                    if ($next_maintenance === null || $m < $next_maintenance) $next_maintenance = $m;
                }
            }

            $next_maintenance = $next_maintenance ? date('M j, Y', strtotime($next_maintenance)) : '—';

            $walletStmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
            $walletStmt->execute([$user_id]);
            $wallet = $walletStmt->fetch(PDO::FETCH_ASSOC) ?: ['balance'=>0];

            $summary = [
                'active_projects' => $active_count,
                'total_spent' => floatval($tot['total_spent']),
                'total_roi' => floatval($tot['total_roi']),
                'next_maintenance' => $next_maintenance
            ];

            respond('success', 'Summary loaded', ['summary'=>$summary, 'wallet'=>$wallet]);
        } catch (Exception $e) {
            error_log("maintenance:get_summary: ".$e->getMessage());
            respond('error','Failed to load summary.');
        }
        break;

    /* ======================
       GET PLANS
       ====================== */
    case 'get_plans':
        respond('success','Plans loaded',['plans'=>array_values($plansRef)]);
        break;

    /* ======================
       GET ACTIVE
       ====================== */
    case 'get_active':
        try {
            $stmt = $pdo->prepare("SELECT * FROM maintenance WHERE user_id = ? AND status = 'active' ORDER BY created_at DESC");
            $stmt->execute([$user_id]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $out = [];
            foreach ($rows as $r) {
                $plan = $plansRef[$r['plan_id']] ?? null;
                $maturity = $r['maturity_date'] ?? ($plan ? add_days($r['created_at'], $plan['duration_days']) : null);
                $out[] = [
                    'id' => $r['id'],
                    'plan_name' => $plan['name'] ?? ($r['plan_name'] ?? 'Unknown'),
                    'amount' => $r['amount'],
                    'roi_percent' => $plan['roi_percent'] ?? 0,
                    'duration_days' => $plan['duration_days'] ?? null,
                    'created_at' => date('M j, Y', strtotime($r['created_at'])),
                    'maturity_date' => $maturity ? date('M j, Y', strtotime($maturity)) : '—',
                    'status' => $r['status'] ?? 'active'
                ];
            }
            respond('success','Active maintenance loaded',['maintenances'=>$out]);
        } catch (Exception $e) {
            error_log("maintenance:get_active: ".$e->getMessage());
            respond('error','Failed to load active maintenance items.');
        }
        break;

    /* ======================
       GET MATURED
       ====================== */
    case 'get_matured':
        try {
            $stmt = $pdo->prepare("SELECT * FROM maintenance WHERE user_id = ? AND status = 'matured' ORDER BY created_at ASC");
            $stmt->execute([$user_id]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $out = [];
            foreach ($rows as $r) {
                $plan = $plansRef[$r['plan_id']] ?? null;
                $maturity = $r['maturity_date'] ?? ($plan ? add_days($r['created_at'], $plan['duration_days']) : null);
                $roi_earned = $r['roi_earned'] ?: round($r['amount'] * (($plan['roi_percent'] ?? 0) / 100), 2);
                $out[] = [
                    'id' => $r['id'],
                    'plan_name' => $plan['name'] ?? ($r['plan_name'] ?? 'Unknown'),
                    'amount' => $r['amount'],
                    'roi_earned' => $roi_earned,
                    'maturity_date' => $maturity ? date('M j, Y', strtotime($maturity)) : '—',
                    'total_payout' => round($r['amount'] + $roi_earned, 2),
                    'status' => $r['status'] ?? 'matured'
                ];
            }
            respond('success','Matured maintenance loaded',['maintenances'=>$out]);
        } catch (Exception $e) {
            error_log("maintenance:get_matured: ".$e->getMessage());
            respond('error','Failed to load matured contributions.');
        }
        break;

    /* ======================
       START MAINTENANCE (with email)
       ====================== */
    case 'start_maintenance':
        $planId = intval($input['plan_id'] ?? 0);
        $amount = floatval($input['amount'] ?? 0);
        if ($planId <= 0 || $amount <= 0) respond('error','Invalid plan or amount.');
        $plan = $plansRef[$planId] ?? null;
        if (!$plan) respond('error','Invalid plan.');
        if ($amount < $plan['min']) respond('error',"Minimum for {$plan['name']} is $" . number_format($plan['min']));

        try {
            $pdo->beginTransaction();

            $wallet = $pdo->prepare("SELECT balance FROM wallets WHERE user_id=? FOR UPDATE");
            $wallet->execute([$user_id]);
            $w = $wallet->fetch(PDO::FETCH_ASSOC);
            if (!$w || $w['balance'] < $amount) {
                $pdo->rollBack();
                respond('error','Insufficient balance.');
            }

            $pdo->prepare("UPDATE wallets SET balance = balance - ?, total_investments = total_investments + ? WHERE user_id = ?")
                ->execute([$amount, $amount, $user_id]);

            $created_at = now_iso();
            $maturity_date = add_days(date('Y-m-d'), $plan['duration_days']);

            $ins = $pdo->prepare("INSERT INTO maintenance (user_id, plan_id, plan_name, amount, roi_earned, frequency, status, maturity_date, created_at)
                                  VALUES (?, ?, ?, ?, 0, 'once', 'active', ?, ?)");
            $ins->execute([$user_id, $planId, $plan['name'], $amount, $maturity_date, $created_at]);
            $cid = $pdo->lastInsertId();

            $ref = 'MNT-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));
            $details = json_encode(['plan_name'=>$plan['name'],'plan_id'=>$planId,'contrib_id'=>$cid]);
            $pdo->prepare("INSERT INTO transactions (user_id,type,amount,reference,status,method,details,created_at)
                           VALUES (?,?,?,?, 'completed','wallet_address',?,?)")
                ->execute([$user_id,'maintenance',$amount,$ref,$details,now_iso()]);

            $pdo->commit();

            // Send user + admin emails
            try {
                $userStmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
                $userStmt->execute([$user_id]);
                $u = $userStmt->fetch(PDO::FETCH_ASSOC);

                if ($u) {
                    sendEmail([
                        'to' => $u['email'],
                        'template' => 'maintenance_started',
                        'variables' => [
                            'user_name' => $u['full_name'] ?? 'User',
                            'plan_name' => $plan['name'],
                            'amount' => number_format($amount, 2),
                            'roi_percent' => $plan['roi_percent'],
                            'maturity_date' => $maturity_date,
                            'reference' => $ref
                        ]
                    ]);

                    sendEmail([
                        'to' => ADMIN_CONTACT_EMAIL,
                        'template' => 'admin_maintenance_notification',
                        'variables' => [
                            'user_name' => $u['full_name'] ?? 'User',
                            'user_email' => $u['email'],
                            'plan_name' => $plan['name'],
                            'amount' => number_format($amount, 2),
                            'reference' => $ref
                        ]
                    ]);
                }
            } catch (Exception $e) {
                error_log("maintenance email error: " . $e->getMessage());
            }

            respond('success','Maintenance plan started.',['reference'=>$ref]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("start_maintenance: ".$e->getMessage());
            respond('error','Failed to start maintenance plan.');
        }
        break;

    /* ======================
       UNLOCK
       ====================== */
    case 'unlock':
        $id = intval($input['maintenance_id'] ?? $input['id'] ?? 0);
        $early = intval($input['early'] ?? 0);
        if ($id <= 0) respond('error','Invalid maintenance ID.');
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("SELECT * FROM maintenance WHERE id=? AND user_id=? FOR UPDATE");
            $stmt->execute([$id,$user_id]);
            $c = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$c) { $pdo->rollBack(); respond('error','Not found.'); }

            $plan = $plansRef[$c['plan_id']] ?? null;
            if (!$plan) { $pdo->rollBack(); respond('error','Missing plan data.'); }

            $amount = $c['amount'];
            $maturity_date = $c['maturity_date'] ?? add_days($c['created_at'], $plan['duration_days']);
            $roi_earned = 0; $payout = 0;

            if ($early) {
                $daysElapsed = (new DateTime())->diff(new DateTime($c['created_at']))->days;
                $factor = min($daysElapsed / max(1, $plan['duration_days']), 1);
                $roi_earned = $amount * ($plan['roi_percent'] / 100) * $factor;
                $penalty = $amount * 0.015 * $factor;
                $payout = $amount + $roi_earned - $penalty;
            } else {
                if (strtotime($maturity_date) > strtotime('today')) {
                    $pdo->rollBack();
                    respond('error','Not matured yet.');
                }
                $roi_earned = $amount * ($plan['roi_percent'] / 100);
                $payout = $amount + $roi_earned;
            }

            $pdo->prepare("UPDATE maintenance SET roi_earned=?, status='unlocked', updated_at=NOW() WHERE id=?")
                ->execute([round($roi_earned,2), $id]);
            $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id=?")
                ->execute([round($payout,2), $user_id]);

            $ref = 'MNT-UNL-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            $details = json_encode(['subtype'=>$early?'early_unlock':'maturity_unlock','roi_earned'=>$roi_earned,'total_payout'=>$payout]);
            $pdo->prepare("INSERT INTO transactions (user_id,type,amount,reference,status,method,details,created_at)
                           VALUES (?,?,?,?, 'completed','wallet_address',?,?)")
                ->execute([$user_id,'maintenance',$payout,$ref,$details,now_iso()]);
            $pdo->commit();

            // Email notifications
            try {
                $userStmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
                $userStmt->execute([$user_id]);
                $u = $userStmt->fetch(PDO::FETCH_ASSOC);

                if ($u) {
                    $template = $early ? 'maintenance_unlocked_early' : 'maintenance_matured';
                    sendEmail([
                        'to' => $u['email'],
                        'template' => $template,
                        'variables' => [
                            'user_name' => $u['full_name'] ?? 'User',
                            'plan_name' => $plan['name'],
                            'amount' => number_format($amount, 2),
                            'roi_earned' => number_format($roi_earned, 2),
                            'total_payout' => number_format($payout, 2),
                            'maturity_date' => $maturity_date,
                            'reference' => $ref
                        ]
                    ]);
                }
            } catch (Exception $e) {
                error_log("maintenance unlock email failed: " . $e->getMessage());
            }

            respond('success','Unlock complete.',['payout'=>$payout,'reference'=>$ref]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("maintenance:unlock: ".$e->getMessage());
            respond('error','Unlock failed.');
        }
        break;

    default:
        respond('error','Invalid action.');
}
