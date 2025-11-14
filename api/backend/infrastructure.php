<?php
/**
 * ============================================================
 * HealthRunCare — Infrastructure Backend (Final with Emails)
 * ============================================================
 * Location: /api/backend/infrastructure.php
 *
 * Actions:
 *  - get_summary
 *  - get_plans
 *  - get_active
 *  - get_matured
 *  - start_infrastructure
 *  - unlock
 *
 * Status lifecycle:
 *  - active → matured → unlocked
 * ============================================================
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../utilities/email_temps.php';
require_once __DIR__ . '/email.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}
$user_id = intval($_SESSION['user_id']);

$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?? $_POST ?? $_GET;
$action = $input['action'] ?? null;

try {
    $pdo = getPDO();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
    exit;
}

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
   Ensure required columns exist (plan_id + status)
-----------------------------------------------------------*/
try {
    $cols = $pdo->query("SHOW COLUMNS FROM infrastructure_contributions LIKE 'plan_id'")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE infrastructure_contributions ADD COLUMN plan_id INT NULL AFTER id");
    }

    $cols2 = $pdo->query("SHOW COLUMNS FROM infrastructure_contributions LIKE 'status'")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($cols2)) {
        $pdo->exec("ALTER TABLE infrastructure_contributions ADD COLUMN status ENUM('active','matured','unlocked') NOT NULL DEFAULT 'active' AFTER roi_earned");
    }
} catch (Exception $e) {
    error_log("Column ensure failed: " . $e->getMessage());
}

/* ----------------------------------------------------------
   Plans reference — matches UI
-----------------------------------------------------------*/
$plansRef = [
    1 => ['id'=>1, 'name' => 'Basic Diagnostic Plan', 'roi_percent' => 9.0,  'duration_days' => 365,  'payout_option' => 'quarterly', 'min' => 10000],
    2 => ['id'=>2, 'name' => 'Imaging Growth Plan',    'roi_percent' => 13.5, 'duration_days' => 540,  'payout_option' => 'quarterly', 'min' => 20000],
    3 => ['id'=>3, 'name' => 'Advanced Radiology Plan','roi_percent' => 17.5, 'duration_days' => 730,  'payout_option' => 'monthly',   'min' => 50000],
    4 => ['id'=>4, 'name' => 'Dialysis Infrastructure Plan','roi_percent' => 20.0, 'duration_days' => 900, 'payout_option' => 'quarterly','min' => 100000],
    5 => ['id'=>5, 'name' => 'Complete Operating Room Equipment Plan','roi_percent' => 22.5,'duration_days'=>1095,'payout_option'=>'monthly','min'=>150000],
    6 => ['id'=>6, 'name' => 'Hospital Diagnostic Wing Installation Plan','roi_percent'=>29.0,'duration_days'=>1095,'payout_option'=>'quarterly','min'=>500000],
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
            $stmt = $pdo->prepare("SELECT 
                COALESCE(SUM(amount),0) AS total_funded, 
                COALESCE(SUM(roi_earned),0) AS total_roi 
                FROM infrastructure_contributions WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $tot = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_funded'=>0,'total_roi'=>0];

            $activeStmt = $pdo->prepare("SELECT created_at, plan_id FROM infrastructure_contributions WHERE user_id = ? AND status = 'active'");
            $activeStmt->execute([$user_id]);
            $rows = $activeStmt->fetchAll(PDO::FETCH_ASSOC);

            $active_count = count($rows);
            $next_maturity = null;
            foreach ($rows as $r) {
                $plan_id = intval($r['plan_id'] ?? 0);
                $duration = $plansRef[$plan_id]['duration_days'] ?? null;
                if ($duration) {
                    $maturity = add_days($r['created_at'], $duration);
                    if ($next_maturity === null || $maturity < $next_maturity) $next_maturity = $maturity;
                }
            }

            $next_maturity = $next_maturity ? date('M j, Y', strtotime($next_maturity)) : '—';

            $walletStmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
            $walletStmt->execute([$user_id]);
            $wallet = $walletStmt->fetch(PDO::FETCH_ASSOC) ?: ['balance'=>0];

            $summary = [
                'active_projects' => $active_count,
                'total_funded' => floatval($tot['total_funded']),
                'total_roi' => floatval($tot['total_roi']),
                'next_inspection' => $next_maturity
            ];
            respond('success', 'Summary loaded', ['summary'=>$summary, 'wallet'=>$wallet]);
        } catch (Exception $e) {
            error_log("get_summary: ".$e->getMessage());
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
            $stmt = $pdo->prepare("SELECT * FROM infrastructure_contributions WHERE user_id = ? AND status = 'active' ORDER BY created_at DESC");
            $stmt->execute([$user_id]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $out = [];
            foreach ($rows as $r) {
                $plan = $plansRef[$r['plan_id']] ?? null;
                if (!$plan) continue;

                $maturity = add_days($r['created_at'], $plan['duration_days']);
                $out[] = [
                    'id' => $r['id'],
                    'plan_name' => $plan['name'],
                    'amount' => $r['amount'],
                    'roi_percent' => $plan['roi_percent'],
                    'duration_days' => $plan['duration_days'],
                    'created_at' => date('M j, Y', strtotime($r['created_at'])),
                    'maturity_date' => date('M j, Y', strtotime($maturity)),
                    'status' => $r['status']
                ];
            }
            respond('success','Active investments loaded',['investments'=>$out]);
        } catch (Exception $e) {
            error_log("get_active: ".$e->getMessage());
            respond('error','Failed to load active investments.');
        }
        break;

    /* ======================
       GET MATURED
       ====================== */
    case 'get_matured':
        try {
            $stmt = $pdo->prepare("SELECT * FROM infrastructure_contributions WHERE user_id = ? AND status = 'matured' ORDER BY created_at ASC");
            $stmt->execute([$user_id]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $out = [];
            foreach ($rows as $r) {
                $plan = $plansRef[$r['plan_id']] ?? null;
                if (!$plan) continue;
                $maturity = add_days($r['created_at'], $plan['duration_days']);
                $roi_earned = $r['roi_earned'] ?: round($r['amount'] * $plan['roi_percent'] / 100, 2);
                $out[] = [
                    'id' => $r['id'],
                    'plan_name' => $plan['name'],
                    'amount' => $r['amount'],
                    'roi_earned' => $roi_earned,
                    'maturity_date' => date('M j, Y', strtotime($maturity)),
                    'total_payout' => round($r['amount'] + $roi_earned, 2),
                    'status' => $r['status']
                ];
            }
            respond('success','Matured investments loaded',['investments'=>$out]);
        } catch (Exception $e) {
            error_log("get_matured: ".$e->getMessage());
            respond('error','Failed to load matured contributions.');
        }
        break;

    /* ======================
       START INFRASTRUCTURE (with email)
       ====================== */
    case 'start_infrastructure':
        $planId = intval($input['plan_id'] ?? 0);
        $amount = floatval($input['amount'] ?? 0);
        if ($planId<=0 || $amount<=0) respond('error','Invalid plan or amount.');
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

            $ins = $pdo->prepare("INSERT INTO infrastructure_contributions (user_id, plan_id, amount, status, created_at) VALUES (?, ?, ?, 'active', ?)");
            $ins->execute([$user_id, $planId, $amount, now_iso()]);
            $cid = $pdo->lastInsertId();

            $ref = 'INF-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));
            $details = json_encode(['plan_name'=>$plan['name'],'plan_id'=>$planId,'contrib_id'=>$cid]);
            $pdo->prepare("INSERT INTO transactions (user_id,type,amount,reference,status,method,details,created_at)
                           VALUES (?,?,?,?, 'completed','wallet_address',?,?)")
                ->execute([$user_id,'infrastructure',$amount,$ref,$details,now_iso()]);
            $pdo->commit();

            // ✅ Send user + admin emails
            try {
                $userStmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
                $userStmt->execute([$user_id]);
                $u = $userStmt->fetch(PDO::FETCH_ASSOC);

                if ($u) {
                    sendEmail([
                        'to' => $u['email'],
                        'template' => 'infrastructure_started',
                        'variables' => [
                            'user_name' => $u['full_name'] ?? 'User',
                            'plan_name' => $plan['name'],
                            'amount' => number_format($amount, 2),
                            'roi_percent' => $plan['roi_percent'],
                            'maturity_date' => add_days(date('Y-m-d'), $plan['duration_days']),
                            'reference' => $ref
                        ]
                    ]);

                    sendEmail([
                        'to' => ADMIN_CONTACT_EMAIL,
                        'template' => 'admin_infrastructure_notification',
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
                error_log("Infrastructure email error: " . $e->getMessage());
            }

            respond('success','Infrastructure investment started.',['reference'=>$ref]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("start_infrastructure: ".$e->getMessage());
            respond('error','Failed to start investment.');
        }
        break;

    /* ======================
       UNLOCK (with email)
       ====================== */
    case 'unlock':
        $id = intval($input['investment_id'] ?? 0);
        $early = intval($input['early'] ?? 0);
        if ($id<=0) respond('error','Invalid investment ID.');
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("SELECT * FROM infrastructure_contributions WHERE id=? AND user_id=? FOR UPDATE");
            $stmt->execute([$id,$user_id]);
            $c = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$c) { $pdo->rollBack(); respond('error','Not found.'); }

            $plan = $plansRef[$c['plan_id']] ?? null;
            if (!$plan) { $pdo->rollBack(); respond('error','Missing plan data.'); }

            $amount = $c['amount'];
            $maturity_date = add_days($c['created_at'], $plan['duration_days']);
            $roi_earned = 0; $payout = 0;

            if ($early) {
                $days = (new DateTime())->diff(new DateTime($c['created_at']))->days;
                $factor = min($days / max(1,$plan['duration_days']), 1);
                $roi_earned = $amount * $plan['roi_percent']/100 * $factor;
                $penalty = $amount * 0.015 * $factor;
                $payout = $amount + $roi_earned - $penalty;
            } else {
                if (strtotime($maturity_date) > strtotime('today')) {
                    $pdo->rollBack();
                    respond('error','Not matured yet.');
                }
                $roi_earned = $amount * $plan['roi_percent']/100;
                $payout = $amount + $roi_earned;
            }

            $pdo->prepare("UPDATE infrastructure_contributions SET roi_earned=?, status='unlocked', updated_at=NOW() WHERE id=?")
                ->execute([round($roi_earned,2), $id]);
            $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id=?")
                ->execute([round($payout,2), $user_id]);

            $ref = 'INF-UNL-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            $details = json_encode(['subtype'=>$early?'early_unlock':'maturity_unlock','roi_earned'=>$roi_earned,'total_payout'=>$payout]);
            $pdo->prepare("INSERT INTO transactions (user_id,type,amount,reference,status,method,details,created_at)
                           VALUES (?,?,?,?, 'completed','wallet_address',?,?)")
                ->execute([$user_id,'infrastructure',$payout,$ref,$details,now_iso()]);
            $pdo->commit();

            // ✅ Send email notification
            try {
                $userStmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
                $userStmt->execute([$user_id]);
                $u = $userStmt->fetch(PDO::FETCH_ASSOC);

                if ($u) {
                    $template = $early ? 'infrastructure_unlocked_early' : 'infrastructure_matured';
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
                error_log("Unlock email send failed: " . $e->getMessage());
            }

            respond('success','Unlock complete.',['payout'=>$payout,'reference'=>$ref]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("unlock: ".$e->getMessage());
            respond('error','Unlock failed.');
        }
        break;

    default:
        respond('error','Invalid action.');
}
