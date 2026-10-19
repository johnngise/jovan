<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$campaign = trim($_GET['campaign'] ?? '');
$paymentAccounts = require __DIR__ . '/config/payment-accounts.php';
$errors = [];
$success = false;
$csrfToken = $_SESSION['donation_csrf'] ?? bin2hex(random_bytes(32));
$_SESSION['donation_csrf'] = $csrfToken;
$values = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'country' => '',
    'amount' => '',
    'currency' => 'USD',
    'payment_method' => '',
    'donation_type' => 'one_time',
    'frequency' => '',
    'anonymous' => '',
    'message' => '',
    'consent' => '',
];

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS donations (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            campaign VARCHAR(255) NULL,
            donor_name VARCHAR(150) NOT NULL,
            email VARCHAR(190) NOT NULL,
            phone VARCHAR(50) NULL,
            country VARCHAR(100) NULL,
            amount DECIMAL(12,2) NOT NULL,
            currency VARCHAR(3) NOT NULL DEFAULT 'USD',
            payment_method VARCHAR(80) NOT NULL,
            anonymous TINYINT(1) NOT NULL DEFAULT 0,
            message TEXT NULL,
            status ENUM('new','contacted','completed','cancelled') NOT NULL DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $columns = $pdo->query("SHOW COLUMNS FROM donations")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('donation_type', $columns, true)) {
        $pdo->exec("ALTER TABLE donations ADD donation_type VARCHAR(20) NOT NULL DEFAULT 'one_time' AFTER amount");
    }
    if (!in_array('frequency', $columns, true)) {
        $pdo->exec("ALTER TABLE donations ADD frequency VARCHAR(20) NULL AFTER donation_type");
    }
} catch (PDOException $exception) {
    $errors[] = 'Le formulaire de don est temporairement indisponible. Veuillez réessayer plus tard.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$errors) {
    $submittedToken = (string) ($_POST['csrf_token'] ?? '');
    if (!hash_equals($csrfToken, $submittedToken)) {
        $errors[] = 'Votre session a expiré. Veuillez actualiser la page puis réessayer.';
    }
    if (trim((string) ($_POST['website'] ?? '')) !== '') {
        $errors[] = 'La demande n’a pas pu être traitée.';
    }

    foreach ($values as $key => $default) {
        $values[$key] = trim((string) ($_POST[$key] ?? $default));
    }

    $campaign = trim((string) ($_POST['campaign'] ?? $campaign));
    $values['anonymous'] = isset($_POST['anonymous']) ? '1' : '';
    $values['consent'] = isset($_POST['consent']) ? '1' : '';
    $values['donation_type'] = in_array($values['donation_type'], ['one_time', 'recurring'], true) ? $values['donation_type'] : '';
    $values['frequency'] = in_array($values['frequency'], ['weekly', 'monthly', 'yearly'], true) ? $values['frequency'] : '';

    if (!$errors && $values['name'] === '') {
        $errors[] = 'Votre nom complet est obligatoire.';
    }
    if (!$errors && !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Veuillez saisir une adresse e-mail valide.';
    }
    if (!$errors && (!is_numeric($values['amount']) || (float) $values['amount'] <= 0)) {
        $errors[] = 'Veuillez saisir un montant de don supérieur à zéro.';
    }
    if (!$errors && !in_array($values['currency'], ['USD', 'CDF', 'EUR'], true)) {
        $errors[] = 'La devise sélectionnée est invalide.';
    }
    if (!$errors && !in_array($values['payment_method'], ['M-Pesa', 'Airtel Money', 'Orange Money', 'PayPal', 'Stripe / carte bancaire', 'Virement bancaire', 'À convenir'], true)) {
        $errors[] = 'Le mode de paiement sélectionné est invalide.';
    }
    if (!$errors && $values['consent'] !== '1') {
        $errors[] = 'Vous devez accepter que vos informations soient utilisées pour traiter votre demande.';
    }
    if (!$errors && $values['donation_type'] === '') {
        $errors[] = 'Veuillez choisir le type de don.';
    }
    if (!$errors && $values['donation_type'] === 'recurring' && $values['frequency'] === '') {
        $errors[] = 'Veuillez choisir la fréquence du don périodique.';
    }
    if ($values['donation_type'] === 'one_time') {
        $values['frequency'] = '';
    }

    if (!$errors) {
        $stmt = $pdo->prepare("
            INSERT INTO donations
                (campaign, donor_name, email, phone, country, amount, donation_type, frequency, currency, payment_method, anonymous, message)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $campaign ?: null,
            $values['name'],
            $values['email'],
            $values['phone'] ?: null,
            $values['country'] ?: null,
            number_format((float) $values['amount'], 2, '.', ''),
            $values['donation_type'],
            $values['frequency'] ?: null,
            $values['currency'],
            $values['payment_method'],
            $values['anonymous'] === '1' ? 1 : 0,
            $values['message'] ?: null,
        ]);
        $success = true;
        $values = array_fill_keys(array_keys($values), '');
        $values['currency'] = 'USD';
        $values['payment_method'] = '';
        $values['donation_type'] = 'one_time';
        $values['frequency'] = '';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faire un don | ORPAIS</title>
    <meta name="description" content="Soutenez les actions d’ORPAIS en remplissant le formulaire de don.">
    <link rel="icon" href="assets/img/logo.jpg">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/tailwind.css" rel="stylesheet">
    <style>
        .donation-header { background: linear-gradient(120deg, #164e68, #4da9c2); color: #fff; padding: 150px 0 72px; }
        .donation-header h1 { font-size: 44px; font-weight: 700; }
        .donation-header p { color: #e8f8f8; max-width: 680px; }
        .donation-shell { margin-top: -30px; position: relative; z-index: 2; }
        .donation-card, .donation-info { background: #fff; border: 1px solid #dff3f5; border-radius: 18px; box-shadow: 0 14px 34px rgba(22, 78, 104, .1); }
        .donation-card { padding: 34px; }
        .donation-info { background: #164e68; color: #fff; padding: 32px; }
        .donation-info h2 { font-size: 25px; }
        .donation-info p, .donation-info li { color: #c3e1e5; line-height: 1.7; }
        .donation-info ul { list-style: none; padding: 0; }
        .donation-info li { margin: 17px 0; }
        .donation-info li i { color: #f2b093; margin-right: 10px; }
        .donation-card h2 { color: #164e68; font-size: 25px; }
        .donation-card .form-label { color: #163b4d; font-weight: 600; }
        .donation-card .form-control, .donation-card .form-select { border-color: #d4e6e9; border-radius: 10px; min-height: 47px; }
        .donation-card .form-control:focus, .donation-card .form-select:focus { border-color: #4da9c2; box-shadow: 0 0 0 .2rem rgba(77, 169, 194, .16); }
        .donation-submit { background: #d9865f; border: 0; border-radius: 10px; color: #fff; font-weight: 700; padding: 13px 22px; }
        .donation-submit:hover { background: #bd6846; color: #fff; }
        .donation-success { border: 1px solid #b9e5c8; border-radius: 12px; background: #effbf2; color: #24733e; padding: 16px; }
        .donation-choice { border: 1px solid #d4e6e9; border-radius: 12px; cursor: pointer; display: block; height: 100%; padding: 18px; transition: .2s; }
        .donation-choice:has(input:checked) { background: #eef9fb; border-color: #4da9c2; box-shadow: 0 0 0 .2rem rgba(77,169,194,.12); }
        .payment-accounts { background: #fff8f3; border: 1px solid #f1d2c2; border-radius: 12px; padding: 18px; }
        .payment-accounts strong { color: #164e68; }
        .donation-progress { align-items: center; display: flex; gap: 10px; margin-bottom: 25px; }
        .donation-progress span { align-items: center; background: #e8f4f6; border-radius: 50%; color: #164e68; display: inline-flex; font-weight: 700; height: 32px; justify-content: center; width: 32px; }
        .donation-progress span.active { background: #d9865f; color: #fff; }
        .donation-progress i { color: #9bb9c1; flex: 1; max-width: 55px; }
        .amount-option { border-radius: 8px; min-width: 58px; }
        .donation-summary { background: #f4fafb; border: 1px solid #dcecef; border-radius: 14px; padding: 22px; position: sticky; top: 25px; }
        .payment-heading { color: #164e68; font-size: 18px; font-weight: 700; margin-bottom: 12px; }
        .payment-choice { align-items: center; background: #fff; border: 1px solid #d4e6e9; border-radius: 10px; cursor: pointer; display: flex; gap: 10px; min-height: 52px; padding: 12px 14px; transition: .2s; }
        .payment-choice:has(input:checked) { background: #eef9fb; border-color: #4da9c2; box-shadow: 0 0 0 .2rem rgba(77,169,194,.12); }
        .payment-choice input { accent-color: #164e68; }
        .donation-summary .summary-amount { color: #164e68; font-size: 34px; font-weight: 800; }
        .donation-step[hidden] { display: none !important; }
        .step-heading { border-bottom: 1px solid #e5eff1; color: #164e68; margin-bottom: 20px; padding-bottom: 12px; }
        .step-heading span { background: #d9865f; border-radius: 50%; color: #fff; display: inline-flex; height: 30px; justify-content: center; margin-right: 8px; padding-top: 5px; width: 30px; }
        .step-actions { display: flex; justify-content: space-between; margin-top: 24px; }
        .step-button { background: #164e68; border: 0; border-radius: 9px; color: #fff; font-weight: 700; padding: 12px 20px; }
        .step-button.secondary { background: #e8f4f6; color: #164e68; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<header class="donation-header">
    <div class="container">
        <h1>Faire un don</h1>
        <p>Votre soutien permet à ORPAIS de renforcer la paix, l’autonomisation et la cohésion sociale dans les communautés.</p>
    </div>
</header>

<main class="py-5">
    <div class="container donation-shell">
        <div class="row g-4">
            <div class="col-lg-7">
                <section class="donation-card">
                    <div class="donation-progress" aria-label="Étapes du don"><span class="active">1</span><i></i><span>2</span><i></i><span>3</span></div>
                    <p class="small text-muted mb-4">Choisir votre don &nbsp;›&nbsp; Vos coordonnées &nbsp;›&nbsp; Confirmation</p>
                    <h2>Soutenir une initiative</h2>
                    <p class="text-muted mb-4">Choisissez un don ponctuel ou périodique, puis indiquez le moyen de paiement souhaité.</p>
                    <?php if ($success): ?>
                        <div class="donation-success mb-4"><i class="bi bi-check-circle me-2"></i>Merci pour votre intention de don. Notre équipe vous contactera prochainement.</div>
                    <?php endif; ?>
                    <?php if ($errors): ?>
                        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div>
                    <?php endif; ?>
                    <form method="post" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                        <div class="d-none" aria-hidden="true"><label for="website">Site web</label><input id="website" name="website" tabindex="-1" autocomplete="off"></div>
                        <input type="hidden" name="campaign" value="<?= e($campaign) ?>">
                        <div class="donation-step" data-step="1">
                            <h3 class="step-heading"><span>1</span>Choisissez votre contribution</h3>
                            <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Type de don *</label>
                                <div class="row g-2">
                                    <div class="col-md-6"><label class="donation-choice"><input class="form-check-input me-2" type="radio" name="donation_type" value="one_time" required <?= $values['donation_type'] === 'one_time' ? 'checked' : '' ?>><strong>Don unique</strong><small class="d-block text-muted mt-1">Une seule contribution aujourd’hui.</small></label></div>
                                    <div class="col-md-6"><label class="donation-choice"><input class="form-check-input me-2" type="radio" name="donation_type" value="recurring" <?= $values['donation_type'] === 'recurring' ? 'checked' : '' ?>><strong>Don périodique</strong><small class="d-block text-muted mt-1">Une contribution chaque semaine, mois ou année.</small></label></div>
                                </div>
                            </div>
                            <div class="col-md-6" id="frequency-field">
                                <label class="form-label" for="donation-frequency">Fréquence *</label>
                                <select id="donation-frequency" name="frequency" class="form-select">
                                    <option value="">Choisir une fréquence</option>
                                    <option value="weekly" <?= $values['frequency'] === 'weekly' ? 'selected' : '' ?>>Chaque semaine</option>
                                    <option value="monthly" <?= $values['frequency'] === 'monthly' ? 'selected' : '' ?>>Chaque mois</option>
                                    <option value="yearly" <?= $values['frequency'] === 'yearly' ? 'selected' : '' ?>>Chaque année</option>
                                </select>
                            </div>
                            </div>
                            <div class="step-actions"><span></span><button type="button" class="step-button next-step">Continuer <i class="bi bi-arrow-right ms-2"></i></button></div>
                        </div>
                        <div class="donation-step" data-step="2" hidden>
                            <h3 class="step-heading"><span>2</span>Vos coordonnées et votre paiement</h3>
                            <div class="row g-3">
                            <div class="col-md-6"><label class="form-label" for="donor-name">Nom complet *</label><input id="donor-name" name="name" class="form-control" value="<?= e($values['name']) ?>" required></div>
                            <div class="col-md-6"><label class="form-label" for="donor-email">Adresse e-mail *</label><input id="donor-email" type="email" name="email" class="form-control" value="<?= e($values['email']) ?>" required></div>
                            <div class="col-md-6"><label class="form-label" for="donor-phone">Téléphone</label><input id="donor-phone" name="phone" class="form-control" value="<?= e($values['phone']) ?>"></div>
                            <div class="col-md-6"><label class="form-label" for="donor-country">Pays</label><input id="donor-country" name="country" class="form-control" value="<?= e($values['country']) ?>"></div>
                            <div class="col-md-5"><label class="form-label" for="donation-amount">Montant *</label><div class="d-flex flex-wrap gap-2 mb-2"><button type="button" class="btn btn-sm btn-outline-secondary amount-option" data-amount="25">25</button><button type="button" class="btn btn-sm btn-outline-secondary amount-option" data-amount="50">50</button><button type="button" class="btn btn-sm btn-outline-secondary amount-option" data-amount="100">100</button><button type="button" class="btn btn-sm btn-outline-secondary amount-option" data-amount="250">250</button></div><input id="donation-amount" type="number" min="0.01" step="0.01" name="amount" class="form-control" value="<?= e($values['amount']) ?>" required></div>
                            <div class="col-md-3"><label class="form-label" for="donation-currency">Devise *</label><select id="donation-currency" name="currency" class="form-select"><?php foreach (['USD', 'CDF', 'EUR'] as $currency): ?><option value="<?= $currency ?>" <?= $values['currency'] === $currency ? 'selected' : '' ?>><?= $currency ?></option><?php endforeach; ?></select></div>
                            <div class="col-12">
                                <div class="payment-heading">Comment souhaitez-vous effectuer votre paiement ? *</div>
                                <div class="row g-2" role="radiogroup" aria-label="Mode de paiement">
                                    <?php foreach (['M-Pesa', 'Airtel Money', 'Orange Money', 'PayPal', 'Stripe / carte bancaire', 'Virement bancaire'] as $method): ?>
                                        <div class="col-md-6 col-lg-4"><label class="payment-choice tw:flex"><input type="radio" name="payment_method" value="<?= e($method) ?>" <?= $values['payment_method'] === $method ? 'checked' : '' ?> required><span><?= e($method) ?></span></label></div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="form-text">Sélectionnez un seul moyen de paiement pour continuer.</div>
                            </div>
                            <div class="col-12 payment-accounts">
                                <strong><i class="bi bi-credit-card me-2"></i>Comptes de paiement en ligne</strong>
                                <div class="row mt-2">
                                    <?php foreach ($paymentAccounts['mobile_money'] as $provider => $account): ?><div class="col-md-4"><small><b><?= e($provider) ?></b><br><?= e($account) ?></small></div><?php endforeach; ?>
                                    <div class="col-md-6 mt-2"><small><b>PayPal</b><br><?= e($paymentAccounts['paypal']) ?></small></div>
                                    <div class="col-md-6 mt-2"><small><b>Carte bancaire / Stripe</b><br><?= e($paymentAccounts['stripe']) ?></small></div>
                                </div>
                                <small class="d-block text-muted mt-2">Les coordonnées définitives seront affichées dès leur configuration.</small>
                            </div>
                            </div>
                            <div class="step-actions"><button type="button" class="step-button secondary previous-step"><i class="bi bi-arrow-left me-2"></i>Retour</button><button type="button" class="step-button next-step">Continuer <i class="bi bi-arrow-right ms-2"></i></button></div>
                        </div>
                        <div class="donation-step" data-step="3" hidden>
                            <h3 class="step-heading"><span>3</span>Vérifiez et confirmez votre demande</h3>
                            <div class="row g-3">
                            <div class="col-12"><label class="form-label" for="donor-message">Message ou précision</label><textarea id="donor-message" name="message" class="form-control" rows="4" placeholder="Indiquez, par exemple, l’initiative que vous souhaitez soutenir."><?= e($values['message']) ?></textarea></div>
                            <div class="col-12"><div class="form-check"><input id="anonymous" type="checkbox" name="anonymous" value="1" class="form-check-input" <?= $values['anonymous'] === '1' ? 'checked' : '' ?>><label class="form-check-label" for="anonymous">Je souhaite que mon don reste anonyme.</label></div></div>
                            <div class="col-12"><div class="form-check"><input id="consent" type="checkbox" name="consent" value="1" class="form-check-input" required <?= $values['consent'] === '1' ? 'checked' : '' ?>><label class="form-check-label" for="consent">J’accepte que mes coordonnées soient utilisées uniquement pour traiter ma demande de don. *</label></div></div>
                            </div>
                            <div class="step-actions"><button type="button" class="step-button secondary previous-step"><i class="bi bi-arrow-left me-2"></i>Retour</button>
                                <button class="donation-submit" type="submit"><i class="bi bi-heart me-2"></i>Confirmer ma demande de don</button>
                            </div>
                        </div>
                    </form>
                </section>
            </div>
            <div class="col-lg-5">
                <aside class="donation-info">
                    <div class="donation-summary text-dark mb-4">
                        <div class="small text-uppercase text-muted">Votre contribution</div>
                        <div class="summary-amount"><span id="summary-amount">0.00</span> <span id="summary-currency">USD</span></div>
                        <div id="summary-frequency" class="small text-muted">Don unique</div>
                    </div>
                    <h2>Votre générosité compte</h2>
                    <p>Chaque contribution aide nos équipes et nos partenaires à transformer durablement les communautés.</p>
                    <ul>
                        <li><i class="bi bi-shield-check"></i>Traitement confidentiel de vos informations</li>
                        <li><i class="bi bi-chat-heart"></i>Un accompagnement personnalisé</li>
                        <li><i class="bi bi-bar-chart-line"></i>Un impact concret et suivi</li>
                    </ul>
                    <?php if ($campaign): ?><div class="mt-4 pt-3 border-top border-light"><small>Campagne sélectionnée</small><strong class="d-block mt-1"><?= e($campaign) ?></strong></div><?php endif; ?>
                </aside>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script>
const frequencyField = document.getElementById('frequency-field');
const frequencySelect = document.getElementById('donation-frequency');
function updateDonationType() {
    const recurring = document.querySelector('input[name="donation_type"]:checked')?.value === 'recurring';
    frequencyField.style.display = recurring ? '' : 'none';
    frequencySelect.required = recurring;
    if (!recurring) frequencySelect.value = '';
}
document.querySelectorAll('input[name="donation_type"]').forEach(function (input) {
    input.addEventListener('change', updateDonationType);
});
updateDonationType();
const form = document.querySelector('form');
const steps = Array.from(document.querySelectorAll('.donation-step'));
const progressSteps = Array.from(document.querySelectorAll('.donation-progress span'));
let currentStep = 0;

function showStep(index) {
    currentStep = index;
    steps.forEach(function (step, stepIndex) {
        step.hidden = stepIndex !== currentStep;
    });
    progressSteps.forEach(function (step, stepIndex) {
        step.classList.toggle('active', stepIndex <= currentStep);
    });
    steps[currentStep].scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function validateCurrentStep() {
    const fields = steps[currentStep].querySelectorAll('input, select, textarea');
    for (const field of fields) {
        if (!field.checkValidity()) {
            field.reportValidity();
            return false;
        }
    }
    return true;
}

document.querySelectorAll('.next-step').forEach(function (button) {
    button.addEventListener('click', function () {
        if (validateCurrentStep() && currentStep < steps.length - 1) {
            showStep(currentStep + 1);
        }
    });
});
document.querySelectorAll('.previous-step').forEach(function (button) {
    button.addEventListener('click', function () {
        if (currentStep > 0) showStep(currentStep - 1);
    });
});
showStep(0);
document.querySelectorAll('.amount-option').forEach(function (button) {
    button.addEventListener('click', function () {
        document.getElementById('donation-amount').value = button.dataset.amount;
        updateSummary();
    });
});
const amountInput = document.getElementById('donation-amount');
const currencyInput = document.getElementById('donation-currency');
function updateSummary() {
    document.getElementById('summary-amount').textContent = amountInput.value || '0.00';
    document.getElementById('summary-currency').textContent = currencyInput.value;
    const recurring = document.querySelector('input[name="donation_type"]:checked')?.value === 'recurring';
    const frequency = document.getElementById('donation-frequency').selectedOptions[0]?.textContent || '';
    document.getElementById('summary-frequency').textContent = recurring && frequency ? frequency : 'Don unique';
}
amountInput.addEventListener('input', updateSummary);
currencyInput.addEventListener('change', updateSummary);
document.getElementById('donation-frequency').addEventListener('change', updateSummary);
document.querySelectorAll('input[name="donation_type"]').forEach(function (input) {
    input.addEventListener('change', updateSummary);
});
updateSummary();
</script>
</body>
</html>
